<?php

namespace App\Http\Controllers;

use Request;
//use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Session;
use View;
use Mail;
use Input;
use Exception;
use App\Activity;
use App\Assignment;
use App\File;
use App\User;
use App\Contact;
use App\Site;
use App\Branch;
use App\Email;
use App\Event;
use App\Project;
use App\DeadInterval;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use PHPExcel_Worksheet_Drawing;
use App\Http\Traits\FilesTrait;
use App\Http\Traits\ProjectTrait;

class AssignmentController extends Controller
{
    use FilesTrait;
    use ProjectTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id)) {
            return View('app.index', ['service' => 'project', 'user' => null]);
        }
        if($user->acc_project==0)
            return redirect()->action('LoginController@logout', ['service' => 'project']);

        $service = Session::get('service');

        $prj = Input::get('prj');
        $mode = Input::get('mode');

        $assignments = Assignment::where('id','>',0);

        $last_stat = count(Assignment::$status_names) - 1; //$assignments->first()->last_stat(); //Retrieve the last stat existent

        if(!is_null($prj)&&$prj!=0)
            $assignments = $assignments->where('project_id', $prj);

        if($user->priv_level>=2||$user->role=='Director regional'||($user->area=='Gerencia Tecnica'&&$user->priv_level==1)){

            if($mode=='rb')
                $assignments = $assignments->where('type','Radiobases');
            elseif($mode=='fo')
                $assignments = $assignments->where('type','Fibra óptica');

            //$assignments = $assignments->whereNotIn('status', ['Concluído','No asignado']);
        }
        else{
            $assignments = $assignments->where('type',$user->work_type)->whereNotIn('status', [0,$last_stat]);
        }

        $assignments = $assignments->orderBy('id', 'desc')->paginate(20);

        /* old code for retrieving files related to each assignment
        $files = File::join('assignments', 'files.imageable_id', '=', 'assignments.id')
            ->select('files.id', 'files.name', 'files.imageable_id', 'files.created_at')
            ->where('imageable_type', '=', 'App\Assignment')
            ->get();
        */
        foreach($assignments as $assignment)
        {
            /*
            foreach($assignment->sites as $site){
                foreach ($site->orders as $order) {
                    $site->assigned_price += $order->pivot->assigned_amount;
                }
                $assignment->assigned_price += $site->assigned_price;
            }
            */

            $assignment->quote_from = Carbon::parse($assignment->quote_from);
            $assignment->quote_to = Carbon::parse($assignment->quote_to);
            $assignment->start_line = Carbon::parse($assignment->start_line);
            $assignment->deadline = Carbon::parse($assignment->deadline);
            $assignment->start_date = Carbon::parse($assignment->start_date);
            $assignment->end_date = Carbon::parse($assignment->end_date);
            $assignment->billing_from = Carbon::parse($assignment->billing_from);
            $assignment->billing_to = Carbon::parse($assignment->billing_to);

            /* separated to another function
            if($assignment->status!='Concluído'&&$assignment->status!='No asignado'){
                $site_percentage = 0;
                $total_quoted = 0;
                $total_executed = 0;
                $total_charged = 0;
                $count = 0;
                foreach($assignment->sites as $site){
                    $site_percentage = $site_percentage + $site->percentage_completed;
                    $total_quoted = $total_quoted + $site->quote_price;
                    $total_executed = $total_executed + $site->executed_price;
                    $total_charged = $total_charged + $site->charged_price;
                    $count++;
                }
                if($count==0)
                    $count=1;
                $assignment->percentage_completed = $site_percentage/$count;

                //if($assignment->quote_price==0)
                    $assignment->quote_price = $total_quoted;
                //if($assignment->assigned_price==0)
                    //$assignment->assigned_price = $total_assigned;

                $assignment->executed_price = $total_executed;
                $assignment->charged_price = $total_charged;
                $assignment->save();
            }
            */
            foreach($assignment->guarantees as $guarantee){
                $guarantee->expiration_date = Carbon::parse($guarantee->expiration_date)->hour(0)->minute(0)->second(0);
                $guarantee->start_date = Carbon::parse($guarantee->start_date)->hour(0)->minute(0)->second(0);
            }

            foreach($assignment->files as $file)
            {
                $file->created_at = Carbon::parse($file->created_at)->hour(0)->minute(0)->second(0);
            }
            
            /* Add general progress values for key items */
            $this->get_key_item_values($assignment);
        }
        
        $current_date = Carbon::now();
        $current_date->hour = 0;
        $current_date->minute = 0;
        $current_date->second = 0;
        
        /*
        $main_tasks = array();

        foreach($assignments as $assignment){
            if($assignment->type=='Fibra óptica'){
                $general_progress = array('assignment_id' => $assignment->id, 'cable_projected' => 0,
                    'cable_executed' => 0, 'splice_projected' => 0, 'splice_executed' => 0);

                foreach($assignment->sites as $site){
                    foreach($site->tasks as $task){
                        if (stripos($task->name, 'tendido')!==FALSE&&stripos($task->name, 'relevamiento')===FALSE){
                            $general_progress['cable_projected'] += $task->total_expected;
                            $general_progress['cable_executed'] += $task->progress;
                        }
                        elseif(stripos($task->name, 'empalme')!==FALSE){
                            $general_progress['splice_projected'] += $task->total_expected;
                            $general_progress['splice_executed'] += $task->progress;
                        }
                    }
                }

                $main_tasks[] = $general_progress;
            }
        }

        return $main_tasks;
        */
        
        return View::make('app.assignment_brief', ['assignments' => $assignments, 'service' => $service,
            'current_date' => $current_date, 'user' => $user]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $clients = Assignment::select('client')->where('client', '<>', '')->groupBy('client')->get();
        $types_award = Assignment::select('type_award')->where('type_award','<>','Licitación')
            ->where('type_award', '<>', '')->groupBy('type_award')->get();
        $projects = Project::select('id','name')->where('valid_to','>=',Carbon::now()->subYears(2))
            ->where('status','<>','No asignado')->get();

        $branches = Branch::select('id', 'name', 'city')->where('name','<>','')->where('active', 1)->orderBy('name')->get();

        $current_date = Carbon::now()->format('Y-m-d');

        $last_stat = count(Assignment::$status_names) - 1;
        //$last_stat = Assignment::first()->last_stat();
        
        return View::make('app.assignment_form', ['assignment' => 0, 'clients' => $clients, 'service' => $service,
            'action_flag' => 0, 'projects' => $projects, 'types_award' => $types_award, 'current_date' => $current_date,
            'branches' => $branches, 'last_stat' => $last_stat, 'user' => $user]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $form_data = Request::all();

        if($form_data['quote_to']!='')
            $form_data['quote_to'] = $form_data['quote_to'].' 23:59:59';
        if($form_data['end_date']!='')
            $form_data['end_date'] = $form_data['end_date'].' 23:59:59';

        $assignment = new Assignment($form_data);

        $rel_number = $assignment->status_number('Relevamiento');
        $exec_number = $assignment->status_number('Ejecución');

        $v = \Validator::make($form_data, [
            'name'                  => 'required',
            'literal_code'          => 'required',
            'client_code'           => 'required',
            'project_id'            => 'required',//|exists:projects,id',
            'type'                  => 'required_if:project_id,0',
            'sub_type'              => 'required',
            'client'                => 'required_if:project_id,0',
            'other_client'          => 'required_if:client,Otro',
            'type_award'            => 'required',
            'other_type_award'      => 'required_if:type_award,Otro',
            'status'                => 'required',
            'branch_id'             => 'required',
            //'branch'                => 'required',
            'resp_name'             => 'regex:/^[\pL\s\-]+$/u',
            'contact_name'          => 'required|regex:/^[\pL\s\-]+$/u',
            'quote_from'            => 'required_if:status,'.$rel_number.'|date',
            'quote_to'              => /*'required_if:status,'.$rel_number.'|*/'date|after:quote_from',
            'start_date'            => 'required_if:status,'.$exec_number.'|date',
            'end_date'              => /*'required_if:status,'.$exec_number.'|*/'date|after:start_date',
            'file'                  => 'required_unless:type_award,Licitación|mimes:pdf',
        ],
            [
                'name.required'          => 'Debe especificar el nombre del proyecto!',
                'literal_code.required'  => 'Debe especificar el código abreviado de asignación!',
                'client_code.required'   => 'Debe especificar el código de asignación según el cliente!',
                'project_id.required'    => 'Debe seleccionar una opción en el campo Proyecto!',
                //'project_id.exists'      => 'Ocurrió un error al recuperar la información de proyecto,
                'type.required_if'       => 'Debe especificar el área de trabajo!',
                'sub_type.required'      => 'Debe especificar el tipo de trabajo!',
                'client.required_if'     => 'Debe especificar un cliente!',
                'other_client.required_if'   => 'Debe especificar un cliente!',
                'type_award.required'    => 'Debe especificar el tipo de adjudicación!',
                'other_type_award.required_if' => 'Debe especificar el tipo de adjudicación!',
                'status.required'        => 'Debe especificar el estado de la asignación!',
                'branch_id.required'     => 'Debe indicar la oficina que será responsable de ejecutar el trabajo!',
                'resp_name.regex'        => 'El nombre del responsable de ABROS solo puede contener letras',
                'contact_name.required'  => 'Debe especificar el nombre del responsable por parte del cliente',
                'contact_name.regex'     => 'El nombre del responsable del cliente solo puede contener letras',
                'quote_from.required_if' => 'Debe especificar la fecha de inicio de cotización!',
                'quote_from.date'        => 'El formato de la fecha de inicio de cotización es incorrecto!',
                //'quote_to.required_if'   => 'Debe especificar la fecha de fin de cotización!',
                'quote_to.date'          => 'El formato de la fecha de fin de cotización es incorrecto!',
                'quote_to.after'         => 'La fecha de fin de cotización no puede ser anterior a la fecha de inicio!',
                'start_date.required_if' => 'Debe especificar la fecha prevista de inicio de ejecución!',
                'start_date.date'        => 'El formato de la fecha de inicio de ejecución es incorrecto!',
                //'end_date.required_if'   => 'Debe especificar la fecha prevista de fin de ejecución!',
                'end_date.date'          => 'El formato de la fecha de fin de ejecución es incorrecto!',
                'end_date.after'         => 'La fecha de fin de ejecución no puede ser anterior a la fecha de inicio!',
                'file.required_unless'   => 'Debe cargar el documento de asignación del proyecto!',
                'file.mimes'             => 'El documento de asignación debe estar en formato PDF!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        if($assignment->project_id!=0){
            $project = Project::find($assignment->project_id);

            if($project){
                $assignment->client = $project->client;
                $assignment->type = $project->type;
            }
            else{
                Session::flash('message', 'Ocurrió un error al recuperar la información de proyecto,
                                                intente de nuevo por favor.');
                return redirect()->back()->withInput();
            }
        }

        $assignment->client = $assignment->client=='Otro' ? Request::input('other_client') : $assignment->client;
        $assignment->type_award = $assignment->type_award=='Otro' ? Request::input('other_type_award') :
            $assignment->type_award;

        //$assignment->status = empty(Request::input('status')) ? 'Relevamiento' : $assignment->status;

        if($assignment->status==$rel_number){
            $assignment->quote_from = empty($assignment->quote_from) ? Carbon::now() : $assignment->quote_from;
            $assignment->quote_to = empty(Request::input('quote_days')) ?
                (empty($assignment->quote_to) ? Carbon::now()->addDays(7) :
                    $assignment->quote_to) : Carbon::parse($assignment->quote_from)->addDays(Request::input('quote_days'));
        }
        elseif($assignment->status==$exec_number){
            $assignment->start_line = empty($assignment->start_line) ? Carbon::now() : $assignment->start_line;
            $assignment->deadline = empty(Request::input('exec_days_assigned')) ?
                (empty($assignment->deadline) ? Carbon::now()->addDays(20) :
                    $assignment->deadline) : Carbon::parse($assignment->start_line)->addDays(Request::input('exec_days_assigned'));

            $assignment->start_date = empty($assignment->start_date) ? Carbon::now() : $assignment->start_date;
            $assignment->end_date = empty(Request::input('exec_days')) ?
                (empty($assignment->end_date) ? Carbon::now()->addDays(20) :
                    $assignment->end_date) : Carbon::parse($assignment->start_date)->addDays(Request::input('exec_days'));
        }

        if(!empty($assignment->billing_from)&&!empty(Request::input('billing_days'))){
            $assignment->billing_from = Carbon::parse($assignment->billing_from);
            $assignment->billing_to = $assignment->billing_from->addDays(Request::input('billing_days'));
        }
        
        $assignment->user_id = $user->id;
        $responsible = User::select('id')->where('name',Request::input('resp_name'))->first();
        $contact = Contact::select('id')->where('name',Request::input('contact_name'))->first();

        $assignment->resp_id = $responsible=='' ? 0 : $responsible->id;

        if($contact==''){
            $contact = new Contact;
            $contact->name = Request::input('contact_name');
            $contact->company = $assignment->client;
            $contact->save();
            $assignment->contact_id = $contact->id;
        }
        else
            $assignment->contact_id = $contact->id;

        $assignment->save();

        $this->fill_code_column();

        /* A new event is recorded to register the creation of the assignment */
        $this->add_event('assignment created',$assignment);
        
        /* The assignation document is stored */
        if($assignment->type_award!='Licitación'){
            $newFile = Request::file('file');
            $filename_hint = 'asig';

            $name = 'ASG_'.$assignment->id.'_'.$filename_hint;

            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path() . '/files/';
            $FileName = $name.'.'.$FileType;

            $upload = \Storage::disk('local')->put($FileName, \File::get($newFile));

            if ($upload) {
                $file = new File;
                $file->name = $FileName;
                $file->path = $FilePath;
                $file->type = $FileType;
                $file->size = $FileSize;

                $file->user_id = $user->id;
                $file->description = 'Documento de asignación';

                $file->imageable()->associate($assignment /*Assignment::find($assignment->id)*/);
                $file->save();
            }
        }

        $pm_assigned = $assignment->resp_id!=0 ? User::find($assignment->resp_id) : 0;

        /* Send a notification email to the head of the Technical department */
        $this->send_mail_notification($assignment, 'store', $pm_assigned, $user);

        if(!empty($pm_assigned)){
            /* send mail to new pm */
            $this->send_mail_notification($assignment, 'pm', $pm_assigned, $user);
            //$this->send_notification_to_pm($assignment);
        }
        
        Session::flash('message', "La asignación fue registrada en el sistema correctamente");
        return redirect()->route('assignment.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $assignment = Assignment::find($id);

        $last_stat = $assignment->last_stat();

        /*
        foreach($assignment->sites as $site){
            foreach ($site->orders as $order) {
                $site->assigned_price += $order->pivot->assigned_amount;
            }
            $assignment->assigned_price += $site->assigned_price;
        }
        */
        
        return View::make('app.assignment_info', ['assignment' => $assignment, 'last_stat' => $last_stat,
            'service' => $service, 'user' => $user]);
    }
    
    /*
    public function show_financial_details($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $assignment = Assignment::find($id);

        return View::make('app.assignment_financial_details', ['assignment' => $assignment, 'service' => $service,
            'user' => $user]);
    }
    */
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $assignment = Assignment::find($id);

        $assignment->quote_from = Carbon::parse($assignment->quote_from)->format('Y-m-d');
        $assignment->quote_to = Carbon::parse($assignment->quote_to)->format('Y-m-d');
        $assignment->start_line = Carbon::parse($assignment->start_line)->format('Y-m-d');
        $assignment->deadline = Carbon::parse($assignment->deadline)->format('Y-m-d');
        $assignment->start_date = Carbon::parse($assignment->start_date)->format('Y-m-d');
        $assignment->end_date = Carbon::parse($assignment->end_date)->format('Y-m-d');
        $assignment->billing_from = Carbon::parse($assignment->billing_from)->format('Y-m-d');
        $assignment->billing_to = Carbon::parse($assignment->billing_to)->format('Y-m-d');
            //$assignment->billing_to->year<1 ? null : $assignment->billing_to->format('Y-m-d');

        $clients = Assignment::select('client')->where('client', '<>', '')->groupBy('client')->get();
        $types_award = Assignment::select('type_award')->where('type_award','<>','Licitación')
            ->where('type_award', '<>', '')->groupBy('type_award')->get();

        $projects = Project::select('id','name')->where('valid_to','>=',Carbon::now()->subYears(2))
            ->where('status','<>','No asignado')->get();

        $branches = Branch::select('id', 'name', 'city')->where('name','<>','')->where('active', 1)->orderBy('name')->get();

        $current_date = Carbon::now()->format('Y-m-d');

        $last_stat = $assignment->last_stat();

        return View::make('app.assignment_form', ['assignment' => $assignment, 'clients' => $clients, 'service' => $service,
            'action_flag' => 0, 'projects' => $projects, 'types_award' => $types_award, 'current_date' => $current_date,
            'branches' => $branches, 'last_stat' => $last_stat, 'user' => $user]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $form_data = Request::all();

        if($form_data['quote_to']!='')
            $form_data['quote_to'] = $form_data['quote_to'].' 23:59:59';
        if($form_data['end_date']!='')
            $form_data['end_date'] = $form_data['end_date'].' 23:59:59';

        $assignment = Assignment::find($id);
        $old_info = Assignment::find($id);

        $old_pm = $assignment->resp_id;
        //$action_flag = Request::input('action_flag');
        $rel_number = $assignment->status_number('Relevamiento');
        $exec_number = $assignment->status_number('Ejecución');

        $v = \Validator::make($form_data, [
            'name'                  => 'required',
            'literal_code'          => 'required',
            'client_code'           => 'required',
            'project_id'            => 'required',//|exists:projects,id',
            'type'                  => 'required_if:project_id,0',
            'sub_type'              => 'required',
            'client'                => 'required_if:project_id,0',
            'other_client'          => 'required_if:client,Otro',
            'type_award'            => 'required',
            'other_type_award'      => 'required_if:type_award,Otro',
            'status'                => 'required',
            'branch_id'             => 'required',
            'resp_name'             => 'regex:/^[\pL\s\-]+$/u',
            'contact_name'          => 'required|regex:/^[\pL\s\-]+$/u',
            'quote_from'            => 'required_if:status,'.$rel_number.'|date',
            'quote_to'              => /*'required_if:status,'.$rel_number.'|*/'date|after:quote_from',
            'start_date'            => 'required_if:status,'.$exec_number.'|date',
            'end_date'              => /*'required_if:status,'.$exec_number.'|*/'date|after:start_date',
        ],
            [
                'name.required'          => 'Debe especificar el nombre del proyecto!',
                'literal_code.required'  => 'Debe especificar el código abreviado de asignación!',
                'client_code.required'   => 'Debe especificar el código de asignación según el cliente!',
                'project_id.required'    => 'Debe seleccionar una opción en el campo Proyecto!',
                //'project_id.exists'      => 'Ocurrió un error al recuperar la información de proyecto,
                'type.required_if'       => 'Debe especificar el área de trabajo!',
                'sub_type.required'      => 'Debe especificar el tipo de trabajo!',
                'client.required_if'     => 'Debe especificar un cliente!',
                'other_client.required_if'   => 'Debe especificar un cliente!',
                'type_award.required'    => 'Debe especificar el tipo de adjudicación!',
                'other_type_award.required_if' => 'Debe especificar el tipo de adjudicación!',
                'status.required'        => 'Debe especificar el estado de la asignación!',
                'branch_id.required'     => 'Debe indicar la oficina que será responsable de ejecutar el trabajo!',
                'resp_name.regex'        => 'El nombre del responsable de ABROS solo puede contener letras',
                'contact_name.required'  => 'Debe especificar el nombre del responsable por parte del cliente',
                'contact_name.regex'     => 'El nombre del responsable del cliente solo puede contener letras',
                'quote_from.required_if' => 'Debe especificar la fecha de inicio de cotización!',
                'quote_from.date'        => 'El formato de la fecha de inicio de cotización es incorrecto!',
                //'quote_to.required_if'   => 'Debe especificar la fecha de fin de cotización!',
                'quote_to.date'          => 'El formato de la fecha de fin de cotización es incorrecto!',
                'quote_to.after'         => 'La fecha de fin de cotización no puede ser anterior a la fecha de inicio!',
                'start_date.required_if' => 'Debe especificar la fecha prevista de inicio de ejecución!',
                'start_date.date'        => 'El formato de la fecha de inicio de ejecución es incorrecto!',
                //'end_date.required_if'   => 'Debe especificar la fecha prevista de fin de ejecución!',
                'end_date.date'          => 'El formato de la fecha de fin de ejecución es incorrecto!',
                'end_date.after'         => 'La fecha de fin de ejecución no puede ser anterior a la fecha de inicio!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $assignment->fill($form_data);

        if($assignment->project_id!=0){
            $project = Project::find($assignment->project_id);

            if($project){
                $assignment->client = $project->client;
                $assignment->type = $project->type;
            }
            else{
                Session::flash('message', 'Ocurrió un error al recuperar la información de proyecto,
                                                intente de nuevo por favor.');
                return redirect()->back()->withInput();
            }
        }
        
        $assignment->client = $assignment->client=='Otro' ? Request::input('other_client') : $assignment->client;
        $assignment->type_award = $assignment->type_award=='Otro' ? Request::input('other_type_award') :
            $assignment->type_award;

        if($assignment->status==$rel_number/*'Relevamiento'*/){
            $assignment->quote_from = empty($assignment->quote_from) ? Carbon::now() : $assignment->quote_from;
            $assignment->quote_to = empty(Request::input('quote_days')) ?
                (empty($assignment->quote_to) ? Carbon::now()->addDays(7) : $assignment->quote_to) :
                Carbon::parse($assignment->quote_from)->addDays(Request::input('quote_days'));
        }
        elseif($assignment->status==$exec_number/*'Ejecución'*/){
            $assignment->start_line = empty($assignment->start_line) ? Carbon::now() : $assignment->start_line;
            $assignment->deadline = empty(Request::input('exec_days_assigned')) ?
                (empty($assignment->deadline) ? Carbon::now()->addDays(20) : $assignment->deadline) :
                Carbon::parse($assignment->start_line)->addDays(Request::input('exec_days_assigned'));

            $assignment->start_date = empty($assignment->start_date) ? Carbon::now() : $assignment->start_date;
            $assignment->end_date = empty(Request::input('exec_days')) ?
                (empty($assignment->end_date) ? Carbon::now()->addDays(20) : $assignment->end_date) :
                Carbon::parse($assignment->start_date)->addDays(Request::input('exec_days'));
        }

        if(!empty($assignment->billing_from)&&!empty(Request::input('billing_days'))){
            $assignment->billing_from = Carbon::parse($assignment->billing_from);
            $assignment->billing_to = $assignment->billing_from->addDays(Request::input('billing_days'));
        }

        $responsible = User::select('id')->where('name',Request::input('resp_name'))->first();
        $contact = Contact::select('id')->where('name',Request::input('contact_name'))->first();

        $assignment->resp_id = $responsible=='' ? 0 : $responsible->id;

        if($contact==''){
            $contact = new Contact;
            $contact->name = Request::input('contact_name');
            $contact->company = $assignment->client;
            $contact->save();
            $assignment->contact_id = $contact->id;
        }
        else
            $assignment->contact_id = $contact->id;

        $assignment->save();

        foreach($assignment->sites as $site){
            $this->new_stat_site($site, $assignment->status); //Set the status of the child sites
        }

        if($assignment->status==$assignment->last_stat()/*'Concluído'*/||$assignment->status==0/*'No asignado'*/){
            /*
            foreach($assignment->sites as $site){
                if($site->status!='Concluído'&&$site->status!='No asignado'){
                    $site->status = $assignment->status;
                    $site->save();

                    foreach($site->tasks as $task){
                        if($task->status!='Concluído'&&$task->status!='No asignado'){
                            $task->status = $assignment->status;
                            $task->save();

                            foreach($task->activities as $activity){
                                foreach($activity->files as $file){
                                    $this->blockFile($file);
                                }
                            }
                        }
                    }
                    
                    foreach($site->files as $file){
                        $this->blockFile($file);
                    }
                }
            }
            */
            
            foreach($assignment->files as $file){
                $this->blockFile($file);
            }
        }

        /* If a date interval changes */
        if($assignment->start_line!=$old_info->start_line||$assignment->deadline!=$old_info->deadline||
            $assignment->start_date!=$old_info->start_date||$assignment->end_date!=$old_info->end_date||
            $assignment->quote_from!=$old_info->quote_from||$assignment->quote_to!=$old_info->quote_to){

            $this->update_dates($assignment);
        }

        /* If Project Manager changes */
        if($assignment->resp_id!=0&&$assignment->resp_id!=$old_pm){
            /* A new event is recorded to register the change of PM */
            $this->add_event('new pm',$assignment);

            /* send mail to new pm */
            $this->send_mail_notification($assignment, 'pm', 0, $user);
            //$this->send_notification_to_pm($assignment);
        }

        Session::flash('message', "Datos actualizados correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('assignment.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $assignment = Assignment::find($id);
        $message = '';
        $erasable = true;

        if(!$assignment){
            $message = 'No se encontró el registro solicitado!';
            $erasable = false;
        }

        if($assignment->status==$assignment->last_stat()/*'Concluído'*/){
            $message = 'Esta asignación no puede ser borrada por que ya ha sido marcada como "Concluída"!';
            $erasable = false;
        }

        if($assignment->sites->count()>0){
            $message = 'Esta asignación no puede ser borrada porque tiene asociados uno o mas sitios!';
            $erasable = false;
        }

        if($assignment->stipend_requests->count()>0){
            $message = 'Esta asignación no puede ser borrada porque tiene asociadas una o mas solicitudes de viáticos!';
            $erasable = false;
        }

        if($assignment->guarantees->count()>0){
            $message = 'Esta asignación no puede ser borrada porque tiene asociadas una o mas polizas de garantía!';
            $erasable = false;
        }

        if(!$erasable){
            Session::flash('message', $message);
            return redirect()->back();
        }

        $error = false;

        if(!$error){
            foreach($assignment->events as $event){

                foreach($event->files as $file){
                    $error = $this->removeFile($file);
                    if($error)
                        break;
                }

                if($error)
                    break;

                $event->delete();
            }
        }

        if(!$error){
            foreach($assignment->dead_intervals as $dead_interval){

                foreach($dead_interval->files as $file){
                    $error = $this->removeFile($file);
                    if($error)
                        break;
                }

                if($error)
                    break;

                $dead_interval->delete();
            }
        }

        if(!$error){
            foreach($assignment->files as $file){
                $error = $this->removeFile($file);
                if($error)
                    break;
            }
        }

        if (!$error) {
            $assignment->delete();

            Session::flash('message', "El registro fue eliminado del sistema");
            if(Session::has('url'))
                return redirect(Session::get('url'));
            else
                return redirect()->route('assignment.index');
        }
        else {
            Session::flash('message', "Error al borrar el registro, por favor consulte al administrador. $error");
            return redirect()->back();
        }
    }
    
    public function modify_status($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $match = false; //Boolean to determine if a matching condition is applied

        $assignment = Assignment::find($id);
        $action = Input::get('action');
        $message = '';

        if($action=='upgrade'){
            if($assignment->status<$assignment->last_stat()){
                
                $assignment->status += 1;

                if($assignment->statuses($assignment->status)=='Ejecución'){
                    $assignment->start_date = Carbon::now();
                    $assignment->end_date = Carbon::now()->addDays(20);
                }
                elseif($assignment->statuses($assignment->status)=='Cobro'){
                    $assignment->billing_from = Carbon::now();
                }
                elseif($assignment->statuses($assignment->status)=='Concluído'){
                    $assignment->billing_to = Carbon::now();

                    foreach($assignment->files as $file){
                        $this->blockFile($file);
                    }
                }
                
                $assignment->save();

                foreach($assignment->sites as $site){
                    $this->new_stat_site($site, $assignment->status); //Set the status of the child sites
                }

                $match = true;
                $message = "El estado de la asignación ha cambiado a ".$assignment->statuses($assignment->status);
            }
            
            /*
            if($assignment->status=='Relevamiento'){
                $assignment->status = 'Cotizado';
                $assignment->save();

                foreach($assignment->sites as $site){
                    $this->new_stat_site($site, 'Cotizado'); //Set the status of the child sites
                }

                /*
                $dead_interval = new DeadInterval;
                $dead_interval->user_id = $user->id;
                $dead_interval->date_from = Carbon::now();
                $dead_interval->reason = 'Período de espera tras envío de cotización';
                $dead_interval->relatable()->associate(Assignment::find($id));
                $dead_interval->save();
                
            }
            elseif($assignment->status=='Cotizado'){
                $assignment->status = 'Ejecución';
                $assignment->start_date = Carbon::now();
                $assignment->end_date = Carbon::now()->addDays(20);
                $assignment->save();

                foreach($assignment->sites as $site){
                    $this->new_stat_site($site, 'Ejecución'); //Set the status of the child sites
                }

                /*
                $dead_intervals_open = DeadInterval::where('closed',0)->where('relatable_id',$assignment->id)
                    ->where('relatable_type','App\Assignment')->get();

                foreach($dead_intervals_open as $dead_interval){
                    $dead_interval->date_to = Carbon::now();
                    $dead_interval->date_from = Carbon::parse($dead_interval->date_from)->hour(0)->minute(0)->second(0);
                    $dead_interval->total_days = Carbon::now()->hour(0)->minute(0)->second(0)
                        ->diffInDays($dead_interval->date_from);
                    $dead_interval->closed = 1;
                    $dead_interval->save();
                }
                
            }
            elseif($assignment->status=='Ejecución'){
                $assignment->status = 'Revisión';
                $assignment->save();

                foreach($assignment->sites as $site){
                    $this->new_stat_site($site, 'Revisión'); //Set the status of the child sites
                }

                /*
                $dead_interval = new DeadInterval;
                $dead_interval->user_id = $user->id;
                $dead_interval->date_from = Carbon::now();
                $dead_interval->reason = 'Período de espera tras conclusión de trabajos';
                $dead_interval->relatable()->associate(Assignment::find($id));
                $dead_interval->save();
                
            }
            elseif($assignment->status=='Revisión') {
                $assignment->status = 'Cobro';
                $assignment->billing_from = Carbon::now();
                $assignment->save();

                foreach($assignment->sites as $site){
                    $this->new_stat_site($site, 'Cobro'); //Set the status of the child sites
                }

                /*
                $dead_intervals_open = DeadInterval::where('closed',0)->where('relatable_id',$assignment->id)
                    ->where('relatable_type','App\Assignment')->get();

                foreach($dead_intervals_open as $dead_interval){
                    $dead_interval->date_to = Carbon::now();
                    $dead_interval->date_from = Carbon::parse($dead_interval->date_from)->hour(0)->minute(0)->second(0);
                    $dead_interval->total_days = Carbon::now()->hour(0)->minute(0)->second(0)
                        ->diffInDays($dead_interval->date_from);
                    $dead_interval->closed = 1;
                    $dead_interval->save();
                }
                
            }
            elseif($assignment->status=='Cobro'){
                $assignment->status = 'Concluído';
                $assignment->billing_to = Carbon::now();
                $assignment->save();

                foreach($assignment->sites as $site){
                    $this->new_stat_site($site, 'Concluído'); //Set the status of the child sites
                }

                /*
                foreach($assignment->sites as $site){
                    if($site->status!='Concluído'&&$site->status!='No asignado'){
                        $site->status = $assignment->status;
                        $site->save();

                        foreach($site->tasks as $task){
                            if($task->status!='Concluído'&&$task->status!='No asignado'){
                                $task->status = $assignment->status;
                                $task->save();

                                foreach($task->activities as $activity){
                                    foreach($activity->files as $file){
                                        $this->blockFile($file);
                                    }
                                }
                            }
                        }
                        
                        foreach($site->files as $file){
                            $this->blockFile($file);
                        }
                    }
                }
                

                foreach($assignment->files as $file){
                    $this->blockFile($file);
                }
            }

            $match = true;
            $message = "El estado de la asignación ha cambiado a $assignment->status";
            */
        }
        elseif($action=='downgrade'){
            if($assignment->status>1){
                
                $assignment->status -= 1;
                
                if($assignment->statuses($assignment->status)=='Cotización'){
                    $assignment->start_date = '0000-00-00 00:00:00';
                    $assignment->end_date = '0000-00-00 00:00:00';
                }
                elseif($assignment->statuses($assignment->status)=='Certificación (Control de calidad)'){
                    $assignment->billing_from = '0000-00-00 00:00:00';
                }
                elseif($assignment->statuses($assignment->status)=='Cobro'){
                    $assignment->billing_to = '0000-00-00 00:00:00';

                    foreach($assignment->files as $file){
                        $this->unblockFile($file);
                    }
                }
                
                $assignment->save();

                foreach($assignment->sites as $site){
                    $this->new_stat_site($site, $assignment->status); //Set the status of the child sites
                }

                $match = true;
                $message = "El estado de la asignación ha cambiado a ".$assignment->statuses($assignment->status);
            }
            
            /*
            if($assignment->status=='Cotizado'){
                $assignment->status = 'Relevamiento';
                $assignment->save();

                foreach($assignment->sites as $site){
                    $this->new_stat_site($site, 'Relevamiento'); //Set the status of the child sites
                }
            }
            elseif($assignment->status=='Ejecución'){
                $assignment->status = 'Cotizado';
                $assignment->start_date = '0000-00-00 00:00:00';
                $assignment->end_date = '0000-00-00 00:00:00';
                $assignment->save();

                foreach($assignment->sites as $site){
                    $this->new_stat_site($site, 'Cotizado'); //Set the status of the child sites
                }
            }
            elseif($assignment->status=='Revisión'){
                $assignment->status = 'Ejecución';
                $assignment->save();

                foreach($assignment->sites as $site){
                    $this->new_stat_site($site, 'Ejecución'); //Set the status of the child sites
                }
            }
            elseif($assignment->status=='Cobro') {
                $assignment->status = 'Revisión';
                $assignment->billing_from = '0000-00-00 00:00:00';
                $assignment->save();

                foreach($assignment->sites as $site){
                    $this->new_stat_site($site, 'Revisión'); //Set the status of the child sites
                }
            }
            elseif($assignment->status=='Concluído'){
                $assignment->status = 'Cobro';
                $assignment->billing_to = '0000-00-00 00:00:00';
                $assignment->save();

                foreach($assignment->sites as $site){
                    $this->new_stat_site($site, 'Cobro'); //Set the status of the child sites
                }

                foreach($assignment->files as $file){
                    $this->unblockFile($file);
                }
            }

            $match = true;
            $message = "El estado de la asignación ha cambiado a $assignment->status";
            */
        }
        elseif($action=='close'){
            $assignment->status = 0 /*'No asignado'*/;
            $assignment->save();

            foreach($assignment->sites as $site){
                $this->new_stat_site($site, 0 /*'No asignado'*/); //Set the status of the child sites
            }

            /*
            foreach($assignment->sites as $site){
                if($site->status!='Concluído'&&$site->status!='No asignado'){
                    $site->status = $assignment->status;
                    $site->save();

                    foreach($site->tasks as $task){
                        if($task->status!='Concluído'&&$task->status!='No asignado'){
                            $task->status = $assignment->status;
                            $task->save();

                            foreach($task->activities as $activity){
                                foreach($activity->files as $file){
                                    $this->blockFile($file);
                                }
                            }
                        }
                    }
                    
                    foreach($site->files as $file){
                        $this->blockFile($file);
                    }
                }
            }
            */

            foreach($assignment->files as $file){
                $this->blockFile($file);
            }

            $match = true;
            $message = "Este registro ha sido marcado como No asignado";
        }

        if($match){
            $this->add_event('status changed', $assignment); //Record an event for the date the status was changed

            Session::flash('message', $message);
            if(Session::has('url'))
                return redirect(Session::get('url'));
            else
                return redirect()->route('assignment.index');
        }
        else{
            /* default redirection if no match is found */
            return redirect()->back();
        }
    }

    public function refresh_data($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id)) {
            return View('app.index', ['service' => 'project', 'user' => null]);
        }
        if($user->acc_project==0)
            return redirect()->action('LoginController@logout', ['service' => 'project']);

        $assignment = Assignment::find($id);
        //$assignments = Assignment::where('id', '>', 0)->whereNotIn('status', ['Concluído','No asignado'])->get();
        //$assignments = Assignment::where('status', 'Ejecución')->get();

        if(!$assignment){
            Session::flash('message', 'No se encontró la información solicitada.
                Revise la dirección e intente de nuevo por favor');
            return redirect()->back();
        }

        foreach($assignment->sites as $site)
        {
            foreach($site->tasks as $task)
            {
                $this->refresh_task($task);
            }

            $this->refresh_site($site);
        }

        $this->refresh_assignment($assignment);

        /*
            if($assignment->status!='Concluído'&&$assignment->status!='No asignado'){
                $site_percentage = 0;
                $total_quoted = 0;
                $total_executed = 0;
                $total_charged = 0;
                $total_assigned = 0;
                $count = 0;

                foreach($assignment->sites as $site){
                    $site_percentage += $site->percentage_completed;
                    $total_quoted += $site->quote_price;
                    $total_executed += $site->executed_price;
                    $total_charged += $site->charged_price;
                    $count++;

                    foreach ($site->orders as $order) {
                        $site->assigned_price += $order->pivot->assigned_amount;
                    }
                    $total_assigned += $site->assigned_price;
                }

                $assignment->percentage_completed = $site_percentage/($count==0 ? 1 : $count);

                //if($assignment->quote_price==0)
                $assignment->quote_price = $total_quoted;
                //if($assignment->assigned_price==0)
                $assignment->assigned_price = $total_assigned;

                $assignment->executed_price = $total_executed;
                $assignment->charged_price = $total_charged;

                $assignment->save();
            }
            */

        Session::flash('message', 'Datos actualizados correctamente');
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('assignment.index');
    }

    function send_mail_notification($assignment, $mode, $pm_assigned, $user)
    {
        if($mode=='store'){
            $recipient = User::where('area','Gerencia Tecnica')->where('priv_level',3)->first();
            $cc = $user;

            $data = array('recipient' => $recipient, 'pm_assigned' => $pm_assigned, 'assignment' => $assignment);

            $subject = 'Nueva asignación agregada al sistema';
            $mail_structure = 'emails.assig_created';
        }
        elseif($mode=='pm'){
            /* send mail to the person designated as Project Manager PM */
            $recipient = User::find($assignment->resp_id);
            $cc = $user;

            $data = array('recipient' => $recipient, 'assignment' => $assignment);

            $subject = 'Nueva asignación recibida';
            $mail_structure = 'emails.pm_assignation';
        }

        if($recipient){
            $view = View::make($mail_structure, $data);
            $content = (string) $view;
            $success = 1;

            try {
                Mail::send($mail_structure, $data, function($message) use($recipient, $cc, $subject) {
                    $message->to($recipient->email, $recipient->name)
                        ->cc($cc->email)
                        ->subject($subject)
                        ->from('postmaster@gerteabros.com', 'postmaster@gerteabros.com');
                });
            } catch (Exception $ex) {
                $success = 0;
            }

            $email = new Email;
            $email->sent_by = 'postmaster@gerteabros.com';
            $email->sent_to = $recipient->email;
            $email->sent_cc = $user->email;
            $email->subject = $subject;
            $email->content = $content;
            $email->success = $success;
            $email->save();
        }
    }

    public function add_event($type, $assignment)
    {
        $user = Session::get('user');

        $event = new Event;
        $event->user_id = $user->id;
        $event->date = Carbon::now();
        
        $prev_number = Event::select('number')->where('eventable_id',$assignment->id)
            ->where('eventable_type','App\Assignment')->orderBy('number','desc')->first();
        
        $event->number = $prev_number ? $prev_number->number+1 : 1;

        if($type=='new pm'){
            $event->description = 'Cambio de Project Manager';
            $event->detail = $assignment->responsible ?
                $assignment->responsible->name.' es asignado(a) como Project Manager de este proyecto' :
                'Este proyecto no cuenta con un Project Manager asignado.';
        }
        elseif($type=='assignment created'){
            $event->description = 'Nuevo proyecto';
            $event->detail = 'Se agrega el proyecto: '.$assignment->name.' al sistema';
        }
        elseif($type=='status changed'){
            $event->description = 'Cambio de estado';
            $event->detail = "$user->name ha cambiado el estado de la asignación $assignment->code a ".
                $assignment->statuses($assignment->status).". Este cambio ha sido replicado en sus respectivos sitios e items";
        }

        $event->responsible_id = $user->id;
        $event->eventable()->associate($assignment /*Assignment::find($assignment->id)*/);
        $event->save();
    }

    public function fill_code_column()
    {
        $assignments = Assignment::where('code','')->get();
        
        foreach($assignments as $assignment){
            
            $assignment->code = 'ASG-'.str_pad($assignment->id, 4, "0", STR_PAD_LEFT).date('-y');
            //'ASG-'.str_pad($assignment->id, 4, "0", STR_PAD_LEFT).date_format($assignment->created_at,'-y');

            $assignment->save();
        }
    }

    function get_key_item_values($assignment)
    {
        if($assignment->type=='Fibra óptica'){
            $assignment->cable_projected = $assignment->cable_executed = $assignment->cable_percentage = 0;
            $assignment->splice_projected = 0;
            $assignment->splice_executed = 0;
            $assignment->splice_percentage = 0;
            $assignment->posts_projected = 0;
            $assignment->posts_executed = 0;
            $assignment->posts_percentage = 0;
            $assignment->meassures_projected = 0;
            $assignment->meassures_executed = 0;
            $assignment->meassures_percentage = 0;

            foreach($assignment->sites as $site){
                if($site->status>0 /*'No asignado'*/){
                    foreach($site->tasks as $task){
                        $this->get_task_sum_values($task, $assignment);
                    }
                }
            }

            $assignment->cable_percentage = $this->get_percentage($assignment->cable_executed, $assignment->cable_projected);
            $assignment->splice_percentage = $this->get_percentage($assignment->splice_executed, $assignment->splice_projected);
            $assignment->posts_percentage = $this->get_percentage($assignment->posts_executed, $assignment->posts_projected);
            $assignment->meassures_percentage = $this->get_percentage($assignment->meassures_executed,
                $assignment->meassures_projected);
        }
    }

    function get_key_item_values_per_site($site)
    {
        if($site->status>0/*'No asignado'*/){
            $site->cable_projected = $site->cable_executed = $site->cable_percentage = 0;
            $site->splice_projected = 0;
            $site->splice_executed = 0;
            $site->splice_percentage = 0;
            $site->posts_projected = 0;
            $site->posts_executed = 0;
            $site->posts_percentage = 0;
            $site->meassures_projected = 0;
            $site->meassures_executed = 0;
            $site->meassures_percentage = 0;

            foreach($site->tasks as $task){
                $this->get_task_sum_values($task, $site);
            }

            $site->cable_percentage = $this->get_percentage($site->cable_executed, $site->cable_projected);
            $site->splice_percentage = $this->get_percentage($site->splice_executed, $site->splice_projected);
            $site->posts_percentage = $this->get_percentage($site->posts_executed, $site->posts_projected);
            $site->meassures_percentage = $this->get_percentage($site->meassures_executed, $site->meassures_projected);
        }
    }

    function get_task_sum_values($task, $model)
    {
        if($task->status>0/*'No asignado'*/){
            if($task->summary_category){
                if($task->summary_category->cat_name=='fo_cable'){
                    $model->cable_projected += $task->total_expected;
                    $model->cable_executed += $task->progress;
                }
                elseif($task->summary_category->cat_name=='fo_splice'){
                    $model->splice_projected += $task->total_expected;
                    $model->splice_executed += $task->progress;
                }
                elseif($task->summary_category->cat_name=='fo_post'){
                    $model->posts_projected += $task->total_expected;
                    $model->posts_executed += $task->progress;
                }
                elseif($task->summary_category->cat_name=='fo_measure'){
                    $model->meassures_projected += $task->total_expected;
                    $model->meassures_executed += $task->progress;
                }
            }
            
            /*
            if ((stripos($task->name, 'tendido')!==FALSE&&stripos($task->name, 'cable')!==FALSE)||
                stripos($task->name, 'lineal')!==FALSE){
                $model->cable_projected += $task->total_expected;
                $model->cable_executed += $task->progress;
            }
            elseif(stripos($task->name, 'empalme')!==FALSE&&stripos($task->name, 'ejecución')!==FALSE){
                $model->splice_projected += $task->total_expected;
                $model->splice_executed += $task->progress;
            }
            elseif(stripos($task->name, 'poste')!==FALSE&&(stripos($task->name, 'madera')!==FALSE||
                    stripos($task->name, 'prfv')!==FALSE||stripos($task->name, 'hormig')!==FALSE)&&
                    stripos($task->name, 'traslado')===FALSE){
                $model->posts_projected += $task->total_expected;
                $model->posts_executed += $task->progress;
            }
            elseif(stripos($task->name, 'medida')!==FALSE){
                $model->meassures_projected += $task->total_expected;
                $model->meassures_executed += $task->progress;
            }
            */
        }
    }

    public function per_site_general_progress($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $assignment = Assignment::find($id);

        $this->get_key_item_values($assignment);
        
        foreach($assignment->sites as $site){
            $this->get_key_item_values_per_site($site);
        }

        $current_date = Carbon::now()->format('Y-m-d');

        return View::make('app.assignment_per_site_general_progress', ['assignment' => $assignment,
            'service' => $service, 'current_date' => $current_date, 'user' => $user]);
    }

    public function items_general_progress($id)
    {
        $user = Session::get('user');
        if((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $assignment = Assignment::find($id);

        $items = collect();

        foreach($assignment->sites as $site){
            foreach($site->tasks as $task){
                if($items->count()==0){
                    $items->push($task);
                }
                else{
                    if($items->contains('name', $task->name)){
                        $item = $items->where('name', $task->name)->first();
                        $item->total_expected += $task->total_expected;
                        $item->progress += $task->progress;
                    }
                    else{
                        $items->push($task);
                    }
                }
            }
        }

        $items = $items->sortBy('name');

        return View::make('app.assignment_items_general_progress', ['assignment' => $assignment, 'service' => $service,
            'items' => $items, 'user' => $user]);
    }

    function get_percentage($numerator, $denominator)
    {
        $denominator = $denominator==0 ? 1 : $denominator;

        $percentage = number_format(($numerator/$denominator)*100,2);

        return $percentage;
    }

    function update_dates($assignment)
    {
        foreach($assignment->sites as $site){
            if($site->status!=0&&$site->status!=$site->last_stat()){
                $site->start_line = $assignment->start_line;
                $site->deadline = $assignment->deadline;
                $site->start_date = $assignment->start_date;
                $site->end_Date = $assignment->end_date;

                $site->save();

                foreach($site->tasks as $task){
                    if($task->status!=0&&$task->status!=$task->last_stat()){
                        $task->start_date = $site->start_line;
                        $task->end_date = $site->deadline;

                        $task->save();
                    }
                }
            }
        }
    }

    public function select_mail_recipient($type)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');
        $asg = Input::get('asg');

        $assignment = Assignment::find($asg);

        if(!$assignment){
            Session::flash('message', 'No se encontró la información solicitada! Intente de nuevo por favor');
            return redirect()->back();
        }

        return View::make('app.assignment_recipient_form', ['assignment' => $assignment, 'asg' => $asg,
            'service' => $service, 'user' => $user, 'type' => $type]);
    }

    public function send_selected_mail(Request $request, $type)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $v = \Validator::make(Request::all(), [
            'asg_id'                => 'required|exists:assignments,id',
            'recipient'             => 'required|email',
        ],
            [
                'asg_id.required'           => 'Debe especificar la asignación a la cual hará referencia el correo!',
                'asg_id.exists'             => 'No se encontró la información solicitada en el servidor! Por favor revise
                    el nombre de la asignación e intente de nuevo',
                'recipient.required'        => 'Debe especificar la dirección de correo electrónico a la que se enviará el mensaje!',
                'recipient.email'           => 'Debe indicar una dirección de correo válida!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }
        
        $asg_id = Request::input('asg_id');

        $assignment = Assignment::find($asg_id);

        $assignment->quote_from = Carbon::parse($assignment->quote_from);
        $assignment->quote_to = Carbon::parse($assignment->quote_to);
        $assignment->start_line = Carbon::parse($assignment->start_line);
        $assignment->deadline = Carbon::parse($assignment->deadline);
        $assignment->start_date = Carbon::parse($assignment->start_date);
        $assignment->end_date = Carbon::parse($assignment->end_date);
        $assignment->billing_from = Carbon::parse($assignment->billing_from);
        $assignment->billing_to = Carbon::parse($assignment->billing_to);

        $this->get_key_item_values($assignment);

        $current_date = Carbon::now()->hour(0)->minute(0)->second(0);

        $subject = Request::input('subject');
        $comments = Request::input('comments');

        $table = View::make('app.assignment_brief_summary_table', ['assignment' => $assignment,
                'current_date' => $current_date]);

        /* send mail */
        $recipient = Request::input('recipient');
        
        $data = array('recipient' => $recipient, 'comments' => $comments, 'table' => $table, 'assignment' => $assignment);

        $mail_structure = 'emails.assignment_requested_mail';

        $view = View::make($mail_structure, $data);
        $content = (string) $view;

        $success = 1;

        try {
            Mail::send($mail_structure, $data, function($message) use($recipient, $subject, $user) {
                $message->to($recipient)
                    ->subject($subject)
                    ->cc($user->email)
                    ->from('postmaster@gerteabros.com', 'postmaster@gerteabros.com');
            });
        } catch (Exception $ex) {
            $success = 0;
        }

        $email = new Email;
        $email->sent_by = 'postmaster@gerteabros.com';
        $email->sent_to = $recipient;
        $email->sent_cc = $user->email;
        $email->subject = $subject;
        $email->content = $content;
        $email->success = $success;
        $email->save();

        Session::flash('message', "Se ha procesado su solicitud correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('assignment.index');
    }

    public function expense_report_form($type)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');
        $types = Assignment::select('type')->where('type', '<>', '')->groupBy('type')->get();
        $clients = Assignment::select('client')->where('client','<>','')->groupBy('client')->get();

        $last_stat = count(Assignment::$status_names) - 1;

        $assignments = Assignment::select('id','name')->whereNotIn('status',[0, $last_stat])->get();

        return View::make('app.assignment_expense_report_form', ['type' => $type, 'service' => $service,
            'user' => $user, 'types' => $types, 'clients' => $clients, 'assignments' => $assignments]);
    }

    public function expense_report(Request $request, $type)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $form_data = Request::all();

        if($form_data['to']!='')
            $form_data['to'] = $form_data['to'].' 23:59:59';

        $v = \Validator::make($form_data, [
            'from'            => 'required|date',
            'to'              => 'required|date|after:from',
        ],
            [
                'required'          => 'Este campo es obligatorio!',
                'date'              => 'Valor no válido para el campo fecha!',
                'after'             => 'La fecha de fin no puede ser anterior a la fecha de inicio!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', 'Sucedió un error al enviar el formulario!');
            return redirect()->back()->withErrors($v)->withInput();
        }

        $last_stat = count(Assignment::$status_names) - 1;

        $assignments = Assignment::whereNotIn('status',[0, $last_stat]);

        if(!empty($form_data['id']))
            $assignments = $assignments->where('id', $form_data['id']);
        if(!empty($form_data['client']))
            $assignments = $assignments->where('client', $form_data['client']);
        if(!empty($form_data['type']))
            $assignments = $assignments->where('type', $form_data['type']);
        if(!empty($form_data['project_id']))
            $assignments = $assignments->where('project_id', $form_data['project_id']);

        $assignments = $assignments->get();

        $from = Carbon::parse($form_data['from']);
        $to = Carbon::parse($form_data['to']);

        if($type=='stipend'){
            $parent = !empty($form_data['project_id']) ? Project::find($form_data['project_id']) : 0;

            foreach($assignments as $assignment){
                $assignment->requests_number = 0;
                $assignment->viatic_total = 0;
                $assignment->additionals_total = 0;

                //foreach($assignment->sites as $site){
                    foreach($assignment->stipend_requests as $stipend_request){
                        $stipend_request->date_from = Carbon::parse($stipend_request->date_from);

                        if($stipend_request->date_from->between($from, $to)&&$stipend_request->status=='Completed'){
                            $assignment->viatic_total += $stipend_request->total_amount;
                            $assignment->additionals_total += $stipend_request->additional;

                            $assignment->requests_number++;
                        }
                    }
                //}
            }

            return View::make('app.assignment_expense_report', ['type' => $type, 'service' => $service, 'user' => $user,
                'assignments' => $assignments, 'from' => $from, 'to' => $to, 'form_data' => $form_data, 'parent' => $parent]);
        }
        elseif($type=='stipend_per_tech'){
            $employees = collect();

            $parent = Assignment::find($form_data['id']);

            foreach($assignments as $assignment){
                foreach($assignment->stipend_requests as $stipend_request){
                    if($employees->contains('id',$stipend_request->employee_id)){
                        $employee = $employees->where('id', $stipend_request->employee_id)->first();
                    }
                    else{
                        $employee = $stipend_request->employee;

                        $employee->requests_number = 0;
                        $employee->viatic_total = 0;
                        $employee->additionals_total = 0;

                        $employees->push($employee);
                    }

                    $stipend_request->date_from = Carbon::parse($stipend_request->date_from);

                    if($stipend_request->date_from->between($from, $to)&&$stipend_request->status=='Completed'){
                        $employee->viatic_total += $stipend_request->total_amount;
                        $employee->additionals_total += $stipend_request->additional;

                        $employee->requests_number++;
                    }
                }
            }

            return View::make('app.assignment_expense_report_per_tech', ['type' => $type, 'service' => $service,
                'user' => $user, 'employees' => $employees, 'from' => $from, 'to' => $to, 'form_data' => $form_data,
                'parent' => $parent]);
        }
        else{
            Session::flash('message', 'No se reconocen los parámetros necesarios para generar el reporte!');
            if(Session::has('url'))
                return redirect(Session::get('url'));
            else
                return redirect()->route('assignment.index');
        }
    }

    public function generate_from_model($type)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $last_stat = count(Assignment::$status_names) - 1;

        $assignments = Assignment::whereNotIn('status',[0, $last_stat]);

        $from = Input::get('from');
        $to = Input::get('to');
        $id = Input::get('id');
        $client = Input::get('client');
        $area = Input::get('area');
        $project_id = Input::get('project_id');

        if($id!='')
            $assignments = $assignments->where('id', $id);
        if($client!='')
            $assignments = $assignments->where('client', $client);
        if($area!='')
            $assignments = $assignments->where('type', $area);
        if($project_id!='')
            $assignments = $assignments->where('project_id', $project_id);

        $assignments = $assignments->get();

        $from = Carbon::parse($from);
        $to = Carbon::parse($to);

        if($type=='stipend'){
            foreach($assignments as $assignment){
                $assignment->requests_number = 0;
                $assignment->viatic_total = 0;
                $assignment->additionals_total = 0;

                foreach($assignment->stipend_requests as $stipend_request){
                    $stipend_request->date_from = Carbon::parse($stipend_request->date_from);

                    if($stipend_request->date_from->between($from, $to)&&$stipend_request->status=='Completed'){
                        $assignment->viatic_total += $stipend_request->total_amount;
                        $assignment->additionals_total += $stipend_request->additional;

                        $assignment->requests_number++;
                    }
                }
            }

            $sheet_content = collect();
            $excel_name = 'Reporte de gastos - asignaciones';
            $sheet_name = 'Asignaciones';

            foreach($assignments as $assignment)
            {
                $sheet_content->prepend(
                    [   'Asignación'    => $assignment->name,
                        '# Solicitudes' => $assignment->requests_number,
                        'Viaticos [Bs]' => number_format($assignment->viatic_total,2),
                        'Adicionales [Bs]'   => number_format($assignment->additionals_total,2),
                        'Total [Bs]'    => number_format($assignment->viatic_total+$assignment->additionals_total,2),
                    ]);
            }

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }
        elseif($type=='stipend_per_tech'){
            $employees = collect();

            foreach($assignments as $assignment){
                foreach($assignment->stipend_requests as $stipend_request){
                    if($employees->contains('id',$stipend_request->employee_id)){
                        $employee = $employees->where('id', $stipend_request->employee_id)->first();
                    }
                    else{
                        $employee = $stipend_request->employee;

                        $employee->requests_number = 0;
                        $employee->viatic_total = 0;
                        $employee->additionals_total = 0;

                        $employees->push($employee);
                    }

                    $stipend_request->date_from = Carbon::parse($stipend_request->date_from);

                    if($stipend_request->date_from->between($from, $to)&&$stipend_request->status=='Completed'){
                        $employee->viatic_total += $stipend_request->total_amount;
                        $employee->additionals_total += $stipend_request->additional;

                        $employee->requests_number++;
                    }
                }
            }

            $sheet_content = collect();
            $excel_name = 'Reporte de gastos - asignaciones por tecnico';
            $sheet_name = 'Gastos por tecnico-asignacion';

            foreach($employees as $employee)
            {
                if($employee->requests_number>0){
                    $sheet_content->prepend(
                        [   'Empleado'      => $employee->first_name.' '.$employee->last_name,
                            '# Solicitudes' => $employee->requests_number,
                            'Viaticos [Bs]' => number_format($employee->viatic_total,2),
                            'Adicionales [Bs]'   => number_format($employee->additionals_total,2),
                            'Total [Bs]'    => number_format($employee->viatic_total+$employee->additionals_total,2),
                        ]);
                }
            }

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }
        else{
            Session::flash('message', 'No se reconocen los parámetros necesarios para generar el reporte!');
            if(Session::has('url'))
                return redirect(Session::get('url'));
            else
                return redirect()->route('assignment.index');
        }
    }

    public function create_excel($excel_name, $sheet_name, $sheet_content)
    {
        Excel::create($excel_name, function($excel) use($sheet_name,$sheet_content) {

            $excel->sheet($sheet_name, function($sheet) use($sheet_content) {

                $sheet->fromArray($sheet_content);

            });
        })->export('xls');
    }
}
