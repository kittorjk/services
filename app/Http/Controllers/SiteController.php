<?php

namespace App\Http\Controllers;

use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use View;
use Input;
use App\Site;
use App\File;
use App\User;
use App\Assignment;
use App\Contact;
use App\Event;
use App\DeadInterval;
use App\StipendRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use PHPExcel_Worksheet_Drawing;
use App\Http\Traits\FilesTrait;
use App\Http\Traits\ProjectTrait;

class SiteController extends Controller
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
        if ((is_null($user))||(!$user->id)) {
            return View('app.index', ['service'=>'project', 'user'=>null]);
        }
        if($user->acc_project==0)
            return redirect()->action('LoginController@logout', ['service' => 'project']);

        return redirect()->back();

        /*
        $service = Session::get('service');

        $last_stat = Site::first()->last_stat();

        if($user->priv_level>=3||$user->role=='Director regional'||($user->area!='Gerencia Tecnica'&&$user->priv_level>=2)){
            /*
            $sites = Site::join('assignments', 'assignments.id', '=', 'sites.assignment_id')
                ->select('sites.*')
                ->whereNotIn('sites.status', ['Concluído','No asignado'])
                ->orderBy('sites.assignment_id')->paginate(20);

            $sites = Site::where('id', '>', 0)->whereNotIn('status', [0, $last_stat])
                //->whereNotIn('status', ['Concluído','No asignado'])
                //->where('name','not like','%Main')
                ->orderBy('assignment_id')->paginate(20);
        }
        else{
            $sites = Site::join('assignments', 'assignments.id', '=', 'sites.assignment_id')
                ->select('sites.*')
                ->whereNotIn('sites.status', [0, $last_stat])
                //->whereNotIn('sites.status', ['Concluído','No asignado'])
                ->where('assignments.type',$user->work_type)
                ->orderBy('sites.assignment_id')->paginate(20);
        }

        /*
        $files = File::join('sites', 'files.imageable_id', '=', 'sites.id')
            ->select('files.id', 'files.name', 'files.imageable_id', 'files.created_at')
            ->where('imageable_type', 'App\Site')
            ->get();

        foreach($sites as $site)
        {
            $site->start_line = Carbon::parse($site->start_line);
            $site->deadline = Carbon::parse($site->deadline);
            $site->start_date = Carbon::parse($site->start_date);
            $site->end_date = Carbon::parse($site->end_date);

            /* Separated to another function
            if($site->status!='Concluído'&&$site->status!='No asignado'){
                $task_percentage = 0;
                $total_quoted = 0;
                $total_executed = 0;
                $count = 0;
                foreach($site->tasks as $task){
                    $task_percentage = $task_percentage+(($task->progress/$task->total_expected)*100);
                    $total_quoted = $total_quoted + $task->assigned_price;
                    $total_executed = $total_executed + $task->executed_price;
                    $count++;
                }
                if($count==0)
                    $count=1;
                $site->percentage_completed = $task_percentage/$count;

                //if($site->quote_price==0)
                    $site->quote_price = $total_quoted;
                //if($site->assigned_price==0)
                    //$site->assigned_price = $total_assigned;

                $site->executed_price = $total_executed;
                $site->save();
            }

            foreach($site->files as $file)
            {
                $file->created_at = Carbon::parse($file->created_at)->hour(0)->minute(0)->second(0);
            }

            /* Add general progress values for key items
            $this->get_key_item_values($site);
        }

        $current_date = Carbon::now();
        $current_date->hour = 0;
        $current_date->minute = 0;
        $current_date->second = 0;

        return View::make('app.site_brief', ['assignment_info' => 0, 'sites' => $sites, 'user' => $user,
            'service' => $service, 'current_date' => $current_date]);
        */
    }

    public function sites_per_project($id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $assignment_info = Assignment::find($id);

        if($assignment_info){
            //$sites = Site::where('assignment_id',$id)->orderBy('name')->paginate(20);
            $sites = $assignment_info->sites()->orderBy('name')->paginate(20);

            //$last_stat = $sites->first()->last_stat();

            /*
            $files = File::join('sites', 'files.imageable_id', '=', 'sites.id')
                ->select('files.id', 'files.name', 'files.imageable_id', 'files.created_at')
                ->where('imageable_type', 'App\Site')
                ->get();
            */
            foreach($sites as $site)
            {
                $site->start_line = Carbon::parse($site->start_line);
                $site->deadline = Carbon::parse($site->deadline);
                $site->start_date = Carbon::parse($site->start_date);
                $site->end_date = Carbon::parse($site->end_date);

                /* Separated to another function
                if($site->status!='Concluído'&&$site->status!='No asignado'){
                    $task_percentage = 0;
                    $total_quoted = 0;
                    $total_executed = 0;
                    $count = 0;
                    foreach($site->tasks as $task){
                        $task_percentage = $task_percentage+(($task->progress/$task->total_expected)*100);
                        $total_quoted = $total_quoted + $task->assigned_price;
                        $total_executed = $total_executed + $task->executed_price;
                        $count++;
                    }
                    if($count==0)
                        $count=1;
                    $site->percentage_completed = $task_percentage/$count;

                    //if($site->quote_price==0)
                        $site->quote_price = $total_quoted;
                    //if($site->assigned_price==0)
                        //$site->assigned_price = $total_assigned;

                    $site->executed_price = $total_executed;
                    $site->save();
                }
                */
                foreach($site->files as $file)
                {
                    $file->created_at = Carbon::parse($file->created_at)->hour(0)->minute(0)->second(0);
                }

                /* Add general progress values for key items */
                $this->get_key_item_values($site);
            }

            $current_date = Carbon::now();
            $current_date->hour = 0;
            $current_date->minute = 0;
            $current_date->second = 0;

            return View::make('app.site_brief', ['assignment_info' => $assignment_info, 'sites' => $sites, 'user' => $user,
                'service' => $service, 'current_date' => $current_date]);
        }
        else{
            Session::flash('message', 'Error al recuperar la información solicitada del servidor!
                Registro de asignación no encontrado');
            return redirect()->back();
        }
    }

    public function site_items_calendar($id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');
        
        $service = Session::get('service');

        $site = Site::find($id);

        if(!$site){
            Session::flash('message', "No se encontró la página solicitada,
                revise la dirección e intente de nuevo por favor");
            return redirect()->back();
        }
        /*
        if($user->priv_level>=1)
            $activities = Activity::where('task_id',$id)->orderBy('number')->paginate(20);
        else
            $activities = Activity::where('task_id',$id)->where('user_id',$user->id)->orderBy('number')->paginate(20);
        */
        foreach($site->tasks as $task)
        {
            $task->start_date = Carbon::parse($task->start_date);
            $task->end_date = Carbon::parse($task->end_date);
            
            foreach($task->activities as $activity){
                $activity->date = Carbon::parse($activity->date);
            }
        }

        $site->start_date = Carbon::parse($site->start_date);
        $site->end_date = Carbon::parse($site->end_date);

        $from_date = $site->start_date;
        $to_date = $site->end_date;

        foreach($site->tasks as $task){
            foreach ($task->activities as $activity){
                if($activity->date<$from_date)
                    $from_date = $from_date->subDays($activity->date->DiffInDays($from_date));
                elseif($activity->date>$to_date)
                    $to_date = $to_date->addDays($activity->date->DiffInDays($to_date));
            }
        }

        //$interval = $site->start_date->diffInDays($site->end_date);
        //$date = $site->start_date;

        $interval = $from_date->diffInDays($to_date)+2;
        $date = $from_date;

        $current_date = Carbon::now()->hour(0)->minute(0)->second(0);

        return View::make('app.site_brief_calendar_style', ['site' => $site, 'service' => $service, 
            'current_date' => $current_date, 'user' => $user, 'interval' => $interval, 'date' => $date]);
    }

    public function site_schedule()
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $asg_id = Input::get('asg_id');
        $opt = Input::get('opt');

        $service = Session::get('service');

        $assignment = Assignment::find($asg_id);

        if(!$assignment){
            Session::flash('message', "No se encontró la página solicitada, revise la dirección e intente de nuevo por favor");
            return redirect()->back();
        }
        
        foreach($assignment->sites as $site)
        {
            $site->start_date = Carbon::parse($site->start_date);
            $site->end_date = Carbon::parse($site->end_date);
            $site->start_line = Carbon::parse($site->start_line);
            $site->deadline = Carbon::parse($site->deadline);

            foreach($site->tasks as $task){
                $task->start_date = Carbon::parse($task->start_date);
                $task->end_date = Carbon::parse($task->end_date);

                foreach($task->activities as $activity){
                    $activity->date = Carbon::parse($activity->date);
                }
            }
        }

        $assignment->start_line = Carbon::parse($assignment->start_line);
        $assignment->deadline = Carbon::parse($assignment->deadline);
        $assignment->start_date = Carbon::parse($assignment->start_date);
        $assignment->end_date = Carbon::parse($assignment->end_date);

        $from = $assignment->start_date->lt($assignment->start_line) ? $assignment->start_date : $assignment->start_line;
        $to = $assignment->end_date->gt($assignment->deadline) ? $assignment->end_date : $assignment->deadline;

        /*
        if($assignment->start_date->year<1)
            $assignment->start_date = Carbon::now()->hour(0)->minute(0)->second(0);
        if($assignment->end_date->year<1)
            $assignment->end_date = Carbon::now()->hour(23)->minute(59)->second(59);
        */

        //$interval = $assignment->start_date->diffInDays($assignment->end_date)+2;
        $interval = $from->diffInDays($to) +2;
        $date = Carbon::parse($from); //$assignment->start_date->hour(0)->minute(0)->second(0);

        $current_date = Carbon::now()->hour(0)->minute(0)->second(0);
        
        return View::make('app.site_schedule_view', ['assignment' => $assignment, 'service' => $service,
            'current_date' => $current_date, 'user' => $user, 'interval' => $interval, 'date' => $date, 'opt' => $opt]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $last_stat = count(Site::$status_options) -1; //Site::first()->last_stat();

        $assignment = Assignment::find($id);

        if(!$assignment){
            Session::flash('message', 'No se encontró información de la asignación seleccionada!');
            return redirect()->back();
        }
        elseif($assignment->status==0||$assignment->status==$assignment->last_stat()){
            Session::flash('message', 'No puede agregar un nuevo sitio a una asignación concluída o no asignada!');
            return redirect()->back();
        }
        /*
        if($id==0){
            $assignments = Assignment::select('id', 'name')
                ->whereNotIn('status', [0, $last_stat])
                //->whereNotIn('status', ['Concluído','No asignado'])
                ->get();
        }
        else
            $assignments = Assignment::where('id', $id)->get();
        */
        $current_date = Carbon::now()->format('Y-m-d');

        return View::make('app.site_form', ['site' => 0, 'assignment' => $assignment, 'assignment_id' => $id,
            'user' => $user, 'service' => $service, 'last_stat' => $last_stat, 'current_date' => $current_date]);
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

        if($form_data['deadline']!='')
            $form_data['deadline'] = $form_data['deadline'].' 23:59:59';
        if($form_data['end_date']!='')
            $form_data['end_date'] = $form_data['end_date'].' 23:59:59';

        $v = \Validator::make($form_data, [
            'name'                  => 'required',
            'assignment_id'         => 'required',
            'resp_name'             => 'regex:/^[\pL\s\-]+$/u',
            'contact_name'          => 'regex:/^[\pL\s\-]+$/u',
            'start_line'            => 'date',
            'deadline'              => 'date|after:start_line',
            'start_date'            => 'date',
            'end_date'              => 'date|after:start_date',
        ],
            [
                'name.required'          => 'Debe especificar el nombre del sitio!',
                'assignment_id.required' => 'Debe especificar el proyecto al que pertenece el sitio!',
                'resp_name.regex'        => 'El nombre del responsable de ABROS solo puede contener letras',
                'contact_name.regex'     => 'El nombre del responsable del cliente solo puede contener letras',
                'start_line.date'        => 'El formato de la fecha de inicio asignada es incorrecto!',
                'deadline.date'          => 'El formato de la fecha de fin asignada es incorrecto!',
                'deadline.after'         => 'La fecha de fin asignada no puede ser anterior a la fecha de inicio!',
                'start_date.date'        => 'El formato de la fecha de inicio propia es incorrecto!',
                'end_date.date'          => 'El formato de la fecha de fin propia es incorrecto!',
                'end_date.after'         => 'La fecha de fin propia no puede ser anterior a la fecha de inicio!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $site = new Site($form_data);
        
        if($site->latitude>90||$site->latitude<-90){
            Session::flash('message', 'Inserte un valor de latitud de orígen válido!');
            return redirect()->back()->withInput();
        }

        if($site->longitude>180||$site->longitude<-180){
            Session::flash('message', 'Inserte un valor de longitud de orígen válido!');
            return redirect()->back()->withInput();
        }

        if($site->lat_destination>90||$site->lat_destination<-90){
            Session::flash('message', 'Inserte un valor de latitud de destino válido!');
            return redirect()->back()->withInput();
        }

        if($site->long_destination>180||$site->long_destination<-180){
            Session::flash('message', 'Inserte un valor de longitud de destino válido!');
            return redirect()->back()->withInput();
        }

        $site->status = 1; //Relevamiento /*empty(Request::input('status')) ? 1 : $site->status;*/

        $site->origin_name = $site->origin_name ?: $site->name;
        
        $site->start_line = empty($site->start_line) ? Carbon::now() : $site->start_line;
        $site->deadline = empty(Request::input('interval_days_assigned')) ?
            (empty($site->deadline) ? Carbon::now()->addDays(20) :
                $site->deadline) : Carbon::parse($site->start_line)->addDays(Request::input('interval_days_assigned'));
        
        $site->start_date = empty($site->start_date) ? Carbon::now() : $site->start_date;
        $site->end_date = empty(Request::input('interval_days')) ?
            (empty($site->end_date) ? Carbon::now()->addDays(20) :
                $site->end_date) : Carbon::parse($site->start_date)->addDays(Request::input('interval_days'));

        $site->user_id = $user->id;

        $responsible = User::select('id')->where('name', Request::input('resp_name'))->first();
        $site->resp_id = $responsible=='' ? 0 : $responsible->id;

        if(!empty(Request::input('contact_name'))){
            $contact = Contact::select('id')->where('name', Request::input('contact_name'))->first();

            if($contact=='') {
                $contact = new Contact;
                $contact->name = Request::input('contact_name');
                //$assignment = Assignment::find($site->assignment_id);
                $contact->company = $site->assignment->client;
                $contact->save();
            }
            $site->contact_id = $contact->id;
        }
        else{
            $site->contact_id = $site->assignment->contact_id;
        }

        $site->save();

        $this->fill_code_column();

        Session::flash('message', "El sitio fue agregado al sistema correctamente");
        return redirect()->action('SiteController@sites_per_project', ['id' => $site->assignment_id]);
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
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $site = Site::find($id);

        /*
        foreach ($site->orders as $order) {
            $site->assigned_price = $site->assigned_price + $order->pivot->assigned_amount;
        }
        /*
        $files = File::select('id','user_id','name','updated_at')->where('imageable_id', $id)
            ->where('imageable_type', 'App\Site')->get();

        $responsible = User::find($site->resp_id);
        $contact = Contact::find($site->contact_id);
        */
        return View::make('app.site_info', ['site' => $site, 'service' => $service, 'user' => $user]);
    }
    /*
    public function show_financial_details($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $site = Site::find($id);

        return View::make('app.site_financial_details', ['site' => $site, 'service' => $service,
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
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $site = Site::find($id);

        if(!$site){
            Session::flash('message', 'No se encontró el registro solicitado, revise la dirección e intente de nuevo.');
            return redirect()->back();
        }

        $last_stat = $site->last_stat();

        $assignment = $site->assignment;

        if(!$assignment){
            Session::flash('message', 'No se encontró información de la asignación seleccionada!');
            return redirect()->back();
        }
        /*
        $assignments = Assignment::select('id','name')
            ->whereNotIn('status', [$last_stat, 0]) //->whereNotIn('status', ['Concluído','No asignado'])
            ->orWhere('id', '=', $site->assignment_id)
            ->get();
        */
        $current_date = Carbon::now()->format('Y-m-d');
        /*
        $responsible = User::select('name')->where('id', $site->resp_id)->first();
        $resp_name = empty($responsible) ? '' : $responsible->name;

        $contact = Contact::select('name')->where('id', $site->contact_id)->first();
        $contact_name = empty($contact) ? '' : $contact->name;
        */
        $site->start_line = Carbon::parse($site->start_line)->format('Y-m-d');
        $site->deadline = Carbon::parse($site->deadline)->format('Y-m-d');
        $site->start_date = Carbon::parse($site->start_date)->format('Y-m-d');
        $site->end_date = Carbon::parse($site->end_date)->format('Y-m-d');

        return View::make('app.site_form', ['site' => $site, 'assignment' => $assignment, 'last_stat' => $last_stat,
            'assignment_id' => $site->assignment_id, 'user' => $user, 'service' => $service, 'current_date' => $current_date]);
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

        if($form_data['deadline']!='')
            $form_data['deadline'] = $form_data['deadline'].' 23:59:59';
        if($form_data['end_date']!='')
            $form_data['end_date'] = $form_data['end_date'].' 23:59:59';
        
        $v = \Validator::make($form_data, [
            'name'                  => 'required',
            'assignment_id'         => 'required',
            'status'                => 'required',
            'resp_name'             => 'regex:/^[\pL\s\-]+$/u',
            'contact_name'          => 'regex:/^[\pL\s\-]+$/u',
            'start_line'            => 'date',
            'deadline'              => 'date|after:start_line',
            'start_date'            => 'date',
            'end_date'              => 'date|after:start_date',
        ],
            [
                'name.required'          => 'Debe especificar el nombre del sitio!',
                'assignment_id.required' => 'Debe especificar el proyecto al que pertenece el sitio!',
                'status.required'        => 'Debe especificar el estado del sitio!',
                'resp_name.regex'        => 'El nombre del responsable de ABROS solo puede contener letras',
                'contact_name.regex'     => 'El nombre del responsable del cliente solo puede contener letras',
                'start_line.date'        => 'El formato de la fecha de inicio asignada es incorrecto!',
                'deadline.date'          => 'El formato de la fecha de fin asignada es incorrecto!',
                'deadline.after'         => 'La fecha de fin asignada no puede ser anterior a la fecha de inicio!',
                'start_date.date'        => 'El formato de la fecha de inicio es incorrecto!',
                'end_date.date'          => 'El formato de la fecha de fin es incorrecto!',
                'end_date.after'         => 'La fecha de fin no puede ser anterior a la fecha de inicio!',
            ]
        );
        
        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $site = Site::find($id);
        $old_info = Site::find($id);
        
        $site->fill($form_data);

        if($site->latitude>90||$site->latitude<-90){
            Session::flash('message', 'Inserte un valor de latitud de orígen válido!');
            return redirect()->back()->withInput();
        }

        if($site->longitude>180||$site->longitude<-180){
            Session::flash('message', 'Inserte un valor de longitud de orígen válido!');
            return redirect()->back()->withInput();
        }

        if($site->lat_destination>90||$site->lat_destination<-90){
            Session::flash('message', 'Inserte un valor de latitud de destino válido!');
            return redirect()->back()->withInput();
        }

        if($site->long_destination>180||$site->long_destination<-180){
            Session::flash('message', 'Inserte un valor de longitud de destino válido!');
            return redirect()->back()->withInput();
        }

        $site->origin_name = $site->origin_name ?: $site->name;
        
        $site->start_line = empty($site->start_line) ? Carbon::now() : $site->start_line;
        $site->deadline = empty(Request::input('interval_days_assigned')) ?
            (empty($site->deadline) ? Carbon::now()->addDays(20) :
                $site->deadline) : Carbon::parse($site->start_line)->addDays(Request::input('interval_days_assigned'));

        $site->start_date = empty($site->start_date) ? Carbon::now() : $site->start_date;
        $site->end_date = empty(Request::input('interval_days')) ?
            (empty($site->end_date) ? Carbon::now()->addDays(20) :
                $site->end_date) : Carbon::parse($site->start_date)->addDays(Request::input('interval_days'));

        $responsible = User::select('id')->where('name', Request::input('resp_name'))->first();
        $site->resp_id = $responsible=='' ? 0 : $responsible->id;

        if(!empty(Request::input('contact_name'))){
            $contact = Contact::select('id')->where('name', Request::input('contact_name'))->first();

            if($contact=='') {
                $contact = new Contact;
                $contact->name = Request::input('contact_name');
                //$assignment = Assignment::find($site->assignment_id);
                $contact->company = $site->assignment->client;
                $contact->save();
            }
            $site->contact_id = $contact->id;
        }
        else{
            $site->contact_id = $site->assignment->contact_id;
        }

        $site->save();

        foreach($site->tasks as $task){
            $this->new_stat_task($task, $site->status); //Set the status of the child tasks
        }
        
        if($site->status==$site->last_stat()/*'Concluído'*/||$site->status==0/*'No asignado'*/){
            /*            
            foreach($site->tasks as $task){
                if($task->status!='Concluído'&&$task->status!='No asignado'){
                    if($site->status=='Relevamiento'||$site->status=='Cotizado')
                        $task->status = 'En espera';
                    elseif($site->status=='Cobro')
                        $task->status = 'Revisión';
                    else
                        $task->status = $site->status;

                    $task->save();

                    foreach($task->activities as $activity){
                        foreach($activity->files as $file){
                            $this->blockFile($file);
                        }
                    }
                }
            }
            */
            
            foreach($site->files as $file){
                $this->blockFile($file);
            }
        }

        /* If a date interval changes */
        if($site->start_line!=$old_info->start_line||$site->deadline!=$old_info->deadline||
            $site->start_date!=$old_info->start_date||$site->end_date!=$old_info->end_date){
            $this->update_dates($site);
        }

        Session::flash('message', "Datos actualizados correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->action('SiteController@sites_per_project', ['id' => $site->assignment_id]);
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

        $site = Site::find($id);
        $return_id = $site->assignment_id;

        if($site->status==$site->last_stat()/*'Concluído'*/){
            Session::flash('message', 'Este sitio no puede ser borrado por que ya ha sido marcado como "Concluído"!');
            return redirect()->back();
        }

        if($site->orders->count()>0){
            Session::flash('message', 'Este sitio no puede ser borrado porque tiene asociada una orden de compra!');
            return redirect()->back();
        }

        if($site->stipend_requests->count()>0){
            Session::flash('message', 'Este sitio no puede ser borrado porque cuenta con solicitudes de viáticos!');
            return redirect()->back();
        }

        $error = false;

        if($site->rbs_char)
            $site->rbs_char->delete();

        foreach($site->tasks as $task){

            foreach($task->activities as $activity){

                foreach($activity->files as $file){
                    $error = $this->removeFile($file);
                    if($error)
                        break;
                }

                if($error)
                    break;

                $activity->delete();
            }

            if($error)
                break;

            foreach($task->events as $event){

                foreach($event->files as $file){
                    $error = $this->removeFile($file);
                    if($error)
                        break;
                }

                if($error)
                    break;

                $event->delete();
            }

            if($error)
                break;

            $task->delete();
        }

        if(!$error){
            foreach($site->events as $event){

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
            foreach($site->dead_intervals as $dead_interval){
                
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
            foreach($site->files as $file){
                $error = $this->removeFile($file);
                if($error)
                    break;
            }
        }

        if (!$error) {
            $site->delete();

            Session::flash('message', "El registro fue eliminado del sistema");
            if(Session::has('url'))
                return redirect(Session::get('url'));
            else
                return redirect()->action('SiteController@sites_per_project', ['id' => $return_id]);
        }
        else {
            Session::flash('message', "Error al borrar el registro, por favor consulte al administrador");
            return redirect()->back();
        }
    }

    public function clear_site($id)
    {
        //Deletes all tasks within a site

        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');
        
        $site = Site::find($id);

        if($site){
            $file_error = false;

            foreach($site->tasks as $task){

                foreach($task->activities as $activity){

                    foreach($activity->files as $file){
                        $file_error = $this->removeFile($file);
                        if($file_error)
                            break;
                    }

                    if (!$file_error)
                        $activity->delete();
                    else
                        break;
                }

                if(!$file_error){
                    foreach($task->events as $event){

                        foreach($event->files as $file){
                            $file_error = $this->removeFile($file);
                            if($file_error)
                                break;
                        }

                        if (!$file_error)
                            $event->delete();
                        else
                            break;
                    }
                }

                if (!$file_error)
                    $task->delete();
                else
                    break;
            }

            if (!$file_error) {
                Session::flash('message', "Todos los items de este sitio han sido eliminados");
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->action('TaskController@tasks_per_site', ['id' => $site->id]);
            }
            else {
                Session::flash('message', "Error al borrar un registro, por favor consulte al administrador. $file_error");
                return redirect()->back();
            }
        }
        else {
            Session::flash('message', "Error al ejecutar el borrado, no se encontró el sitio solicitado.");
            return redirect()->back();
        }
    }

    public function modify_status($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $match = false; //Boolean to determine if a matching condition is applied
        
        $site = Site::find($id);
        $action = Input::get('action');
        $message = '';

        if($action=='upgrade'){
            if($site->status<$site->last_stat()){

                $site->status += 1;

                if($site->status==$site->last_stat()){
                    foreach($site->files as $file){
                        $this->blockFile($file);
                    }
                }

                $site->save();

                foreach($site->tasks as $task){
                    $this->new_stat_task($task, $site->status); //Set the status of the child tasks
                }

                $match = true;
                $message = "El estado del sitio ha cambiado a ".$site->statuses($site->status);
            }

            /*
            if($site->status=='Relevamiento'){
                $site->status = 'Cotizado';
                $site->save();
                
                foreach($site->tasks as $task){
                    $this->new_stat_task($task, 'Cotizado'); //Set the status of the child tasks
                }
            }
            elseif($site->status=='Cotizado'){
                $site->status = 'Ejecución';
                //$site->start_date = Carbon::now();
                //$site->end_date = Carbon::now()->addDays(20);
                $site->save();

                foreach($site->tasks as $task){
                    $this->new_stat_task($task, 'Ejecución'); //Set the status of the child tasks
                }
            }
            elseif($site->status=='Ejecución'){
                $site->status = 'Revisión';
                $site->save();

                foreach($site->tasks as $task){
                    $this->new_stat_task($task, 'Revisión'); //Set the status of the child tasks
                }

                /*
                $dead_interval = new DeadInterval;
                $dead_interval->user_id = $user->id;
                $dead_interval->date_from = Carbon::now();
                $dead_interval->reason = 'Período de espera tras conclusión de trabajos en sitio';
                $dead_interval->relatable()->associate(Site::find($id));
                $dead_interval->save();
            }
            elseif($site->status=='Revisión') {
                $site->status = 'Cobro';
                $site->save();

                foreach($site->tasks as $task){
                    $this->new_stat_task($task, 'Cobro'); //Set the status of the child tasks
                }
                
                /*
                $open_dead_intervals = DeadInterval::where('closed',0)->where('relatable_id',$site->id)
                    ->where('relatable_type','App\Site')->get();

                foreach($open_dead_intervals as $dead_interval){
                    $dead_interval->date_to = Carbon::now();
                    $dead_interval->date_from = Carbon::parse($dead_interval->date_from)->hour(0)->minute(0)->second(0);
                    $dead_interval->total_days = Carbon::now()->hour(0)->minute(0)->second(0)
                        ->diffInDays($dead_interval->date_from);
                    $dead_interval->closed = 1;
                    $dead_interval->save();
                }
            }
            elseif($site->status=='Cobro'){
                $site->status = 'Concluído';
                $site->save();

                foreach($site->tasks as $task){
                    $this->new_stat_task($task, 'Concluído'); //Set the status of the child tasks
                }
                /*
                foreach($site->tasks as $task){
                    if($task->status!='Concluído'&&$task->status!='No asignado'){
                        $task->status = 'Concluído'; //$site->status;
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

            $match = true;
            $message = "El estado del sitio ha cambiado a $site->status";
            */
        }
        elseif($action=='downgrade'){
            if($site->status>1){

                $site->status -= 1;

                if($site->status==($site->last_stat()-1)){
                    foreach($site->files as $file){
                        $this->unblockFile($file);
                    }
                }

                $site->save();

                foreach($site->tasks as $task){
                    $this->new_stat_task($task, $site->status); //Set the status of the child tasks
                }

                $match = true;
                $message = "El estado del sitio ha cambiado a ".$site->statuses($site->status);
            }

            /*
            if($site->status=='Cotizado'){
                $site->status = 'Relevamiento';
                $site->save();

                foreach($site->tasks as $task){
                    $this->new_stat_task($task, 'Relevamiento'); //Set the status of the child tasks
                }
            }
            elseif($site->status=='Ejecución'){
                $site->status = 'Cotizado';
                //$site->start_date = '0000-00-00 00:00:00';
                //$site->end_date = '0000-00-00 00:00:00';
                $site->save();

                foreach($site->tasks as $task){
                    $this->new_stat_task($task, 'Cotizado'); //Set the status of the child tasks
                }
            }
            elseif($site->status=='Revisión'){
                $site->status = 'Ejecución';
                $site->save();

                foreach($site->tasks as $task){
                    $this->new_stat_task($task, 'Ejecución'); //Set the status of the child tasks
                }
            }
            elseif($site->status=='Cobro') {
                $site->status = 'Revisión';
                $site->save();

                foreach($site->tasks as $task){
                    $this->new_stat_task($task, 'Revisión'); //Set the status of the child tasks
                }
            }
            elseif($site->status=='Concluído'){
                $site->status = 'Cobro';
                $site->save();

                foreach($site->tasks as $task){
                    $this->new_stat_task($task, 'Cobro'); //Set the status of the child tasks
                }

                foreach($site->files as $file){
                    $this->unblockFile($file);
                }
            }

            $match = true;
            $message = "El estado del sitio ha cambiado a $site->status";
            */
        }
        elseif($action=='close'){
            $site->status = 0 /*'No asignado'*/;
            $site->save();

            foreach($site->tasks as $task){
                $this->new_stat_task($task, 0 /*'No asignado'*/); //Set the status of the child tasks
            }
            /*
            foreach($site->tasks as $task){
                if($task->status!='Concluído'&&$task->status!='No asignado'){
                    $task->status = $site->status;
                    $task->save();

                    foreach($task->activities as $activity){
                        foreach($activity->files as $file){
                            $this->blockFile($file);
                        }
                    }
                }
            }
            */
            foreach($site->files as $file){
                $this->blockFile($file);
            }

            $match = true;
            $message = "Este registro ha sido marcado como No asignado";
        }

        if($match){
            $this->add_event('status changed', $site); //Record an event for the date the status was changed

            Session::flash('message', $message);
            if(Session::has('url'))
                return redirect(Session::get('url'));
            else
                return redirect()->action('SiteController@sites_per_project', ['id' => $site->assignment_id]);
        }
        else{
            /* default redirection if no match is found */
            return redirect()->back();
        }
    }

    function add_event($type, $site)
    {
        $user = Session::get('user');

        $event = new Event;
        $event->user_id = $user->id;
        $event->date = Carbon::now();

        $prev_number = Event::select('number')->where('eventable_id',$site->id)
            ->where('eventable_type','App\Site')->orderBy('number','desc')->first();

        $event->number = $prev_number ? $prev_number->number+1 : 1;

        if($type=='status changed'){
            $event->description = 'Cambio de estado';
            $event->detail = "$user->name ha cambiado el estado del sitio $site->code a ".$site->statuses($site->status).
                ". Este cambio ha sido replicado en sus respectivos items";
        }

        $event->responsible_id = $user->id;
        $event->eventable()->associate($site /*Site::find($site->id)*/);
        $event->save();
    }

    public function control_form($id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $site = Site::find($id);

        return View::make('app.site_control_form', ['site' => $site, 'user' => $user, 'service' => $service]);
    }

    public function config_control($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $site = Site::find($id);

        $v = \Validator::make(Request::all(), [
            'budget'                => 'required|numeric',
            'vehicle_dev_cost'      => 'numeric',
        ],
            [
                'budget.required'          => 'Debe especificar el monto presupuestado para este sitio!',
                'budget.numeric'           => 'El monto presupuestado introducido es inválido!',
                'vehicle_dev_cost.numeric' => 'El valor de depreciación es inválido!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back();
        }

        $site->budget = Request::input('budget');
        $site->vehicle_dev_cost = Request::input('vehicle_dev_cost');

        $site->save();

        Session::flash('message', "Parámetros actualizados correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->action('SiteController@sites_per_project', ['id' => $site->assignment_id]);
    }

    public function refresh_data($id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        /*
        if($id==0){
            $assignments = Assignment::whereNotIn('status', ['Concluído','No asignado'])->get();
            $sites = Site::where('id', '>', 0)->whereNotIn('status', ['Concluído','No asignado'])->get();
        }
        else{
        */
        $assignment = Assignment::find($id);
        //$sites = Site::where('assignment_id',$id)->whereNotIn('status', ['Concluído','No asignado'])->get();

        if(!$assignment){
            Session::flash('message', 'No se encontró la información solicitada. Revise la dirección e intente de nuevo.');
            return redirect()->back();
        }
        
        foreach($assignment->sites as $site)
        {
            foreach($site->tasks as $task){
                $this->refresh_task($task);
            }
            
            $this->refresh_site($site);
            /*
            if($site->status!='Concluído'&&$site->status!='No asignado'){
                $task_percentage = 0;
                $total_quoted = 0;
                $total_executed = 0;
                $count = 0;

                foreach($site->tasks as $task){
                    $task_percentage += (($task->progress/$task->total_expected)*100);
                    $total_quoted += $task->assigned_price;
                    $total_executed += $task->executed_price;
                    $count++;
                }

                $site->percentage_completed = $task_percentage/($count==0 ? 1 : $count);

                //if($site->quote_price==0)
                $site->quote_price = $total_quoted;
                //if($site->assigned_price==0)
                //$site->assigned_price = $total_assigned;

                $site->executed_price = $total_executed;

                foreach ($site->orders as $order) {
                    $site->assigned_price += $order->pivot->assigned_amount;
                }

                $site->save();
            }
            */
        }
        
        $this->refresh_assignment($assignment);
        
        Session::flash('message', 'Datos actualizados correctamente');

        /*
        if($id==0)
            return redirect()->action('SiteController@index');
        else
        */
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->action('SiteController@sites_per_project', ['id' => $id]);
    }
    
    public function fill_code_column()
    {
        $sites = Site::where('code','')->get();
        
        foreach($sites as $site){
            $site->code = 'ST-'.str_pad($site->id, 4, "0", STR_PAD_LEFT).
                date_format($site->created_at,'-y');

            $site->save();
        }
    }

    function get_key_item_values($site)
    {
        if($site->assignment->type=='Fibra óptica'){
            $site->cable_projected = 0;
            $site->cable_executed = 0;
            $site->splice_projected = 0;
            $site->splice_executed = 0;

            foreach($site->tasks as $task){
                if($task->status>0/*'No asignado'*/){
                    if($task->summary_category){
                        if($task->summary_category->cat_name=='fo_cable'){
                            $site->cable_projected += $task->total_expected;
                            $site->cable_executed += $task->progress;
                        }
                        elseif($task->summary_category->cat_name=='fo_splice'){
                            $site->splice_projected += $task->total_expected;
                            $site->splice_executed += $task->progress;
                        }
                    }
                }

                /*
                if (stripos($task->name, 'tendido')!==FALSE&&stripos($task->name, 'cable')!==FALSE){
                    $site->cable_projected += $task->total_expected;
                    $site->cable_executed += $task->progress;
                }
                elseif(stripos($task->name, 'empalme')!==FALSE&&stripos($task->name, 'ejecución')!==FALSE){
                    $site->splice_projected += $task->total_expected;
                    $site->splice_executed += $task->progress;
                }
                */
            }
        }
    }

    function update_dates($site)
    {
        foreach($site->tasks as $task){
            if($task->status!=0&&$task->status!=$task->last_stat()){
                $task->start_date = $site->start_line;
                $task->end_date = $site->deadline;

                $task->save();
            }
        }
    }

    public function expense_report_form($type, $asg_id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $last_stat = count(Site::$status_options) - 1;

        $sites = Site::select('id','name')->where('assignment_id',$asg_id)->whereNotIn('status',[0, $last_stat])->get();

        return View::make('app.site_expense_report_form', ['type' => $type, 'service' => $service,
            'user' => $user, 'sites' => $sites, 'asg_id' => $asg_id]);
    }

    public function expense_report(Request $request, $type, $asg_id)
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

        $last_stat = count(Site::$status_options) - 1;

        $sites = Site::where('assignment_id',$asg_id)->whereNotIn('status',[0, $last_stat]);

        if(!empty($form_data['id']))
            $sites = $sites->where('id', $form_data['id']);

        $sites = $sites->get();

        $assignment = Assignment::find($asg_id);

        $from = Carbon::parse($form_data['from']);
        $to = Carbon::parse($form_data['to']);

        if($type=='stipend'){
            $requests_total = 0;
            $viatic_totals = 0;
            $additionals_total = 0;

            foreach($sites as $site){
                $site->requests_number = 0;
                $site->viatic_total = 0;
                $site->additionals_total = 0;

                foreach($site->stipend_requests as $stipend_request){
                    $stipend_request->date_from = Carbon::parse($stipend_request->date_from);

                    if($stipend_request->date_from->between($from, $to)&&$stipend_request->status=='Completed'){
                        $qty = $stipend_request->sites()->count();

                        $site->viatic_total += $stipend_request->total_amount/$qty;
                        $site->additionals_total += $stipend_request->additional/$qty;

                        $site->requests_number++;

                        $requests_total++;

                        $viatic_totals += $stipend_request->total_amount/$qty;
                        $additionals_total += $stipend_request->additional/$qty;
                    }
                }
            }

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

            $assignment->diff_requests = $assignment->requests_number - $requests_total;
            $assignment->diff_viatic = $assignment->viatic_total - $viatic_totals;
            $assignment->diff_additional = $assignment->additionals_total - $additionals_total;

            return View::make('app.site_expense_report', ['type' => $type, 'service' => $service, 'user' => $user,
                'sites' => $sites, 'from' => $from, 'to' => $to, 'asg_id' => $asg_id, 'assignment' => $assignment,
                'form_data' => $form_data]);
        }
        elseif($type=='stipend_per_tech'){
            $employees = collect();

            $parent = Site::find($form_data['id']);

            foreach($sites as $site){
                foreach($site->stipend_requests as $stipend_request){
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
                        $qty = $stipend_request->sites()->count();

                        $employee->viatic_total += $stipend_request->total_amount/$qty;
                        $employee->additionals_total += $stipend_request->additional/$qty;

                        $employee->requests_number++;
                    }
                }
            }

            return View::make('app.site_expense_report_per_tech', ['type' => $type, 'service' => $service,
                'user' => $user, 'employees' => $employees, 'from' => $from, 'to' => $to, 'asg_id' => $asg_id,
                'assignment' => $assignment, 'form_data' => $form_data, 'parent' => $parent]);
        }
        elseif($type=='stipend_per_tech_empty_asg'){
            $employees = collect();

            foreach($assignment->stipend_requests as $stipend_request){
                if($stipend_request->sites()->count()==0&&$stipend_request->status=='Completed'){
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

            return View::make('app.site_expense_report_per_tech', ['type' => $type, 'service' => $service,
                'user' => $user, 'employees' => $employees, 'from' => $from, 'to' => $to, 'asg_id' => $asg_id,
                'assignment' => $assignment, 'form_data' => $form_data, 'parent' => 0]);
        }
        else{
            Session::flash('message', 'No se reconocen los parámetros necesarios para generar el reporte!');
            return redirect()->back();
        }
    }

    public function generate_from_model($type, $asg_id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $last_stat = count(Site::$status_options) - 1;

        $sites = Site::where('assignment_id',$asg_id)->whereNotIn('status',[0, $last_stat]);

        $from = Input::get('from');
        $to = Input::get('to');
        $id = Input::get('id');
        $client = Input::get('client');
        $area = Input::get('area');
        $assignment_id = Input::get('assignment_id');

        if($id!='')
            $sites = $sites->where('id', $id);
        if($client!='')
            $sites = $sites->where('client', $client);
        if($area!='')
            $sites = $sites->where('type', $area);
        if($assignment_id!='')
            $sites = $sites->where('assignment_id', $assignment_id);

        $sites = $sites->get();

        $assignment = Assignment::find($asg_id);

        $from = Carbon::parse($from);
        $to = Carbon::parse($to);

        if($type=='stipend'){
            $requests_total = 0;
            $viatic_totals = 0;
            $additionals_total = 0;

            foreach($sites as $site){
                $site->requests_number = 0;
                $site->viatic_total = 0;
                $site->additionals_total = 0;

                foreach($site->stipend_requests as $stipend_request){
                    $stipend_request->date_from = Carbon::parse($stipend_request->date_from);

                    if($stipend_request->date_from->between($from, $to)&&$stipend_request->status=='Completed'){
                        $qty = $stipend_request->sites()->count();

                        $site->viatic_total += $stipend_request->total_amount/$qty;
                        $site->additionals_total += $stipend_request->additional/$qty;

                        $site->requests_number++;

                        $requests_total++;

                        $viatic_totals += $stipend_request->total_amount/$qty;
                        $additionals_total += $stipend_request->additional/$qty;
                    }
                }
            }

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

            $assignment->diff_requests = $assignment->requests_number - $requests_total;
            $assignment->diff_viatic = $assignment->viatic_total - $viatic_totals;
            $assignment->diff_additional = $assignment->additionals_total - $additionals_total;

            $sheet_content = collect();
            $excel_name = 'Reporte de gastos - sitios';
            $sheet_name = 'Sitios';

            foreach($sites as $site)
            {
                $sheet_content->prepend(
                    [   'Sitio'         => $site->name,
                        '# Solicitudes' => $site->requests_number,
                        'Viaticos [Bs]' => number_format($site->viatic_total,2),
                        'Adicionales [Bs]'   => number_format($site->additionals_total,2),
                        'Total [Bs]'    => number_format($site->viatic_total+$site->additionals_total,2),
                    ]);
            }

            $sheet_content->prepend(
                [   'Sitio'         => 'Solicitudes sin sitio',
                    '# Solicitudes' => $assignment->diff_requests,
                    'Viaticos [Bs]' => number_format($assignment->diff_viatic,2),
                    'Adicionales [Bs]'   => number_format($assignment->diff_additional,2),
                    'Total [Bs]'    => number_format($assignment->diff_viatic+$assignment->diff_additional,2),
                ]);

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }
        elseif($type=='stipend_per_tech'){
            $employees = collect();

            foreach($sites as $site){
                foreach($site->stipend_requests as $stipend_request){
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
                        $qty = $stipend_request->sites()->count();

                        $employee->viatic_total += $stipend_request->total_amount/$qty;
                        $employee->additionals_total += $stipend_request->additional/$qty;

                        $employee->requests_number++;
                    }
                }
            }

            $sheet_content = collect();
            $excel_name = 'Reporte de gastos - sitios por tecnico';
            $sheet_name = 'Gastos por tecnico-sitio';

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
        elseif($type=='stipend_per_tech_empty_asg'){
            $employees = collect();

            foreach($assignment->stipend_requests as $stipend_request){
                if($stipend_request->sites()->count()==0&&$stipend_request->status=='Completed'){
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
            $excel_name = 'Reporte de gastos - sitios por tecnico';
            $sheet_name = 'Gastos por tecnico-sitio';

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
            return redirect()->back();
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

    public function set_global_dates_form($id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $assignment = Assignment::find($id);

        if(!$assignment){
            Session::flash('message', 'No se encontró la asignación solicitada!, revise la dirección e intente de nuevo por favor');
            return redirect()->back();
        }

        $interval = Input::get('interval');
        $mode = Input::get('mode');

        if($interval=='exec'){
            $assignment->start_date = Carbon::parse($assignment->start_date)->format('Y-m-d');
            $assignment->end_date = Carbon::parse($assignment->end_date)->format('Y-m-d');
        }
        elseif($interval=='asg'){
            $assignment->start_line = Carbon::parse($assignment->start_line)->format('Y-m-d');
            $assignment->deadline = Carbon::parse($assignment->deadline)->format('Y-m-d');
        }

        return View::make('app.site_set_dates_form', ['assignment' => $assignment, 'interval' => $interval,
            'mode' => $mode, 'user' => $user, 'service' => $service]);
    }

    public function set_global_dates($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $form_data = Request::all();

        if($form_data['mode']=='set'){
            $form_data['to'] = $form_data['to']!='' ? $form_data['to'].' 23:59:59' : '';

            $v = \Validator::make($form_data, [
                'from'                  => 'required|date',
                'to'                    => 'required|date|after:from'
            ],
                [
                    'required'                  => 'Este campo es obligatorio!',
                    'date'                      => 'La fecha introducida es inválida!',
                    'after'                     => 'La fecha "Hasta" debe ser posterior a la fecha "Desde"!',
                ]
            );
        }
        else{
            $v = \Validator::make($form_data, [
                'diff_days'                  => 'required|numeric',
            ],
                [
                    'required'                  => 'Este campo es obligatorio!',
                    'numeric'                   => 'Debe introducir un número entero!',
                ]
            );
        }

        if ($v->fails())
        {
            Session::flash('message', 'Sucedió un error al enviar el formulario!');
            return redirect()->back()->withErrors($v)->withInput();
        }

        $assignment = Assignment::find($id);

        if(!$assignment){
            Session::flash('message', 'No se encontró la asignación referenciada! revise la dirección e intente de nuevo por favor');
            return redirect()->back();
        }

        $mode = $form_data['mode'];
        $interval = $form_data['interval'];

        if($mode=='set'){
            $from = $form_data['from'];
            $to = $form_data['to'];

            if($interval=='exec'){
                $assignment->start_date = $from;
                $assignment->end_date = $to;

                $assignment->save();

                foreach($assignment->sites as $site){
                    $site->start_date = $from;
                    $site->end_date = $to;

                    $site->save();
                }
            }
            elseif($interval=='asg'){
                $assignment->start_line = $from;
                $assignment->deadline = $to;

                $assignment->save();

                foreach($assignment->sites as $site){
                    $site->start_line = $from;
                    $site->deadline = $to;

                    $site->save();
                }
            }
        }
        elseif($mode=='add'){
            $diff_days = $form_data['diff_days'];

            if($interval=='exec'){
                if($assignment->start_date!='0000-00-00 00:00:00')
                    $assignment->start_date = Carbon::parse($assignment->start_date)->addDays($diff_days);
                if($assignment->edn_date!='0000-00-00 00:00:00')
                    $assignment->end_date = Carbon::parse($assignment->end_date)->addDays($diff_days);

                $assignment->save();

                foreach($assignment->sites as $site){
                    if($site->start_date!='0000-00-00 00:00:00')
                        $site->start_date = Carbon::parse($site->start_date)->addDays($diff_days);
                    if($site->end_date!='0000-00-00 00:00:00')
                        $site->end_date = Carbon::parse($site->end_date)->addDays($diff_days);

                    $site->save();
                }
            }
            elseif($interval=='asg'){
                if($assignment->start_line!='0000-00-00 00:00:00')
                    $assignment->start_line = Carbon::parse($assignment->start_line)->addDays($diff_days);
                if($assignment->deadline!='0000-00-00 00:00:00')
                    $assignment->deadline = Carbon::parse($assignment->deadline)->addDays($diff_days);

                $assignment->save();

                foreach($assignment->sites as $site){
                    if($site->start_line!='0000-00-00 00:00:00')
                        $site->start_line = Carbon::parse($site->start_line)->addDays($diff_days);
                    if($site->deadline!='0000-00-00 00:00:00')
                        $site->deadline = Carbon::parse($site->deadline)->addDays($diff_days);

                    $site->save();
                }
            }
        }
        elseif($mode=='sub'){
            $diff_days = $form_data['diff_days'];

            if($interval=='exec'){
                if($assignment->start_date!='0000-00-00 00:00:00')
                    $assignment->start_date = Carbon::parse($assignment->start_date)->subDays($diff_days);
                if($assignment->end_date!='0000-00-00 00:00:00')
                    $assignment->end_date = Carbon::parse($assignment->end_date)->subDays($diff_days);

                $assignment->save();

                foreach($assignment->sites as $site){
                    if($site->start_date!='0000-00-00 00:00:00')
                        $site->start_date = Carbon::parse($site->start_date)->subDays($diff_days);
                    if($site->end_Date!='0000-00-00 00:00:00')
                        $site->end_date = Carbon::parse($site->end_date)->subDays($diff_days);

                    $site->save();
                }
            }
            elseif($interval=='asg'){
                if($assignment->start_line!='0000-00-00 00:00:00')
                    $assignment->start_line = Carbon::parse($assignment->start_line)->subDays($diff_days);
                if($assignment->deadline!='0000-00-00 00:00:00')
                    $assignment->deadline = Carbon::parse($assignment->deadline)->subDays($diff_days);

                $assignment->save();

                foreach($assignment->sites as $site){
                    if($site->start_line!='0000-00-00 00:00:00')
                        $site->start_line = Carbon::parse($site->start_line)->subDays($diff_days);
                    if($site->deadline!='0000-00-00 00:00:00')
                        $site->deadline = Carbon::parse($site->deadline)->subDays($diff_days);

                    $site->save();
                }
            }
        }

        Session::flash('message', "Fechas actualizadas correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->action('SiteController@sites_per_project', ['id' => $assignment->id]);
    }
}
