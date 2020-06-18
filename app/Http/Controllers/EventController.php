<?php

namespace App\Http\Controllers;

use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use View;
use App\Event;
use App\File;
use App\User;
use App\Project;
use App\Activity;
use App\Assignment;
use App\Site;
use App\Task;
use App\OC;
use App\Invoice;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
use App\Http\Traits\FilesTrait;

class EventController extends Controller
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
        if ((is_null($user))||(!$user->id)) {
            return View('app.index', ['service' => 'project', 'user' => null]);
        }

        if ($user->acc_project == 0)
            return redirect()->action('LoginController@logout', ['service' => 'project']);

        return redirect()->back();
    }
    /*
    public function site_selector($id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $project_info = Project::find($id);
        $sites = Event::distinct()->select('project_site')->where('project_id','=',$id)->get();

        $project_sites = collect();

        foreach($sites as $site){
            $helper = Event::where('project_site','=',$site->project_site)->where('project_id','=',$id)
                ->OrderBy('event_number','desc')->first();
            $project_sites->prepend($helper);
        }

        $project_sites = $project_sites->sortBy('project_site');

        $service = Session::get('service');

        return View::make('app.event_brief', ['projects' => 0, 'project_info' => $project_info,
            'project_sites' => $project_sites, 'service' => $service, 'user' => $user]);
    }
    */
    public function events_per_type($type, $id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $current_date = Carbon::now();
        $current_date->hour = 0;
        $current_date->minute = 0;
        $current_date->second = 0;

        $type_info = collect();
        $open = true;
        
        if ($type == 'site') {
            $type_info = Site::find($id);
            $open = $type_info && ($type_info->status != $type_info->last_stat() && $type_info->status != 0) ? true : false;
        } elseif ($type == 'assignment') {
            $type_info = Assignment::find($id);
            $open = $type_info && ($type_info->status != $type_info->last_stat() && $type_info->status != 0) ? true : false;
        } elseif ($type == 'task') {
            $type_info = Task::find($id);
            $open = $type_info && ($type_info->status != $type_info->last_stat() && $type_info->status != 0) ? true : false;
        } elseif ($type == 'oc') {
            $type_info = OC::find($id);
        } elseif ($type == 'invoice') {
            $type_info = Invoice::find($id);
        }
        
        if (!$type_info) {
            Session::flash('message', "No se encontró la página solicitada, revise la dirección e intente de nuevo por favor");
            return redirect()->back();
        }

        $events = Event::where('eventable_id',$id)->where('eventable_type','like',"%$type%");

        if ($user->priv_level < 1)
            $events = $events->where('user_id', $user->id);

        $events = $events->orderBy('number')->paginate(20);

        foreach ($events as $event) {
            $event->date = Carbon::parse($event->date);
        }
        
        return View::make('app.event_brief', ['type_info' => $type_info, 'events' => $events, 'type' => $type,
            'open' => $open, 'id' => $id, 'service' => $service, 'current_date' => $current_date, 'user' => $user]);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($type, $id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $type_info = collect();
        
        if ($type == 'site')
            $type_info = Site::find($id);
        elseif ($type == 'assignment')
            $type_info = Assignment::find($id);
        elseif ($type == 'task')
            $type_info = Task::find($id);
        elseif ($type == 'oc')
            $type_info = OC::find($id);
        elseif ($type == 'invoice')
            $type_info = Invoice::find($id);

        if (!$type_info) {
            Session::flash('message', "No se encontró la página solicitada, revise la dirección e intente de nuevo por favor");
            return redirect()->back();
        }

        $event_types = Event::select('description')->where('eventable_type','like',"%$type%")
            ->where('description','<>','')->groupBy('description')->get();

        $current_date = Carbon::now()->format('Y-m-d');
        //$site_name = str_replace("_", " ", $name);

        return View::make('app.event_form', ['event' => 0, 'user' => $user, 'service' => $service,
            'event_types' => $event_types, 'current_date' => $current_date, 'type_info' => $type_info,
            'type' => $type, 'id' => $id]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $type, $id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $form_data = Request::all();

        $form_data['date_to'] = $form_data['date_to'] != '' ? $form_data['date_to'].' 23:59:59' : '';

        $v = \Validator::make($form_data, [
            'description'           => 'required',
            'other_description'     => 'required_if:description,Otro',
            'detail'                => 'required',
            'date'                  => 'date',
            'date_to'               => 'date|after:date',
        ],
            [
                'description.required'          => 'Debe especificar el tipo de evento!',
                'other_description.required_if' => 'Debe especificar el tipo de evento!',
                'detail.required'               => 'Debe proporcionar un breve resumen del evento!',
                'date.date'                     => 'El campo "Desde" debe contener una fecha válida!',
                'date_to.date'                  => 'El campo "Hasta" debe contener una fecha válida!',
                'date_to.after'                 => 'El fecha "Hasta" no puede ser anterior a la fecha "Desde"!',
            ]
        );

        if ($v->fails()) {
            Session::flash('message', $v->messages()->first());
            return redirect()->back();
        }

        $event = new Event($form_data);

        $event->description = $event->description == "Otro" ? Request::input('other_description') : $event->description;

        $prev_number = Event::select('number')->where('eventable_id', $id)->where('eventable_type','like',"%$type%")
            ->OrderBy('number','desc')->first();

        $event->number = empty($prev_number) ? 1 : $prev_number->number + 1;
        $event->user_id = $user->id;
        
        if ($type == 'site')
            $event->eventable()->associate(Site::find($id));
        elseif ($type == 'assignment')
            $event->eventable()->associate(Assignment::find($id));
        elseif ($type == 'task')
            $event->eventable()->associate(Task::find($id));
        elseif ($type == 'oc')
            $event->eventable()->associate(OC::find($id));
        elseif ($type == 'invoice')
            $event->eventable()->associate(Invoice::find($id));

        $responsible = User::select('id')->where('name', Request::input('responsible_name'))->first();

        $event->responsible_id = empty($responsible) ? 0 : $responsible->id;

        $event->date = $event->date ?: Carbon::now();

        $total_days = Request::input('total_days');

        if (!empty($total_days)) {
            $days_added = $total_days;
            $days_added = $days_added == 0 ? 1 : $days_added;

            $event->date_to = Carbon::parse($event->date)->addDays($days_added);
        } elseif(empty($event->date_to)) {
            $event->date_to = Carbon::now();
        }

        $event->user_generated = 1;
        $event->save();

        Session::flash('message', "El evento fue registrado correctamente");
        if (Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->action('EventController@events_per_type', [ 'type' => $type , 'id' => $id ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($type, $id)
    {
        /*
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $site_name = str_replace("_", " ", $name);
        
        $project_info = Project::find($id);
        $events = Event::where('project_id', $id)->where('project_site', $site_name)->OrderBy('event_number')
            ->paginate(20);

        $user_names = User::select('id','name')->get();

        $files = File::where('imageable_type', 'App\Event')->get();

        $service = Session::get('service');

        return View::make('app.event_list', ['project_info' => $project_info, 'events' => $events, 'files' => $files,
            'user_names' => $user_names,
            'site_name' => $site_name, 'service' => $service, 'user' => $user]);
        */
    }

    /*
    public function show_event_info($id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $event = Event::find($id);
        $project_info = Project::find($event->project_id);
        $event_user_name = User::select('name')->where('id', $event->user_id)->first();

        $files = File::where('imageable_type', 'App\Event')->where('imageable_id', $id)->get();

        return View::make('app.event_info', ['project_info' => $project_info, 'event' => $event, 'files' => $files,
            'event_user_name' => $event_user_name, 'service' => $service, 'user' => $user]);
    }
    */

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($type, $id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $event = Event::find($id);

        $type_info = $event->eventable;

        /*
        if($type=='site')
            $type_info = Site::find($event->eventable_id);
        elseif($type=='assignment')
            $type_info = Assignment::find($event->eventable_id);
        elseif($type=='oc')
            $type_info = OC::find($event->eventable_id);
        elseif($type=='invoice')
            $type_info = Invoice::find($event->eventable_id);
        else
            $type_info = collect();
        */

        if (!$event || !$type_info) {
            Session::flash('message', "No se encontró la página solicitada, revise la dirección e intente de nuevo por favor");
            return redirect()->back();
        }

        $event_types = Event::select('description')->where('eventable_type','like',"%$type%")
            ->where('description','<>','')->groupBy('description')->get();

        $event->date = Carbon::parse($event->date)->format('Y-m-d');
        $event->date_to = Carbon::parse($event->date_to)->format('Y-m-d');
        $current_date = Carbon::now()->format('Y-m-d');

        return View::make('app.event_form', ['event' => $event, 'user' => $user, 'service' => $service,
            'event_types' => $event_types, 'current_date' => $current_date, 'type_info' => $type_info,
            'type' => $type, 'id' => $id]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $type, $id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $form_data = Request::all();

        $form_data['date_to'] = $form_data['date_to'] != '' ? $form_data['date_to'].' 23:59:59' : '';

        $v = \Validator::make($form_data, [
            'description'           => 'required',
            'other_description'     => 'required_if:description,Otro',
            'detail'                => 'required',
            'date'                  => 'date',
            'date_to'               => 'date|after:date',
        ],
            [
                'description.required'          => 'Debe especificar el tipo de evento!',
                'other_description.required_if' => 'Debe especificar el tipo de evento!',
                'detail.required'               => 'Debe proporcionar un breve resumen del evento!',
                'date.required'                 => 'Debe especificar la fecha del evento!',
                'date.date'                     => 'El campo "Desde" debe contener una fecha válida!',
                'date_to.date'                  => 'El campo "Hasta" debe contener una fecha válida!',
                'date_to.after'                 => 'El fecha "Hasta" no puede ser anterior a la fecha "Desde"!',
            ]
        );

        if ($v->fails()) {
            Session::flash('message', $v->messages()->first());
            return redirect()->back();
        }

        $event = Event::find($id);

        $event->fill($form_data);

        $event->description = $event->description == "Otro" ? Request::input('other_description') : $event->description;

        $responsible = User::select('id')->where('name', Request::input('responsible_name'))->first();
        $event->responsible_id = empty($responsible) ? 0 : $responsible->id;

        $event->date = $event->date ?: Carbon::now();

        $total_days = Request::input('total_days');

        if (!empty($total_days)) {
            $days_added = $total_days;
            $days_added = $days_added == 0 ? 1 : $days_added;

            $event->date_to = Carbon::parse($event->date)->addDays($days_added);
        } elseif (empty($event->date_to)) {
            $event->date_to = Carbon::now();
        }

        $event->save();

        Session::flash('message', "El evento fue modificado");
        if (Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->action('EventController@events_per_type', ['type' => $type, 'id' => $event->eventable_id]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($type, $id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $event = Event::find($id);

        if ($event) {
            $return_id = $event->eventable_id;
            $file_error = false;

            foreach ($event->files as $file) {
                $file_error = $this->removeFile($file);
                if ($file_error)
                    break;

                /*
                $success = 0;

                try {
                    \Storage::disk('local')->delete($file->name);
                    $success++;
                } catch (ModelNotFoundException $ex) {
                    $success++;
                }

                if($success>0)
                    $file->delete();
                */
            }

            if (!$file_error) {
                $event->delete();

                Session::flash('message', "El evento ha sido eliminado");
                if (Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->action('EventController@events_per_type', ['type' => $type, 'id' => $return_id]);
            } else {
                Session::flash('message', "Error al borrar el registro, por favor consulte al administrador. $file_error");
                return redirect()->back();
            }
        } else {
            Session::flash('message', "Se produjo un error al ejecutar el borrado! No se encontró el registro indicado");
            return redirect()->back();
        }
    }
}
