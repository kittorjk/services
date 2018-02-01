<?php

namespace App\Http\Controllers;

use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use View;
use Input;
use App\Calibration;
use App\DeviceHistory;
use App\User;
use App\Device;
use Carbon\Carbon;
use App\Http\Traits\FilesTrait;

class CalibrationController extends Controller
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

        $dvc = Input::get('dvc');   // Filter records by device id

        if(($user->priv_level==2&&$user->area=='Gerencia Tecnica')||$user->work_type=='Almacén'||$user->priv_level>=3){

            $calibrations = Calibration::where('id', '>', 0);

            if(!is_null($dvc))
                $calibrations = $calibrations->where('device_id', $dvc);
            else
                $calibrations = $calibrations->where('completed',0)->orwhere('date_in','>=',Carbon::now()->subDays(60));

            $calibrations = $calibrations->orderBy('updated_at','desc')->paginate(20);
        }
        else{
            Session::flash('message', "Usted no tiene permiso para ver esta sección");
            return redirect()->back();
        }

        return View::make('app.calibration_brief', ['calibrations' => $calibrations, 'service' => $service,
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
        $devices = Device::where('flags','like','0%')->get();

        $preselected_id = Input::get('id') ? Input::get('id') : 0;

        return View::make('app.calibration_form', ['calibration' => 0, 'devices' => $devices, 'service' => $service,
            'preselected_id' => $preselected_id, 'user' => $user]);
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
            'date_in'               => 'date',
            'date_out'              => 'date|after:date_in',
        ],
            [
                'device_id.required'            => 'Debe seleccionar un equipo!',
                'date_in.date'                  => 'La fecha de ingreso a calibración tiene un formato incorrecto!',
                'date_out.date'                 => 'La fecha de salida de calibración tiene un formato incorrecto!',
                'date_out.after'                => 'La fecha de salida no puede ser anterior a la fecha de ingreso!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back();
        }

        $calibration = new Calibration(Request::all());

        $calibration->user_id = $user->id;

        if(empty(Request::input('date_in'))){
            $calibration->date_in = Carbon::now();
        }

        $calibration->save();

        /* Update changes in device record */
        $device = Device::find($calibration->device_id);
        $device->status = 'En calibración';
        $device->flags = '1000';
        $device->save();

        /* insert new entry on device history table */
        $this->insert_history_record($calibration, $device, 'store_calibration');

        Session::flash('message', "Se ha insertado un nuevo registro de calibración en el sistema");
        return redirect()->route('calibration.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        /*
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $calibration = Calibration::find($id);

        return View::make('app.calibration_info', ['calibration' => $calibration, 'service' => $service,
            'user' => $user]);
        */
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

        $calibration = Calibration::find($id);

        $devices = Device::where('flags','like','0%')->orwhere('id',$calibration->device_id)->get();

        $preselected_id = $calibration->device_id;

        $calibration->date_in = Carbon::parse($calibration->date_in)->format('Y-m-d');
        $calibration->date_out = Carbon::parse($calibration->date_out)->format('Y-m-d');

        return View::make('app.calibration_form', ['calibration' => $calibration, 'devices' => $devices,
            'service' => $service, 'preselected_id' => $preselected_id, 'user' => $user]);
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

        $calibration = Calibration::find($id);
        $prev_device = Device::find($calibration->device_id);

        $v = \Validator::make(Request::all(), [
            'device_id'             => 'required',
            'date_in'               => 'date',
            'date_out'              => 'date|after:date_in',
        ],
            [
                'device_id.required'            => 'Debe seleccionar un equipo!',
                'date_in.date'                  => 'La fecha de ingreso a calibración tiene un formato incorrecto!',
                'date_out.date'                 => 'La fecha de salida de calibración tiene un formato incorrecto!',
                'date_out.after'                => 'La fecha de salida no puede ser anterior a la fecha de ingreso!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back();
        }

        $calibration->fill(Request::all());

        $calibration->user_id = $user->id;

        $calibration->save();

        if($prev_device->id!=$calibration->device_id){
            /* Update changes in new device record */
            $device = Device::find($calibration->device_id);
            $device->status = 'En mantenimiento';
            $device->flags = '1000';
            $device->save();

            /* Make available previous device */
            $prev_device->status = 'Disponible';
            $prev_device->flags = '0001';
            $prev_device->save();
        }
        
        Session::flash('message', "El registro de calibración ha sido modificado");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('calibration.index');
    }

    public function close_record($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $calibration = Calibration::find($id);

        $calibration->date_out = Carbon::now();
        $calibration->completed = 1;

        $calibration->save();

        foreach($calibration->files as $file){
            $this->blockFile($file);
        }
        
        /* Set device record to available */
        $device = Device::find($calibration->device_id);
        $device->status = 'Disponible';
        $device->flags = '0001';
        $device->save();

        /* Insert new entry on device history table */
        $this->insert_history_record($calibration, $device, 'close_calibration');

        Session::flash('message', "El registro de calibración ha sido modificado");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('calibration.index');
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

    public function insert_history_record($calibration, $device, $mode)
    {
        $device_history = new DeviceHistory;
        $device_history->device_id = $device->id;

        if($mode=='store_calibration'){
            $device_history->type = 'Calibración';
            $device_history->contents = 'El equipo '.$device->type.' '.$device->model.' con S/N '.$device->serial.
                ' es puesto en mantenimiento por motivo de calibración';
        }
        elseif($mode=='close_calibration'){
            $device_history->type = 'Fin de calibración';
            $device_history->contents = 'El equipo '.$device->type.' '.$device->model.' con S/N '.$device->serial.
                ' sale de calibración con el siguiente resumen: '.$calibration->detail;
        }

        $device_history->status = $device->status;
        $device_history->historyable()->associate(Calibration::find($calibration->id));
        $device_history->save();
    }
}
