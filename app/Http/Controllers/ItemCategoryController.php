<?php

namespace App\Http\Controllers;

use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use View;
use Input;
use App\User;
use App\Item;
use App\ItemCategory;
use App\File;
use App\Activity;
use App\Project;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
use App\Http\Traits\FilesTrait;
use App\Http\Traits\ProjectTrait;

class ItemCategoryController extends Controller
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
            return View('app.index', ['service' => 'project', 'user' => null]);
        }
        if($user->acc_project==0)
            return redirect()->action('LoginController@logout', ['service' => 'project']);
        
        $categories = ItemCategory::where('id', '>', 0)->where('status',1)->orderBy('name')->paginate(20);

        $service = Session::get('service');

        return View::make('app.item_categories_brief', ['categories' => $categories, 'service' => $service,
            'user' => $user]);
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
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $category = ItemCategory::find($id);

        $clients = ItemCategory::select('client')->where('client', '<>', '')->groupBy('client')->get();
        $projects = Project::select('id','name')->where('status', 'Activo')->get();

        return View::make('app.item_categories_form', ['category' => $category, 'clients' => $clients,
            'projects' => $projects, 'service' => $service, 'user' => $user]);
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

        $modify_category = ItemCategory::find($id);
        $new_name = false;

        if(Request::input('name')!=$modify_category->name){
            $new_name = true;

            $v = \Validator::make(Request::all(), [
                'name'               => 'required|unique:item_categories',
            ],
                [
                    'name.unique'                     => 'Éste nombre de categoría ya está en uso!',
                    'name.required'                   => 'Debe especificar el nombre de la categoría!',
                ]
            );

            if ($v->fails())
            {
                Session::flash('message', $v->messages()->first());
                return redirect()->back()->withInput();
            }
        }

        $v = \Validator::make(Request::all(), [
            'area'              => 'required',
            'client'            => 'required',
            'other_client'      => 'required_if:client,Otro',
        ],
            [
                'area.required'                   => 'Debe especificar el área de trabajo en que se aplica ésta categoría!',
                'client.required'                 => 'Debe especificar un Cliente!',
                'other_client.required_if'        => 'Debe especificar un Cliente!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $modify_category->fill(Request::all());

        $modify_category->client = $modify_category->client=="Otro" ? Request::input('other_client') :
            $modify_category->client;

        $modify_category->save();

        if($new_name){
            foreach($modify_category->items as $item){
                $item->category = $modify_category->name;
                $item->save();
            }
        }

        Session::flash('message', "Datos actualizados correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('item_category.index');
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

    public function stat_change()
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $id = Input::get('id');
        $action = Input::get('action');

        $category = ItemCategory::find($id);

        if($category){
            if($action=='close'){
                $category->status = 0;
                $category->save();

                Session::flash('message', 'La categoría ha sido archivada');
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->route('item_category.index');
            }
            else{
                Session::flash('message', 'No se reconoce la accción solicitada!');
                return redirect()->back();
            }
        }
        else{
            Session::flash('message', 'No se encontró el registro solicitado!');
            return redirect()->back();
        }
    }
}
