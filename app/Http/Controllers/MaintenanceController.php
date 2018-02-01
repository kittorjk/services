<?php

namespace App\Http\Controllers;

use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use View;
use Input;
use App\Maintenance;
use App\Driver;
use App\User;
use App\Vehicle;
use App\Device;
use App\ServiceParameter;
use App\VehicleHistory;
use App\DeviceHistory;
use Carbon\Carbon;
use App\Http\Traits\FilesTrait;

class MaintenanceController extends Controller
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
            return View('app.index', ['service' => 'active', 'user' => null]);
        }
        if($user->acc_active==0)
            return redirect()->action('LoginController@logout', ['service' => 'active']);

        $service = Session::get('service');

        $vhc = Input::get('vhc');
        $vh_id = Input::get('vh_id');
        $dvc = Input::get('dvc');
        $dv_id = Input::get('dv_id');

        $maintenances = Maintenance::where('id','>',0);

        if($vhc)
            $maintenances = $maintenances->where('vehicle_id','<>',0); // Filter to only vehicles
        
        if($dvc)
            $maintenances = $maintenances->where('device_id','<>',0); // Filter to only devices

        if(!is_null($vh_id))
            $maintenances = $maintenances->where('vehicle_id', $vh_id); // Filter using a vehicle id

        if(!is_null($dv_id))
            $maintenances = $maintenances->where('device_id', $dv_id); // Filter using a device id

        /* Filter results according to user permissions */
        if(($user->priv_level>=1&&$user->area=='Gerencia Tecnica')||$user->priv_level>=3){
            $maintenances = $maintenances->where('id','<>',0);
        }
        elseif($user->work_type=='Almacén'){
            $maintenances = $maintenances->where(function ($query) use($user) {
                $query->where('device_id','<>',0)->orwhere('user_id','=',$user->id);
            });
        }
        elseif($user->work_type=='Transporte'){
            $maintenances = $maintenances->where(function ($query) use($user) {
                $query->where('vehicle_id','<>',0)->orwhere('user_id','=',$user->id);
            });
        }
        else{
            $maintenances = $maintenances->where('user_id',$user->id);
            /*
            ->where(function ($query) use($user){
                $query->where('user_id',$user->id)
                    ->orwhere(function ($query1) use($user){
                        $query1->where('device_id','<>',0)->with(['devices' => function ($query2) use($user){
                            $query2->where('responsible', $user->id);
                        }]);
                    })
                    ->orwhere(function ($query1) use($user){
                        $query1->where('vehicle_id','<>',0)->with(['vehicles' => function ($query2) use($user){
                            $query2->where('responsible', $user->id);
                        }]);
                    });
            });
            */
        }

        $maintenances = $maintenances/*->where('completed', 0)*/->orderBy('id','desc')->paginate(20);

        return View::make('app.maintenance_brief', ['maintenances' => $maintenances, 'service' => $service,
            'user' => $user]);
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
        $service_parameters = ServiceParameter::where('group', 'Mantenimiento')->get();

        return View::make('app.maintenance_form', ['maintenance' => 0, 'service_parameters' => $service_parameters, 
            'service' => $service, 'user' => $user]);
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
            'active_type'           => 'required',
            'active'                => 'required',
            'type'                  => 'required',
            'cost'                  => 'numeric',
        ],
            [
                'active_type.required'          => 'Debe seleccionar un tipo de activo!',
                'active.required'               => 'Debe seleccionar un activo!',
                'type.required'                 => 'Debe especificar el tipo de mantenimiento!',
                'cost.numeric'                  => 'El campo costo debe contener solo números!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $maintenance = new Maintenance(Request::all());

        $maintenance->user_id = $user->id;
        $maintenance->completed = 0;
        $message = '';

        $active_type = Request::input('active_type');
        $active = Request::input('active');

        if($active_type=='vehicle' /*Request::input('active_type')*/){
            $vehicle = Vehicle::where('license_plate', $active /*Request::input('active')*/)->first();
            $vehicle->status = 'En mantenimiento';
            $vehicle->flags = '1000';
            $vehicle->save();

            $maintenance->vehicle_id = $vehicle->id;
            $maintenance->usage = $vehicle->mileage;
            $maintenance->save();

            /* insert new entry on vehicle history table */
            $this->add_vhc_history_record($vehicle, $maintenance, 'store', $user);

            $message = "Se movió el vehículo a activos en mantenimiento";
        }
        elseif($active_type=='device' /*Request::input('active_type')*/){
            $device = Device::where('serial', $active /*Request::input('active')*/)->first();
            $device->status = 'En mantenimiento';
            $device->flags = '1000';
            $device->save();

            $maintenance->device_id = $device->id;
            $maintenance->save();
            
            /* insert new entry on device history table */
            $this->add_dvc_history_record($device, $maintenance, 'store', $user);

            $message = "Se movió el equipo a activos en mantenimiento";
        }

        Session::flash('message', $message);
        return redirect()->route('maintenance.index');
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

        $maintenance = Maintenance::find($id);

        $maintenance->date = Carbon::parse($maintenance->date);
        $maintenance->created_at = Carbon::parse($maintenance->created_at);

        return View::make('app.maintenance_info', ['maintenance' => $maintenance, 'service' => $service, 'user' => $user]);
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

        $maintenance = Maintenance::find($id);
        $service_parameters = ServiceParameter::where('group','Mantenimiento')->get();

        return View::make('app.maintenance_form', ['maintenance' => $maintenance, 'service_parameters' => $service_parameters,
            'service' => $service, 'user' => $user]);
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
            'cost'  => 'numeric',
        ],
            [
                'cost.numeric'  => 'El campo costo debe contener solo números!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $maintenance = Maintenance::find($id);

        $maintenance->fill(Request::all());

        $maintenance->save();

        Session::flash('message', "El registro de mantenimiento del activo $maintenance->active ha sido actualizado");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('maintenance.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function close_maintenance($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $maintenance = Maintenance::find($id);

        $maintenance->date = Carbon::now();
        $maintenance->completed = 1;
        $maintenance->save();

        foreach($maintenance->files as $file){
            $this->blockFile($file);
        }

        if($maintenance->vehicle_id!=0&&$maintenance->vehicle){
            $vehicle = $maintenance->vehicle; /*Vehicle::find($maintenance->vehicle_id);*/

            if($vehicle->user&&$vehicle->user->work_type=='Transporte'){
                $vehicle->status = 'Disponible';
                $vehicle->flags = '0001';
            }
            else{
                $vehicle->status = 'Activo';
                $vehicle->flags = '0010';
            }

            $vehicle->save();

            /* insert new entry on vehicle history table */
            $this->add_vhc_history_record($vehicle, $maintenance, 'close', $user);
        }
        if($maintenance->device_id!=0&&$maintenance->device){
            $device = $maintenance->device; /*Device::find($maintenance->device_id);*/

            if($device->user&&$device->user->work_type=='Almacén'){
                $device->status = 'Disponible';
                $device->flags = '0001';
            }
            else{
                $device->status = 'Activo';
                $device->flags = '0010';
            }
            
            $device->save();

            /* insert new entry on device history table */
            $this->add_dvc_history_record($device, $maintenance, 'close', $user);
        }

        Session::flash('message', "El mantenimiento del activo $maintenance->active ha terminado");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('maintenance.index');
    }

    public function maintenance_required_list($type)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');
        
        $service = Session::get('service');

        if($type=='vehicle'){
            $vehicles = Vehicle::where('status', '<>', 'En mantenimiento')->get();
            /*
            foreach($vehicles as $vehicle){

                $difference = 0;
                //$maintenance = Maintenance::where('vehicle_id',$vehicle->id)->orderBy('created_at','desc')->first();

                if($maintenance)
                    $difference = $vehicle->mileage - $maintenance->usage;
                /*
                $r = $vehicle->mileage/2500;
                $whole_num = floor($r);
                $decimal = $r - $whole_num;

                if($vehicle->flags[1]==1||($difference>2400&&$difference<2600)||($difference>4900&&$difference<5100)||
                    ($difference>9900&&$difference<10100)){}
            }
            */

            foreach($vehicles as $vehicle){
                if($vehicle->last_maintenance)
                    $vehicle->last_maintenance->date = Carbon::parse($vehicle->last_maintenance->date);
            }

            return View::make('app.maintenance_required_vehicles', ['vehicles' => $vehicles, 'service' => $service,
                'user' => $user]);
        }
        elseif($type=='device'){
            $devices = Device::where('flags', 'like', '01%')->get();

            foreach($devices as $device){
                if($device->last_maintenance)
                    $device->last_maintenance->date = Carbon::parse($device->last_maintenance->date);
            }

            return View::make('app.maintenance_required_devices', ['devices' => $devices, 'service' => $service,
                'user' => $user]);
        }
        
        /* default redirection if no match is found */
        return redirect()->back();
    }

    public function move_to_maintenance(Request $request, $type)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');
        
        //$service = Session::get('service');

        $maintenance = new Maintenance(Request::all());

        $maintenance->user_id = $user->id;
        $maintenance->completed = 0;
        $maintenance->save();
        
        $message = '';

        if($type=='vehicle'&&$maintenance->vehicle){
            $vehicle = $maintenance->vehicle; //Vehicle::find($maintenance->vehicle_id);
            $vehicle->status = 'En mantenimiento';
            $vehicle->flags = '1000';
            $vehicle->save();

            /* insert new entry on vehicle history table */
            $this->add_vhc_history_record($vehicle, $maintenance, 'move', $user);

            $message = "Se movió el vehículo a activos en mantenimiento";
        }
        elseif($type=='device'&&$maintenance->device){
            $device = $maintenance->device; //Device::find($maintenance->device_id);
            $device->status = 'En mantenimiento';
            $device->flags = '1000';
            $device->save();

            /* insert new entry on device history table */
            $this->add_dvc_history_record($device, $maintenance, 'move', $user);

            $message = "Se movió el equipo a activos en mantenimiento";
        }

        Session::flash('message', $message);
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('maintenance.index');
    }

    function add_vhc_history_record($vehicle, $maintenance, $mode, $user)
    {
        $vehicle_history = new VehicleHistory;
        $vehicle_history->vehicle_id = $vehicle->id;

        if($mode=='store'||$mode=='move'){
            $vehicle_history->type = 'Mantenimiento';
            $vehicle_history->contents = 'El vehículo es puesto en mantenimiento '.$maintenance->type.' por '.$user->name;
        }
        elseif($mode=='close'){
            $vehicle_history->type = 'Fin de mantenimiento';
            $vehicle_history->contents = 'El vehículo sale de mantenimiento con el siguiente detalle de trabajos: '.
                $maintenance->detail;
        }

        $vehicle_history->status = $vehicle->status;
        $vehicle_history->historyable()->associate($maintenance /*Maintenance::find($maintenance->id)*/);
        $vehicle_history->save();
    }

    function add_dvc_history_record($device, $maintenance, $mode, $user)
    {
        $device_history = new DeviceHistory;
        $device_history->device_id = $device->id;

        if($mode=='store'||$mode=='move'){
            $device_history->type = 'Mantenimiento';
            $device_history->contents = 'El equipo es puesto en mantenimiento '.$maintenance->type.' por '.$user->name;
        }
        elseif($mode=='close'){
            $device_history->type = 'Fin de mantenimiento';
            $device_history->contents = 'El equipo sale de mantenimiento con el siguiente detalle de trabajos: '.
                $maintenance->detail;
        }

        $device_history->status = $device->status;
        $device_history->historyable()->associate($maintenance /*Maintenance::find($maintenance->id)*/);
        $device_history->save();
    }
}
