<?php

namespace App\Http\Controllers;

use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use View;
use App\Device;
use App\DeviceCharacteristic;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;

class DeviceCharacteristicController extends Controller
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

        return redirect()->back();
    }

    public function device_characteristics($id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $device_info = Device::find($id);

        if(!$device_info){
            Session::flash('message', 'No se encontró la información solicitada!');
            return redirect()->back();
        }

        $characteristics = $device_info->characteristics()->paginate(20); //DeviceCharacteristic::where('device_id', $id)

        $current_date = Carbon::now()->hour(0)->minute(0)->second(0);

        return View::make('app.device_characteristics_brief', ['device_info' => $device_info, 'service' => $service,
            'characteristics' => $characteristics, 'current_date' => $current_date, 'user' => $user]);
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

        $device = Device::find($id);

        if(!$device){
            Session::flash('message', "No se pudo recuperar el registro solicitado, intente de nuevo por favor.");
            return redirect()->back();
        }

        $types = DeviceCharacteristic::select('type')->where('type','<>','')->groupBy('type')->get();
        $units = DeviceCharacteristic::select('units')->where('units','<>','')->groupBy('units')->get();
        
        $current_date = Carbon::now()->format('Y-m-d');

        return View::make('app.device_characteristics_form', ['characteristic' => 0, 'user' => $user,
            'service' => $service, 'device' => $device, 'types' => $types, 'units' => $units, 
            'current_date' => $current_date]);
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
            'device_id'             => 'required',
            'type'                  => 'required',
            'other_type'            => 'required_if:type,Otro',
            'value'                 => 'required',
        ],
            [
                'device_id.required'         => 'Debe especificar el equipo al que pertenece esta característica!',
                'type.required'              => 'Debe especificar el tipo de característica!',
                'other_type.required_if'     => 'Debe especificar el tipo de característica!',
                'value.required'             => 'Debe especificar el valor de la característica!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $characteristic = new DeviceCharacteristic(Request::all());

        $characteristic->type = $characteristic->type=="Otro" ? Request::input('other_type') : $characteristic->type;
        
        if ($characteristic->units=="Otro") {
            $characteristic->units = Request::input('other_units');
            if ($characteristic->units==""&&is_numeric($characteristic->value)) {
                Session::flash('message', "Debe especificar las unidades de la característica si el valor  
                    es numérico!");
                return redirect()->back()->withInput();
            }
        }

        $characteristic->save();

        Session::flash('message', "Se agregó una nueva característica a este equipo correctamente");
        return redirect()->route('device.index');
        //return redirect()->action('DeviceCharacteristicController@device_characteristics',
        //    ['id' => $characteristic->device_id]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return redirect()->back();
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

        $characteristic = DeviceCharacteristic::find($id);

        $device = $characteristic->device; //Device::find($id);

        $types = DeviceCharacteristic::select('type')->where('type','<>','')->groupBy('type')->get();
        $units = DeviceCharacteristic::select('units')->where('units','<>','')->groupBy('units')->get();

        $current_date = Carbon::now()->format('Y-m-d');

        return View::make('app.device_characteristics_form', ['characteristic' => $characteristic, 'user' => $user,
            'service' => $service, 'device' => $device, 'types' => $types, 'units' => $units,
            'current_date' => $current_date]);
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
            'device_id'             => 'required',
            'type'                  => 'required',
            'other_type'            => 'required_if:type,Otro',
            'value'                 => 'required',
        ],
            [
                'device_id.required'         => 'Debe especificar el equipo al que pertenece esta característica!',
                'type.required'              => 'Debe especificar el tipo de característica!',
                'other_type.required_if'     => 'Debe especificar el tipo de característica!',
                'value.required'             => 'Debe especificar el valor de la característica!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $characteristic = DeviceCharacteristic::find($id);

        $characteristic->fill(Request::all());

        $characteristic->type = $characteristic->type=="Otro" ? Request::input('other_type') : $characteristic->type;

        if ($characteristic->units=="Otro") {
            $characteristic->units = Request::input('other_units');
            if ($characteristic->units==""&&is_numeric($characteristic->value)) {
                Session::flash('message', "Debe especificar las unidades de la característica si el valor es numérico!");
                return redirect()->back()->withInput();
            }
        }

        $characteristic->save();

        Session::flash('message', "El registro ha sido modificado");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('device.index');
        //return redirect()->action('DeviceCharacteristicController@device_characteristics',
        //    ['id' => $characteristic->device_id]);
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

        $record = DeviceCharacteristic::find($id);
        //$device_id = $record->device_id;

        if($record) {
            $record->delete();

            Session::flash('message', "El registro ha sido eliminado");
            if(Session::has('url'))
                return redirect(Session::get('url'));
            else
                return redirect()->route('device.index');
            //return redirect()->action('DeviceCharacteristicController@device_characteristics', ['id' => $device_id]);
        }
        else {
            Session::flash('message', "Error al borrar el registro, intente de nuevo por favor");
            return redirect()->back();
        }
    }
}
