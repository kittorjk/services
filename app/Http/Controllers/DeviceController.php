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
use App\Device;
use App\Maintenance;
use App\DvcFailureReport;
use App\Operator;
use App\DeviceHistory;
use App\Email;
use App\Branch;
use Carbon\Carbon;
use App\Http\Traits\FilesTrait;

class DeviceController extends Controller
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

        $devices = Device::where('id', '>', 0);

        if(!(($user->priv_level>=1&&$user->area=='Gerencia Tecnica')||$user->priv_level>=3||$user->work_type=='Almacén'))
            $devices = $devices->where('responsible', $user->id);

        $db_query = $devices->where('status','<>','Baja')->orderBy('updated_at','desc')->get();

        Session::put('db_query', $db_query);

        $devices = $devices->orderBy('updated_at','desc')->paginate(20);

        foreach($devices as $device){
            if($device->last_operator)
                $device->last_operator->date = Carbon::parse($device->last_operator->date);
        }

        return View::make('app.device_brief', ['devices' => $devices, 'service' => $service, 'user' => $user]);
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
        $owners = Device::select('owner')->where('owner', '<>', 'ABROS')->where('owner','<>','')->groupBy('owner')->get();

        $branches = Branch::select('id', 'name', 'city')->where('name','<>','')->where('active', 1)->orderBy('name')->get();
        
        return View::make('app.device_form', ['device' => 0, 'owners' => $owners, 'service' => $service,
            'branches' => $branches, 'user' => $user]);
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
            'serial'            => 'required|unique:devices',
            'type'              => 'required',
            'model'             => 'required',
            'owner'             => 'required',
            'other_owner'       => 'required_if:owner,Otro',
            'branch_id'         => 'required|exists:branches,id',
            'value'             => 'numeric',
        ],
            [
                'unique'                          => 'Este número de serie ya está registrado!',
                'serial.required'                 => 'Debe especificar el número de serie del equipo!',
                'type.required'                   => 'Debe especificar el tipo de equipo!',
                'model.required'                  => 'Debe especificar el modelo del equipo!',
                'owner.required'                  => 'Debe especificar el propietario del equipo!',
                'other_owner.required_if'         => 'Debe especificar el propietario del equipo!',
                'branch_id.required'              => 'Debe especificar la sucursal a la que está asignado el equipo!',
                'branch_id.exists'                => 'La sucursal seleccionada no existe en el sistema!',
                'value.numeric'                   => 'El campo valor debe contener sólo números!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $device = new Device(Request::all());

        $device->owner = $device->owner=="Otro" ? Request::input('other_owner') : $device->owner;

        //$branch = Branch::find($device->branch_id);

        /*
        if ($device->owner == "Otro") {
            $device->owner = Request::input('other_owner');
            if ($device->owner == "") {
                Session::flash('message', "Debe especificar el propietario del equipo!");
                return redirect()->back()->withInput();
            }
        }
        */

        $responsible = User::where('work_type', 'Almacén')->where('branch_id', $device->branch_id)->first();

        $device->responsible = $responsible ? $responsible->id : $user->id;
        $device->destination = 'Almacén'; //$device->branch;
        $device->status = 'Disponible';
        $device->flags = '0001';

        $branch = Branch::find($device->branch_id);

        $device->branch = $branch ? $branch->city : 'La Paz';

        $device->save();

        /* Insert new entry on device history table */
        $this->add_history_record($device, 'store', $device, $user);

        Session::flash('message', "Equipo registrado correctamente");
        return redirect()->route('device.index');
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

        $device = Device::find($id);
        $operator = Operator::where('device_id', $id)->orderBy('updated_at','desc')->first();
        
        $exists_picture = false;
        foreach($device->files as $file){
            if($file->type=='jpg'||$file->type=='jpeg'||$file->type=='png')
                $exists_picture = true;
        }

        return View::make('app.device_info', ['device' => $device, 'operator' => $operator, 'service' => $service, 
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

        $device = Device::find($id);
        $owners = Device::select('owner')->where('owner','<>','ABROS')->where('owner','<>','')->groupBy('owner')->get();

        $branches = Branch::select('id', 'name', 'city')->where('name','<>','')->where('active', 1)->orderBy('name')->get();

        return View::make('app.device_form', ['device' => $device, 'owners' => $owners, 'service' => $service,
            'branches' => $branches, 'user' => $user]);
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

        $device = Device::find($id);

        $v = \Validator::make(Request::all(), [
            'serial'               => 'required|unique:devices',
        ],
            [
                'unique'                          => 'Este número de serie ya está registrado!',
                'serial.required'                 => 'Debe especificar el número de serie del equipo!',
            ]
        );

        if(Request::input('serial')!=$device->serial){
            if ($v->fails())
            {
                Session::flash('message', $v->messages()->first());
                return redirect()->back()->withInput();
            }
        }

        $v = \Validator::make(Request::all(), [
            'type'              => 'required',
            'model'             => 'required',
            'owner'             => 'required',
            'other_owner'       => 'required_if:owner,Otro',
            'branch_id'         => 'required|exists:branches,id',
            'value'             => 'numeric',
        ],
            [
                'type.required'                   => 'Debe especificar el tipo de equipo!',
                'model.required'                  => 'Debe especificar el modelo del equipo!',
                'owner.required'                  => 'Debe especificar el propietario del equipo!',
                'other_owner.required_if'         => 'Debe especificar el propietario del equipo!',
                'branch_id.required'              => 'Debe especificar la sucursal a la que está asignado el equipo!',
                'branch_id.exists'                => 'La sucursal seleccionada no existe en el sistema!',
                'value.numeric'                   => 'El campo valor debe contener sólo números!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $device->fill(Request::all());

        $device->owner = $device->owner=="Otro" ? Request::input('other_owner') : $device->owner;

        /*
        if ($device->owner == "Otro") {
            $device->owner = Request::input('other_owner');
            if ($device->owner == "") {
                Session::flash('message', "Debe especificar el propietario del equipo!");
                return redirect()->back()->withInput();
            }
        }
        */

        if($device->status=='En mantenimiento'){
            $device->flags = '1000';

            $maintenance = new Maintenance();
            $maintenance->user_id = $user->id;
            $maintenance->active = $device->serial;
            $maintenance->device_id = $device->id;

            $maintenance->save();

            /* Insert new entry on device history table */
            $this->add_history_record($device, 'maintenance', $maintenance, $user);
        }
        else{
            foreach($device->maintenances as $maintenance){
                if($maintenance->completed==0){
                    $maintenance->date = Carbon::now();
                    $maintenance->completed = 1;
                    $maintenance->save();

                    foreach($maintenance->files as $file){
                        $this->blockFile($file);
                    }
                }
            }

            if($device->status=='Activo')
                $device->flags = '0010';
            elseif($device->status=='Disponible')
                $device->flags = '0001';
            elseif($device->status=='Baja')
                $device->flags = '0000';
        }

        $branch = Branch::find($device->branch_id);

        $device->branch = $branch ? $branch->city : 'La Paz';

        $device->save();

        Session::flash('message', "Datos actualizados correctamente!");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('device.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Session::flash('message', 'Device records can only be marked as "Baja"');
        return redirect()->back();
    }

    public function report_malfunction_form($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $device = Device::find($id);

        if(!$device){
            Session::flash('message', "Ocurrió un error al recuperar los datos del servidor, intente de nuevo por favor.");
            return redirect()->back();
        }

        return View::make('app.device_malfunction_form', ['device' => $device, 'service' => $service,
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
                'condition.required'        => 'Debe especificar el problema existente en el equipo!',
                'condition.filled'          => 'El campo "Condición actual del equipo" no puede estar vacío!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $device = Device::find($id);
        
        $device->fill($form_data);

        $device->status = 'Requiere mantenimiento';
        
        if($device->flags[1]==0)
            $device->flags = str_pad($device->flags+100, 4, "0", STR_PAD_LEFT);
            //this flag works with "available" and "active" flags

        $device->save();

        /* Record failure report*/
        $this->add_failure_report($device, $user);

        /* Insert new entry on device history table */
        $this->add_history_record($device, 'malfunction', $device, $user);

        /* Send email notification to responsible of equipments */
        $this->send_mail($device, $user);

        Session::flash('message', "El problema ha sido reportado");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('device.index');
    }

    public function main_pic_id_form($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');
        $device = Device::find($id);

        if(!$device){
            Session::flash('message', "Ocurrió un error al recuperar los datos del servidor, intente de nuevo por favor.");
            return redirect()->back();
        }

        return View::make('app.change_main_pic_id_form', ['model' => $device, 'type' => 'device', 'service' => $service,
            'user' => $user]);
    }

    public function change_main_pic_id(Request $request, $id)
    {
        $device = Device::find($id);

        if(!$device){
            Session::flash('message', "Ocurrió un error al recuperar los datos del servidor, intente de nuevo por favor.");
            return redirect()->back();
        }

        $device->main_pic_id = Request::input('new_id');
        $device->save();

        Session::flash('message', 'La imagen principal de éste equipo ha cambiado');
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('device.index');
    }

    public function disable_form()
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $dvc_id = Input::get('dvc_id');

        $device = Device::find($dvc_id);

        if(!$device){
            Session::flash('message', "Ocurrió un error al recuperar los datos del servidor, intente de nuevo por favor.");
            return redirect()->back();
        }

        return View::make('app.device_disable_form', ['device' => $device, 'service' => $service, 'user' => $user]);
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

        $id = Request::input('device_id');

        $device = Device::find($id);

        $device->fill(Request::all());

        $device->status = 'Baja';
        $device->flags = '0000';

        $device->save();

        foreach($device->maintenances as $maintenance){
            if($maintenance->completed==0){
                $maintenance->date = Carbon::now();
                $maintenance->completed = 1;
                $maintenance->save();

                foreach($maintenance->files as $file){
                    $this->blockFile($file);
                }
            }
        }

        foreach($device->files as $file){
            $this->blockFile($file);
        }

        /* Insert new entry on device history table */
        $this->add_history_record($device, 'disable', $device, $user);

        Session::flash('message', "El equipo con serial $device->serial ha sido dado de baja");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('device.index');
    }

    function add_history_record($device, $mode, $model, $user)
    {
        $device_history = new DeviceHistory;
        $device_history->device_id = $device->id;
        $device_history->status = $device->status;

        if($mode=='store'){
            $device_history->type = 'Nuevo registro';
            $device_history->contents = 'El equipo '.$device->type.' '.$device->model.' con número de serie '.
                $device->serial.' es registrado en el sistema de seguimiento de activos';
        }
        elseif($mode=='maintenance'){
            $device_history->type = 'Mantenimiento';
            $device_history->contents = 'El equipo '.$device->serial.' es puesto en mantenimiento';
        }
        elseif($mode=='malfunction'){
            $device_history->type = 'Reporte de falla';
            $device_history->contents = ($user ? $user->name : 'Se').
                ' reporta las siguientes condiciones en el equipo: '.$device->condition;
        }
        elseif($mode=='disable'){
            $device_history->type = 'Baja de equipo';
            $device_history->contents = ($user ? $user->name : 'Se').
                ' da de baja este equipo por el siguiente motivo: '.$device->condition;
        }

        $device_history->historyable()->associate($model /*$device||$maintenance*/);
        $device_history->save();
    }

    function add_failure_report($device, $user)
    {
        $report = new DvcFailureReport();
        $report->code = 'RFD-'.Carbon::now()->format('ymdhis');
        $report->user_id = $user->id;
        $report->device_id = $device->id;
        $report->status = 0; //Pending status
        $report->reason = $device->condition;
        $report->save();
    }

    function send_mail($device, $user)
    {
        $recipient = User::where('work_type', 'Almacén')->where('branch_id', $device->branch_id)->first();

        if($recipient){
            $data = array('recipient' => $recipient, 'responsible' => $user, 'device' => $device);
            $cc = $user->email;

            $mail_structure = 'emails.device_malfunction_reported';
            $subject = 'Se reportó un problema con un equipo';

            $view = View::make($mail_structure, $data /*['recipient' => $recipient, 'responsible' => $user,
            'device' => $device]*/);
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
