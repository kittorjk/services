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
use App\Tender;
use App\Project;
use App\File;
use App\User;
use App\Contact;
use App\Email;
use App\Event;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use PHPExcel_Worksheet_Drawing;
use App\Http\Traits\FilesTrait;

class TenderController extends Controller
{
    use FilesTrait;
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

        $mode = Input::get('mode');

        if($mode=='asg')
            $tenders = Tender::where('status','Asignado');
        elseif($mode=='nsg')
            $tenders = Tender::where('status','No asignado');
        elseif($mode=='np')
            $tenders = Tender::where('status','No presentado');
        else
            $tenders = Tender::where('status', 'Activo')->orwhere('status','=','Documentación enviada')
                ->orwhere('status','=','Asignado');

        $tenders = $tenders->orderBy('id', 'desc')->paginate(20);

        $tenders->ending = 0;
        $tenders->ended = 0;

        foreach($tenders as $tender){
            if($tender->application_deadline!='0000-00-00 00:00:00'){
                $tender->application_deadline = Carbon::parse($tender->application_deadline)
                    ->setTime(0,0,0);

                if($tender->applied==0&&$tender->status=='Activo'){
                    if(Carbon::now()->diffInDays($tender->application_deadline,false)<=5&&
                        Carbon::now()->diffInDays($tender->application_deadline,false)>=0){
                        $tenders->ending++;
                    }
                    elseif((Carbon::now()->diffInDays($tender->application_deadline,false)<0)){
                        $tenders->ended++;
                    }
                }
            }
        }

        return View::make('app.tender_brief', ['tenders' => $tenders, 'service' => $service, 'user' => $user]);
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

        $clients = Tender::select('client')->where('client', '<>', '')->groupBy('client')->get();

        return View::make('app.tender_form', ['tender' => 0, 'clients' => $clients, 'service' => $service,
            'user' => $user]);
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

        $v = \Validator::make($form_data, [
            'name'                  => 'required',
            'area'                  => 'required',
            'client'                => 'required',
            'other_client'          => 'required_if:client,Otro',
            'contact_name'          => 'regex:/^[\pL\s\-]+$/u',
            'application_deadline'  => 'date',
        ],
            [
                'name.required'             => 'Debe especificar el nombre o identificación de la licitación!',
                'area.required'             => 'Debe especificar el area de trabajo!',
                'client.required'           => 'Debe especificar un cliente!',
                'other_client.required_if'  => 'Debe especificar un cliente!',
                'contact_name.regex'        => 'El nombre de la persona de contacto del cliente solo puede contener
                                                letras y espacios!',
                'application_deadline.date' => 'Debe introducir una fecha de plazo para presentación a convocatoria válida!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $tender = new Tender($form_data);

        $tender->client = $tender->client=="Otro" ? Request::input('other_client') : $tender->client;

        if(empty($tender->application_deadline)){
            $days_to_deadline = Request::input('days_to_deadline');

            if($days_to_deadline>0){
                $tender->application_deadline = Carbon::now()->addDays($days_to_deadline);
            }
            else{
                Session::flash('message', 'Debe indicar la fecha limite para presentación a esta licitación');
                return redirect()->back();
            }
        }

        $tender->user_id = $user->id;

        /* Get the id of the contact querying the name given */
        $contact_name = Request::input('contact_name');

        if($contact_name!=''){
            $contact = Contact::select('id')->where('name',$contact_name)->first();

            if($contact==''){
                /* Create a new contact record if no match was found */
                $contact = new Contact;
                $contact->name = $contact_name;
                $contact->company = $tender->client;
                $contact->save();
            }

            $tender->contact_id = $contact->id;
        }

        $tender->status = 'Activo';

        $tender->save();

        $this->fill_code_column('tender');

        /* Send email notification */
        $this->send_email_notification($tender, 'store');

        Session::flash('message', "Se agregó una nueva licitación al sistema");
        return redirect()->route('tender.index');
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

        $tender = Tender::find($id);

        $tender->application_deadline = Carbon::parse($tender->application_deadline)->format('d-m-Y');

        return View::make('app.tender_info', ['tender' => $tender, 'service' => $service, 'user' => $user]);
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

        $tender = Tender::find($id);

        $clients = Tender::select('client')->where('client', '<>', '')->groupBy('client')->get();

        $tender->application_deadline = Carbon::parse($tender->application_deadline)->format('Y-m-d');

        return View::make('app.tender_form', ['tender' => $tender, 'clients' => $clients, 'service' => $service,
            'user' => $user]);
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

        $v = \Validator::make($form_data, [
            'name'                  => 'required',
            'area'                  => 'required',
            'client'                => 'required',
            'other_client'          => 'required_if:client,Otro',
            'contact_name'          => 'regex:/^[\pL\s\-]+$/u',
            'application_deadline'  => 'date',
        ],
            [
                'name.required'             => 'Debe especificar el nombre o identificación de la licitación!',
                'area.required'             => 'Debe especificar el area de trabajo!',
                'client.required'           => 'Debe especificar un cliente!',
                'other_client.required_if'  => 'Debe especificar un cliente!',
                'contact_name.regex'        => 'El nombre de la persona de contacto del cliente solo puede contener
                                                letras y espacios!',
                'application_deadline.date' => 'Debe introducir una fecha de plazo para presentación a convocatoria válida!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $tender = Tender::find($id);

        $tender->fill($form_data);

        $tender->client = $tender->client=="Otro" ? Request::input('other_client') : $tender->client;

        if(empty($tender->application_deadline)){
            $days_to_deadline = Request::input('days_to_deadline');

            if($days_to_deadline>0){
                $tender->application_deadline = Carbon::now()->addDays($days_to_deadline);
            }
            else{
                Session::flash('message', 'Debe indicar la fecha limite para presentación a esta licitación');
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
                $contact->company = $tender->client;
                $contact->save();
            }

            $tender->contact_id = $contact->id;
        }

        $tender->save();

        /* Send email notification */
        //$this->send_email_notification($tender, 'update');

        Session::flash('message', "La licitación $tender->name ha sido modificada");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('tender.index');
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

        $tender = Tender::find($id);

        if($tender){
            if($tender->status!='Activo'){
                Session::flash('message', "Error al ejecutar el borrado, sólo puede eliminar una licitación con estado 'Activo'!");
                return redirect()->back();
            }

            $file_error = false;

            foreach($tender->files as $file){
                $file_error = $this->removeFile($file);

                if($file_error)
                    break;
            }

            if (!$file_error) {
                $tender->delete();

                Session::flash('message', "El registro fue eliminado del sistema");
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->route('tender.index');
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

    public function chg_stat_close($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $tender = Tender::find($id);

        $tender->status = 'No asignado';
        $tender->save();

        foreach($tender->files as $file){
            $this->blockFile($file);
        }

        Session::flash('message', "La licitación $tender->name ha sido marcada como 'No asignada'");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('tender.index');
    }

    public function chg_stat_sent($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $tender = Tender::find($id);

        $tender->applied = 1;
        $tender->application_details .= '\nSe envió documentación de presentación a esta licitación en fecha '.
            Carbon::now()->format('d-m-Y');
        $tender->status = 'Documentación enviada';
        $tender->save();

        Session::flash('message', "Se ha registrado el envío de documentación de presentación a la licitación $tender->name.");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('tender.index');
    }

    public function chg_stat_won($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $tender = Tender::find($id);

        $tender->status = 'Asignado';
        $tender->save();

        foreach($tender->files as $file){
            $this->blockFile($file);
        }

        Session::flash('message', "La licitación $tender->name ha sido marcada como 'Asignada'");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('tender.index');
    }

    public function add_contract_form($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $tender = Tender::find($id);

        $current_date = Carbon::now()->format('Y-m-d');

        return View::make('app.project_form', ['project' => 0, 'clients' => 0, 'service' => $service,
            'current_date' => $current_date, 'user' => $user, 'awards' => 0, 'tender' => $tender]);
    }

    public function add_contract(Request $request, $id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $form_data = Request::all();
        $form_data['valid_to'] = $form_data['valid_to']!='' ? $form_data['valid_to'].' 23:59:59' : '';

        $v = \Validator::make($form_data, [
            'name'                  => 'required',
            'contact_name'          => 'required|regex:/^[\pL\s\-]+$/u',
            'valid_from'            => 'required|date',
            'valid_to'              => 'date|after:valid_from',
        ],
            [
                'name.required'             => 'Debe especificar el nombre del proyecto!',
                'contact_name.required'     => 'Debe especificar el nombre de la persona de contacto del cliente!',
                'contact_name.regex'        => 'El nombre de la persona de contacto del cliente solo puede contener
                                                letras y espacios!',
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

        $tender = Tender::find($id);

        $project = new Project($form_data);

        $project->client = $tender->client;
        $project->award = 'Licitación';
        $project->type = $tender->area;
        $project->tender_id = $tender->id;

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

            if(!$contact){
                /* Create a new contact record if no match was found */
                $contact = new Contact;
                $contact->name = $contact_name;
                $contact->company = $project->client;
                $contact->save();
            }

            $project->contact_id = $contact->id;
        }

        $current_date = Carbon::now();
        $valid_from = Carbon::parse($project->valid_from);
        $valid_to = Carbon::parse($project->valid_to);

        if($current_date->between($valid_from,$valid_to))
            $project->status = 'Activo';
        else
            $project->status = 'Finalizado';

        $project->save();

        $this->fill_code_column('project');

        /* Send email notification */
        $this->send_email_notification($tender, 'add_contract');

        Session::flash('message', "Se agregó el contrato $project->name al sistema");
        return redirect()->route('project.index');
    }

    public function send_email_notification($model, $mode)
    {
        $user = Session::get('user');

        $recipient = User::where('area','Gerencia General')->where('priv_level',3)->first();
        $carbon_copies = User::select('email')
            ->where(function ($query) {$query->where('area','Gerencia General')->where('priv_level',2);})
            ->orwhere(function ($query) {
                $query->where('priv_level','>=',3);
            })
            ->get();

        $cc = array();
        foreach($carbon_copies as $carbon_copy){
            if($carbon_copy->email)
                $cc[] = $carbon_copy->email;
        }

        $mail_structure = '';
        $subject = '';

        if($mode=='store'){
            $data = array('recipient' => $recipient, 'tender' => $model);
            $mail_structure = 'emails.tender_added';
            $subject = 'Nueva licitación agregada al sistema';
        }
        elseif($mode=='add_contract'){
            $data = array('recipient' => $recipient, 'project' => $model);
            $mail_structure = 'emails.project_added';
            $subject = 'Nuevo proyecto agregado al sistema';
        }

        if($mail_structure!=''){
            $view = View::make($mail_structure, $data);
            $content = (string) $view;

            $success = 1;
            try {
                Mail::send($mail_structure, $data, function($message) use($recipient,$user,$cc,$subject) {
                    $message->to($recipient->email, $recipient->name)
                        ->cc($cc)
                        ->subject($subject);
                    $message->from('postmaster@gerteabros.com', 'postmaster@gerteabros.com');
                });
            } catch (Exception $ex) {
                $success = 0;
            }

            $email = new Email;
            $email->sent_by = 'postmaster@gerteabros.com';
            $email->sent_to = $recipient->email;
            $email->sent_cc = implode(', ', $cc);
            $email->subject = $subject;
            $email->content = $content;
            $email->success = $success;
            $email->save();
        }
    }

    public function fill_code_column($mode)
    {
        if($mode=='tender'){
            $tenders = Tender::where('code','')->get();

            foreach($tenders as $tender){
                $tender->code = 'LCT-'.str_pad($tender->id, 3, "0",
                        STR_PAD_LEFT).date_format($tender->created_at,'y');

                $tender->save();
            }
        }
        elseif($mode=='project'){
            $projects = Project::where('code','')->get();

            foreach($projects as $project){
                $project->code = 'PRJ-'.str_pad($project->id, 3, "0",
                        STR_PAD_LEFT).date_format($project->created_at,'y');

                $project->save();
            }
        }
    }
}
