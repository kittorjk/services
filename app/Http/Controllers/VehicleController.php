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
use App\User;
use App\Vehicle;
use App\Driver;
use App\Maintenance;
use App\VhcFailureReport;
use App\ServiceParameter;
use App\Guarantee;
use App\Email;
use App\Branch;
// use App\VehicleHistory;
use Carbon\Carbon;
use App\Http\Traits\FilesTrait;
use App\Http\Traits\ActiveTrait;

class VehicleController extends Controller
{
    use FilesTrait;
    use ActiveTrait;
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

        if(($user->priv_level>=1&&$user->area=='Gerencia Tecnica')||$user->priv_level>=3||$user->work_type=='Transporte'){
            $vehicles = Vehicle::where('id', '>', 0)->where('status','<>','Baja')->orderBy('updated_at','desc');
        }
        else{
            $vehicles = Vehicle::where('responsible',$user->id)->where('status','<>','Baja')->orderBy('updated_at','desc');
        }

        Session::put('db_query', $vehicles->get());
        $vehicles = $vehicles->paginate(20);

        return View::make('app.vehicle_brief', ['vehicles' => $vehicles, 'service' => $service, 'user' => $user]);
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

        $owners = Vehicle::select('owner')->where('owner', '<>', 'ABROS')->where('owner','<>','')->groupBy('owner')->get();

        $branches = Branch::select('id', 'name')->where('name','<>','')->where('active', 1)->orderBy('name')->get();

        return View::make('app.vehicle_form', ['vehicle' => 0, 'owners' => $owners, 'service_parameters' => 0,
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
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $v = \Validator::make(Request::all(), [
            'license_plate'     => 'required|unique:vehicles|regex:/^[0-9]{3,4}[A-Z]{3}$/',
            'type'              => 'required',
            'mileage'           => 'required|numeric',
            'owner'             => 'required',
            'other_owner'       => 'required_if:owner,Otro',
            'branch_id'         => 'required|exists:branches,id',
        ],
            [
                'unique'                          => 'Este número de placa ya está registrado!',
                'license_plate.regex'             => 'Ingrese un número de placa válido!',
                'license_plate.required'          => 'Debe especificar el número de placa!',
                'type.required'                   => 'Debe especificar el tipo de vehículo!',
                'mileage.required'                => 'Debe especificar el kilometraje actual del vehículo!',
                'mileage.numeric'                 => 'Valor de kilometraje inválido!',
                'owner.required'                  => 'Debe especificar el propietario del vehículo!',
                'other_owner.required_if'         => 'Debe especificar el propietario del vehículo!',
                'branch_id.required'              => 'Debe especificar la sucursal a la que está asignado el vehículo!',
                'branch_id.exists'                => 'La sucursal seleccionada no existe en el sistema!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $vehicle = new Vehicle(Request::all());

        $vehicle->owner = $vehicle->owner=="Otro" ? Request::input('other_owner') : $vehicle->owner;

        $branch = Branch::find($vehicle->branch_id);

        $responsible = User::where('work_type', 'Transporte')->where('branch_id', $vehicle->branch_id)->where('status', 'Activo')->first();

        $vehicle->responsible = $responsible ? $responsible->id : $user->id;
        $vehicle->destination = $branch->city;
        $vehicle->status = 'Disponible';
        $vehicle->flags = '0001';

        $branch = Branch::find($vehicle->branch_id);

        $vehicle->branch = $branch ? $branch->city : 'La Paz';

        $vehicle->save();

        /* insert new entry on vehicle history table */
        // $this->add_history_record($vehicle, 'store', $vehicle, $user);
        $this->add_vhc_history_record($vehicle, $vehicle, 'store', $user, 'vehicle');
        
        Session::flash('message', "Vehículo registrado correctamente");
        return redirect()->route('vehicle.index');
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

        $vehicle = Vehicle::find($id);
        $driver = Driver::where('vehicle_id',$id)->orderBy('updated_at','desc')->first();

        $exists_picture=false;
        foreach($vehicle->files as $file){
            if($file->type=='jpg'||$file->type=='jpeg'||$file->type=='png')
                $exists_picture=true;
        }

        $vehicle->gas_inspection_exp = Carbon::parse($vehicle->gas_inspection_exp);
        
        return View::make('app.vehicle_info', ['vehicle' => $vehicle, 'driver' => $driver, 'service' => $service, 
            'exists_picture' => $exists_picture, 'user' => $user]);
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
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $vehicle = Vehicle::find($id);
        $owners = Vehicle::select('owner')->where('owner', '<>', 'ABROS')->where('owner','<>','')->groupBy('owner')->get();
        $service_parameters = ServiceParameter::where('group','Mantenimiento')->get();

        $branches = Branch::select('id', 'name')->where('name','<>','')->where('active', 1)->orderBy('name')->get();

        return View::make('app.vehicle_form', ['vehicle' => $vehicle, 'service_parameters' => $service_parameters,
            'owners' => $owners, 'branches' => $branches, 'service' => $service, 'user' => $user]);
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
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $vehicle = Vehicle::find($id);
        
        if(Request::input('license_plate')!=$vehicle->license_plate){
            $v = \Validator::make(Request::all(), [
                'license_plate'               => 'required|unique:vehicles|regex:/^[0-9]{3,4}[A-Z]{3}$/',
            ],
                [
                    'unique'                          => 'Este número de placa ya está registrado!',
                    'license_plate.regex'             => 'Ingrese un número de placa válido!',
                    'license_plate.required'          => 'Debe especificar el número de placa!',
                ]
            );
            
            if ($v->fails())
            {
                Session::flash('message', $v->messages()->first());
                return redirect()->back()->withInput();
            }
        }
        
        $v = \Validator::make(Request::all(), [
            'type'              => 'required',
            'mileage'           => 'required|numeric',
            'owner'             => 'required',
            'other_owner'       => 'required_if:owner,Otro',
            'branch_id'         => 'required|exists:branches,id',
        ],
            [
                'type.required'                   => 'Debe especificar el tipo de vehículo!',
                'mileage.required'                => 'Debe especificar el kilometraje actual del vehículo!',
                'mileage.numeric'                 => 'Valor de kilometraje inválido!',
                'owner.required'                  => 'Debe especificar el propietario del vehículo!',
                'other_owner.required_if'         => 'Debe especificar el propietario del vehículo!',
                'branch_id.required'              => 'Debe especificar la sucursal a la que está asignado el vehículo!',
                'branch_id.exists'                => 'La sucursal seleccionada no existe en el sistema!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $vehicle->fill(Request::all());
        
        $vehicle->owner = $vehicle->owner=="Otro" ? Request::input('other_owner') : $vehicle->owner;

        /*
        if($vehicle->status=='En mantenimiento'){
            $vehicle->flags = '1000';

            $maintenance = new Maintenance();
            $maintenance->user_id = $user->id;
            $maintenance->active = $vehicle->license_plate;
            $maintenance->vehicle_id = $vehicle->id;
            $maintenance->usage = $vehicle->mileage;
            $maintenance->type = Request::input('maintenance_type');

            if($maintenance->type == 'Preventivo')
                $maintenance->parameter_id = Request::input('parameter_id');

            $maintenance->save();

            // insert new entry on vehicle history table
            $vehicle_history = new VehicleHistory;
            $vehicle_history->vehicle_id = $vehicle->id;
            $vehicle_history->type = 'Mantenimiento';
            $vehicle_history->contents = 'El vehículo '.$vehicle->serial.' es puesto en mantenimiento';
            $vehicle_history->status = $vehicle->status;
            $vehicle_history->historyable()->associate(Maintenance::find($maintenance->id));
            $vehicle_history->save();
        }
        else{
            foreach($vehicle->maintenances as $maintenance){
                if($maintenance->completed==0){
                    $maintenance->date = Carbon::now();
                    $maintenance->completed = 1;
                    $maintenance->save();

                    foreach($maintenance->files as $file){
                        $this->blockFile($file);
                    }
                }
            }

            if($vehicle->status=='Activo')
                $vehicle->flags = '0010';
            if($vehicle->status=='Disponible'){
                $vehicle->flags = '0001';
                //$vehicle->responsible = 0;
            }
            elseif($vehicle->status=='Baja')
                $vehicle->flags = '0000';
        }
        */

        $branch = Branch::find($vehicle->branch_id);

        $vehicle->branch = $branch ? $branch->city : 'La Paz';

        $vehicle->save();

        Session::flash('message', "Datos actualizados correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('vehicle.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Session::flash('message', 'Vehicle records can\'t be deleted, only marked as "Baja"');
        return redirect()->back();
    }

    public function report_malfunction_form($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $vehicle = Vehicle::find($id);

        if(!$vehicle){
            Session::flash('message', "Ocurrió un error al recuperar los datos del servidor, intente de nuevo por favor.");
            return redirect()->back();
        }

        return View::make('app.vehicle_malfunction_form', ['vehicle' => $vehicle, 'service' => $service,
            'user' => $user]);
    }

    public function record_malfunction_report(Request $request, $id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $form_data = Request::all();

        $v = \Validator::make($form_data, [
            'condition'             => 'required|filled',
        ],
            [
                'condition.required'        => 'Debe especificar el problema existente en el vehículo!',
                'condition.filled'          => 'El campo "condiciones" no puede estar vacío!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $vehicle = Vehicle::find($id);

        $vehicle->fill($form_data);

        $vehicle->status = 'Requiere mantenimiento';

        if($vehicle->flags[1]==0)
            $vehicle->flags = str_pad($vehicle->flags+100, 4, "0", STR_PAD_LEFT);
            //this flag works with available and active flags

        $vehicle->save();

        /* Record failure report*/
        $this->add_failure_report($vehicle, $user);
        
        /* insert new entry on vehicle history table */
        // $this->add_history_record($vehicle, 'malfunction', $vehicle, $user);
        $this->add_vhc_history_record($vehicle, $vehicle, 'malfunction', $user, 'vehicle');
        
        // Send email notification to responsible of transports
        $this->send_mail($vehicle, $user);

        Session::flash('message', "El problema descrito ha sido reportado");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('vehicle.index');
    }
    
    public function link_model_form($type, $id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $vehicle = Vehicle::find($id);
        
        if(empty($vehicle)){
            Session::flash('message', "Ocurrió un error al recuperar información del servidor, intente de nuevo por favor");
            return redirect()->back();
        }

        $options = 0;

        if($type=='policy')
            $options = Guarantee::where('type', 'Automotores')->where('closed',0)->get();

        return View::make('app.vehicle_link_form', ['vehicle' => $vehicle, 'options' => $options,
            'service' => $service, 'user' => $user, 'type' => $type]);
    }

    public function record_linked_model(Request $request, $type, $id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $vehicle = Vehicle::find($id);

        $message = '';
        $option_id = Request::input('option_id');

        if($type=='policy'){
            $vehicle->policy_id = $option_id;

            $guarantee = Guarantee::find($option_id);

            if($guarantee)
                $message = 'Se enlazó la poliza: '.$guarantee->code.' al vehículo: '.$vehicle->license_plate;
            else
                $message = 'No se enlazó ninguna poliza al vehículo: '.$vehicle->license_plate;
        }

        $vehicle->save();

        Session::flash('message', $message);
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('vehicle.index');
    }

    public function main_pic_id_form($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');
        $vehicle = Vehicle::find($id);

        if(!$vehicle){
            Session::flash('message', "Ocurrió un error al recuperar los datos del servidor, intente de nuevo por favor.");
            return redirect()->back();
        }

        return View::make('app.change_main_pic_id_form', ['model' => $vehicle, 'type' => 'vehicle', 'service' => $service,
            'user' => $user]);
    }

    public function change_main_pic_id(Request $request, $id)
    {
        $vehicle = Vehicle::find($id);

        if(!$vehicle){
            Session::flash('message', "Ocurrió un error al recuperar los datos del servidor, intente de nuevo por favor.");
            return redirect()->back();
        }

        $vehicle->main_pic_id = Request::input('new_id');
        $vehicle->save();

        Session::flash('message', 'La imagen principal de éste vehículo ha cambiado');
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('vehicle.index');
    }

    public function disable_form()
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $vhc_id = Input::get('vhc_id');
        
        $vehicle = Vehicle::find($vhc_id);

        if(!$vehicle){
            Session::flash('message', "Ocurrió un error al recuperar los datos del servidor, intente de nuevo por favor.");
            return redirect()->back();
        }

        return View::make('app.vehicle_disable_form', ['vehicle' => $vehicle, 'service' => $service, 'user' => $user]);
    }

    public function disable_record(Request $request)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $v = \Validator::make(Request::all(), [
            'condition'             => 'required|filled',
        ],
            [
                'condition.required'        => 'Debe especificar el motivo para dar de baja este vehículo!',
                'condition.filled'          => 'El campo "Motivo de baja" no puede estar vacío!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $id = Request::input('vehicle_id');

        $vehicle = Vehicle::find($id);

        $vehicle->fill(Request::all());

        $vehicle->status = 'Baja';
        $vehicle->flags = '0000';
        
        $vehicle->save();

        foreach($vehicle->maintenances as $maintenance){
            if($maintenance->completed==0){
                $maintenance->date = Carbon::now();
                $maintenance->completed = 1;
                $maintenance->save();

                foreach($maintenance->files as $file){
                    $this->blockFile($file);
                }
            }
        }

        foreach($vehicle->files as $file){
            $this->blockFile($file);
        }

        /* insert new entry on vehicle history table */
        // $this->add_history_record($vehicle, 'disable', $vehicle, $user);
        $this->add_vhc_history_record($vehicle, $vehicle, 'disable', $user, 'vehicle');

        Session::flash('message', "El vehículo con placa $vehicle->license_plate ha sido dado de baja");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('vehicle.index');
    }

    /*
    function add_history_record($vehicle, $mode, $model, $user)
    {
        $vehicle_history = new VehicleHistory;
        $vehicle_history->vehicle_id = $vehicle->id;

        if($mode=='store'){
            $vehicle_history->type = 'Nuevo registro';
            $vehicle_history->contents = 'El vehículo '.$vehicle->type.' '.$vehicle->model.' con placa '.
                $vehicle->license_plate.' es registrado en el sistema de seguimiento de activos';
        }
        elseif($mode=='malfunction'){
            $vehicle_history->type = 'Reporte de falla';
            $vehicle_history->contents = ($user ? $user->name : 'Se').
                ' reportó las siguientes condiciones en el vehículo: '.$vehicle->condition;
        }
        elseif($mode=='disable'){
            $vehicle_history->type = 'Baja de vehículo';
            $vehicle_history->contents = ($user ? $user->name : 'Se').
                ' da de baja este vehículo por el siguiente motivo: '.$vehicle->condition;
        }

        $vehicle_history->status = $vehicle->status;
        $vehicle_history->historyable()->associate($model //Vehicle::find($vehicle->id));
        $vehicle_history->save();
    }
    */

    function add_failure_report($vehicle, $user)
    {
        $report = new VhcFailureReport();
        $report->code = 'RFV-'.Carbon::now()->format('ymdhis');
        $report->user_id = $user->id;
        $report->vehicle_id = $vehicle->id;
        $report->status = 0; //Pending status
        $report->reason = $vehicle->condition;
        $report->save();
    }

    function send_mail($vehicle, $user)
    {
        $recipient = User::where('work_type', 'Transporte')->where('branch_id', $vehicle->branch_id)->first();

        if($recipient){
            $data = array('recipient' => $recipient, 'responsible' => $user, 'vehicle' => $vehicle);
            $cc = $user->email;

            $mail_structure = 'emails.vehicle_malfunction_reported';
            $subject = 'Se reportó un problema con un vehículo';

            $view = View::make($mail_structure, $data /*['recipient' => $recipient, 'responsible' => $user,
            'vehicle' => $vehicle]*/);
            $content = (string) $view;

            $success = 1;

            try {
                Mail::send($mail_structure, $data, function($message) use($recipient, $cc, $subject) {
                    $message->to($recipient->email, $recipient->name)
                        ->cc($cc)
                        ->subject($subject)
                        ->from('postmaster@gerteabros.com', 'postmaster@gerteabros.com');
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
}
