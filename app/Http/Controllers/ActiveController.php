<?php

namespace App\Http\Controllers;

use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Session;
use View;
use Hash;

use App\ClientSession;
use App\Device;
use App\DeviceRequirement;
use App\Driver;
use App\File;
use App\Maintenance;
use App\Operator;
use App\User;
use App\Vehicle;
use App\VehicleCondition;
use App\VehicleRequirement;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
use App\Http\Traits\UserTrait;

class ActiveController extends Controller
{
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
            return View('app.index', ['service' => 'active', 'user' => null]);
        }
        if($user->acc_active==0)
            return redirect()->action('LoginController@logout', ['service' => 'active']);

        Session::put('service', 'active');
        
        $service = Session::get('service');
        
        $this->trackService($user, $service);

        if(($user->priv_level>=1&&$user->area=='Gerencia Tecnica')||$user->priv_level>=3){
            $drivers = Driver::where('id', '>', 0)->orderBy('date','desc')->take(5)->get();
            $operators = Operator::where('id','>',0)->orderBy('date','desc')->take(5)->get();

            $vehicle_requirements = VehicleRequirement::where('id', '>', 0)->orderBy('created_at', 'desc')->take(5)->get();
            $device_requirements = DeviceRequirement::where('id', '>', 0)->orderBy('created_at','desc')->take(5)->get();

            //$condition_records = VehicleCondition::where('id','>',0)->orderBy('created_at','desc')->take(5)->get();
        }
        elseif($user->work_type=='Almacén'){
            $drivers = Driver::where(function ($query) use($user){
                $query->where('who_delivers',$user->id)->orwhere('who_receives','=',$user->id);
            })->orderBy('date','desc')->take(5)->get();

            $operators = Operator::where('id','>',0)->orderBy('date','desc')->take(5)->get();

            $vehicle_requirements = VehicleRequirement::where(function ($query) use($user){
                $query->where('from_id',$user->id)->orwhere('for_id','=',$user->id);
            })->orderBy('created_at','desc')->take(5)->get();

            $device_requirements = DeviceRequirement::where('id', '>', 0)->orderBy('created_at','desc')->take(5)->get();

            //$drivers = Driver::where('who_delivers',$user->id)->orwhere('who_receives','=',$user->id)
            //    ->orderBy('date','desc')->take(5)->get();

            //$condition_records = VehicleCondition::where('user_id',$user->id)->orderBy('created_at','desc')->take(5)->get();
        }
        elseif($user->work_type=='Transporte'){
            $drivers = Driver::where('id', '>', 0)->orderBy('date','desc')->take(5)->get();

            $operators = Operator::where(function ($query) use($user){
                $query->where('who_delivers',$user->id)->orwhere('who_receives','=',$user->id);
            })->orderBy('date','desc')->take(5)->get();

            $vehicle_requirements = VehicleRequirement::where('id', '>', 0)->orderBy('created_at', 'desc')->take(5)->get();

            $device_requirements = DeviceRequirement::where(function ($query) use($user){
                $query->where('from_id',$user->id)->orwhere('for_id','=',$user->id);
            })->orderBy('created_at','desc')->take(5)->get();
            
            //$operators = Operator::where('who_delivers',$user->id)->orwhere('who_receives','=',$user->id)
            //    ->orderBy('date','desc')->take(5)->get();

            //$condition_records = VehicleCondition::where('id','>',0)->orderBy('created_at','desc')->take(5)->get();
        }
        else{
            $drivers = Driver::where(function ($query) use($user){
                $query->where('who_delivers',$user->id)->orwhere('who_receives','=',$user->id);
            })->orderBy('date','desc')->take(5)->get();

            $operators = Operator::where(function ($query) use($user){
                $query->where('who_delivers',$user->id)->orwhere('who_receives','=',$user->id);
            })->orderBy('date','desc')->take(5)->get();

            $vehicle_requirements = VehicleRequirement::where(function ($query) use($user){
                $query->where('from_id',$user->id)->orwhere('for_id','=',$user->id);
            })->orderBy('created_at','desc')->take(5)->get();

            $device_requirements = DeviceRequirement::where(function ($query) use($user){
                $query->where('from_id',$user->id)->orwhere('for_id','=',$user->id);
            })->orderBy('created_at','desc')->take(5)->get();
            
            /*
            $drivers = Driver::where('who_delivers',$user->id)->orwhere('who_receives','=',$user->id)
                ->orderBy('date','desc')->take(5)->get();
            $operators = Operator::where('who_delivers',$user->id)->orwhere('who_receives','=',$user->id)
                ->orderBy('date','desc')->take(5)->get();
            $condition_records = VehicleCondition::where('user_id',$user->id)->orderBy('created_at','desc')->take(5)->get();
            */
        }

        $vehicles = Vehicle::where('status', '<>', 'En mantenimiento')->get();

        $vehicle_maintenance_counter = 0;
        foreach($vehicles as $vehicle){

            //$difference = 0;
            //$maintenance = Maintenance::where('vehicle_id','=',$vehicle->id)->orderby('created_at','desc')->first();
            /*
            if($vehicle->last_maintenance)
                $difference = $vehicle->mileage - $vehicle->last_maintenance->usage;

            /*
            $r = $vehicle->mileage/2500;
            $entero = floor($r);
            $decimal = $r-$entero;
            */
            if($vehicle->flags[1]==1||
                ($vehicle->last_mant20000&&(($vehicle->mileage-$vehicle->last_mant20000->usage)>19900))||
                (!$vehicle->last_mant20000&&$vehicle->mileage>19900)||
                ($vehicle->last_mant10000&&(($vehicle->mileage-$vehicle->last_mant10000->usage)>9900))||
                (!$vehicle->last_mant10000&&$vehicle->mileage>9900)||
                ($vehicle->last_mant5000&&(($vehicle->mileage-$vehicle->last_mant5000->usage)>4900))||
                (!$vehicle->last_mant5000&&$vehicle->mileage>4900)||
                ($vehicle->last_mant2500&&(($vehicle->mileage-$vehicle->last_mant2500->usage)>2400))||
                (!$vehicle->last_mant2500&&$vehicle->mileage>2400)
            )
                //($difference>2400&&$difference<2600)||($difference>4900&&$difference<5100)||
                //($difference>9900&&$difference<10100))
            {
                $vehicle_maintenance_counter++;
            }
        }
        
        $device_maintenance_counter = Device::where('flags', 'like','01%')->count(); //->get(); $devices->count();

        return View::make('app.active_brief', ['operators' => $operators, 'drivers' => $drivers,
            'vehicle_maintenance_counter' => $vehicle_maintenance_counter, 'vehicles' => $vehicles,
            'device_maintenance_counter' => $device_maintenance_counter, /*'devices' => $devices,*/
            /*'condition_records' => $condition_records,*/ 'vehicle_requirements' => $vehicle_requirements,
            'device_requirements' => $device_requirements, 'service' => $service, 'user' => $user]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
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
}
