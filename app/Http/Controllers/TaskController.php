<?php

namespace App\Http\Controllers;

use App\ItemCategory;
use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use View;
use Input;
use App\User;
use App\Site;
use App\Task;
use App\Item;
use App\File;
use App\Event;
//use App\Assignment;
//use App\Contact;
use App\Activity;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
use App\Http\Traits\FilesTrait;
use App\Http\Traits\ProjectTrait;

class TaskController extends Controller
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
        return redirect()->back();
        
        /*
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id)) {
            return View('app.index', ['service'=>'project', 'user'=>null]);
        }
        if($user->acc_project==0)
            return redirect()->action('LoginController@logout', ['service' => 'project']);

        $service = Session::get('service');

        if($user->priv_level==4){
            $tasks = Task::whereNotIn('status', ['Concluído','No ejecutado'])
                ->where('updated_at', '>=', Carbon::now()->startOfMonth())->orderBy('site_id')->paginate(20);

            foreach($tasks as $task)
            {
                $task->start_date = Carbon::parse($task->start_date);
                $task->end_date = Carbon::parse($task->end_date);

                if($task->status!='Concluído'&&$task->status!='No ejecutado'){
                    $activities = Activity::where('task_id', $task->id)->get();
                    $task->progress=0;

                    foreach($activities as $activity){
                        $task->progress = $task->progress+$activity->progress;
                    }
                    $task->executed_price = $task->progress*$task->quote_price;
                    $task->save();
                }
            }
        }
        else{
            return redirect()->back();
        }

        $current_date = Carbon::now();
        $current_date->hour = 0;
        $current_date->minute = 0;
        $current_date->second = 0;
        
        return View::make('app.task_brief', ['site_info' => 0, 'tasks' => $tasks, 'service' => $service, 
            'current_date' => $current_date, 'user' => $user]);
        */
    }

    public function tasks_per_site($id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $site_info = Site::find($id);

        if(!$site_info){
            Session::flash('message', 'Sucedió un error al recuperar la información solicitada, revise la dirección 
                e intente de nuevo por favor');
            return redirect()->back();
        }

        //$tasks = Task::where('site_id',$id)->orderBy('number')->paginate(20);
        $tasks = $site_info->tasks()->orderBy('item_id')->paginate(20);
        $last_stat = $tasks->count()>0 ? $tasks->first()->last_stat() : (count(Task::$status_options) -1);
            //Task::first()->last_stat();

        foreach($tasks as $task)
        {
            $task->start_date = Carbon::parse($task->start_date);
            $task->end_date = Carbon::parse($task->end_date);

            /* Separated to another function
            if($task->status!='Concluído'&&$task->status!='No asignado'){
                $activities = Activity::where('task_id',$task->id)->get();
                $task->progress=0;
                
                foreach($activities as $activity){
                    $task->progress = $task->progress+$activity->progress;
                }
                $task->executed_price = $task->progress*$task->quote_price;
                $task->save();
            }
            */
        }

        $current_date = Carbon::now();
        $current_date->hour = 0;
        $current_date->minute = 0;
        $current_date->second = 0;

        return View::make('app.task_brief', ['site_info' => $site_info, 'tasks' => $tasks, 'last_stat' => $last_stat,
            'service' => $service, 'current_date' => $current_date, 'user' => $user]);
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

        $site = Site::find($id);

        if(!$site){
            //$sites = Site::whereNotIn('status', ['Concluído','No asignado'])->get();
            Session::flash('message', 'Ocurrió un error al recuperar información del sitio, intente de nuevo por favor');
            return redirect()->back();
        }

        $site->start_date = Carbon::parse($site->start_date)->format('Y-m-d');
        $site->end_date = Carbon::parse($site->end_date)->format('Y-m-d');
        
        $categories = Item::select('category')->where('category', '<>', '')->groupBy('category')->get();

        $current_date = Carbon::now()->format('Y-m-d');

        $last_stat = count(Task::$status_options) -1; //Task::first()->last_stat();

        return View::make('app.task_form', ['task' => 0, 'user' => $user, 'service' => $service, 'last_stat' => $last_stat,
            'site' => $site, 'categories' => $categories, 'current_date' => $current_date, 'site_id' => $id]);
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

        if($form_data['end_date']!='')
            $form_data['end_date'] = $form_data['end_date'].' 23:59:59';

        $v = \Validator::make($form_data, [
            'category'              => 'required',
            'name'                  => 'required',
            'pondered_weight'       => 'min:1|max:10',
            'site_id'               => 'required',
            'resp_name'             => 'required|regex:/^[\pL\s\-]+$/u',
            'total_expected'        => 'required|numeric',
            'units'                 => 'required',
            //'progress'              => 'numeric',
            'start_date'            => 'required',
            'end_date'              => 'required|after:start_date',
        ],
            [
                'category.required'          => 'Debe especificar la categoría a la que pertenece el nuevo item!',
                'name.required'              => 'Debe especificar el nombre de la tarea!',
                //'pondered_weight.required'   => 'El valor de peso ponderado es obligatorio!',
                'pondered_weight.min'        => 'El peso ponderado no puede ser inferior a 1!',
                'pondered_weight.max'        => 'El peso ponderado no puede ser mayor a 10!',
                'site_id.required'           => 'Debe especificar el sitio al que pertenece la tarea!',
                'total_expected.required'    => 'Debe especificar la cantidad contratada!',
                'total_expected.numeric'     => 'La cantidad contratada sólo puede contener números!',
                'units.required'             => 'Debe especificar las unidades de medida!',
                //'progress.numeric'           => 'La cantidad de avance sólo puede contener números!',
                'resp_name.required'         => 'Debe especificar el nombre del responsable de ejecutar la tarea',
                'resp_name.regex'            => 'El nombre del responsable solo puede contener letras',
                'start_date.required'        => 'Debe especificar la fecha de inicio de los trabajos en sitio!',
                'end_date.required'          => 'Debe especificar la fecha de fin de los trabajos en sitio!',
                'end_date.after'             => 'La fecha de fin no puede ser anterior a la fecha de inicio!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $task = new Task($form_data);

        if($task->total_expected==0){
            Session::flash('message', 'Debe especificar la cantidad proyectada!');
            return redirect()->back()->withInput();
        }

        if($task->pondered_weight&&($task->pondered_weight<1||$task->pondered_weight>10)){
            Session::flash('message', 'El peso ponderado debe estar en el rango de 1 a 10!');
            return redirect()->back()->withInput();
        }

        $task->pondered_weight = $task->pondered_weight ?: 1;
        
        if(empty($task->status)){
            $task->status = Carbon::parse($task->start_date)<Carbon::now() ?
                $task->status_number('Ejecución') : 1 /* Initial state */;
        }

        $task->quote_price = Request::input('cost_unit_central') ?: 1;
        /*
        elseif(Request::input('use_this')=='remote_cost')
            $task->quote_price = Request::input('cost_unit_remote');
        else
            $task->quote_price = 0;
        */

        $prev_number = Task::select('number')->where('site_id',$task->site_id)->OrderBy('number','desc')->first();
        $task->number = empty($prev_number) ? 1 : $prev_number->number+1;

        $task->user_id = $user->id;
        $task->assigned_price = $task->total_expected*$task->quote_price;

        $responsible = User::select('id')->where('name',Request::input('resp_name'))->first();
        $task->responsible = $responsible ? $responsible->id : 0;

        /* insert new item
        $item = new Item;
        $item->number = Request::input('number');
        $item->description = $task->name;
        $item->units = $task->units;
        $item->cost_unit_central = Request::input('cost_unit_central') ?: 1;
        //$item->cost_unit_remote = Request::input('cost_unit_remote');
        $item->detail = $task->description;
        $item->category = Request::input('category');
        $item->subcategory = Request::input('subcategory');
        $item->client_code = Request::input('client_code');
        $item->area = $user->work_type;

        $item->save();
        */

        $task->item_id = 0; //$item->id;
        
        $task->additional = 1; //True

        $task->save();

        $this->fill_code_column();

        Session::flash('message', "El item fue agregado correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->action('TaskController@tasks_per_site', ['id' => $task->site_id]);
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
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $task = Task::find($id);
        /*
        $activities = Activity::where('task_id', $task->id)->get();

        $task->progress=0;

        foreach($activities as $activity){
            $task->progress = $task->progress+$activity->progress;
        }
        
        $responsible = User::find($task->responsible);
        */
        
        return View::make('app.task_info', ['task' => $task, 'service' => $service, 'user' => $user]);
    }
    
    /*
    public function show_financial_details($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $task = Task::find($id);

        return View::make('app.task_financial_details', ['task' => $task, 'service' => $service,
            'user' => $user]);
    }
    */
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

        $task = Task::find($id);

        //$sites = Site::whereNotIn('status', ['Concluído','No asignado'])->get();
        //$site = Site::find($task->site_id);
        $site = $task->site;
        $last_stat = $task->last_stat();

        /*
        $resp_name = User::select('name')->where('id', $task->responsible)->first();
        $resp_name = empty($resp_name) ? '' : $resp_name->name;
        */
        $categories = Item::select('category')->where('category', '<>', '')->groupBy('category')->get();

        $site->start_date = Carbon::parse($site->start_date)->format('Y-m-d');
        $site->end_date = Carbon::parse($site->end_date)->format('Y-m-d');

        $current_date = Carbon::now()->format('Y-m-d');

        $task->start_date = Carbon::parse($task->start_date)->format('Y-m-d');
        $task->end_date = Carbon::parse($task->end_date)->format('Y-m-d');

        return View::make('app.task_form', ['task' => $task, 'categories' => $categories, 'site' => $site,
            'user' => $user, 'service' => $service, 'last_stat' => $last_stat, 'current_date' => $current_date,
            'site_id' => $task->site_id]);
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

        $form_data = Request::all();

        if($form_data['end_date']!='')
            $form_data['end_date'] = $form_data['end_date'].' 23:59:59';

        $v = \Validator::make($form_data, [
            'name'                  => 'required',
            'pondered_weight'       => 'min:1|max:10',
            'site_id'               => 'required',
            'status'                => 'required',
            'resp_name'             => 'required|regex:/^[\pL\s\-]+$/u',
            'total_expected'        => 'required|numeric',
            'units'                 => 'required',
            //'progress'              => 'numeric',
            'start_date'            => 'required',
            'end_date'              => 'required|after:start_date',
        ],
            [
                'name.required'              => 'Debe especificar el nombre de la tarea!',
                //'pondered_weight.required'   => 'El valor de peso ponderado es obligatorio!',
                'pondered_weight.min'        => 'El peso ponderado no puede ser inferior a 1!',
                'pondered_weight.max'        => 'El peso ponderado no puede ser mayor a 10!',
                'site_id.required'           => 'Debe especificar el sitio al que pertenece la tarea!',
                'status.required'            => 'Debe especificar el estado de la tarea!',
                'total_expected.required'    => 'Debe especificar la cantidad contratada!',
                'total_expected.numeric'     => 'La cantidad contratada sólo puede contener números!',
                'units.required'             => 'Debe especificar las unidades de medida!',
                //'progress.numeric'           => 'La cantidad de avance sólo puede contener números!',
                'resp_name.required'         => 'Debe especificar el nombre del responsable de ejecutar la tarea',
                'resp_name.regex'            => 'El nombre del responsable solo puede contener letras',
                'start_date.required'        => 'Debe especificar la fecha de inicio de los trabajos en sitio!',
                'end_date.required'          => 'Debe especificar la fecha de fin de los trabajos en sitio!',
                'end_date.after'             => 'la fecha de fin no puede ser anterior a la fecha de inicio!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $task = Task::find($id);
        $original_number = $task->number;

        $task->fill($form_data);
        
        if($task->total_expected==0){
            Session::flash('message', 'Debe especificar la cantidad proyectada!');
            return redirect()->back()->withInput();
        }

        if($task->pondered_weight<1||$task->pondered_weight>10){
            Session::flash('message', 'El peso ponderado debe estar en el rango de 1 a 10!');
            return redirect()->back()->withInput();
        }

        $task->pondered_weight = $task->pondered_weight ?: 1;

        $task->quote_price = Request::input('cost_unit_central');

        $resp_record = User::select('id')->where('name',Request::input('resp_name'))->first();
        $task->responsible = $resp_record ? $resp_record->id : 0;

        $task->assigned_price = $task->total_expected*$task->quote_price;
        $task->number = $task->number ?: $original_number;

        /*
        if($task->item){
            $item = $task->item; //Item::find($task->item_id);

            $item->description = $task->name;
            $item->number = $task->number; //Request::input('number');
            $item->client_code = Request::input('client_code');
            $item->units = $task->units;
            //$item->cost_unit_central = Request::input('cost_unit_central');
            $item->detail = $task->description;
            $item->category = Request::input('category');
            $item->subcategory = Request::input('subcategory');
            //$item->area = $user->work_type;

            //if ($item->category == "Otro") {
            //    $item->category = Request::input('other_category') ?: 'Otros';
            //}

            $item->save();
        }
        */

        $task->save();

        Session::flash('message', "Datos actualizados correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->action('TaskController@tasks_per_site', ['id' => $task->site_id]);
    }

    public function list_items($id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        /*
        if($id==0){
            $last_stat = Site::first()->last_stat();
            
            $sites = Site::whereNotIn('status', [$last_stat/*'Concluído',0/*'No asignado'])->get();
        }
        else
            $sites = Site::where('id', $id)->get();

        $items = Item::where('area', $user->work_type)->get();
        */

        $site = Site::find($id);
        $assignment = $site->assignment;

        $categories = ItemCategory::select('name');
        //Item::select('category')->where('category', '<>', '')->where('area', $user->work_type)->groupBy('category')->get();

        if($assignment->project_id!=0&&ItemCategory::where('project_id', $assignment->project_id)->count()>0){
            $categories = $categories->where('project_id', $assignment->project_id);
        }

        $categories = $categories->where('status', 1)->get();

        $current_date = Carbon::now()->format('Y-m-d');

        return View::make('app.task_item_list', ['categories' => $categories, 'current_date' => $current_date,
            'site_id' => $id, 'user' => $user, 'service' => $service]);
    }

    public function add_from_list(Request $request, $id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        //$service = Session::get('service');

        $results = Request::all();

        $listed_items = Request::input('listed_items');

        $num_added = 0;
        $num_repeated = 0;

        $site = Site::find($id);

        for($i=0;$i<$listed_items;$i++){
            if(!empty($results['item_'.$i])&&!empty($results['quantity_'.$i])&&$results['quantity_'.$i]>0){
                $item = Item::find($results['item_'.$i]);
                $already_added = false;

                foreach($site->tasks as $task){
                    if($task->item_id==$item->id){
                        $already_added = true; //Check if an item is already present in a site
                        $num_repeated++;
                    }
                }

                if(!$already_added){
                    $task = new Task;
                    $task->user_id = $user->id;
                    $task->site_id = $id;
                    $task->item_id = $item->id;

                    $prev_number = Task::select('number')->where('site_id',$task->site_id)->OrderBy('number','desc')->first();
                    $task->number = empty($prev_number) ? 1 : $prev_number->number+1;

                    $task->name = $item->description;
                    $task->description = $item->detail;
                    $task->pondered_weight = 1; //Default value for weights
                    $task->total_expected = $results['quantity_'.$i];
                    $task->units = $item->units;
                    $task->progress = 0;
                    $task->responsible = 0;
                    $task->quote_price = $item->cost_unit_central;
                    $task->assigned_price = $task->total_expected*$task->quote_price;
                    $task->start_date = $site->start_date;
                    $task->end_date = $site->end_date;
                    $task->additional = strpos($item->category, 'Adicionales')!==false ? 1 : 0;

                    $task->status = Carbon::parse($task->start_date)<Carbon::now() ?
                        $task->status_number('Ejecución') : 1 /* Initial state */;

                    $task->save();

                    $num_added++;
                }
            }
        }

        $this->fill_code_column(); //Complete records with empty code

        Session::flash('message', ($num_added==0 ? "No seleccionó ningún item." :
            ($num_added==1 ? "Se agregó un item a este sitio." : "Se agregaron $num_added items a este sitio.")).
            ($num_repeated>0 ? " Se seleccionaron $num_repeated items repetidos!" : ""));

        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->action('TaskController@tasks_per_site', ['id' => $id]);
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

        $task = Task::find($id);

        if($task){
            $to_return_id = $task->site_id;
            $file_error = false;

            foreach($task->activities as $activity){

                foreach($activity->files as $file){
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

                if (!$file_error)
                    $activity->delete();
                else
                    break;
            }

            if(!$file_error){
                foreach($task->events as $event){

                    foreach($event->files as $file){
                        $file_error = $this->removeFile($file);
                        if($file_error)
                            break;
                    }

                    if (!$file_error)
                        $event->delete();
                    else
                        break;
                }
            }

            if (!$file_error) {
                $task->delete();

                Session::flash('message', "El registro fue eliminado del sistema");
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->action('TaskController@tasks_per_site', ['id' => $to_return_id]);
            }
            else {
                Session::flash('message', "Error al borrar el registro, por favor consulte al administrador. $file_error");
                return redirect()->back();
            }
        }
        else {
            Session::flash('message', "Error al ejecutar el borrado, no se encontró el registro solicitado.");
            return redirect()->back();
        }
    }

    public function clear_task($id)
    {
        //Deletes all activities within an item

        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $task = Task::find($id);

        if($task){
            $file_error = false;

            foreach($task->activities as $activity){

                foreach($activity->files as $file){
                    $file_error = $this->removeFile($file);
                    if($file_error)
                        break;
                }

                if (!$file_error)
                    $activity->delete();
                else
                    break;
            }

            if (!$file_error) {
                Session::flash('message', "Todas las actividades de este item han sido eliminadas");
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->action('TaskController@tasks_per_site', ['id' => $task->site_id]);
            }
            else {
                Session::flash('message', "Error al borrar un registro, por favor consulte al administrador. $file_error");
                return redirect()->back();
            }
        }
        else {
            Session::flash('message', "Error al ejecutar el borrado, no se encontró el item solicitado.");
            return redirect()->back();
        }
    }
    
    public function modify_status($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $match = false; //Boolean to determine if a matching condition is applied

        $task = Task::find($id);
        $action = Input::get('action');
        $message = '';

        if($action=='upgrade'){
            if($task->status<$task->last_stat()){

                $task->status += 1;

                if($task->status==$task->last_stat()){
                    foreach($task->activities as $activity){
                        foreach($activity->files as $file){
                            $this->blockFile($file);
                        }
                    }
                }

                $task->save();

                $match = true;
                $message = "El estado del ítem ha cambiado a ".$task->statuses($task->status);
            }

            /*
            if($task->status=='En espera'){
                $task->status = 'Ejecución';
                $task->save();
            }
            elseif($task->status=='Ejecución'){
                $task->status = 'Revisión';
                $task->save();
            }
            elseif($task->status=='Revisión') {
                $task->status = 'Concluído';
                $task->save();

                foreach($task->activities as $activity){
                    foreach($activity->files as $file){
                        $this->blockFile($file);
                    }
                }
            }

            $match = true;
            $message = "El estado del ítem ha cambiado a $task->status";
            */
        }
        elseif($action=='downgrade'){
            if($task->status>1){

                $task->status -= 1;

                if($task->status==$task->last_stat()-1){
                    foreach($task->activities as $activity) {
                        foreach ($activity->files as $file) {
                            $this->unblockFile($file);
                        }
                    }
                }

                $task->save();

                $match = true;
                $message = "El estado del ítem ha cambiado a ".$task->statuses($task->status);
            }

            /*
            if($task->status=='Ejecución'){
                $task->status = 'En espera';
                $task->save();
            }
            elseif($task->status=='Revisión'){
                $task->status = 'Ejecución';
                $task->save();
            }
            elseif($task->status=='Concluído'){
                $task->status = 'Revisión';
                $task->save();

                foreach($task->activities as $activity) {
                    foreach ($activity->files as $file) {
                        $this->unblockFile($file);
                    }
                }
            }

            $match = true;
            $message = "El estado del ítem ha cambiado a $task->status";
            */
        }
        elseif($action=='close'){
            $task->status = 0 /*'No asignado'*/;
            $task->save();

            foreach($task->activities as $activity){
                foreach($activity->files as $file){
                    $this->blockFile($file);
                }
            }

            $match = true;
            $message = "Este registro ha sido marcado como No asignado";
        }

        if($match){
            $this->add_event('status changed', $task); //Record an event for the date the status was changed

            Session::flash('message', $message);
            if(Session::has('url'))
                return redirect(Session::get('url'));
            else
                return redirect()->action('TaskController@tasks_per_site', ['id' => $task->site_id]);
        }
        else{
            /* default redirection if no match is found */
            return redirect()->back();
        }
    }

    function add_event($type, $task)
    {
        $user = Session::get('user');

        $event = new Event;
        $event->user_id = $user->id;
        $event->date = Carbon::now();

        $prev_number = Event::select('number')->where('eventable_id',$task->id)
            ->where('eventable_type','App\Task')->orderBy('number','desc')->first();

        $event->number = $prev_number ? $prev_number->number+1 : 1;

        if($type=='status changed'){
            $event->description = 'Cambio de estado';
            $event->detail = "$user->name ha cambiado el estado del item $task->code a ".$task->statuses($task->status);
        }

        $event->responsible_id = $user->id;
        $event->eventable()->associate($task /*Task::find($task->id)*/);
        $event->save();
    }

    public function refresh_data($id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');
        
        //$tasks = Task::where('site_id',$id)->orderBy('number')->get();
        $site = Site::find($id);
        $assignment = $site&&$site->assignment ? $site->assignment : 0;

        foreach($site->tasks as $task)
        {
            $this->refresh_task($task);
            /*
            if($task->status!='Concluído'&&$task->status!='No asignado'){
                //$activities = Activity::where('task_id',$task->id)->get();
                $task->progress = 0;

                foreach($task->activities as $activity){
                    $task->progress += $activity->progress;
                }
                
                $task->executed_price = $task->progress*$task->quote_price;
                $task->save();
            }
            */
        }
        if($site){
            $this->refresh_site($site);
        }
        if($assignment){
            $this->refresh_assignment($assignment);
        }

        Session::flash('message', 'Datos actualizados correctamente');
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->action('TaskController@tasks_per_site', ['id' => $id]);
    }

    public function fill_code_column()
    {
        $tasks = Task::where('code','')->get();

        foreach($tasks as $task){
            $task->code = 'TK-'.str_pad($task->id, 4, "0", STR_PAD_LEFT).'0'.$task->number.
                date_format($task->created_at,'-y');

            $task->save();
        }
    }
}
