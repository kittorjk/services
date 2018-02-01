<?php

namespace App\Http\Controllers;

use Request;
//use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Session;
use View;
use Input;
use Exception;
use App\Activity;
use App\Assignment;
use App\File;
use App\User;
use App\Contact;
use App\Site;
use App\Event;
use App\Project;
use App\DeadInterval;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
use App\Http\Traits\FilesTrait;

class DeadIntervalController extends Controller
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
            return View('app.index', ['service' => 'project', 'user' => null]);
        }
        if($user->acc_project==0)
            return redirect()->action('LoginController@logout', ['service' => 'project']);

        $service = Session::get('service');

        $assig_id = Input::get('assig_id');
        $st_id = Input::get('st_id');

        if(!empty($assig_id)){
            $assignment = Assignment::find($assig_id);
            $site = 0;

            $dead_intervals = DeadInterval::where('relatable_id',$assig_id)->where('relatable_type','App\Assignment')
                ->paginate(20);
        }
        elseif(!empty($st_id)){
            $site = Site::find($st_id);
            $assignment = 0;

            $dead_intervals = DeadInterval::where('relatable_id',$st_id)->where('relatable_type','App\Site')
                ->paginate(20);
        }
        else{
            Session::flash('message','Ocurrió un error al recuperar el registro solicitado. Intente de nuevo por favor');
            Return redirect()->back();
        }

        if(empty($assignment)&&empty($site)){
            Session::flash('message','Ocurrió un error al recuperar el registro solicitado. Intente de nuevo por favor');
            Return redirect()->back();
        }

        foreach($dead_intervals as $dead_interval)
        {
            $dead_interval->date_from = Carbon::parse($dead_interval->date_from);
            $dead_interval->date_to = Carbon::parse($dead_interval->date_to);
        }

        $current_date = Carbon::now()->hour(0)->minute(0)->second(0);

        return View::make('app.dead_interval_brief', ['dead_intervals' => $dead_intervals, 'service' => $service,
            'current_date' => $current_date, 'user' => $user, 'assignment' => $assignment, 'site' => $site]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $assig_id = Input::get('assig_id');
        $st_id = Input::get('st_id');

        if(!empty($assig_id)){
            $assignment = Assignment::find($assig_id);
            $site = 0;
        }
        elseif(!empty($st_id)){
            $site = Site::find($st_id);
            $assignment = 0;
        }
        else{
            Session::flash('message','Ocurrió un error al recuperar el registro solicitado. Intente de nuevo por favor');
            Return redirect()->back();
        }

        if(empty($assignment)&&empty($site)){
            Session::flash('message','Ocurrió un error al recuperar el registro solicitado. Intente de nuevo por favor');
            Return redirect()->back();
        }

        $current_date = Carbon::now()->format('Y-m-d');

        return View::make('app.dead_interval_form', ['dead_interval' => 0, 'service' => $service, 'user' => $user,
            'current_date' => $current_date, 'assignment' => $assignment, 'site' => $site]);
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

        $form_data = Request::all();

        $form_data['date_to'] = $form_data['date_to']!='' ? $form_data['date_to'].' 23:59:59' : '';

        $v = \Validator::make($form_data, [
            'assig_id'              => 'required_without:st_id|exists:assignments,id',
            'st_id'                 => 'required_without:assig_id|exists:sites,id',
            'date_from'             => 'date',
            'date_to'               => 'date|after:date_from',
            'reason'                => 'required',
        ],
            [
                'assig_id.required_without' => 'No se pudo recuperar la información de asignación, intente de nuevo por favor',
                'assig_id.exists'      => 'No se pudo recuperar la información de asignación, intente de nuevo por favor',
                'st_id.required_without'    => 'No se pudo recuperar la información de asignación, intente de nuevo por favor',
                'st_id.exists'         => 'No se pudo recuperar la información de asignación, intente de nuevo por favor',
                'date_from.date'       => 'El formato de la fecha de inicio es incorrecto!',
                'date_to.date'         => 'El formato de la fecha de fin es incorrecto!',
                'date_to.after'        => 'La fecha de fin no puede ser anterior a la fecha de inicio!',
                'reason.required'      => 'Debe especificar el motivo de la creación del tiempo muerto!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back();
        }

        //$assignment = 0;
        //$site = 0;

        if(!empty(Request::input('assig_id'))){
            $model = /*$assignment = */ Assignment::find(Request::input('assig_id'));
            $model_name = 'asignación';
        }
        elseif(!empty(Request::input('st_id'))){
            $model = /*$site = */Site::find(Request::input('st_id'));
            $model_name = 'sitio';
        }
        else{
            Session::flash('message', "Sucedió un error al acceder al registro solicitado!
                No puede agregar tiempos muertos quí");
            return redirect()->back();
        }

        foreach($model->dead_intervals as $dead_interval){
            if($dead_interval->closed==0){
                Session::flash('message', ($model_name=='sitio' ? 'Este sitio' :
                        ($model_name=='asignación' ? 'Esta asignación' : 'Este registro')).
                    "ya tiene un intervalo de tiempo muerto abierto, cierre este intervalo para poder agregar uno nuevo");
                return redirect()->back();
            }
        }

        $dead_interval = new DeadInterval($form_data);

        $dead_interval->date_from = empty($dead_interval->date_from) ? Carbon::now() : $dead_interval->date_from;

        if(!empty(Request::input('total_days'))){
            $days_added = Request::input('total_days')-1;
            $days_added = $days_added<0 ? 1 : $days_added;

            $dead_interval->date_to = Carbon::parse($dead_interval->date_from)->addDays($days_added);
        }
        elseif(!empty(Request::input('date_to'))&&empty(Request::input('total_days'))){
            $dead_interval->total_days = Carbon::parse($dead_interval->date_to)
                ->diffInDays(Carbon::parse($dead_interval->date_from)) +1; //Both extremes count
        }
        
        $dead_interval->user_id = $user->id;
        $dead_interval->closed = empty($dead_interval->date_to) ? 0 : 1;

        $dead_interval->relatable()->associate($model /*Assignment::find($assignment->id)*/);

        $dead_interval->save();

        if($dead_interval->date_to!='0000-00-00 00:00:00'||$dead_interval->total_days>0){
            /* Add days of interruption to execution end date in assignment and/or site */
            $this->add_interval_days($dead_interval);
        }

        /* A new event is recorded to point to the creation of the dead interval */
        $this->add_event($dead_interval, $model, $model_name, $user);

        Session::flash('message', "El intervalo de tiempo muerto fue agregado al sistema");
        if($model_name=='asignación')
            return redirect('/dead_interval?assig_id='.$model->id);
        elseif($model_name=='sitio')
            return redirect('/dead_interval?st_id='.$model->id);
        else
            return redirect()->back();
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
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $dead_interval = DeadInterval::find($id);

        $assignment = 0;
        $site = 0;

        if($dead_interval->relatable_type=='App\Assignment')
            $assignment = Assignment::find($dead_interval->relatable_id);
        elseif($dead_interval->relatable_type=='App\Site')
            $site = Site::find($dead_interval->relatable_id);

        if(empty($dead_interval)||(empty($assignment)&&empty($site))){
            Session::flash('message', 'Ocurrió un error al recuperar el registro solicitado. Intente de nuevo por favor');
            return redirect()->back();
        }

        $dead_interval->date_from = Carbon::parse($dead_interval->date_from)->format('Y-m-d');
        $dead_interval->date_to = Carbon::parse($dead_interval->date_to)->format('Y-m-d');

        $current_date = Carbon::now()->format('Y-m-d');

        return View::make('app.dead_interval_form', ['dead_interval' => $dead_interval, 'service' => $service,
            'user' => $user, 'current_date' => $current_date, 'assignment' => $assignment, 'site' => $site]);
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

        $dead_interval = DeadInterval::find($id);

        $old_interval_days = $dead_interval->closed==1 ? $dead_interval->total_days : 0;

        $form_data = Request::all();

        $form_data['date_to'] = $form_data['date_to']!='' ? $form_data['date_to'].' 23:59:59' : '';

        $v = \Validator::make($form_data, [
            'assig_id'              => 'required_without:st_id|exists:assignments,id',
            'st_id'                 => 'required_without:assig_id|exists:sites,id',
            'date_from'             => 'date',
            'date_to'               => 'date|after:date_from',
            'reason'                => 'required',
        ],
            [
                'assig_id.required_without' => 'No se pudo recuperar la información de asignación, intente de nuevo por favor',
                'assig_id.exists'      => 'No se pudo recuperar la información de asignación, intente de nuevo por favor',
                'st_id.required_without'    => 'No se pudo recuperar la información de asignación, intente de nuevo por favor',
                'st_id.exists'         => 'No se pudo recuperar la información de asignación, intente de nuevo por favor',
                'date_from.date'       => 'El formato de la fecha de inicio es incorrecto!',
                'date_to.date'         => 'El formato de la fecha de fin es incorrecto!',
                'date_to.after'        => 'La fecha de fin no puede ser anterior a la fecha de inicio!',
                'reason.required'      => 'Debe especificar el motivo de la creación del tiempo muerto!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back();
        }

        $dead_interval->fill($form_data);

        //$assignment = 0;
        //$site = 0;

        $model = 0;

        if(!empty(Request::input('assig_id'))){
            $model = Assignment::find(Request::input('assig_id'));
            $model_name = 'asignación';
        }
        elseif(!empty(Request::input('st_id'))){
            $model = Site::find(Request::input('st_id'));
            $model_name = 'sitio';
        }

        $dead_interval->date_from = empty($dead_interval->date_from) ? Carbon::now() : $dead_interval->date_from;
        
        if(!empty(Request::input('total_days'))){
            $days_added = Request::input('total_days')-1;
            $days_added = $days_added<0 ? 1 : $days_added;

            $dead_interval->date_to = Carbon::parse($dead_interval->date_from)->addDays($days_added);
        }
        elseif(!empty(Request::input('date_to'))&&empty(Request::input('total_days'))){
            $dead_interval->total_days = Carbon::parse($dead_interval->date_to)
                ->diffInDays(Carbon::parse($dead_interval->date_from)) +1;
        }

        $dead_interval->closed = empty($dead_interval->date_to) ? 0 : 1;

        $dead_interval->save();

        if($dead_interval->date_to!='0000-00-00 00:00:00'||$dead_interval->total_days>0){
            // Subtract previous days of interruption
            $this->sub_interval_days($dead_interval,$old_interval_days);

            // Add days of interruption to execution end date in assignment and/or site
            $this->add_interval_days($dead_interval);
        }
        
        Session::flash('message', "Datos modificados correctamente");

        if(Session::has('url'))
            return redirect(Session::get('url'));
        elseif($model_name=='asignación')
            return redirect('/dead_interval?assig_id='.$model->id);
        elseif($model_name=='sitio')
            return redirect('/dead_interval?st_id='.$model->id);
        else
            return redirect()->back();
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

        $dead_interval = DeadInterval::find($id);

        if($dead_interval){
            $error = false;
            
            foreach($dead_interval->files as $file){
                $error = $this->removeFile($file);
                if($error)
                    break;
            }
            
            if(!$error){
                $old_interval_days = $dead_interval->closed==1 ? $dead_interval->total_days : 0;
                $this->sub_interval_days($dead_interval,$old_interval_days);

                $dead_interval->delete();

                Session::flash('message', "El registro ha sido eliminado");

                if(Session::has('url'))
                    return redirect(Session::get('url'));
                elseif($dead_interval->relatable_type=='App\Assignment')
                    return redirect('/dead_interval?assig_id='.$dead_interval->relatable_id);
                elseif($dead_interval->relatable_type=='App\Site')
                    return redirect('/dead_interval?st_id='.$dead_interval->relatable_id);
                else
                    return redirect()->back();
            }
            else {
                Session::flash('message', "Error al borrar el registro, intente de nuevo por favor.");
                return redirect()->back();
            }
        }
        else {
            Session::flash('message', "Error al borrar el registro, intente de nuevo por favor.");
            return redirect()->back();
        }
    }
    
    public function close_interval($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $dead_interval = DeadInterval::find($id);

        $assignment = 0;
        $site = 0;

        if($dead_interval->relatable_type=='App\Assignment')
            $assignment = Assignment::find($dead_interval->relatable_id);
        elseif($dead_interval->relatable_type=='App\Site')
            $site = Site::find($dead_interval->relatable_id);

        $dead_interval->date_to = Carbon::now();

        $dead_interval->date_from = Carbon::parse($dead_interval->date_from)->hour(0)->minute(0)->second(0);
        $dead_interval->total_days = Carbon::now()->hour(0)->minute(0)->second(0)
            ->diffInDays($dead_interval->date_from) +1;

        $dead_interval->closed = 1;

        $dead_interval->save();

        /* Add days of interruption to execution end date in assignment and/or site */
        $this->add_interval_days($dead_interval);

        Session::flash('message', "El intervalo de tiempo muerto ha sido cerrado");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        elseif($site)
                return redirect('/dead_interval?st_id='.$site->id);
        elseif($assignment)
            return redirect('/dead_interval?assig_id='.$assignment->id);
        else
            return redirect()->back();
    }

    public function add_interval_days($dead_interval)
    {
        if($dead_interval->relatable_type=='App\Assignment'){
            $assignment = Assignment::find($dead_interval->relatable_id);

            if($assignment->end_date!='0000-00-00 00:00:00'){
                $assignment->end_date = Carbon::parse($assignment->end_date)->addDays($dead_interval->total_days);

                $assignment->save();
            }
        }
        elseif($dead_interval->relatable_type=='App\Site'){
            $site = Site::find($dead_interval->relatable_id);

            if($site->end_date!='0000-00-00 00:00:00'){
                $site->end_date = Carbon::parse($site->end_date)->addDays($dead_interval->total_days);

                $site->save();

                $assignment = $site->assignment;

                if($assignment&&$assignment->end_date!='0000-00-00 00:00:00'){
                    $assignment->end_date = Carbon::parse($assignment->end_date)->addDays($dead_interval->total_days);

                    $assignment->save();
                }
            }
        }
    }

    public function sub_interval_days($dead_interval, $old_interval_days)
    {
        if($dead_interval->relatable_type=='App\Assignment'){
            $assignment = Assignment::find($dead_interval->relatable_id);

            if($assignment->end_date!='0000-00-00 00:00:00'){
                $assignment->end_date = Carbon::parse($assignment->end_date)->subDays($old_interval_days);

                $assignment->save();
            }
        }
        elseif($dead_interval->relatable_type=='App\Site'){
            $site = Site::find($dead_interval->relatable_id);

            if($site->end_date!='0000-00-00 00:00:00'){
                $site->end_date = Carbon::parse($site->end_date)->subDays($old_interval_days);

                $site->save();

                $assignment = $site->assignment;

                if($assignment&&$assignment->end_date!='0000-00-00 00:00:00'){
                    $assignment->end_date = Carbon::parse($assignment->end_date)->subDays($old_interval_days);

                    $assignment->save();
                }
            }
        }
    }

    function add_event($dead_interval, $model, $model_name, $user)
    {
        $event = new Event;
        $event->user_id = $user->id;
        $event->date = Carbon::now();
        $event->description = 'Creación de tiempo muerto';
        $event->responsible_id = $user->id;
        $event->detail = 'Se agrega un intervalo de tiempo muerto por concepto de: '.$dead_interval->reason;

        if($model_name=='asignación'){
            $prev_number = Event::select('number')->where('eventable_id',$model->id)
                ->where('eventable_type','App\Assignment')
                ->orderBy('number','desc')->first();
        }
        elseif($model_name=='sitio'){
            $prev_number = Event::select('number')->where('eventable_id',$model->id)
                ->where('eventable_type','App\Site')
                ->orderBy('number','desc')->first();
        }

        $event->number = $prev_number ? $prev_number->number+1 : 1;
        $event->eventable()->associate($model);

        $event->save();
    }
}
