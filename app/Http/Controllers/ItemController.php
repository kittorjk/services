<?php

namespace App\Http\Controllers;

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
use App\ItemCategory;
use App\File;
use App\Event;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
use App\Http\Traits\FilesTrait;
use App\Http\Traits\ProjectTrait;

class ItemController extends Controller
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

        $cat = Input::get('cat');

        $category = ItemCategory::find($cat);

        if(!$category){
            Session::flash('message', 'No se encontraron registros para la categoría solicitada!');
            return redirect()->back();
        }

        $items = $category->items()->orderBy('number', 'asc')->orderBy('created_at', 'asc')->paginate(20);

        $service = Session::get('service');

        return View::make('app.items_per_category', ['category' => $category, 'items' => $items, 'service' => $service,
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

        $item = Item::find($id);

        $category = $item ? $item->item_category : '';

        if(!$item||!$category){
            Session::flash('message', 'No se encontró la información solicitada en el servidor!');
            return redirect()->back();
        }

        $subcategories = Item::select('subcategory')->where('item_category_id', $category->id)
            ->groupBy('subcategory')->get();

        return View::make('app.item_form', ['item' => $item, 'category' => $category, 'subcategories' => $subcategories,
            'service' => $service, 'user' => $user]);
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
            'number'            => 'required',
            'description'       => 'required',
            'units'             => 'required',
            'cost_unit_central' => 'required|numeric',
            'subcategory'       => 'required',
            'other_subcategory' => 'required_if:subcategory,Otro',
        ],
            [
                'number.required'               => 'Debe indicar un número para éste item!',
                'description.required'          => 'Debe especificar el nombre o descripción de éste item!',
                'units.required'                => 'Debe indicar las unidades de medida para éste item!',
                'cost_unit_central.required'    => 'Debe indicar el costo unitario para éste item!',
                'cost_unit_central.numeric'     => 'El campo precio unitario debe contener sólo números!',
                'subcategory.required'          => 'Debe indicar una subcategoría para éste item!',
                'other_subcategory.required_if' => 'Debe indicar una subcategoría para éste item!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $item = Item::find($id);

        $item->fill(Request::all());

        $item->subcategory = $item->subcategory=="Otro" ? Request::input('other_subcategory') : $item->subcategory;

        $item->save();

        //update task that use it
        $this->update_tasks($item);

        Session::flash('message', "Item modificado correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect('/item?cat='.$item->item_category_id);
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

    function update_tasks($item)
    {
        $tasks = Task::where('item_id', $item->id)->get();

        foreach($tasks as $task){
            $task->quote_price = $item->cost_unit_central;
            $task->assigned_price = $task->total_expected*$task->quote_price;
            $task->save();
        }
    }
}
