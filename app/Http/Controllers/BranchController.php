<?php

namespace App\Http\Controllers;

use App\Employee;
use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use View;
use App\User;
use App\Branch;
use Illuminate\Support\Facades\DB;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');
        
        $branches = Branch::where('id', '>', 0)->orderBy('name')->paginate(20);
        
        $service = Session::get('service');

        return View::make('app.branch_brief', ['branches' => $branches, 'service' => $service, 'user' => $user]);
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

        return View::make('app.branch_form', ['branch' => 0, 'service' => $service, 'user' => $user]);
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
            'name'          => 'required',
            'city'          => 'required',
            'phone'         => 'numeric|digits_between:7,8',
            'alt_phone'     => 'numeric|digits_between:7,8',
        ],
            [
                'name.required'         => 'Debe especificar el nombre de la sucursal o asignarle uno en caso de que no lo tenga!',
                'city.required'         => 'Debe especificar la ciudad en la que se ubica la sucursal!',
                'phone.numeric'         => 'El campo "teléfono" sólo puede contener números!',
                'phone.digits_between'  => 'Número de teléfono no válido!',
                'alt_phone.numeric'     => 'El campo "telf alternativo" sólo puede contener números!',
                'alt_phone.digits_between'  => 'Número de teléfono alternativo no válido!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $branch = new Branch(Request::all());

        $branch->active = 1; // Active

        $head_name = Request::input('head_name');

        if($head_name!=''){
            $head_person = Employee::where(function ($query) use($head_name){
                $query->where(DB::raw("CONCAT(`first_name`, ' ', `last_name`)"), 'like', "%$head_name%");
            })->first();

            if(!$head_person){
                Session::flash('message', 'El nombre de encargado de la sucursal especificado no fue encontrado
                en la lista de empleados!');
                return redirect()->back()->withInput();
            }

            $branch->head_id = $head_person->id;
        }
        else{
            $branch->head_id = 0;
        }

        $branch->save();
        
        Session::flash('message', "La sucursal fue registrada correctamente");
        return redirect()->route('branch.index');
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

        $branch = Branch::find($id);

        return View::make('app.branch_info', ['branch' => $branch, 'service' => $service, 'user' => $user]);
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

        $branch = Branch::find($id);

        $service = Session::get('service');

        return View::make('app.branch_form', ['branch' => $branch, 'service' => $service, 'user' => $user]);
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
            'name'          => 'required',
            'city'          => 'required',
            'phone'         => 'numeric|digits_between:7,8',
            'alt_phone'     => 'numeric|digits_between:7,8',
        ],
            [
                'name.required'         => 'Debe especificar el nombre de la sucursal o asignarle uno en caso de que no lo tenga!',
                'city.required'         => 'Debe especificar la ciudad en la que se ubica la sucursal!',
                'phone.numeric'         => 'El campo "teléfono" sólo puede contener números!',
                'phone.digits_between'  => 'Número de teléfono no válido!',
                'alt_phone.numeric'     => 'El campo "telf alternativo" sólo puede contener números!',
                'alt_phone.digits_between'  => 'Número de teléfono alternativo no válido!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $branch = Branch::find($id);
        $branch->fill(Request::all());

        $head_name = Request::input('head_name');

        if($head_name!=''){
            $head_person = Employee::where(function ($query) use($head_name){
                $query->where(DB::raw("CONCAT(`first_name`, ' ', `last_name`)"), 'like', "%$head_name%");
            })->first();

            if(!$head_person){
                Session::flash('message', 'El nombre de encargado de la sucursal especificado no fue encontrado
                en la lista de empleados!');
                return redirect()->back()->withInput();
            }

            $branch->head_id = $head_person->id;
        }
        else{
            $branch->head_id = 0;
        }

        $branch->save();

        Session::flash('message', "Datos modificados correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('branch.index');
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

        Branch::find($id)
            ->update([
                'active'        => 0,
            ]);

        Session::flash('message', 'El registro de sucursal seleccionado ha sido marcado como "Deshabilitado"');
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('branch.index');
    }
}
