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
use App\Driver;
use App\User;
use App\Vehicle;
use App\Email;
use App\Branch;
use App\VehicleHistory;
use App\VehicleRequirement;
use Carbon\Carbon;
use App\Http\Traits\FilesTrait;

class VehicleRequirementController extends Controller
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

        $vhc = Input::get('vhc');

        $requirements = VehicleRequirement::where('id', '>', 0);

        if(!is_null($vhc))
            $requirements = $requirements->where('vehicle_id', $vhc);

        if(!(($user->priv_level>=2&&$user->area=='Gerencia Tecnica')||$user->priv_level>=3||$user->work_type=='Transporte')){
            $requirements = $requirements->where(function ($query) use($user) {
                $query->where('for_id', $user->id)
                    ->orwhere('from_id', '=', $user->id);
            });
        }

        $requirements = $requirements->orderBy('updated_at','desc')->paginate(20);

        return View::make('app.vehicle_requirement_brief', ['requirements' => $requirements, 'service' => $service,
            'user' => $user, 'vhc' => $vhc]);
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

        $vehicle_types = Vehicle::select('type')->where('type', '<>', '')->where('flags', '<>', '0000')
            ->groupBy('type')->orderBy('type')->get();
        //$branches = Vehicle::select('branch')->where('branch', '<>', '')->groupBy('branch')->orderBy('Branch')->get();
        $branches = Branch::select('name','city')->where('name','<>','')->where('active', 1)->orderBy('name')->get();

        return View::make('app.vehicle_requirement_form', ['requirement' => 0, 'vehicle_types' => $vehicle_types,
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
            'branch_origin'         => 'required_if:type,borrow|required_if:type,transfer_branch',
            'vehicle_type'          => 'required',
            'vehicle_id'            => 'required|exists:vehicles,id',
            'from_name'             => 'required|exists:users,name',
            'for_name'              => 'required_if:type,borrow|required_if:type,transfer_tech||exists:users,name',
            'branch_destination'    => 'required_if:type,transfer_branch|required_if:type,devolution',
            'reason'                => 'required',
        ],
            [
                'type.required'             => 'Debe especificar el tipo de requerimiento!',
                'branch_origin.required_if' => 'Debe especificar la ciudad de origen si el requerimiento es por préstamo
                    o por traspaso entre sucursales!',
                'vehicle_type.required'     => 'Debe especificar el tipo de vehículo!',
                'vehicle_id.required'       => 'Debe seleccionar un vehículo!',
                'vehicle_id.exists'         => 'El vehículo seleccionado no fue encontrado en el sistema!',
                'from_name.required'        => 'La persona responsable actual debe figurar en el formulario!',
                'from_name.exists'          => 'El nombre del responsable actual no ha sido encontrado en el sistema!',
                'for_name.required_if'      => 'Debe especificar a quién se entregará el vehículo!',
                'for_name.exists'           => 'El nombre del receptor del vehículo no está registrado en el sistema!',
                'branch_destination.required_if' => 'Debe especificar la ciudad de destino si el requerimiento es por
                    traspaso entre sucursales o por devolución!',
                'reason.required'           => 'Debe especificar el motivo del requerimiento del vehículo!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $requirement = new VehicleRequirement(Request::all());

        if(VehicleRequirement::where('vehicle_id',$requirement->vehicle_id)->where('status',1 /*In process*/)->exists()){
            // If a requirement for this vehicle already exists return with error
            Session::flash('message', "El vehículo seleccionado ya tiene un requerimiento en proceso!");
            return redirect()->back()->withInput();
        }

        $date_time = Carbon::now()->format('ymdhis');
        $requirement->code = 'VR-'.$date_time;
        $requirement->user_id = $user->id;

        if($requirement->type=='devolution'||$requirement->type=='transfer_branch')
            $person_for = User::where('work_type','Transporte')->where('branch', $requirement->branch_destination)->where('status', 'Activo')->first();
        else
            $person_for = User::select('id')->where('name',Request::input('for_name'))->first();

        $person_from = User::select('id')->where('name',Request::input('from_name'))->first();

        if($person_for==''){
            Session::flash('message', "No se ha encontrado en el sistema un registro del receptor del vehículo");
            return redirect()->back()->withInput();
        }
        else
            $requirement->for_id = $person_for->id;

        if($person_from==''){
            Session::flash('message', "No se ha encontrado en el sistema un registro del responsable actual del vehículo!");
            return redirect()->back()->withInput();
        }
        else
            $requirement->from_id = $person_from->id;

        $requirement->status = 1; //In process

        $requirement->save();

        $vehicle = $requirement->vehicle;

        /* Insert new entry on vehicle history table */
        $this->add_history_record($vehicle, $requirement, 'store', $user);

        /* Send notification mail */
        $this->send_email($requirement, 'store' /*$recipient, $cc, $data, $mail_structure, $subject*/);

        Session::flash('message', "El requerimiento de vehículo fue registrado correctamente");
        return redirect()->route('vehicle_requirement.index');
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

        $requirement = VehicleRequirement::find($id);

        return View::make('app.vehicle_requirement_info', ['requirement' => $requirement, 'service' => $service,
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

        $requirement = VehicleRequirement::find($id);

        $vehicle_types = Vehicle::select('type')->where('type', '<>', '')->where('flags', '<>', '0000')
            ->groupBy('type')->orderBy('type')->get();
        //$branches = Vehicle::select('branch')->where('branch', '<>', '')->groupBy('branch')->orderBy('Branch')->get();
        $branches = Branch::select('name','city')->where('name','<>','')->where('active', 1)->orderBy('name')->get();

        return View::make('app.vehicle_requirement_form', ['requirement' => $requirement, 'vehicle_types' => $vehicle_types,
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
            'type'                  => 'required',
            'branch_origin'         => 'required_if:type,borrow|required_if:type,transfer_branch',
            'vehicle_type'          => 'required',
            'vehicle_id'            => 'required|exists:vehicles,id',
            'from_name'             => 'required|exists:users,name',
            'for_name'              => 'required_if:type,borrow|required_if:type,transfer_tech||exists:users,name',
            'branch_destination'    => 'required_if:type,transfer_branch|required_if:type,devolution',
            'reason'                => 'required',
        ],
            [
                'type.required'             => 'Debe especificar el tipo de requerimiento!',
                'branch_origin.required_if' => 'Debe especificar la ciudad de origen si el requerimiento es por préstamo
                    o por traspaso entre sucursales!',
                'vehicle_type.required'     => 'Debe especificar el tipo de vehículo!',
                'vehicle_id.required'       => 'Debe seleccionar un vehículo!',
                'vehicle_id.exists'         => 'El vehículo seleccionado no fue encontrado en el sistema!',
                'from_name.required'        => 'La persona responsable actual debe figurar en el formulario!',
                'from_name.exists'          => 'El nombre del responsable actual no ha sido encontrado en el sistema!',
                'for_name.required_if'      => 'Debe especificar a quién se entregará el vehículo!',
                'for_name.exists'           => 'El nombre del receptor del vehículo no está registrado en el sistema!',
                'branch_destination.required_if' => 'Debe especificar la ciudad de destino si el requerimiento es por
                    traspaso entre sucursales o por devolución!',
                'reason.required'           => 'Debe especificar el motivo del requerimiento del vehículo!',
            ]
        );

        if ($v->fails()) {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $requirement = VehicleRequirement::find($id);

        $requirement->fill(Request::all());

        if (VehicleRequirement::where('vehicle_id', $requirement->vehicle_id)->where('status', 1 /*In process*/)
            ->where('id', '<>', $id)->exists()) {
            // If a requirement for this vehicle already exists return with error
            Session::flash('message', "El vehículo seleccionado ya tiene un requerimiento en proceso!");
            return redirect()->back()->withInput();
        }

        if($requirement->type=='devolution'||$requirement->type=='transfer_branch')
            $person_for = User::where('work_type','Transporte')->where('branch', $requirement->branch_destination)->where('status', 'Activo')->first();
        else
            $person_for = User::select('id')->where('name',Request::input('for_name'))->first();

        $person_from = User::select('id')->where('name', Request::input('from_name'))->first();

        if ($person_for == '') {
            Session::flash('message', "No se ha encontrado en el sistema un registro del receptor del vehículo!");
            return redirect()->back()->withInput();
        } else
            $requirement->for_id = $person_for->id;

        if ($person_from == '') {
            Session::flash('message', "No se ha encontrado en el sistema un registro del responsable actual del vehículo!");
            return redirect()->back()->withInput();
        } else
            $requirement->from_id = $person_from->id;

        $requirement->save();

        $vehicle = $requirement->vehicle;

        /* Insert new entry on vehicle history table */
        $this->add_history_record($vehicle, $requirement, 'update', $user);

        /* Send notification mail */
        $this->send_email($requirement, 'update' /*$recipient, $cc, $data, $mail_structure, $subject*/);

        Session::flash('message', "Requerimiento modificado correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('vehicle_requirement.index');
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

        $requirement = VehicleRequirement::find($id);

        if($requirement){
            $requirement->delete();

            Session::flash('message', "El registro fue eliminado del sistema");
            if(Session::has('url'))
                return redirect(Session::get('url'));
            else
                return redirect()->route('vehicle_requirement.index');
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

        $requirement = VehicleRequirement::find($id);

        if(!$requirement){
            Session::flash('message', 'No se encontró el registro solicitado!');
            return redirect()->back();
        }

        return View::make('app.vehicle_requirement_reject_form', ['requirement' => $requirement,
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

        $requirement = VehicleRequirement::find($id);

        $requirement->fill(Request::all());
        $requirement->status = 0; //Rejected
        $requirement->stat_change = Carbon::now();

        $requirement->save();

        $vehicle = $requirement->vehicle;

        /* Insert new entry on vehicle history table */
        $this->add_history_record($vehicle, $requirement, 'reject', $user);

        /* Send notification mail */
        $this->send_email($requirement, 'reject' /*$recipient, $cc, $data, $mail_structure, $subject*/);

        Session::flash('message', "El requerimiento ha sido rechazado");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('vehicle_requirement.index');
    }
    
    function send_email($requirement, $mode /*$recipient, $cc, $data, $mail_structure, $subject*/)
    {
        $cc = '';
        $mail_structure = '';
        $subject = '';
        $recipient = null;

        if($mode=='store'){
            $recipient = $requirement->person_from;
            $cc = $requirement->person_for ? $requirement->person_for->email : '';
            $mail_structure = 'emails.new_vehicle_requirement';
            $subject = 'Requerimiento de entrega de vehículo';
        }
        elseif($mode=='update'){
            $recipient = $requirement->person_from;
            $cc = $requirement->person_for ? $requirement->person_for->email : '';
            $mail_structure = 'emails.new_vehicle_requirement';
            $subject = 'Requerimiento de entrega de vehículo';
        }
        elseif($mode=='reject'){
            $recipient = $requirement->person_for;
            $cc = $requirement->user ? $requirement->user->email : '';
            $mail_structure = 'emails.vehicle_requirement_rejected';
            $subject = 'Requerimiento de entrega de vehículo rechazado';
        }

        if($recipient){
            $data = array('recipient' => $recipient, 'requirement' => $requirement);

            $view = View::make($mail_structure, $data /*['recipient' => $recipient, 'requirement' => $requirement]*/);
            $content = (string)$view;

            $success = 1;

            try {
                Mail::send($mail_structure, $data, function ($message) use ($recipient, $cc, $subject) {
                    $message->to($recipient->email, $recipient->name)
                        ->subject($subject)
                        ->from('postmaster@gerteabros.com', 'postmaster@gerteabros.com');

                    if(filter_var($cc, FILTER_VALIDATE_EMAIL)){
                        $message->cc($cc);
                    }
                });
            } catch (Exception $ex) {
                $success = 0;
            }

            $email = new Email;
            $email->sent_by = 'postmaster@gerteabros.com';
            $email->sent_to = $recipient->email;
            $email->sent_cc = $cc;
            $email->subject = $subject;
            $email->content = $content;
            $email->success = $success;
            $email->save();
        }
    }

    function add_history_record($vehicle, $requirement, $mode, $user)
    {
        $vehicle_history = new VehicleHistory;
        $vehicle_history->vehicle_id = $vehicle->id;

        if($mode=='store'){
            $vehicle_history->type = 'Requerimiento ('.VehicleRequirement::$types[$requirement->type].')';
            $vehicle_history->contents = $user->name.' elaboró un requerimiento para el vehículo '.$vehicle->type.' '.
                $vehicle->model.' con placa '.$vehicle->license_plate.' con el siguiente motivo: '.$requirement->reason;
        }
        elseif($mode=='update'){
            $vehicle_history->type = 'Requerimiento ('.VehicleRequirement::$types[$requirement->type].') modificado';
            $vehicle_history->contents = $user->name.' modificó el requerimiento '.$requirement->code.' del vehículo '.
                $vehicle->type.' '.$vehicle->model.' con placa '.$vehicle->license_plate.' con el siguiente detalle: '.
                $requirement->reason;
        }
        elseif($mode=='reject'){
            $vehicle_history->type = 'Requerimiento ('.VehicleRequirement::$types[$requirement->type].') rechazado';
            $vehicle_history->contents = $user->name.' rechazó el requerimiento '.$requirement->code.' del vehículo '.
                $vehicle->type.' '.$vehicle->model.' con placa '.$vehicle->licnese_plate.' debido a: '.$requirement->stat_obs;
        }

        $vehicle_history->status = $vehicle->status;
        $vehicle_history->historyable()->associate($requirement);
        $vehicle_history->save();
    }
}
