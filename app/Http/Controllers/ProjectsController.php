<?php

namespace App\Http\Controllers;

use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Session;
use View;
use Mail;
use Input;
use Exception;

use App\Assignment;
use App\ClientSession;
use App\Contact;
use App\Email;
use App\Employee;
use App\Event;
use App\File;
use App\Project;
use App\StipendRequest;
use App\Tender;
use App\User;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use PHPExcel_Worksheet_Drawing;
use App\Http\Traits\FilesTrait;
use App\Http\Traits\UserTrait;

class ProjectsController extends Controller
{
    use FilesTrait;
    use UserTrait;
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

        /* redirect flash message to another controller
        $message = Session::get('message');
        Session::flash('message', " $message ");
        return redirect()->route('assignment.index');
        */

        Session::put('service', 'project');
        
        $service = Session::get('service');
        
        $this->trackService($user, $service);

        //if($user->priv_level>=2||($user->priv_level==1&&$user->area=='Gerencia Tecnica')){

            $mode = Input::get('mode');

            if($mode=='rb')
                $option = 'Radiobases';
            elseif($mode=='fo')
                $option = 'Fibra óptica';

            if($mode=='arch'){
                $projects = Project::where('status','<>','Activo');
            }
            elseif(!empty($option)){
                $projects = Project::where('type',$option)->where('status', 'Activo');
                    //->where('valid_to', '>=', Carbon::now()->subMonths(3))
            }
            else{
                $projects = Project::where('status', 'Activo');
                    //->where('valid_to', '>=', Carbon::now()->subMonths(3))
            }

            $projects = $projects->orderBy('id', 'desc')->paginate(20);
        /*}
        else{
            Session::flash('message', "Usted no tiene permiso para ver la página solicitada!");
            return redirect()->back();
        }*/

        $projects->ending = 0;

        foreach($projects as $project){

            if($project->application_deadline!='0000-00-00 00:00:00')
                $project->application_deadline = Carbon::parse($project->application_deadline)->setTime(0,0,0);
                    //->hour(0)->minute(0)->second(0);

            $project->valid_to = Carbon::parse($project->valid_to)->hour(0)->minute(0)->second(0);

            foreach($project->guarantees as $guarantee){
                $guarantee->expiration_date = Carbon::parse($guarantee->expiration_date)->setTime(0,0,0);
                    //->hour(0)->minute(0)->second(0);
                $guarantee->start_date = Carbon::parse($guarantee->start_date)->setTime(0,0,0);
                    //->hour(0)->minute(0)->second(0);
            }

            if($project->status=='Activo'&&($project->user_id==$user->id||$user->priv_level>=3)){
                if(Carbon::now()->diffInDays($project->valid_to,false)<=5&&
                    Carbon::now()->diffInDays($project->valid_to,false)>=0){
                    $projects->ending++;
                }
            }
        }

        return View::make('app.project_brief', ['projects' => $projects, 'service' => $service, 'user' => $user]);
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
        
        $clients = Project::select('client')->where('client', '<>', '')->groupBy('client')->get();
        $awards = Project::select('award')->where('award', '<>', '')->groupBy('award')->get();

        $current_date = Carbon::now()->format('Y-m-d');

        return View::make('app.project_form', ['project' => 0, 'clients' => $clients, 'service' => $service,
            'current_date' => $current_date, 'user' => $user, 'awards' => $awards, 'tender' => 0]);
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
        $form_data['valid_to'] = $form_data['valid_to']!='' ? $form_data['valid_to'].' 23:59:59' : '';

        $v = \Validator::make($form_data, [
            'name'                  => 'required',
            'type'                  => 'required',
            'client'                => 'required',
            'other_client'          => 'required_if:client,Otro',
            'contact_name'          => 'required|regex:/^[\pL\s\-]+$/u',
            'award'                 => 'required',
            'other_award'           => 'required_if:award,Otro',
            //'application_deadline'  => 'date',
            'valid_from'            => 'required|date',
            'valid_to'              => 'date|after:valid_from',
        ],
            [
                'name.required'             => 'Debe especificar el nombre del contrato!',
                'type.required'             => 'Debe especificar el tipo de trabajo!',
                'client.required'           => 'Debe especificar un cliente!',
                'other_client.required_if'  => 'Debe especificar un cliente!',
                'contact_name.required'     => 'Debe especificar el nombre de la persona de contacto del cliente!',
                'contact_name.regex'        => 'El nombre de la persona de contacto del cliente solo puede contener
                                                letras y espacios!',
                'award.required'            => 'Debe especificar el tipo de adjudicación!',
                'other_award.required_if'   => 'Debe especificar el tipo de adjudicación!',
                //'application_deadline.date' => 'La fecha de plazo para presentación a convocatoria es incorrecta!',
                'valid_from.required'       => 'Debe indicar la fecha desde la que es/será válido el contrato!',
                'valid_from.date'           => 'Introduzca una fecha válida en el campo "Desde"!',
                'valid_to.date'             => 'Introduzca una fecha válida en el campo "Hasta"!',
                'valid_to.after'            => 'La fecha "Hasta" no puede ser anterior a la fecha "Desde"!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $project = new Project($form_data);

        $project->client = $project->client=="Otro" ? Request::input('other_client') : $project->client;
        $project->award = $project->award=='Otro' ? Request::input('other_award') : $project->award;

        /*
        if($project->award=='Licitación'){
            $project->application_deadline = empty($project->application_deadline) ?
                (empty(Request::input('days_to_deadline')) ? Carbon::now()->addDays(10) :
                    Carbon::now()->addDays(Request::input('days_to_deadline'))) : $project->application_deadline;
        }
        */

        if(empty($project->valid_to))
        {
            $valid_days = Request::input('valid_days');

            if(!empty($valid_days)&&$valid_days>0)
                $project->valid_to = Carbon::parse($project->valid_from)->addDays($valid_days);
            else{
                Session::flash('message', 'Debe indicar la fecha de fin de validez del contrato o indicar una cantidad de días!');
                return redirect()->back();
            }
        }

        $project->user_id = $user->id;

        /* Get the id of the contact querying the name given */
        $contact_name = Request::input('contact_name');

        if($contact_name!=''){
            $contact = Contact::select('id')->where('name',$contact_name)->first();

            if($contact==''){
                /* Create a new contact record if no match was found */
                $contact = new Contact;
                $contact->name = $contact_name;
                $contact->company = $project->client;
                $contact->save();
            }

            $project->contact_id = $contact->id;
        }

        //$project->valid_from = Carbon::now();
        //$project->valid_to = Carbon::now()->addMonths(3);

        $current_date = Carbon::now();
        $valid_from = Carbon::parse($project->valid_from);
        $valid_to = Carbon::parse($project->valid_to);

        if($current_date->between($valid_from,$valid_to))
            $project->status = 'Activo';
        else
            $project->status = 'Finalizado';

        $project->save();

        $this->fill_code_column();

        /* Send email notification */
        $this->send_email_notification($project, 'store');

        Session::flash('message', "Se agregó un contrato al sistema");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('project.index');
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

        $project = Project::find($id);

        //$project->application_deadline = Carbon::parse($project->application_deadline)->format('d/m/Y');
        $project->valid_from = Carbon::parse($project->valid_from)->format('d-m-Y');
        $project->valid_to = Carbon::parse($project->valid_to)->format('d-m-Y');

        return View::make('app.project_info', ['project' => $project, 'service' => $service, 'user' => $user]);
    }

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

        $project = Project::find($id);

        $project->valid_from = Carbon::parse($project->valid_from)->format('Y-m-d');
        $project->valid_to = Carbon::parse($project->valid_to)->format('Y-m-d');

        $clients = Project::select('client')->where('client', '<>', '')->groupBy('client')->get();
        $awards = Project::select('award')->where('award', '<>', '')->groupBy('award')->get();

        $current_date = Carbon::now()->format('Y-m-d');

        return View::make('app.project_form', ['project' => $project, 'clients' => $clients, 'service' => $service,
            'current_date' => $current_date, 'user' => $user, 'awards' => $awards, 'tender' => 0]);
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
        $form_data['valid_to'] = $form_data['valid_to']!='' ? $form_data['valid_to'].' 23:59:59' : '';

        $v = \Validator::make($form_data, [
            'name'                  => 'required',
            'type'                  => 'required',
            'client'                => 'required',
            'other_client'          => 'required_if:client,Otro',
            'contact_name'          => 'required|regex:/^[\pL\s\-]+$/u',
            'award'                 => 'required',
            'other_award'           => 'required_if:award,Otro',
            //'application_deadline'  => 'date',
            'valid_from'            => 'required|date',
            'valid_to'              => 'date|after:valid_from',
        ],
            [
                'name.required'             => 'Debe especificar el nombre del proyecto!',
                'type.required'             => 'Debe especificar el tipo de trabajo!',
                'client.required'           => 'Debe especificar un cliente!',
                'other_client.required_if'  => 'Debe especificar un cliente!',
                'contact_name.required'     => 'Debe especificar el nombre de la persona de contacto del cliente!',
                'contact_name.regex'        => 'El nombre de la persona de contacto del cliente solo puede contener
                                                letras y espacios!',
                'award.required'            => 'Debe especificar el tipo de adjudicación!',
                'other_award.required_if'   => 'Debe especificar el tipo de adjudicación!',
                //'application_deadline.date' => 'La fecha de plazo para presentación a convocatoria es incorrecta!',
                'valid_from.required'       => 'Debe indicar la fecha desde la que es/será válido el contrato!',
                'valid_from.date'           => 'Introduzca una fecha válida en el campo "Desde"!',
                'valid_to.date'             => 'Introduzca una fecha válida en el campo "Hasta"!',
                'valid_to.after'            => 'La fecha "Hasta" no puede ser anterior a la fecha "Desde"!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $project = Project::find($id);

        $project->fill($form_data);

        $project->client = $project->client=="Otro" ? Request::input('other_client') : $project->client;
        $project->award = $project->award=='Otro' ? Request::input('other_award') : $project->award;

        /*
        if($project->award=='Licitación') {
            $project->application_deadline = empty($project->application_deadline) ?
                (empty(Request::input('days_to_deadline')) ? Carbon::now()->addDays(10) :
                    Carbon::now()->addDays(Request::input('days_to_deadline'))) : $project->application_deadline;
        }
        */

        if(empty($project->valid_to))
        {
            $valid_days = Request::input('valid_days');

            if(!empty($valid_days)&&$valid_days>0)
                $project->valid_to = Carbon::parse($project->valid_from)->addDays($valid_days);
            else{
                Session::flash('message', 'Debe indicar la fecha de fin de validez del contrato o indicar una cantidad de días!');
                return redirect()->back();
            }
        }

        /* Get the id of the contact querying the name given */
        $contact_name = Request::input('contact_name');

        if($contact_name!=''){
            $contact = Contact::select('id')->where('name',$contact_name)->first();

            if($contact==''){
                /* Create a new contact record if no match was found */
                $contact = new Contact;
                $contact->name = $contact_name;
                $contact->company = $project->client;
                $contact->save();
            }

            $project->contact_id = $contact->id;
        }
        
        //$project->valid_to = Carbon::now()->addMonths(3);

        $current_date = Carbon::now();
        $valid_from = Carbon::parse($project->valid_from);
        $valid_to = Carbon::parse($project->valid_to);

        if($current_date->between($valid_from,$valid_to))
            $project->status = 'Activo';
        else
            $project->status = 'Finalizado';

        $project->save();

        Session::flash('message', "Registro modificado correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('project.index');
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

        $project = Project::find($id);

        if($project){
            if($project->assignments->count()>=0){
                Session::flash('message', "Error al ejecutar el borrado, no puede eliminar un contrato que ya cuenta
                    con asignaciones!");
                return redirect()->back();
            }

            $file_error = false;

            foreach($project->files as $file){
                $file_error = $this->removeFile($file);
                
                if($file_error)
                    break;
            }
            
            if (!$file_error) {
                $project->delete();

                Session::flash('message', "El registro fue eliminado del sistema");
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->route('project.index');
            }
            else {
                Session::flash('message', "Error al borrar el registro, por favor consulte al administrador. $file_error");
                return redirect()->back();
            }
        }
        else {
            Session::flash('message', "Error al ejecutar el borrado, no se encontró el registro solicitado!");
            return redirect()->back();
        }
    }

    public function close_record($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $project = Project::find($id);

        $project->valid_to = Carbon::now();
        $project->status = 'No asignado';
        $project->save();

        foreach($project->files as $file){
            $this->blockFile($file);
        }
        
        Session::flash('message', "El contrato ha sido marcado como 'No asignado'");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('project.index');
    }

    /*
    public function mark_application_done($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $project = Project::find($id);

        $project->applied = 1;
        $project->application_details .= '\nSe envió documentación de aplicación a convocatoria en fecha '.
            Carbon::now()->format('d/m/Y');
        $project->save();

        Session::flash('message', "Se registró la aplicación a la convocatoria de la licitación correctamente.");
        return redirect()->route('project.index');
    }
    */

    public function add_assignment($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $project = Project::find($id);

        if(!$project){
            Session::flash('message', 'Ocurrió un error al recuperar la información del contrato,
                                                intente de nuevo por favor.');
            return redirect()->back();
        }

        $assignment = new Assignment;

        $assignment->name = $project->name;
        $assignment->project_id = $project->id;
        $assignment->client = $project->client;
        $assignment->type = $project->type;
        $assignment->type_award = $project->award;
        $assignment->status = 1 /* Initial status */;

        $assignment->quote_from = Carbon::now();
        $assignment->quote_to = Carbon::now()->addDays(7);

        $assignment->user_id = $user->id;
        $assignment->contact_id = $project->contact_id;

        $assignment->save();
        // Save the first time to get an id
        $assignment->code = 'ASG-'.str_pad($assignment->id, 4, "0", STR_PAD_LEFT).date('-y');

        $assignment->save();
        
        /* A new event is recorded to register the creation of the assignment */
        $event = new Event;
        $event->user_id = $user->id;
        $event->date = Carbon::now();
        $event->number = 1;
        $event->description = 'Nueva asignación';
        $event->detail = 'Se agrega la asignación: '.$assignment->name.' perteneciente al contrato: '.$project->name.
            ' al sistema';
        $event->responsible_id = $user->id;
        $event->eventable()->associate($assignment /*Assignment::find($assignment->id)*/);
        $event->save();
        
        /* Send a notification email to the head of the Technical department */
        $this->send_email_notification($assignment, 'add_assignment');

        Session::flash('message', "Se creó la asignación y se almacenó en el sistema correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('assignment.index');
    }

    public function send_email_notification($model, $mode)
    {
        $user = Session::get('user');

        if($mode=='store'){
            $recipient = User::where('area','Gerencia General')->where('priv_level',3)->first();
            $carbon_copies = User::select('email')
                ->where(function ($query) {$query->where('area','Gerencia General')->where('priv_level',2);})
                ->orwhere(function ($query) {
                    //$query->where('area','Gerencia Tecnica')->where('priv_level','>=',3);
                    $query->where('priv_level','>=',3);
                })
                ->get();

            $emails = array();
            foreach($carbon_copies as $carbon_copy){
                if($carbon_copy->email)
                    $emails[] = $carbon_copy->email;
            }

            $data = array('recipient' => $recipient, 'project' => $model);

            $view = View::make('emails.project_added', $data/*['recipient' => $recipient, 'project' => $project]*/);
            $content = (string) $view;
            $success = 1;
            try {
                Mail::send('emails.project_added', $data, function($message) use($recipient,$user,$emails) {
                    $message->to($recipient->email, $recipient->name)
                        ->cc($emails)
                        ->subject('Nuevo contrato agregado al sistema');
                    $message->from('postmaster@gerteabros.com', 'postmaster@gerteabros.com');
                });
            } catch (Exception $ex) {
                $success = 0;
            }

            $email = new Email;
            $email->sent_by = 'postmaster@gerteabros.com';
            $email->sent_to = $recipient->email;
            $email->sent_cc = implode(', ', $emails);
            $email->subject = 'Nuevo proyecto agregado al sistema';
            $email->content = $content;
            $email->success = $success;
            $email->save();
        }
        elseif($mode=='add_assignment'){
            $assignment = $model;

            $recipient = User::where('area','Gerencia Tecnica')->where('priv_level',3)->first();
            $pm_assigned = $assignment->resp_id!=0 ? User::find($assignment->resp_id) : 0;

            $data = array('recipient' => $recipient, 'pm_assigned' => $pm_assigned, 'assignment' => $assignment);
            $subject = 'Nueva asignación agregada al sistema';

            $view = View::make('emails.assig_created', $data /*['recipient' => $recipient, 'pm_assigned' => $pm_assigned,
            'assignment' => $assignment]*/);
            $content = (string) $view;
            $success = 1;

            try {
                Mail::send('emails.assig_created', $data, function($message) use($recipient, $user, $subject) {
                    $message->to($recipient->email, $recipient->name)
                        ->cc($user->email, $user->name)
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

    public function fill_code_column()
    {
        $projects = Project::where('code','')->get();
        
        foreach($projects as $project){
            $project->code = 'PRJ-'.str_pad($project->id, 3, "0",
                    STR_PAD_LEFT).date_format($project->created_at,'y');
            
            $project->save();
        }
    }

    public function expense_report_form($type)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $areas = Project::select('type')->where('type', '<>', '')->groupBy('type')->get();
        $clients = Project::select('client')->where('client','<>','')->groupBy('client')->get();

        $projects = Project::select('id','name')->where('status','Activo')->get();

        return View::make('app.project_expense_report_form', ['type' => $type, 'service' => $service,
            'user' => $user, 'areas' => $areas, 'clients' => $clients, 'projects' => $projects]);
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

        $projects = Project::where('status','Activo');

        if(!empty($form_data['id']))
            $projects = $projects->where('id', $form_data['id']);
        if(!empty($form_data['client']))
            $projects = $projects->where('client', $form_data['client']);
        if(!empty($form_data['type']))
            $projects = $projects->where('type', $form_data['type']);

        $projects = $projects->get();

        $from = Carbon::parse($form_data['from']);
        $to = Carbon::parse($form_data['to']);

        if($type=='stipend'){
            foreach($projects as $project){
                $project->requests_number = 0;
                $project->viatic_total = 0;
                $project->additionals_total = 0;

                foreach($project->assignments as $assignment){
                    foreach($assignment->stipend_requests as $stipend_request){
                        $stipend_request->date_from = Carbon::parse($stipend_request->date_from);

                        if($stipend_request->date_from->between($from, $to)&&$stipend_request->status=='Completed'){
                            $project->viatic_total += $stipend_request->total_amount;
                            $project->additionals_total += $stipend_request->additional;

                            $project->requests_number++;
                        }
                    }
                }
            }

            return View::make('app.project_expense_report', ['type' => $type, 'service' => $service, 'user' => $user,
                'projects' => $projects, 'from' => $from, 'to' => $to, 'form_data' => $form_data]);
        }
        elseif($type=='stipend_per_tech'){
            //$employees = Employee::select('id','first_name','last_name')->get();
            $employees = collect();

            $parent = Project::find($form_data['id']);

            foreach($projects as $project){
                //foreach($employees as $employee){
                    foreach($project->assignments as $assignment){
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

                            //if($stipend_request->employee_id==$employee->id){
                                $stipend_request->date_from = Carbon::parse($stipend_request->date_from);

                                if($stipend_request->date_from->between($from, $to)&&$stipend_request->status=='Completed'){
                                    $employee->viatic_total += $stipend_request->total_amount;
                                    $employee->additionals_total += $stipend_request->additional;

                                    $employee->requests_number++;
                                }
                            //}
                        }
                    }
                //}
            }

            return View::make('app.project_expense_report_per_tech', ['type' => $type, 'service' => $service, 'user' => $user,
                'employees' => $employees, 'from' => $from, 'to' => $to, 'form_data' => $form_data, 'parent' => $parent]);
        }
        else{
            Session::flash('message', 'No se reconocen los parámetros necesarios para generar el reporte!');
            if(Session::has('url'))
                return redirect(Session::get('url'));
            else
                return redirect()->route('project.index');
        }
    }

    public function generate_from_model($type)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $projects = Project::where('status','Activo');

        $from = Input::get('from');
        $to = Input::get('to');
        $id = Input::get('id');
        $client = Input::get('client');
        $area = Input::get('area');

        if($id!='')
            $projects = $projects->where('id', $id);
        if($client!='')
            $projects = $projects->where('client', $client);
        if($area!='')
            $projects = $projects->where('type', $area);

        $projects = $projects->get();

        $from = Carbon::parse($from);
        $to = Carbon::parse($to);

        if($type=='stipend'){
            foreach($projects as $project){
                $project->requests_number = 0;
                $project->viatic_total = 0;
                $project->additionals_total = 0;

                foreach($project->assignments as $assignment){
                    foreach($assignment->stipend_requests as $stipend_request){
                        $stipend_request->date_from = Carbon::parse($stipend_request->date_from);

                        if($stipend_request->date_from->between($from, $to)&&$stipend_request->status=='Completed'){
                            $project->viatic_total += $stipend_request->total_amount;
                            $project->additionals_total += $stipend_request->additional;

                            $project->requests_number++;
                        }
                    }
                }
            }

            $sheet_content = collect();
            $excel_name = 'Reporte de gastos - proyectos';
            $sheet_name = 'Proyectos';

            foreach($projects as $project)
            {
                $sheet_content->prepend(
                    [   'Proyecto'      => $project->name,
                        '# Solicitudes' => $project->requests_number,
                        'Viaticos [Bs]' => number_format($project->viatic_total,2),
                        'Adicionales [Bs]'   => number_format($project->additionals_total,2),
                        'Total [Bs]'    => number_format($project->viatic_total+$project->additionals_total,2),
                    ]);
            }

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }
        elseif($type=='stipend_per_tech'){
            $employees = collect();

            foreach($projects as $project){
                foreach($project->assignments as $assignment){
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
            }

            $sheet_content = collect();
            $excel_name = 'Reporte de gastos - proyectos por tecnico';
            $sheet_name = 'Gastos por tecnico-proyecto';

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
                return redirect()->route('project.index');
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

    public function cron_end()
    {
        $projects = Project::where('status', 'Activo')->where('valid_to','<',Carbon::now())->get();

        $count = 0;

        foreach($projects as $project){
            $project->status = 'Finalizado';
            $project->save();

            foreach($project->files as $file){
                $this->blockFile($file);
            }

            $count++;
        }

        return $count;
    }

    // Old index code for a previous version of the implementation
    /*
        $current_user = $user;
        if($current_user->priv_level>=3){
            $projects = Project::where('id', '>', 0)->orderBy('id', 'desc')->paginate(20);
            $next_step = ['Subir documento de asignación',
                'Subir cotización',
                'Subir pedido de compra',
                'Subir pedido de compra firmado',
                'Subir planilla de cantidades',
                'Subir planilla de cantidades firmada',
                'Subir planilla económica',
                'Subir planilla económica firmada',
                'Subir certificado de control de calidad',
                'Agregar datos de factura',
                'Dar por concluido el proyecto',
                'Concluído'
            ];
        }
        elseif($current_user->area=='Gerencia General'){
            if($current_user->priv_level==1){
                $projects = Project::whereBetween('status', [0,4])->orderBy('id', 'desc')->paginate(20);
                $next_step = ['Subir documento de asignación',
                    ' ',
                    'Subir pedido de compra',
                    'Subir pedido de compra firmado',
                    ' ',
                ];
            }
            elseif($current_user->priv_level==2){
                $projects = Project::whereIn('status', [0,1,2,3,4,9,10])->orderBy('id', 'desc')->paginate(20);
                $next_step = ['Subir documento de asignación',
                    ' ',
                    'Subir pedido de compra',
                    'Subir pedido de compra firmado',
                    ' ',
                    ' ',
                    ' ',
                    ' ',
                    ' ',
                    'Agregar datos de factura',
                    ' ',
                ];
            }
        }
        elseif($current_user->area=='Gerencia Tecnica'&&$current_user->priv_level==2){
            $projects = Project::whereIn('status',[1,2,4,5,6,7,8,9])->orderBy('id','desc')->paginate(20);
            $next_step = [' ',
                'Subir cotización',
                ' ',
                ' ',
                'Subir planilla de cantidades',
                'Subir planilla de cantidades firmada',
                'Subir planilla económica',
                'Subir planilla económica firmada',
                'Subir certificado de control de calidad',
                ' ',
            ];
        }

        $files = File::join('projects', 'files.imageable_id', '=', 'projects.id')
            ->select('files.id', 'files.name', 'files.imageable_id', 'files.created_at')
            ->where('imageable_type', '=', 'App\Project')
            ->get();

        $etapa = ['Proyecto nuevo',
            'Documento de asignación recibido',
            'Cotización enviada',
            'Pedido de compra recibido',
            'Pedido de compra firmado',
            'Planilla de cantidades enviada',
            'Planilla de cantidades firmada',
            'Planilla económica enviada',
            'Planilla económica firmada',
            'Certificado de Control de calidad recibido',
            'Facturado',
            'Concluido',
            'Proyecto no asignado'
        ];

        foreach($projects as $project)
        {
            $project->ini_date = Carbon::parse($project->ini_date);
        }
        foreach($files as $file)
        {
            $file->created_at = Carbon::parse($file->created_at)->hour(0)->minute(0)->second(0);
        }

        $current_date = Carbon::now();
        $current_date->hour = 0;
        $current_date->minute = 0;
        $current_date->second = 0;
        //$current_date->setTimezone('America/La_Paz');

        $service = Session::get('service');

        return View::make('app.project_brief', ['projects' => $projects, 'files' => $files, 'service' => $service,
            'current_date' => $current_date, 'etapa' => $etapa, 'next_step' => $next_step, 'user' => $user]);
        */
    
    /*
    public function show_economic_resume($id)
    {
        //return redirect()->back();

        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $project = Project::find($id);

        return View::make('app.project_economic_resume', ['project' => $project, 'service' => $service, 'user' => $user]);
    }

    public function edit_action($id,$flag)
    {
        //return redirect()->back();

        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $project = Project::find($id);

        $project->ini_date = Carbon::parse($project->ini_date)->format('Y-m-d');
        $project->bill_date = Carbon::parse($project->bill_date)->format('Y-m-d');

        $clients = Project::select('client')->where('client', '<>', '')->groupBy('client')->get();

        return View::make('app.project_form', ['project' => $project, 'clients' => $clients,
            'action_flag' => $flag, 'service' => $service, 'user' => $user]);
    }
    */
}
