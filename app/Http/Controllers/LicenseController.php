<?php

namespace App\Http\Controllers;

use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use View;
use App\User;
use App\License;
use Carbon\Carbon;

class LicenseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        
        $potential_drivers = User::where('id','>',0)->where('status', 'Activo')->orderBy('name')->get();

        return View::make('app.license_form', ['license' => 0, 'service' => $service, 'user' => $user,
            'potential_drivers' => $potential_drivers]);
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

        $license = new License(Request::all());

        $v = \Validator::make(Request::all(), [
            'user_id'          => 'required|unique:licenses',
            'number'           => 'required',
            'category'         => 'required',
            'exp_date'         => 'required|date',
        ],
            [
                'user_id.required'                => 'Debe especificar el usuario a quien pertenece la licencia!',
                'user_id.unique'                  => 'El usuario seleccionado ya tiene registrada una licencia!',
                'number.required'                 => 'Debe especificar el número de la licencia!',
                'category.required'               => 'Debe especificar la categoría según la licencia!',
                'exp_date.required'               => 'Debe especificar la fecha de vencimiento de la licencia!',
                'exp_date.date'                   => 'El campo "fecha de vencimiento" debe contener una fecha válida!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back();
        }

        $license->save();

        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('driver.index');
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

        $license = License::find($id);

        $license->exp_date = Carbon::parse($license->exp_date)->hour(0)->minute(0)->second(0);

        return View::make('app.license_info', ['license' => $license, 'service' => $service, 'user' => $user]);
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

        $license = License::find($id);

        $potential_drivers = User::where('id','>',0)->orderBy('name')->get();

        $license->exp_date = Carbon::parse($license->exp_date)->format('Y-m-d');

        return View::make('app.license_form', ['license' => $license, 'service' => $service, 'user' => $user,
            'potential_drivers' => $potential_drivers]);
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

        $prev_license = License::find($id);

        $v = \Validator::make(Request::all(), [
            'user_id'               => 'unique:licenses',
        ],
            [
                'unique'                  => 'El usuario seleccionado ya tiene registrada una licencia!',
            ]
        );

        if(Request::input('user_id')!=$prev_license->user_id){
            if ($v->fails())
            {
                Session::flash('message', $v->messages()->first());
                return redirect()->back();
            }
        }

        $v = \Validator::make(Request::all(), [
            'user_id'          => 'required',
            'number'           => 'required',
            'category'         => 'required',
            'exp_date'         => 'required|date',
        ],
            [
                'user_id.required'            => 'Debe especificar el usuario a quien pertenece la licencia!',
                'number.required'             => 'Debe especificar el número de la licencia!',
                'category.required'           => 'Debe especificar la categoría según la licencia!',
                'exp_date.required'           => 'Debe especificar la fecha de vencimiento de la licencia!',
                'exp_date.date'               => 'El campo "fecha de vencimiento" debe contener una fecha válida!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back();
        }

        $license = License::find($id);
        $license->fill(Request::all());

        $license->save();

        Session::flash('message', " Datos modificados exitosamente! ");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('driver.index');
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
}
