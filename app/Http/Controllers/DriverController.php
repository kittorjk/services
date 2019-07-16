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
use App\Assignment;
use App\License;
use App\Email;
// use App\VehicleHistory;
use App\VehicleRequirement;
use Carbon\Carbon;
use App\Http\Traits\FilesTrait;
use App\Http\Traits\ActiveTrait;

class DriverController extends Controller
{
    use ActiveTrait;
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
            return View('app.index', ['service' => 'active', 'user' => null]);
        }
        if($user->acc_active==0)
            return redirect()->action('LoginController@logout', ['service' => 'active']);

        $service = Session::get('service');

        $vhc = Input::get('vhc');
        $conf = Input::get('conf');

        $drivers = Driver::where('id', '>', 0);

        if(!is_null($vhc))
            $drivers = $drivers->where('vehicle_id', $vhc);

        if(!is_null($conf)&&$conf=='pending')
            $drivers = $drivers->where('confirmation_flags', 'like', '%0');
        
        if(!(($user->priv_level>=1 && $user->area=='Gerencia Tecnica') || $user->priv_level>=3 || $user->work_type=='Transporte' || $user->work_type=='Director Regional')){
            $drivers = $drivers->where(function ($query) use($user) {
                    $query->where('who_delivers',$user->id)
                        ->orwhere('who_receives','=',$user->id);
                });
        }

        $drivers = $drivers->orderBy('date','desc')->paginate(20);

        return View::make('app.driver_brief', ['drivers' => $drivers, 'service' => $service, 'user' => $user]);
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

        $req = Input::get('req');

        $requirement = VehicleRequirement::find($req);

        if(!$requirement){
            Session::flash('message', 'No se encontró el requerimiento para asignar un vehículo! Por favor
                verifique que dicho requerimiento exista');
            return redirect()->back();
        }

        /*
        $vehicles = collect();
        if($user->work_type=='Transporte'||$user->priv_level>=2){
            //$vehicles = Vehicle::where('status','<>','En mantenimiento')->orderBy('type')->get();
            $vehicles = Vehicle::where('status','Disponible')->orderBy('type')->get();
        }
        /*
        else{
            $last_driver = Driver::where('who_receives','=',$user->id)->orderBy('id','desc')->first();
            if($last_driver)
                $vehicles = Vehicle::where('id','=',$last_driver->vehicle_id)->where('status','<>','En mantenimiento')
                    ->orderBy('type')->get();
        }
        */

        return View::make('app.driver_form', ['driver' => 0, 'requirement' => $requirement, /*'vehicles' => $vehicles,*/
            'user' => $user, 'service' => $service]);
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
            'vehicle_requirement_id'=> 'required',
            'vehicle_id'            => 'required',
            'deliverer_name'        => 'required|exists:users,name',
            'receiver_name'         => 'required|exists:users,name',
            'destination'           => 'required',
            'project_code'          => 'exists:assignments,code', //'regex:[^(PR)-(\d{4})-(\d{2})$]',
            'mileage_before'        => 'required|numeric',
        ],
            [
                'vehicle_requirement_id.required'=> 'Debe seleccionar un requerimiento de vehículo',
                'vehicle_id.required'       => 'Debe seleccionar un vehículo!',
                'deliverer_name.required'   => 'Debe especificar la persona que entrega el vehículo!',
                'deliverer_name.exists'     => 'El nombre de la persona que entrega el vehículo no está registrado en el sistema!',
                'receiver_name.required'    => 'Debe especificar la persona que recibe el vehículo!',
                'receiver_name.exists'      => 'El nombre del receptor del vehículo no está registrado en el sistema!',
                'destination.required'      => 'Debe indicar el destino al que se trasladará el vehículo',
                'project_code.exists'       => 'El código de proyecto indicado no existe!',
                //'project_code.regex'        => 'El código de proyecto no tiene el formato correcto!',
                'mileage_before.required'   => 'Debe especificar el kilometraje del vehículo al momento del cambio!',
                'mileage_before.numeric'    => 'Valor de kilometraje inválido!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $driver = new Driver(Request::all());

        $project_code = Request::input('project_code');

        if(!empty($project_code)){
            //$project_code_exploded = explode('-',Request::input('project_code'));

            $assignment = Assignment::where('code', $project_code)->first(); //find($project_code_exploded[1]);

            if(empty($assignment)){
                Session::flash('message', "No se encontró un registro con el código de proyecto indicado!");
                return redirect()->back()->withInput();
            }
            /*
            elseif(date_format($assignment->created_at,'y')!=$project_code_exploded[2]){
                Session::flash('message', "El año indicado en el código de proyecto no corresponde al proyecto seleccionado!");
                return redirect()->back();
            }
            */
            elseif($assignment->status==$assignment->last_stat()/*'Concluído'*/||$assignment->status==0/*'No asignado'*/){
                Session::flash('message', "El código de proyecto indicado no corresponde a un proyecto activo");
                return redirect()->back()->withInput();
            }
            else{
                $driver->project_id = $assignment->id;
            }

            /*
            if(!empty($assignment)){
                $driver->project_id = $assignment->id;
            }
            else{
                Session::flash('message', " El proyecto indicado no existe! ");
                return redirect()->back();
            }
            */
        }

        $vehicle = Vehicle::find(Request::input('vehicle_id'));

        if($driver->mileage_before<=$vehicle->mileage){
            Session::flash('message', "El kilometraje actual debe ser mayor al último kilometraje registrado del vehículo!");
            return redirect()->back()->withInput();
        }
        
        $driver->user_id = $user->id;
        $driver->date = Carbon::now();

        $deliverer = User::where('name', Request::input('deliverer_name'))->first();
        $receiver = User::where('name', Request::input('receiver_name'))->first();

        if($deliverer==''){
            Session::flash('message', "El nombre de la persona que entrega el vehículo no está registrado en el sistema!");
            return redirect()->back()->withInput();
        }
        else
            $driver->who_delivers = $deliverer->id;

        if($receiver==''){
            Session::flash('message', "El nombre de la persona que recibe el vehículo no está registrado en el sistema!");
            return redirect()->back()->withInput();
        }
        else{
            if($receiver->work_type!='Transporte' && $receiver->work_type!='Director Regional'){
                $vehicles_assigned = Vehicle::where('responsible', $receiver->id)->get();
                if($vehicles_assigned->count()>0){
                    Session::flash('message', "El receptor del vehículo ya tiene asignado un vehículo!");
                    return redirect()->back()->withInput();
                }
            }
            $driver->who_receives = $receiver->id;
        }

        $license = License::where('user_id', $receiver->id)->first();

        if(empty($license)){
            Session::flash('message', "El receptor del vehículo no tiene registrada una licencia de conducir!");
            return redirect()->back()->withInput();
        }
        elseif(Carbon::now()>=Carbon::parse($license->exp_date)){
            Session::flash('message', "La licencia de conducir del receptor del vehículo ha vencido!");
            return redirect()->back()->withInput();
        }

        if($driver->who_delivers==$user->id)
            $driver->confirmation_flags = '0010';
        elseif($driver->who_receives==$user->id)
            $driver->confirmation_flags = '0011'; //Confirmed by both
        else
            $driver->confirmation_flags = '0010'; //Person who delivers confirms by default
        
        $driver->save();

        /* Update last assignation mileage_traveled */
        $last_asg = Driver::where('who_receives', $driver->who_delivers)->where('vehicle_id', $driver->vehicle_id)
            ->orderBy('created_at','desc')->first();

        if($last_asg){
            $last_asg->mileage_traveled = $driver->mileage_before - $last_asg->mileage_before;
            $last_asg->mileage_after = $driver->mileage_before;
            $last_asg->save();
        }

        /* Update requirements status */
        $requirement = $driver->requirement;
        $requirement->status = 2; //Requirement completed
        $requirement->stat_change = Carbon::now();

        $requirement->save();

        /* Update vehicle information */
        $vehicle = $driver->vehicle; //Vehicle::find($driver->vehicle_id);
        $vehicle->mileage = $driver->mileage_before;

        if($requirement->type=='borrow'||$requirement->type=='transfer_tech'){
            $vehicle->status = 'Activo';
            $vehicle->flags = '0010';
        }
        elseif($requirement->type=='transfer_branch'||$requirement->type=='devolution'){
            $vehicle->status = 'Disponible';
            $vehicle->flags = '0001';

            if($requirement->type=='transfer_branch'){
                $vehicle->branch = $requirement->branch_destination;
            }
        }

        $vehicle->destination = $driver->destination;
        $vehicle->responsible = $receiver->id;
        /*
        $r = $vehicle->mileage/2500;
        $whole_num = floor($r);
        $decimal = $r - $whole_num;

        if($decimal>=0.95){
            $vehicle->flags = '0110';
        }
        else{
            $vehicle->flags = '0010';
        }
        */
        $vehicle->save();

        /* insert new entry on vehicle history table */
        $mode = $requirement->type=='devolution' ? 'Devolución' : 'Asignación';
        // $this->record_history_entry($driver, $vehicle, $type);
        $this->add_vhc_history_record($vehicle, $driver, $mode, $user, 'driver');

        /* Send email notification */
        $recipient = User::find($vehicle->responsible);
        $data = array('recipient' => $recipient, 'vehicle' => $vehicle);

        $mail_structure = 'emails.assigned_vehicle';
        $subject = 'Nueva asignación de vehículo';

        $this->send_email($recipient, '', $data, $mail_structure, $subject);

        Session::flash('message', "El cambio de responsable de vehículo fue registrado correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('driver.index');
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

        $driver = Driver::find($id);

        return View::make('app.driver_info', ['driver' => $driver, 'service' => $service, 'user' => $user]);
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

        $driver = Driver::find($id);

        $requirement = $driver->requirement;

        if(!$requirement){
            Session::flash('message', 'No se encontró el requerimiento de vehículo para esta asignación! Por favor
                verifique que dicho requerimiento exista');
            return redirect()->back();
        }

        /*
        $vehicles = Vehicle::where('status','Disponible')->orwhere('id',$driver->vehicle_id)->orderBy('type')->get();

        $driver->date = Carbon::parse($driver->date)->format('Y-m-d');

        $clients = Assignment::select('client')->where('client', '<>', '')->groupBy('client')->get();
        */

        return View::make('app.driver_form', ['driver' => $driver, 'requirement' => $requirement,
            /*'vehicles' => $vehicles,*/ 'service' => $service, 'user' => $user]);
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
            'vehicle_requirement_id'=> 'required',
            'vehicle_id'            => 'required',
            'deliverer_name'        => 'required|exists:users,name',
            'receiver_name'         => 'required|exists:users,name',
            'destination'           => 'required',
            'project_code'          => 'exists:assignments,code', //'regex:[^(PR)-(\d{4})-(\d{2})$]',
            'mileage_before'        => 'required|numeric',
        ],
            [
                'vehicle_requirement_id.required'=> 'Debe seleccionar un requerimiento de vehículo',
                'vehicle_id.required'       => 'Debe seleccionar un vehículo!',
                'deliverer_name.required'   => 'Debe especificar la persona que entrega el vehículo!',
                'deliverer_name.exists'     => 'El nombre de la persona que entrega el vehículo no está registrado en el sistema!',
                'receiver_name.required'    => 'Debe especificar la persona que recibe el vehículo!',
                'receiver_name.exists'      => 'El nombre del receptor del vehículo no está registrado en el sistema!',
                'destination.required'      => 'Debe indicar el destino al que se trasladará el vehículo',
                'project_code.exists'       => 'El código de proyecto indicado no existe!',
                //'project_code.regex'        => 'El código de proyecto no tiene el formato correcto!',
                'mileage_before.required'   => 'Debe especificar el kilometraje del vehículo al momento del cambio!',
                'mileage_before.numeric'    => 'Valor de kilometraje inválido!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $driver = Driver::find($id);

        $driver->fill(Request::all());

        $project_code = Request::input('project_code');

        if(!empty($project_code)){
            //$project_code_exploded = explode('-',Request::input('project_code'));

            $assignment = Assignment::where('code', $project_code)->first(); //find($project_code_exploded[1]);

            if(empty($assignment)){
                Session::flash('message', "No se encontró un registro con el código de proyecto indicado!");
                return redirect()->back()->withInput();
            }
            /*
            elseif(date_format($assignment->created_at,'y')!=$project_code_exploded[2]){
                Session::flash('message', "El año indicado en el código de proyecto no corresponde al proyecto seleccionado!");
                return redirect()->back();
            }
            */
            elseif($assignment->status==$assignment->last_stat()/*'Concluído'*/||$assignment->status==0/*'No asignado'*/){
                Session::flash('message', "El código de proyecto indicado no corresponde a un proyecto activo");
                return redirect()->back()->withInput();
            }
            else{
                $driver->project_id = $assignment->id;
            }

            /*
            if(!empty($assignment)){
                $driver->project_id = $assignment->id;
            }
            else{
                Session::flash('message', " El proyecto indicado no existe! ");
                return redirect()->back();
            }
            */
        }

        $deliverer = User::where('name',Request::input('deliverer_name'))->first();
        $receiver = User::where('name',Request::input('receiver_name'))->first();

        if($deliverer==''){
            Session::flash('message', "El nombre de la persona que entrega el vehículo no está registrado en el sistema!");
            return redirect()->back();
        }
        else
            $driver->who_delivers = $deliverer->id;

        if($receiver==''){
            Session::flash('message', "El nombre de la persona que recibe el vehículo no está registrado en el sistema!");
            return redirect()->back();
        }
        else
            $driver->who_receives = $receiver->id;

        $driver->save();

        /* Update last assignation mileage_traveled */
        $last_asg = Driver::where('who_receives', $driver->who_delivers)->where('vehicle_id', $driver->vehicle_id)
            ->orderBy('created_at','desc')->first();

        if($last_asg){
            $last_asg->mileage_traveled = $driver->mileage_before - $last_asg->mileage_before;
            $last_asg->mileage_after = $driver->mileage_before;
            $last_asg->save();
        }

        /* Update vehicle responsible */
        $vehicle = $driver->vehicle; //Vehicle::find($driver->vehicle_id);
        $vehicle->mileage = $driver->mileage_before;
        $vehicle->destination = $driver->destination;

        $prev_responsible = $vehicle->responsible; //Get previous value to determine if it changed

        $vehicle->responsible = $receiver->id;
        /*
        $r = $vehicle->mileage/2500;
        $whole_num = floor($r);
        $decimal = $r - $whole_num;

        if($decimal>=0.95){
            $vehicle->flags = '0110';
        }
        else{
            $vehicle->flags = '0010';
        }
        */
        $vehicle->save();

        if($prev_responsible!=$vehicle->responsible){
            //If the responsible has changed

            /*
            $vehicle_history = $driver->vehicle_history ?: 0;
            $vehicle_history = VehicleHistory::where('historyable_type','App\Driver')
                ->where('historyable_id',$driver->id)->first();
            */

            /* insert new entry on vehicle history table */
            $mode = 'Corrección de asignación/devolución';
            // $this->record_history_entry($driver, $vehicle, $type);
            $this->add_vhc_history_record($vehicle, $driver, $mode, $user, 'driver');

            /* Send notification email */
            $recipient = User::find($vehicle->responsible);
            $data = array('recipient' => $recipient, 'vehicle' => $vehicle);

            $mail_structure = 'emails.assigned_vehicle';
            $subject = 'Nueva asignación de vehículo';

            $this->send_email($recipient, '', $data, $mail_structure, $subject);
        }

        Session::flash('message', "Datos modificados correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('driver.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //This option is disabled
    }

    public function devolution_form($id)
    {
        return redirect()->back(); //Function deprecated
        /*
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $vehicle = Vehicle::find($id);

        $last_driver = Driver::where('vehicle_id',$vehicle->id)->orderBy('id','desc')->first();
        
        if(!$last_driver){
            Session::flash('message', "Ocurrió un error al recuperar los datos del servidor. Intente de nuevo por favor.");
            return redirect()->back();
        }

        return View::make('app.driver_devolution_form', ['last_driver' => $last_driver, 'vehicle' => $vehicle,
            'service' => $service, 'user' => $user]);
        */
    }

    public function record_devolution(Request $request)
    {
        return redirect()->back(); //Function deprecated
        /*
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $last_driver = Driver::where('vehicle_id',Request::input('vehicle_id'))->orderBy('id','desc')->first();
        $garage_responsible = User::where('work_type','Transporte')->first();

        $driver = new Driver(Request::all());

        $v = \Validator::make(Request::all(), [
            'vehicle_id'            => 'required',
            'mileage_before'        => 'required|numeric',
        ],
            [
                'vehicle_id.required'       => 'Debe seleccionar un vehículo!',
                'mileage_before.required'   => 'Debe especificar el kilometraje del vehículo al momento del cambio!',
                'mileage_before.numeric'    => 'Valor de kilometraje inválido!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back();
        }

        $vehicle = Vehicle::find(Request::input('vehicle_id'));

        if($driver->mileage_before<=$vehicle->mileage){
            Session::flash('message', "El kilometraje indicado debe ser mayor que el kilometraje previo del vehículo!");
            return redirect()->back();
        }

        $driver->user_id = $user->id;
        $driver->date = Carbon::now();

        $driver->who_delivers = $last_driver->who_receives;
        $driver->who_receives = $garage_responsible ? $garage_responsible->id : $user->id;

        $driver->confirmation_flags = '0001';
        $driver->destination = 'Garage';
        $driver->reason = 'Devolución de vehículo a encargado de transporte';
        $driver->save();

        $vehicle = Vehicle::find($driver->vehicle_id);
        $vehicle->mileage = $driver->mileage_before;
        $vehicle->status = 'Disponible';
        $vehicle->flags = '0001';
        $vehicle->responsible = $driver->who_receives;
        $vehicle->destination = 'Garage';

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

        /* insert new entry on vehicle history table
        $vehicle_history = new VehicleHistory;
        $vehicle_history->vehicle_id = $vehicle->id;
        $vehicle_history->type = 'Devolución';
        $vehicle_history->contents = 'El vehículo '.$vehicle->type.' '.$vehicle->model.' con placa '.
            $vehicle->license_plate.' es devuelto a encargado de transporte';
        $vehicle_history->status = $vehicle->status;
        $vehicle_history->historyable()->associate(Driver::find($driver->id));
        $vehicle_history->save();

        Session::flash('message', "El estado del vehículo ha cambiado a disponible correctamente");
        return redirect()->route('vehicle.index');
        */
    }

    public function reception_confirmation_form($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $driver = Driver::find($id);

        if(!$driver){
            Session::flash('message', 'No se encontró el registro solicitado!');
            return redirect()->back();
        }

        return View::make('app.driver_confirmation_form', ['driver' => $driver, 'service' => $service,
            'user' => $user]);
    }

    public function confirm_reception(Request $request, $id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $driver = Driver::find($id);

        $driver->fill(Request::all());

        $driver->date_confirmed = Carbon::now();

        if($driver->confirmation_flags[3]==0){
            $driver->confirmation_flags = str_pad($driver->confirmation_flags+1, 4, "0", STR_PAD_LEFT);

            foreach($driver->files as $file){
                $this->blockFile($file);
            }
        }

        $driver->save();

        $vehicle = $driver->vehicle;

        /* Insert new entry on vehicle history table */
        $mode = 'Confirmación de recepción';
        // $this->record_history_entry($driver, $vehicle, $type);
        $this->add_vhc_history_record($vehicle, $driver, $mode, $user, 'driver');

        Session::flash('message', "Se ha confirmado la recepción de este vehículo");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('driver.index');
    }

    function send_email($recipient, $cc, $data, $mail_structure, $subject)
    {
        $view = View::make($mail_structure, $data /*['recipient' => $recipient, 'vehicle' => $vehicle]*/);
        $content = (string) $view;

        $success = 1;

        try {
            Mail::send($mail_structure, $data, function($message) use($recipient, $subject) {
                $message->to($recipient->email, $recipient->name)
                    ->subject($subject)
                    ->from('postmaster@gerteabros.com', 'postmaster@gerteabros.com');
            });
        } catch (Exception $ex) {
            $success = 0;
        }

        $email = new Email;
        $email->sent_by = 'postmaster@gerteabros.com';
        $email->sent_to = $recipient->email;
        $email->subject = $subject;
        $email->content = $content;
        $email->success = $success;
        $email->save();
    }

    /*
    function record_history_entry($driver, $vehicle, $type)
    {
        $vehicle_history = new VehicleHistory;
        $vehicle_history->vehicle_id = $vehicle->id;
        $vehicle_history->type = $type;

        if($type=='Confirmación de recepción'){
            $vehicle_history->contents = ($driver->receiver ? $driver->receiver->name : '').' confirmó que recibió el vehículo '.
                $vehicle->type.' '.$vehicle->model.' con placa '.$vehicle->license_plate.($driver->confirmation_obs ?
                    ' con las siguientes observaciones: '.$driver->confirmation_obs : '');
        }
        else{
            $vehicle_history->contents = 'Se entrega el vehículo '.$vehicle->type.' '.$vehicle->model.' con placa '.
                $vehicle->license_plate.' a '.$driver->receiver->name;
        }

        $vehicle_history->status = $vehicle->status;
        $vehicle_history->historyable()->associate($driver);
        $vehicle_history->save();
    }
    */
}
