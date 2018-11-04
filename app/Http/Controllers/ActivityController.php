<?php

namespace App\Http\Controllers;

use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use View;
use App\User;
use App\Activity;
use App\Task;
use App\File;
//use App\Assignment;
//use App\Site;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
use App\Http\Traits\FilesTrait;
use App\Http\Traits\ProjectTrait;

class ActivityController extends Controller
{
    use FilesTrait;
    use ProjectTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id)) {
            return View('app.index', ['service'=>'project', 'user'=>null]);
        }
        if($user->acc_project==0)
            return redirect()->action('LoginController@logout', ['service' => 'project']);

        return redirect()->back();
        /*
        $service = Session::get('service');

        $current_date = Carbon::now();
        $current_date->hour = 0;
        $current_date->minute = 0;
        $current_date->second = 0;

        /* All the activities of a year
        $activities = Activity::whereYear('created_at', '=', $current_date->year)->orderBy('id')->paginate(20);

        All the activities for the current month
        $activities = Activity::where('created_at', '>=', Carbon::now()->startOfMonth())->orderBy('id')->paginate(20);

        foreach($activities as $activity)
        {
            $activity->start_date = Carbon::parse($activity->start_date);
        }

        $user_names = User::select('id','name')->get();

        $files = File::where('imageable_type', '=', 'App\Activity')->get();
        
        return View::make('app.activity_brief', ['site_info' => 0, 'task_info' => 0, 'activities' => $activities, 
            'user_names' => $user_names, 'files' => $files, 'service' => $service, 'current_date' => $current_date, 
            'user' => $user]);
        */
    }

    /*
    public function activities_per_site($id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $current_date = Carbon::now();
        $current_date->hour = 0;
        $current_date->minute = 0;
        $current_date->second = 0;

        $site_info = Site::find($id);

        if($user->priv_level>=1){
            $activities = Activity::where('site_id','=',$id)->where('task_id','=',0)->orderBy('task_id','number')
                ->paginate(20);
        }
        else{
            $activities = Activity::where('site_id','=',$id)->where('task_id','=',0)->where('user_id','=',$user->id)
                ->orderBy('task_id','number')->paginate(20);
        }

        foreach($activities as $activity)
        {
            $activity->start_date = Carbon::parse($activity->start_date);
        }

        //$user_names = User::select('id','name')->get();
        //$files = File::where('imageable_type', '=', 'App\Activity')->get();

        return View::make('app.event_brief', ['site_info' => $site_info, /*'task_info' => 0, 'user' => $user,
            /*'user_names' => $user_names, 'files' => $files, 'service' => $service, 'current_date' => $current_date,
            'activities' => $activities]);
    }
    */
    
    public function activities_per_task($id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $task_info = Task::find($id);

        if(!$task_info){
            Session::flash('message', "No se encontró la página solicitada, revise la dirección e intente de nuevo por favor");
            return redirect()->back();
        }

        if($user->priv_level>=1)
            $activities = Activity::where('task_id',$id)->get()/*->orderBy('number')->paginate(20)*/;
        else
            $activities = Activity::where('task_id',$id)->where('user_id',$user->id)->get()/*->orderBy('number')->paginate(20)*/;

        foreach($activities as $activity)
        {
            $activity->date = Carbon::parse($activity->date);
        }

        $task_info->start_date = Carbon::parse($task_info->start_date);
        $task_info->end_date = Carbon::parse($task_info->end_date);
        $task_info->site->start_date = Carbon::parse($task_info->site->start_date);
        $task_info->site->end_date = Carbon::parse($task_info->site->end_date);

        if($task_info->site->end_date->year<=-1){
            Session::flash('message', 'Debe especificar una fecha de fin para este sitio!');
            return redirect()->back();
        }

        $from_date = $task_info->site->start_date;
        $to_date = $task_info->site->end_date;

        foreach ($activities as $activity){
            if($activity->date<$from_date)
                $from_date = $from_date->subDays($activity->date->DiffInDays($from_date));
            elseif($activity->date>$to_date)
                $to_date = $to_date->addDays($activity->date->DiffInDays($to_date));
        }

        //$interval = $task_info->site->start_date->diffInDays($task_info->site->end_date);
        //$date = $task_info->site->start_date;

        $interval = $from_date->diffInDays($to_date)+2;
        $date = $from_date;

        //$user_names = User::select('id','name')->get();
        //$files = File::where('imageable_type', 'App\Activity')->get();

        $current_date = Carbon::now()->hour(0)->minute(0)->second(0);

        return View::make('app.activity_brief', ['task_info' => $task_info, 'activities' => $activities,
            'service' => $service, 'current_date' => $current_date, 'user' => $user, 'interval' => $interval,
            'date' => $date]);
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

        $selector = explode('-', $id);
        $task = 0;
        /*
        if($selector[0]=='st'){
            $site = Site::find($selector[1]);
            //$tasks = Task::where('status','<>','Concluido')->where('site_id','=',$selector[1])->get();
        }
        */
        if($selector[0]=='tk'){
            $task = Task::find($selector[1]);
            //$site = Site::find($task->site_id);
            //$tasks = Task::where('status','<>','Concluido')->where('site_id','=',$task->site_id)->get();
        }

        if(!$task){
            Session::flash('message', "No se encontró la página solicitada, revise la dirección e intente de nuevo por favor");
            return redirect()->back();
        }

        return View::make('app.activity_form', ['activity' => 0, 'user' => $user, 'service' => $service,
            'task' => $task, 'selector' => $selector]);
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

        $activity = new Activity(Request::all());

        $v = \Validator::make(Request::all(), [
            'task_id'           => 'required|exists:tasks,id',
            'responsible_name'  => 'required',
            'progress'          => 'required|numeric',
            'date'              => 'required',
            //'day'               => 'required'
        ],
            [
                'task_id.required'          => 'Debe especificar el Item al que pertenece la actividad!',
                'task_id.exists'            => 'El Item seleccionado no está registrado en el sistema!',
                'responsible_name.required' => 'Debe indicar el responsable de la actividad!',
                'progress.required'         => 'Debe especificar la cantidad avanzada!',
                'progress.numeric'          => 'El avance de la actividad debe ser un número!',
                'date.required'             => 'Debe indicar la fecha del avance',
                //'day.required'              => 'El campo Fecha es obligatorio!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        /*
        if(Request::input('day')=='yesterday')
            $activity->date = Carbon::now()->subDay(1)->format('Y-m-d');
        else
            $activity->date = Carbon::now()->format('Y-m-d'); // Activity filled for today's progress
        */

        $prev_number = Activity::select('number')->where('task_id',$activity->task_id)
                ->OrderBy('number','desc')->first();

        $activity->number = empty($prev_number) ? 1 : $prev_number->number+1;

        $responsible = User::select('id')->where('name',Request::input('responsible_name'))->first();
        $activity->responsible_id = empty($responsible) ? 0 : $responsible->id;

        $activity->user_id = $user->id;

        $activity->save();

        if($activity->task){
            $this->refresh_task($activity->task);

            if($activity->task->site){
                $this->refresh_site($activity->task->site);

                if($activity->task->site->assignment){
                    $this->refresh_assignment($activity->task->site->assignment);
                }
            }
        }

        Session::flash('message', "La actividad fue registrada en el sistema correctamente");

        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->action('ActivityController@activities_per_task', ['id' => $activity->task_id]);

        /* Old code with interaction to cites and ocs tables
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $activity = new Activity(Request::all());
        /*
        if ($activity->type == "Otro"||$activity->type =="") {
            $activity->type = Request::input('other_type');
            if ($activity->type == "") {
                Session::flash('message', " Debe especificar el tipo de actividad / evento! ");
                return redirect()->back()->withInput();
            }
        }

        if ($activity->type == "") {
            Session::flash('message', " Debe especificar el tipo de actividad / evento! ");
            return redirect()->back()->withInput();
        }

        if($activity->task_id!=0&&empty(Request::input('progress'))){
            Session::flash('message', " Debe especificar la cantidad avanzada! ");
            return redirect()->back()->withInput();
        }

        if($activity->start_date==''){
            $activity->start_date = Carbon::now()->format('Y-m-d');
        }

        if($activity->task_id==0){
            $prev_number = Activity::select('number')->where('site_id','=',$activity->site_id)
                ->where('task_id','=',0)
                ->OrderBy('number','desc')->first();
        }
        else{
            $prev_number = Activity::select('number')->where('site_id','=',$activity->site_id)
                ->where('task_id','=',$activity->task_id)
                ->OrderBy('number','desc')->first();
        }

        if(empty($prev_number)){
            $activity->number = 1;
        }
        else{
            $activity->number = $prev_number->number+1;
        }

        if(!empty(Request::input('task_id')))
            $activity->task_id = Request::input('task_id');
        if(!empty(Request::input('progress')))
            $activity->progress = Request::input('progress');
        /*
        if($activity->task_id!=0){
            if($activity->task->total_expected-$activity->task->progress<Request::input('progress')){
                Session::flash('message', " La cantidad avanzada excede el limite propuesto para este item! ");
                return redirect()->back();
            }
        }

        if(!empty(Request::input('add_task'))){

            $v = \Validator::make(Request::all(), [
                'task_id'            => 'required',
                'progress'           => 'numeric',
            ],
                [
                    'task_id.required'        => 'Debe especificar la tarea a la que pertenece la actividad / evento!',
                    'progress.numeric'        => 'La cantidad avanzada debe ser un número!',
                ]
            );

            if ($v->fails())
            {
                Session::flash('message', $v->messages()->first());
                return redirect()->back();
            }
        }

        if(!empty(Request::input('add_oc'))){

            $v = \Validator::make(Request::all(), [
                'oc_id'            => 'required|regex:[^(OC)-(\d{5})$]',
            ],
                [
                    'oc_id.required'        => 'No indicó el código de la OC relacionada a la actividad!',
                    'oc_id.regex'           => 'El código de OC especificado tiene el formato incorrecto!',
                ]
            );

            if ($v->fails())
            {
                Session::flash('message', $v->messages()->first());
                return redirect()->back();
            }

            $exploded_code = explode('-', Request::input('oc_id'));

            $oc = OC::find($exploded_code[1]);

            if($oc!=''){
                $activity->oc_id = $exploded_code[1];
            }
            else{
                Session::flash('message', " El código de OC especificado no existe en el sistema ");
                return redirect()->back();
            }
        }

        if(!empty(Request::input('add_cite'))){

            $v = \Validator::make(Request::all(), [
                'cite_id'            => 'required|regex:[^(AB)-([A-Z]{2,4})-(\d{3})-(\d{4})$]',
            ],
                [
                    'cite_id.required'        => 'No indicó el código de CITE relacionado a la actividad!',
                    'cite_id.regex'           => 'El código de CITE especificado tiene el formato incorrecto!',
                ]
            );

            if ($v->fails())
            {
                Session::flash('message', $v->messages()->first());
                return redirect()->back();
            }

            $exploded_code = explode('-', Request::input('cite_id'));

            $cite = Cite::where('title','=',$exploded_code[0].'-'.$exploded_code[1])
                ->where('num_cite','=',$exploded_code[2])
                ->whereYear('created_at','=',$exploded_code[3])
                ->first();

            if($cite!=''){
                $activity->cite_id = $cite->id;
            }
            else{
                Session::flash('message', " El código de CITE especificado no existe en el sistema ");
                return redirect()->back();
            }
        }

        $activity->user_id = $user->id;

        $activity->save();

        Session::flash('message', " La actividad fue agregada correctamente ");
        if($activity->task_id!=0){
            return redirect()->action('ActivityController@activities_per_task', ['id' => $activity->task_id]);
        }
        else{
            return redirect()->action('ActivityController@activities_per_site', ['id' => $activity->site_id]);
        }
        */
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
        /*
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $activity = Activity::find($id);

        $files = File::where('imageable_type', 'App\Activity')->where('imageable_id', $id)->get();

        return View::make('app.activity_info', ['activity' => $activity, 'files' => $files, 
            'service' => $service, 'user' => $user]);
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
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $selector = explode('-', $id);
        $task = 0;

        $activity = Activity::find($selector[1]);
        
        if($selector[0]=='tk'){
            $task = $activity->task;
        }

        if(!$activity||!$task){
            Session::flash('message', "No se encontró la página solicitada, revise la dirección e intente de nuevo por favor");
            return redirect()->back();
        }
        
        $activity->date = Carbon::parse($activity->date)->format('Y-m-d');
        
        return View::make('app.activity_form', ['activity' => $activity, 'user' => $user, 'service' => $service,
            'task' => $task, 'selector' => $selector]);
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

        $activity = Activity::find($id);

        $v = \Validator::make(Request::all(), [
            'task_id'           => 'required|exists:tasks,id',
            'responsible_name'  => 'required',
            'progress'          => 'required|numeric'
        ],
            [
                'task_id.required'          => 'Debe especificar el Item al que pertenece la actividad!',
                'task_id.exists'            => 'El Item seleccionado no está registrado en el sistema!',
                'responsible_name.required' => 'Debe indicar el responsable de la actividad!',
                'progress.required'         => 'Debe especificar la cantidad avanzada!',
                'progress.numeric'          => 'El avance de la actividad debe ser un número!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $activity->fill(Request::all());

        $responsible = User::select('id')->where('name',Request::input('responsible_name'))->first();
        $activity->responsible_id = empty($responsible) ? 0 : $responsible->id;

        $activity->save();

        if($activity->task){
            $this->refresh_task($activity->task);

            if($activity->task->site){
                $this->refresh_site($activity->task->site);

                if($activity->task->site->assignment){
                    $this->refresh_assignment($activity->task->site->assignment);
                }
            }
        }

        Session::flash('message', "Datos modificados correctamente");

        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->action('ActivityController@activities_per_task', ['id' => $activity->task_id]);
        
        /* Old code for interaction with cites and ocs tables
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $activity = Activity::find($id);

        $activity->fill(Request::all());
        /*
        if ($activity->type == "Otro"||$activity->type =="") {
            $activity->type = Request::input('other_type');
            if ($activity->type == "") {
                Session::flash('message', " Debe especificar el tipo de actividad / evento! ");
                return redirect()->back()->withInput();
            }
        }

        if ($activity->type == "") {
            Session::flash('message', " Debe especificar el tipo de actividad / evento! ");
            return redirect()->back()->withInput();
        }

        if($activity->start_date==''){
            $activity->start_date = Carbon::now()->format('Y-m-d');
        }

        if(!empty(Request::input('add_task'))){

            $v = \Validator::make(Request::all(), [
                'task_id'            => 'required',
                'progress'           => 'numeric',
            ],
                [
                    'task_id.required'        => 'Debe especificar la tarea a la que pertenece la actividad / evento!',
                    'progress.numeric'        => 'La cantidad avanzada debe ser un número!',
                ]
            );

            if ($v->fails())
            {
                Session::flash('message', $v->messages()->first());
                return redirect()->back();
            }

            if($activity->total_expected-$activity->progress<Request::input('progress')){
                Session::flash('message', " La cantidad avanzada excede el limite propuesto para este item! ");
                return redirect()->back();
            }

            $activity->task_id = Request::input('task_id');
            $activity->progress = Request::input('progress');
        }

        if(!empty(Request::input('add_oc'))){

            $v = \Validator::make(Request::all(), [
                'oc_id'            => 'required|regex:[^(OC)-(\d{5})$]',
            ],
                [
                    'oc_id.required'        => 'No indicó el código de la OC relacionada a la actividad!',
                    'oc_id.regex'           => 'El código de OC especificado tiene el formato incorrecto!',
                ]
            );

            if ($v->fails())
            {
                Session::flash('message', $v->messages()->first());
                return redirect()->back();
            }

            $exploded_code = explode('-', Request::input('oc_id'));

            $oc = OC::find($exploded_code[1]);

            if($oc!=''){
                $activity->oc_id = $exploded_code[1];
            }
            else{
                Session::flash('message', " El código de OC especificado no existe en el sistema ");
                return redirect()->back();
            }
        }

        if(!empty(Request::input('add_cite'))){

            $v = \Validator::make(Request::all(), [
                'cite_id'            => 'required|regex:[^(AB)-([A-Z]{2,4})-(\d{3})-(\d{4})$]',
            ],
                [
                    'cite_id.required'        => 'No indicó el código de CITE relacionado a la actividad!',
                    'cite_id.regex'           => 'El código de CITE especificado tiene el formato incorrecto!',
                ]
            );

            if ($v->fails())
            {
                Session::flash('message', $v->messages()->first());
                return redirect()->back();
            }

            $exploded_code = explode('-', Request::input('cite_id'));

            $cite = Cite::where('title','=',$exploded_code[0].'-'.$exploded_code[1])
                ->where('num_cite','=',$exploded_code[2])
                ->whereYear('created_at','=',$exploded_code[3])
                ->first();

            if($cite!=''){
                $activity->cite_id = $cite->id;
            }
            else{
                Session::flash('message', " El código de CITE especificado no existe en el sistema ");
                return redirect()->back();
            }
        }

        $activity->save();

        Session::flash('message', " Datos modificados correctamente ");
        if($activity->task_id!=0){
            return redirect()->action('ActivityController@activities_per_task', ['id' => $activity->task_id]);
        }
        else{
            return redirect()->action('ActivityController@activities_per_site', ['id' => $activity->site_id]);
        }
        */
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

        $activity = Activity::find($id);

        if ($activity) {
            $to_return_id = $activity->task_id;
            $file_error = false;

            foreach ($activity->files as $file) {
                /*
                $success = true;

                try {
                    \Storage::disk('local')->delete($file->name);
                } catch (ModelNotFoundException $ex) {
                    $success = false;
                }

                if($success)
                    $file->delete();
                */
                $file_error = $this->removeFile($file);
                if($file_error)
                    break;
            }

            if (!$file_error) {
                $activity->delete();

                Session::flash('message', "El registro fue eliminado del sistema");

                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->action('ActivityController@activities_per_task', ['id' => $to_return_id]);
            } else {
                Session::flash('message', "Error al borrar el registro, por favor consulte al administrador. $file_error");
                return redirect()->back();
            }
        } else {
            Session::flash('message', "Error al ejecutar el borrado, no se encontró el registro solicitado.");
            return redirect()->back();
        }
    }
}
