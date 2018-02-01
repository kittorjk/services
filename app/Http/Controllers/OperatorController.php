<?php

namespace App\Http\Controllers;

use App\DeviceRequirement;
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
use App\Assignment;
use App\Email;
use App\DeviceHistory;
use Carbon\Carbon;
use App\Http\Traits\FilesTrait;

class OperatorController extends Controller
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

        $dvc = Input::get('dvc');
        $conf = Input::get('conf');

        $operators = Operator::where('id', '>', 0);

        if(!is_null($dvc))
            $operators = $operators->where('device_id', $dvc);

        if(!is_null($conf)&&$conf=='pending')
            $operators = $operators->where('confirmation_flags', 'like', '%0');

        if(!(($user->priv_level>=1&&$user->area=='Gerencia Tecnica')||$user->priv_level>=3||$user->work_type=='Almacén')){
            $operators = $operators->where(function ($query) use($user) {
                $query->where('who_delivers',$user->id)
                    ->orwhere('who_receives','=',$user->id);
            });
        }

        $operators = $operators->orderBy('date','desc')->paginate(20);
        
        return View::make('app.operator_brief', ['operators' => $operators, 'service' => $service, 'user' => $user]);
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

        $requirement = DeviceRequirement::find($req);

        if(!$requirement){
            Session::flash('message', 'No se encontró el requerimiento para asignar un equipo! Por favor
                verifique que dicho requerimiento exista');
            return redirect()->back();
        }
        
        /*
        $devices = collect();
        if($user->priv_level>=2||$user->work_type=='Almacén')
            $devices = Device::where('status','<>','En mantenimiento')->orderBy('type')->get();
        else{
            $last_operator = Operator::where('who_receives',$user->id)->orderBy('id','desc')->first();
            if($last_operator)
                $devices = Device::where('id',$last_operator->device_id)->where('status','<>','En mantenimiento')
                    ->orderBy('type')->get();
        }
        */

        return View::make('app.operator_form', ['operator' => 0, 'requirement' => $requirement, /*'devices' => $devices,*/
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
            'device_requirement_id' => 'required',
            'device_id'             => 'required',
            'deliverer_name'        => 'required|exists:users,name',
            'receiver_name'         => 'required|exists:users,name',
            'project_type'          => 'required',
            'project_code'          => 'exists:assignments,code', //'regex:[^(PR)-(\d{4})-(\d{2})$]',
            'destination'           => 'required',
        ],
            [
                'device_requirement_id'     => 'Debe seleccionar un requerimiento de equipo',
                'device_id.required'        => 'Debe seleccionar un equipo!',
                'deliverer_name.required'   => 'Debe especificar el nombre de la persona que entrega el equipo!',
                'deliverer_name.exists'     => 'El nombre de la persona que entrega el equipo no está registrado en el sistema!',
                'receiver_name.required'    => 'Debe especificar el nombre de la persona que recibe el equipo!',
                'receiver_name.exists'      => 'El nombre del receptor del equipo no está registrado en el sistema!',
                'project_type.required'     => 'Debe especificar el área de trabajo en el que será usado el equipo!',
                'project_code.exists'       => 'El código de proyecto indicado no existe!',
                'destination.required'      => 'Debe indicar el destino al que se trasladará el equipo',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $operator = new Operator(Request::all());

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
                $operator->project_id = $assignment->id;
            }
        }

        $operator->user_id = $user->id;
        $operator->date = Carbon::now();

        $deliverer = User::select('id')->where('name',Request::input('deliverer_name'))->first();
        $receiver = User::select('id')->where('name',Request::input('receiver_name'))->first();

        if($deliverer==''){
            Session::flash('message', "El nombre de la persona que entrega el equipo no está registrado en el sistema!");
            return redirect()->back()->withInput();
        }
        else
            $operator->who_delivers=$deliverer->id;

        if($receiver==''){
            Session::flash('message', "El nombre de la persona que recibe el equipo no está registrado en el sistema!");
            return redirect()->back()->withInput();
        }
        else
            $operator->who_receives=$receiver->id;

        if($operator->who_delivers==$user->id)
            $operator->confirmation_flags = '0010';
        elseif($operator->who_receives==$user->id)
            $operator->confirmation_flags = '0011'; //Confirmed by both
        else
            $operator->confirmation_flags = '0010'; //Person who delivers confirms by default
        
        $operator->save();

        /* Update requirements status */
        $requirement = $operator->requirement;
        $requirement->status = 2; //Requirement completed
        $requirement->stat_change = Carbon::now();

        $requirement->save();

        /* Update device responsible */
        $this->alter_device($operator, 'store', $requirement, $receiver);

        /* Insert new entry on device history table */
        $this->add_history_record($operator, 'store', $requirement);

        /* Send notification email */
        $device = $operator->device;
        $recipient = User::find($device->responsible);
        $data = array('recipient' => $recipient, 'device' => $device);

        $mail_structure = 'emails.assigned_device';
        $subject = 'Nueva asignación de equipo de trabajo en el sistema';

        $this->send_email($recipient, '', $data, $mail_structure, $subject);

        Session::flash('message', "El cambio de responsable de equipo fue registrado correctamente");
        return redirect()->route('operator.index');
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

        $operator = Operator::find($id);

        return View::make('app.operator_info', ['operator' => $operator, 'service' => $service, 'user' => $user]);
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

        $operator = Operator::find($id);

        $requirement = $operator->requirement;

        if(!$requirement){
            Session::flash('message', 'No se encontró el requerimiento de equipo para esta asignación! Por favor
                verifique que dicho requerimiento exista');
            return redirect()->back();
        }
        /*
        $devices = Device::where('status','<>','En mantenimiento')->orwhere('id',$operator->device_id)
            ->orderBy('type')->get();

        $operator->date = Carbon::parse($operator->date)->format('Y-m-d');
        */

        return View::make('app.operator_form', ['operator' => $operator, 'requirement' => $requirement,
            /*'devices' => $devices,*/ 'service' => $service, 'user' => $user]);
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
            'device_requirement_id' => 'required',
            'device_id'             => 'required',
            'deliverer_name'        => 'required|exists:users,name',
            'receiver_name'         => 'required|exists:users,name',
            'project_type'          => 'required',
            'project_code'          => 'exists:assignments,code', //'regex:[^(PR)-(\d{4})-(\d{2})$]',
            'destination'           => 'required',
        ],
            [
                'device_requirement_id'     => 'Debe seleccionar un requerimiento de equipo',
                'device_id.required'        => 'Debe seleccionar un equipo!',
                'deliverer_name.required'   => 'Debe especificar el nombre de la persona que entrega el equipo!',
                'deliverer_name.exists'     => 'El nombre de la persona que entrega el equipo no está registrado en el sistema!',
                'receiver_name.required'    => 'Debe especificar el nombre de la persona que recibe el equipo!',
                'receiver_name.exists'      => 'El nombre del receptor del equipo no está registrado en el sistema!',
                'project_type.required'     => 'Debe especificar el área de trabajo en el que será usado el equipo!',
                'project_code.exists'       => 'El código de proyecto indicado no existe!',
                'destination.required'      => 'Debe indicar el destino al que se trasladará el equipo',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $operator = Operator::find($id);

        $operator->fill(Request::all());

        $project_code = Request::input('project_code');

        if(!empty($project_code)){
            //$project_code_exploded = explode('-',Request::input('project_code'));

            $assignment = Assignment::where('code', $project_code)->first(); //find($project_code_exploded[1]);

            if(empty($assignment)){
                Session::flash('message', "No se encontró un registro del proyecto indicado!");
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
                $operator->project_id = $assignment->id;
            }
        }

        $deliverer = User::select('id')->where('name',Request::input('deliverer_name'))->first();
        $receiver = User::select('id')->where('name',Request::input('receiver_name'))->first();

        if($deliverer==''){
            Session::flash('message', "El nombre de la persona que entrega el equipo no está registrado en el sistema!");
            return redirect()->back()->withInput();
        }
        else
            $operator->who_delivers=$deliverer->id;

        if($receiver==''){
            Session::flash('message', "El nombre de la persona que recibe el equipo no está registrado en el sistema!");
            return redirect()->back()->withInput();
        }
        else
            $operator->who_receives=$receiver->id;

        $operator->save();

        /* Update device responsible */
        $has_change_resp = $this->alter_device($operator, 'update', 0, $receiver);

        if($has_change_resp /*$prev_responsible!=$device->responsible*/){
            //If the responsible has changed

            /* Add entry on device history table */
            $this->add_history_record($operator, 'update', 0);

            /* Send notification email */
            $device = $operator->device;
            $recipient = User::find($device->responsible);
            $data = array('recipient' => $recipient, 'device' => $device);

            $mail_structure = 'emails.assigned_device';
            $subject = 'Nueva asignación de equipo de trabajo en el sistema';

            $this->send_email($recipient, '', $data, $mail_structure, $subject);
        }

        Session::flash('message', "Datos modificados correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('operator.index');
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


    public function devolution($id)
    {
        return redirect()->back(); //Function deprecated
        /*
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        //$service = Session::get('service');

        $device = Device::find($id);

        //$last_operator = Operator::where('device_id',$device->id)->orderBy('id','desc')->first();

        if(!$device->last_operator&&$device->status!='En mantenimiento'){
            Session::flash('message', "Ocurrió un error al recuperar los datos del servidor. Intente de nuevo por favor.");
            return redirect()->back();
        }

        $warehouse_responsible = User::where('work_type','Almacén')->first();

        $operator = new Operator;

        $operator->user_id = $user->id;
        $operator->date = Carbon::now();
        $operator->device_id = $device->id;

        $operator->who_delivers = $device->last_operator->who_receives;
        $operator->who_receives = $warehouse_responsible ? $warehouse_responsible->id : $user->id;

        $operator->confirmation_flags = '0001';
        $operator->reason = 'Devolución de equipo a almacén';
        $operator->save();

        $device->status = 'Disponible';
        $device->flags = '0001';
        $device->responsible = $operator->who_receives;
        $device->destination = 'Almacén';

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

        // insert new entry on device history table
        $device_history = new DeviceHistory;
        $device_history->device_id = $device->id;
        $device_history->type = 'Devolución';
        $device_history->contents = 'Se entrega '.$device->type.' '.$device->model.' con S/N '.$device->serial.
            ' a encargado de equipos';
        $device_history->status = $device->status;
        $device_history->historyable()->associate(Operator::find($operator->id));
        $device_history->save();

        Session::flash('message', "El estado del equipo ha cambiado a disponible");
        return redirect()->route('device.index');
        */
    }

    public function reception_confirmation_form($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $operator = Operator::find($id);

        if(!$operator){
            Session::flash('message', 'No se encontró el registro solicitado!');
            return redirect()->back();
        }

        return View::make('app.operator_confirmation_form', ['operator' => $operator, 'service' => $service,
            'user' => $user]);
    }

    public function confirm_reception(Request $request, $id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $operator = Operator::find($id);

        $operator->fill(Request::all());

        $operator->date_confirmed = Carbon::now();

        if($operator->confirmation_flags[3]==0){
            $operator->confirmation_flags = str_pad($operator->confirmation_flags+1, 4, "0", STR_PAD_LEFT);

            foreach($operator->files as $file){
                $this->blockFile($file);
            }
        }

        $operator->save();

        /* Insert new entry on device history table */
        $this->add_history_record($operator, 'reception_confirmed', 0);

        Session::flash('message', "Se ha confirmado la recepción de este equipo");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('operator.index');
    }

    function send_email($recipient, $cc, $data, $mail_structure, $subject)
    {
        $view = View::make($mail_structure, $data /*['recipient' => $recipient, 'device' => $device]*/);
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

    function alter_device($operator, $mode, $requirement, $receiver)
    {
        $device = $operator->device; //Device::find($operator->device_id);

        /*if($mode=='update')*/
            $prev_responsible = $device->responsible; //Get previous value to determine if it changed

        if($mode=='store'){
            if($requirement->type=='borrow'||$requirement->type=='transfer_tech'){
                $device->status = 'Activo';
                $device->flags = '0010';
            }
            elseif($requirement->type=='transfer_wh'||$requirement->type=='devolution'){
                $device->status = 'Disponible';
                $device->flags = '0001';
                $device->destination = 'Almacén';

                if($requirement->type=='transfer_wh'){
                    $device->branch = $requirement->branch_destination;
                }
            }
        }

        $device->destination = $operator->destination;
        $device->responsible = $receiver->id;

        $device->save();

        return $prev_responsible!=$device->responsible ? true : false;
    }

    function add_history_record($operator, $mode, $requirement)
    {
        $device = $operator->device;

        $device_history = new DeviceHistory;
        $device_history->device_id = $device->id;

        if($mode=='store'){
            $device_history->type = $requirement->type=='devolution' ? 'Devolución' : 'Asignación';
            $device_history->contents = 'Se entrega '.$device->type.' '.$device->model.' con S/N '.$device->serial.' a '.
                $operator->receiver->name;
        }
        elseif($mode=='update'){
            /*$device_history = DeviceHistory::where('historyable_type','App\Operator')
                ->where('historyable_id',$operator->id)->first();*/

            $device_history->type = 'Corrección a la última asignación';
            $device_history->contents = 'Se entrega '.$device->type.' '.$device->model.' con S/N '.$device->serial.' a '.
                $operator->receiver->name;
        }
        elseif($mode=='reception_confirmed'){
            $device_history->type = 'Confirmación de recepción';
            $device_history->contents = ($operator->receiver ? $operator->receiver->name : '').' confirmó que recibió el equipo '.
                $device->type.' '.$device->model.' con S/N '.$device->serial.($operator->confirmation_obs ?
                    ' con las siguientes observaciones: '.$operator->confirmation_obs : '');
        }

        $device_history->status = $device->status;
        $device_history->historyable()->associate($operator /*Operator::find($operator->id)*/);
        $device_history->save();
    }
}
