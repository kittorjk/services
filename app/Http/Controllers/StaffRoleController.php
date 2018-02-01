<?php

namespace App\Http\Controllers;

use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use View;
use App\User;
use App\StaffRole;

class StaffRoleController extends Controller
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
            return View('app.index', ['service' => 'staff', 'user' => null]);
        }
        if($user->acc_staff==0)
            return redirect()->action('LoginController@logout', ['service' => 'staff']);
        
        $roles = StaffRole::where('id', '>', 0)->orderBy('name')->paginate(20);

        $service = Session::get('service');

        return View::make('app.staff_role_brief', ['roles' => $roles, 'service' => $service, 'user' => $user]);
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

        return View::make('app.staff_role_form', ['role' => 0, 'service' => $service, 'user' => $user]);
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
            'name'              => 'required|unique:staff_roles',
            'area'              => 'required',
            'description'       => 'required',
        ],
            [
                'unique'                          => 'Este cargo ya existe!',
                'name.required'                   => 'Debe especificar el nombre del cargo o posición!',
                'area.required'                   => 'Debe seleccionar un área!',
                'description.required'            => 'Debe proporcionar una breve descripción del cargo!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $role = new StaffRole(Request::all());
        
        $area = Request::input('area');

        $search = explode(",","ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u");
        $replace = explode(",","c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u");
        $plain_text = str_replace($search, $replace, $role->name); //Remove special characters from name to generate the code

        $prefix = substr($plain_text, 0, 2);

        $suffix = substr($plain_text, -2);
        $chain = $prefix.'0'.$area.'0'.$suffix;

        $role->user_id = $user->id;
        $role->code = strtoupper($chain);
        $role->in_use = 1; //True / active

        $role->save();

        Session::flash('message', "Se ha registrado un nuevo cargo para personal en el sistema");
        return redirect()->route('staff_role.index');
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

        $role = StaffRole::find($id);
        
        return View::make('app.staff_role_form', ['role' => $role, 'service' => $service, 'user' => $user]);
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

        $role = StaffRole::find($id);
        
        $v = \Validator::make(Request::all(), [
            'name'              => 'required|unique:staff_roles',
            'area'              => 'required',
            'description'       => 'required',
        ],
            [
                'unique'                          => 'Este cargo ya existe!',
                'name.required'                   => 'Debe especificar el nombre del cargo o posición!',
                'area.required'                   => 'Debe seleccionar un área!',
                'description.required'            => 'Debe proporcionar una breve descripción del cargo!',
            ]
        );

        if(Request::input('name')!=$role->name){
            if ($v->fails())
            {
                Session::flash('message', $v->messages()->first());
                return redirect()->back()->withInput();
            }
        }

        $role->fill(Request::all());

        $role->save();
        
        Session::flash('message', "Datos actualizados correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('staff_role.index');
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

        $role = StaffRole::find($id);

        $role->in_use = 0; //False / deactivate

        $role->save();

        Session::flash('message', "Se ha deshabilitado un cargo para personal");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('staff_role.index');
    }

    public function enable_role($id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $role = StaffRole::find($id);

        $role->in_use = 1; //True / activate

        $role->save();

        Session::flash('message', "Se ha habilitado un cargo para personal");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('staff_role.index');
    }
}
