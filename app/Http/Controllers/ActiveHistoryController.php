<?php

namespace App\Http\Controllers;

use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use View;
use App\DeviceHistory;
use App\Device;
use App\VehicleHistory;
use App\Vehicle;
use App\VehicleCondition;
use App\Driver;
use App\Maintenance;
use App\ServiceParameter;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;

class ActiveHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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

    public function vehicle_history_records($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id)) {
            return View('app.index', ['service' => 'active', 'user' => null]);
        }
        if($user->acc_active==0)
            return redirect()->action('LoginController@logout', ['service' => 'active']);

        $service = Session::get('service');

        $vehicle = Vehicle::find($id);
        $vehicle_histories = VehicleHistory::where('vehicle_id',$id)->orderBy('id','desc')->paginate(20);

        if(!$vehicle){
            Session::flash('message', "Sucedió un error al recuperar información del servidor, intente de nuevo por favor");
            return redirect()->back();
        }

        return View::make('app.vehicle_history', ['vehicle_histories' => $vehicle_histories, 'vehicle' => $vehicle,
            'service' => $service, 'user' => $user]);
    }

    public function device_history_records($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id)) {
            return View('app.index', ['service' => 'active', 'user' => null]);
        }
        if($user->acc_active==0)
            return redirect()->action('LoginController@logout', ['service' => 'active']);

        $service = Session::get('service');

        $device = Device::find($id);
        $device_histories = DeviceHistory::where('device_id',$id)->orderBy('id','desc')->paginate(20);

        if(!$device){
            Session::flash('message', "Sucedió un error al recuperar información del servidor, intente de nuevo por favor");
            return redirect()->back();
        }

        return View::make('app.device_history', ['device_histories' => $device_histories, 'device' => $device,
            'service' => $service, 'user' => $user]);
    }
}
