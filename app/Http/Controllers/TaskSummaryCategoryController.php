<?php

namespace App\Http\Controllers;

use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use View;
use Input;
use App\User;
use App\Task;
use App\TaskSummaryCategory;
use Carbon\Carbon;

class TaskSummaryCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return redirect()->back();
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

        $id = Input::get('id');

        $task = Task::find($id);

        if(!$task){
            Session::flash('message', 'No se encontró la información solicitada en el servidor!');
            return redirect()->back();
        }

        return View::make('app.task_summary_category_form', ['category' => 0, 'service' => $service, 'user' => $user,
            'task' => $task]);
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
            'task_id'          => 'required|unique:task_summary_categories',
            'cat_name'         => 'required',
        ],
            [
                'task_id.required'                => 'Debe especificar el item al que se asignará la categoría!',
                'task_id.unique'                  => 'Este item sólo puede pertenecer a una categoría!',
                'cat_name.required'               => 'Debe seleccionar una categoría!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $task_category = new TaskSummaryCategory(Request::all());

        $task_category->user_id = $user->id;

        $task_category->save();

        $task = $task_category->task;

        Session::flash('message', "Se agregó una categoría al item $task->code");
        //return redirect()->route('task.tasks_per_site', array('id' => $task->site_id));
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

        $category = TaskSummaryCategory::find($id);

        $task = $category->task;

        if(!$task){
            Session::flash('message', 'No se encontró la información solicitada en el servidor!');
            return redirect()->back();
        }

        return View::make('app.task_summary_category_form', ['category' => $category, 'service' => $service,
            'user' => $user, 'task' => $task]);
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

        $v = \Validator::make(Request::all(), [
            'task_id'          => 'required',
            'cat_name'         => 'required',
        ],
            [
                'task_id.required'                => 'Debe especificar el item al que se asignará la categoría!',
                'cat_name.required'               => 'Debe seleccionar una categoría!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $task_category = TaskSummaryCategory::find($id);

        $task_category->fill(Request::all());

        $task_category->user_id = $user->id;

        $task_category->save();

        $task = $task_category->task;

        Session::flash('message', "La categoría del item $task->code ha sido modificada");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->action('TaskController@tasks_per_site', ['id' => $task->site_id]);
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

        $task_category = TaskSummaryCategory::find($id);

        if($task_category){
            $task = $task_category->task;

            if ($task) {
                $task_category->delete();

                Session::flash('message', "La categoría del item $task->code fue eliminada del sistema");
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->action('TaskController@tasks_per_site', ['id' => $task->site_id]);
            }
            else {
                Session::flash('message', "Error al borrar el registro, por favor consulte al administrador!");
                return redirect()->back();
            }
        }
        else {
            Session::flash('message', "Error al ejecutar el borrado, no se encontró el registro solicitado!");
            return redirect()->back();
        }
    }
}
