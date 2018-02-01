<?php

namespace App\Http\Controllers;

use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use View;
use Mail;
use Exception;
use App\User;
use App\Warehouse;
use App\Material;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MaterialController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id)) {
            return View('app.index', ['service'=>'warehouse', 'user'=>null]);
        }
        if($user->acc_warehouse==0)
            return redirect()->action('LoginController@logout', ['service' => 'warehouse']);

        $service = Session::get('service');

        $materials = Material::orderBy('name')->paginate(20);

        return View::make('app.material_brief', ['materials' => $materials, 'service' => $service,
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
        $categories = Material::select('category')->where('category','<>','')->groupBy('category')->get();

        return View::make('app.material_form', ['material' => 0, 'categories' => $categories, 'service' => $service,
            'user' => $user]);
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

        $material = new Material(Request::all());

        $v = \Validator::make(Request::all(), [
            'name'              => 'required',
            'type'              => 'required',
            'units'             => 'required',
            'cost_unit'         => 'numeric',
            'category'          => 'required',
            'other_category'    => 'required_if:category,Otro',
        ],
            [
                'name.required'                   => 'Debe especificar el nombre del material!',
                'type.required'                   => 'Debe especificar el tipo de material!',
                'units.required'                  => 'Debe especificar las unidades de medida del material!',
                'cost_unit.numeric'               => 'El valor del campo "Costo por unidad" debe contener sólo números!',
                'category.required'               => 'Debe especificar la categoría a la que pertenece el material!',
                'other_category.required_if'      => 'Debe especificar la categoría a la que pertenece el material!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back();
        }

        $material->category = $material->category=="Otro" ? Request::input('other_category') : $material->category;
        $material->save();

        $this->fill_code_column();

        Session::flash('message', " Nuevo material agregado al sistema correctamente ");
        return redirect()->route('material.index');
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
        $material = Material::find($id);

        return View::make('app.material_info', ['material' => $material, 'service' => $service, 'user' => $user]);
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

        $material = Material::find($id);
        $categories = Material::select('category')->where('category','<>','')->groupBy('category')->get();
        
        return View::make('app.material_form', ['material' => $material, 'categories' => $categories, 'service' => $service,
            'user' => $user]);
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

        $material = Material::find($id);

        $v = \Validator::make(Request::all(), [
            'name'              => 'required',
            'type'              => 'required',
            'units'             => 'required',
            'cost_unit'         => 'numeric',
            'category'          => 'required',
            'other_category'    => 'required_if:category,Otro',
        ],
            [
                'name.required'                   => 'Debe especificar el nombre del material!',
                'type.required'                   => 'Debe especificar el tipo de material!',
                'units.required'                  => 'Debe especificar las unidades de medida del material!',
                'cost_unit.numeric'               => 'El valor del campo "Costo por unidad" debe contener sólo números!',
                'category.required'               => 'Debe especificar la categoría a la que pertenece el material!',
                'other_category.required_if'      => 'Debe especificar la categoría a la que pertenece el material!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back();
        }

        $material->fill(Request::all());

        $material->category = $material->category=="Otro" ? Request::input('other_category') : $material->category;
        
        $material->save();

        Session::flash('message', " Datos de material modificados exitosamente! ");
        return redirect()->route('material.index');
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

        $material = Material::find($id);

        if($material){
            $deletable = true;

            foreach($material->warehouses as $warehouse){
                if($warehouse->pivot->qty>0)
                    $deletable = false;
            }

            if($deletable){
                foreach($material->files as $file){
                    $success = true;

                    try {
                        \Storage::disk('local')->delete($file->name);
                    } catch (ModelNotFoundException $ex) {
                        $success = false;
                    }

                    if($success)
                        $file->delete();
                }

                $material->delete();

                Session::flash('message', " Se eliminó el registro con éxito ");
                return redirect()->route('material.index');
            }

            Session::flash('message', "No se pudo eliminar el material porque uno o más almacenes lo
                tienen registrado en el sistema!");
            return redirect()->back();
        }
        else {
            Session::flash('message', "Error al borrar el registro, revise la dirección e intente de nuevo por favor.");
            return redirect()->back();
        }
    }

    public function main_pic_id_form($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');
        $material = Material::find($id);

        if(!$material){
            Session::flash('message', "Ocurrió un error al recuperar los datos del servidor, intente de nuevo por favor.");
            return redirect()->back();
        }

        return View::make('app.change_main_pic_id_form', ['model' => $material, 'type' => 'material', 'service' => $service,
            'user' => $user]);
    }

    public function change_main_pic_id(Request $request, $id)
    {
        $material = Material::find($id);

        if(!$material){
            Session::flash('message', "Ocurrió un error al recuperar los datos del servidor, intente de nuevo por favor.");
            return redirect()->back();
        }

        $material->main_pic_id = Request::input('new_id');
        $material->save();

        Session::flash('message', 'La imagen principal de éste material ha cambiado');
        return redirect()->route('material.index');
    }
    
    public function fill_code_column()
    {
        $materials = Material::where('code','')->get();
        
        foreach($materials as $material){
            $material->code = 'MT-'.date('ymd-').str_pad($material->id,3,"0",STR_PAD_LEFT);
            
            $material->save();
        }
    }
}
