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
use App\Operator;
use App\User;
use App\Device;
use App\Email;
use App\DeviceHistory;
use App\DeviceRequirement;
use App\Branch;
use Carbon\Carbon;
use App\Http\Traits\FilesTrait;

class DeviceRequirementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id)) {
            return View('app.index', ['service' => 'active', 'user' => null]);
        }
        if($user->acc_active==0)
            return redirect()->action('LoginController@logout', ['service' => 'active']);

        $service = Session::get('service');

        $dvc = Input::get('dvc');

        $requirements = DeviceRequirement::where('id', '>', 0);

        if(!is_null($dvc))
            $requirements = $requirements->where('device_id', $dvc);

        if(!(($user->priv_level>=2&&$user->area=='Gerencia Tecnica')||$user->priv_level>=3||$user->work_type=='Almacén')){
            $requirements = $requirements->where(function ($query) use($user) {
                $query->where('for_id', $user->id)
                    ->orwhere('from_id', '=', $user->id);
            });
        }

        $requirements = $requirements->orderBy('created_at','desc')->paginate(20);

        return View::make('app.device_requirement_brief', ['requirements' => $requirements, 'service' => $service,
            'user' => $user, 'dvc' => $dvc]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $device_types = Device::select('type')->where('type', '<>', '')->where('flags','<>','0000')
            ->groupBy('type')->orderBy('type')->get();
        //$branches = Device::select('branch')->where('branch', '<>', '')->groupBy('branch')->orderBy('Branch')->get();
        $branches = Branch::select('name', 'city')->where('name','<>','')->where('active', 1)->orderBy('name')->get();

        return View::make('app.device_requirement_form', ['requirement' => 0, 'device_types' => $device_types,
            'branches' => $branches, 'service' => $service, 'user' => $user]);
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

        $v = \Validator::make(Request::all(), [
            'type'                  => 'required',
            'branch_origin'         => 'required_if:type,borrow|required_if:type,transfer_wh',
            'device_type'           => 'required',
            'device_id'             => 'required|exists:devices,id',
            'from_name'             => 'required|exists:users,name',
            'for_name'              => 'required_if:type,borrow|required_if:type,transfer_tech', //||exists:users,name',
            'branch_destination'    => 'required_if:type,transfer_wh|required_if:type,devolution',
            'reason'                => 'required',
        ],
            [
                'type.required'             => 'Debe especificar el tipo de requerimiento!',
                'branch_origin.required_if' => 'Debe especificar el almacén de origen si el requerimiento es por préstamo
                    o traspaso entre alamacenes!',
                'device_type.required'      => 'Debe especificar el tipo de equipo!',
                'device_id.required'        => 'Debe seleccionar un equipo!',
                'device_id.exists'          => 'El equipo seleccionado no fue encontrado en el sistema!',
                'from_name.required'        => 'La persona responsable actual debe figurar en el formulario!',
                'from_name.exists'          => 'El nombre del responsable actual no ha sido encontrado en el sistema!',
                'for_name.required_if'      => 'Debe especificar a quién se entregará el equipo!',
                'for_name.exists'           => 'El nombre del receptor del equipo no está registrado en el sistema!',
                'branch_destination.required_if' => 'Debe especificar el almacén de destino si el requerimiento es por
                    traspaso entre almacenes o por devolución!',
                'reason.required'           => 'Debe especificar el motivo del requerimiento del equipo!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $requirement = new DeviceRequirement(Request::all());

        if(DeviceRequirement::where('device_id',$requirement->device_id)->where('status',1 /*In process*/)->exists()){
            // If a requirement for this device already exists return with error
            Session::flash('message', "El equipo seleccionado ya tiene un requerimiento en proceso!");
            return redirect()->back()->withInput();
        }

        $date_time = Carbon::now()->format('ymdhis');
        $requirement->code = 'DR-'.$date_time;
        $requirement->user_id = $user->id;

        if($requirement->type=='devolution'||$requirement->type=='transfer_wh')
            $person_for = User::where('work_type','Almacén')->where('branch', $requirement->branch_destination)->where('status', 'Activo')->first();
        else
            $person_for = User::select('id')->where('name',Request::input('for_name'))->first();

        $person_from = User::select('id')->where('name',Request::input('from_name'))->first();

        if($person_for==''){
            Session::flash('message', "No se ha encontrado en el sistema un registro del receptor del equipo!");
            return redirect()->back()->withInput();
        }
        else
            $requirement->for_id = $person_for->id;

        if($person_from==''){
            Session::flash('message', "No se ha encontrado en el sistema un registro del responsable actual del equipo!");
            return redirect()->back()->withInput();
        }
        else
            $requirement->from_id = $person_from->id;

        $requirement->status = 1; //In process

        $requirement->save();

        $device = $requirement->device;

        /* Insert new entry on device history table */
        $this->add_history_record($device, $requirement, 'store', $user);

        /* Send notification mail */
        $recipient = $requirement->person_from;
        $cc = $requirement->person_for;
        $data = array('recipient' => $recipient, 'requirement' => $requirement, 'cc' => $cc);
        $mail_structure = 'emails.new_device_requirement';
        $subject = 'Requerimiento de entrega de equipo';

        $this->send_email($recipient, $cc, $data, $mail_structure, $subject);

        Session::flash('message', "El requerimiento de equipo fue registrado correctamente");
        return redirect()->route('device_requirement.index');
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

        $requirement = DeviceRequirement::find($id);

        return View::make('app.device_requirement_info', ['requirement' => $requirement, 'service' => $service,
            'user' => $user]);
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

        $requirement = DeviceRequirement::find($id);

        $device_types = Device::select('type')->where('type', '<>', '')->where('flags','<>','0000')
            ->groupBy('type')->orderBy('type')->get();
        //$branches = Device::select('branch')->where('branch', '<>', '')->groupBy('branch')->orderBy('Branch')->get();
        $branches = Branch::select('name', 'city')->where('name','<>','')->where('active', 1)->orderBy('name')->get();

        return View::make('app.device_requirement_form', ['requirement' => $requirement, 'device_types' => $device_types,
            'branches' => $branches, 'service' => $service, 'user' => $user]);
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

        $v = \Validator::make(Request::all(), [
            'type'          => 'required',
            'branch_origin' => 'required_if:type,borrow|required_if:type,transfer_wh',
            'device_type'   => 'required',
            'device_id'     => 'required|exists:devices,id',
            'from_name'     => 'required|exists:users,name',
            'for_name'      => 'required_if:type,borrow|required_if:type,transfer_tech', //||exists:users,name',
            'branch_destination' => 'required_if:type,transfer_wh|required_if:type,devolution',
            'reason'        => 'required',
        ],
            [
                'type.required'         => 'Debe especificar el tipo de requerimiento!',
                'branch_origin.required_if' => 'Debe especificar el almacén de origen si el requerimiento es por préstamo
                    o traspaso entre alamacenes!',
                'device_type.required'  => 'Debe especificar el tipo de equipo!',
                'device_id.required'    => 'Debe seleccionar un equipo!',
                'device_is.exists'      => 'El equipo seleccionado no fue encontrado en el sistema!',
                'from_name.required'    => 'La persona responsable actual debe figurar en el formulario!',
                'from_name.exists'      => 'El nombre del responsable actual no ha sido encontrado en el sistema!',
                'for_name.required'     => 'Debe especificar a quién se entregará el equipo!',
                'for_name.exists'       => 'El nombre del receptor del equipo no está registrado en el sistema!',
                'branch_destination.required_if' => 'Debe especificar el almacén de destino si el requerimiento es por
                    traspaso entre almacenes o por devolución!',
                'reason.required'       => 'Debe especificar el motivo del requerimiento del equipo!',
            ]
        );

        if ($v->fails()) {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $requirement = DeviceRequirement::find($id);

        $requirement->fill(Request::all());

        if (DeviceRequirement::where('device_id', $requirement->device_id)->where('status', 1 /*In process*/)
            ->where('id', '<>', $id)->exists()) {
            // Check if a requirement for this device already exists and is pending
            Session::flash('message', "El equipo seleccionado ya tiene un requerimiento en proceso!");
            return redirect()->back()->withInput();
        }

        if($requirement->type=='devolution'||$requirement->type=='transfer_wh')
            $person_for = User::where('work_type','Almacén')->where('branch', $requirement->branch_destination)->where('status', 'Activo')->first();
        else
            $person_for = User::select('id')->where('name',Request::input('for_name'))->first();
        
        $person_from = User::select('id')->where('name', Request::input('from_name'))->first();

        if ($person_for == '') {
            Session::flash('message', "No se ha encontrado en el sistema un registro del receptor del equipo!");
            return redirect()->back()->withInput();
        } else
            $requirement->for_id = $person_for->id;

        if ($person_from == '') {
            Session::flash('message', "No se ha encontrado en el sistema un registro del responsable actual del equipo!");
            return redirect()->back()->withInput();
        } else
            $requirement->from_id = $person_from->id;

        $requirement->save();

        $device = $requirement->device;

        /* Insert new entry on device history table */
        $this->add_history_record($device, $requirement, 'update', $user);

        /* Send notification mail */
        $recipient = $requirement->person_from;
        $cc = $requirement->person_for;
        $data = array('recipient' => $recipient, 'requirement' => $requirement, 'cc' => $cc);
        $mail_structure = 'emails.new_device_requirement';
        $subject = 'Requerimiento de entrega de equipo';

        $this->send_email($recipient, $cc, $data, $mail_structure, $subject);

        Session::flash('message', "Requerimiento modificado correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('device_requirement.index');
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

        $requirement = DeviceRequirement::find($id);

        if($requirement){
            $requirement->delete();

            Session::flash('message', "El registro fue eliminado del sistema");
            if(Session::has('url'))
                return redirect(Session::get('url'));
            else
                return redirect()->route('device_requirement.index');
        }
        else {
            Session::flash('message', "Error al ejecutar el borrado, no se encontró el registro solicitado.");
            return redirect()->back();
        }
    }

    public function reject_form($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $requirement = DeviceRequirement::find($id);
        
        if(!$requirement){
            Session::flash('message', 'No se encontró el registro solicitado!');
            return redirect()->back();
        }

        return View::make('app.device_requirement_reject_form', ['requirement' => $requirement, 
            'service' => $service, 'user' => $user]);
    }

    public function reject(Request $request, $id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $v = \Validator::make(Request::all(), [
            'stat_obs'      => 'required',
        ],
            [
                'stat_obs.required'     => 'Debe especificar el motivo de rechazo de este requerimiento!',
            ]
        );

        if ($v->fails()) {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $requirement = DeviceRequirement::find($id);

        $requirement->fill(Request::all());
        $requirement->status = 0; //Rejected
        $requirement->stat_change = Carbon::now();
        
        $requirement->save();

        $device = $requirement->device;

        /* Insert new entry on device history table */
        $this->add_history_record($device, $requirement, 'reject', $user);

        /* Send notification mail */
        $recipient = $requirement->person_for;
        $cc = $requirement->user;
        $data = array('recipient' => $recipient, 'requirement' => $requirement, 'cc' => $cc);
        $mail_structure = 'emails.device_requirement_rejected';
        $subject = 'Requerimiento de entrega de equipo rechazado';

        $this->send_email($recipient, $cc, $data, $mail_structure, $subject);

        Session::flash('message', "El requerimiento ha sido rechazado");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('device_requirement.index');
    }

    function send_email($recipient, $cc, $data, $mail_structure, $subject)
    {
        $view = View::make($mail_structure, $data /*['recipient' => $recipient, 'requirement' => $requirement]*/);
        $content = (string)$view;

        $success = 1;

        try {
            Mail::send($mail_structure, $data, function ($message) use ($recipient, $cc, $subject) {
                $message->to($recipient->email, $recipient->name)
                    ->cc($cc->email, $cc->name)
                    ->subject($subject)
                    ->from('postmaster@gerteabros.com', 'postmaster@gerteabros.com');
            });
        } catch (Exception $ex) {
            $success = 0;
        }

        $email = new Email;
        $email->sent_by = 'postmaster@gerteabros.com';
        $email->sent_to = $recipient->email;
        $email->sent_cc = $cc->email;
        $email->subject = $subject;
        $email->content = $content;
        $email->success = $success;
        $email->save();
    }

    function add_history_record($device, $requirement, $mode, $user)
    {
        $device_history = new DeviceHistory;
        $device_history->device_id = $device->id;

        if($mode=='store'){
            $device_history->type = 'Requerimiento ('.DeviceRequirement::$types[$requirement->type].')';
            $device_history->contents = $user->name.' elaboró un requerimiento para el equipo '.$device->type.' '.
                $device->model.' con S/N '.$device->serial.' con el siguiente motivo: '.$requirement->reason;
        }
        elseif($mode=='update'){
            $device_history->type = 'Requerimiento ('.DeviceRequirement::$types[$requirement->type].') modificado';
            $device_history->contents = $user->name.' modificó el requerimiento '.$requirement->code.' del equipo '.
                $device->type.' '.$device->model.' con S/N '.$device->serial.' con el siguiente detalle: '.$requirement->reason;
        }
        elseif($mode=='reject'){
            $device_history->type = 'Requerimiento ('.DeviceRequirement::$types[$requirement->type].') rechazado';
            $device_history->contents = $user->name.' rechazó el requerimiento '.$requirement->code.' del equipo '.
                $device->type.' '.$device->model.' con S/N '.$device->serial.' debido a: '.$requirement->stat_obs;
        }

        $device_history->status = $device->status;
        $device_history->historyable()->associate($requirement);
        $device_history->save();
    }
}
