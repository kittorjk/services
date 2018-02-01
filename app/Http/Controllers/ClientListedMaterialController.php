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
use App\Email;
use App\Site;
use App\ClientListedMaterial;
use Illuminate\Support\Facades\Input;

class ClientListedMaterialController extends Controller
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
            return View('app.index', ['service'=>'project', 'user'=>null]);
        }
        if($user->acc_project==0)
            return redirect()->action('LoginController@logout', ['service' => 'project']);

        $service = Session::get('service');

        $client = Input::get('client');

        if($client==''){
            $listed_materials = ClientListedMaterial::where('id', '>', 0)->paginate(20);
        }
        else{
            $listed_materials = ClientListedMaterial::where('client', $client)->paginate(20);
        }

        return View::make('app.client_listed_material_brief', ['listed_materials' => $listed_materials,
            'client' => $client, 'service' => $service, 'user' => $user]);
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

        $client = Input::get('client');

        $stored_clients = ClientListedMaterial::select('client')->where('client', '<>', '')->groupBy('client')->get();

        return View::make('app.client_listed_material_form', ['listed_material' => 0, 'client' => $client,
            'stored_clients' => $stored_clients, 'user' => $user, 'service' => $service]);
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

        $v = \Validator::make(Request::all(), [
            'client'                => 'required',
            'other_client'          => 'required_if:client,Otro',
            'name'                  => 'required',
            'applies_to'            => 'required',
        ],
            [
                'client.required'               => 'Debe especificar un cliente!',
                'other_client.required_if'      => 'Debe especificar un cliente!',
                'name.required'                 => 'Debe especificar el nombre del item/material!',
                'applies_to.required'           => 'Debe especificar a que solución/escenario se aplica el material!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $listed_material = new ClientListedMaterial(Request::all());

        $listed_material->user_id = $user->id;
        $listed_material->client = $listed_material->client=='Otro' ? Request::input('other_client') : $listed_material->client;

        if(ClientListedMaterial::where('client', $listed_material->client)->where('name', $listed_material->name)->exists()){
            Session::flash('message', 'Un material con el mismo nombre y cliente ya existe!');
            return redirect()->back();
        }

        $listed_material->save();

        Session::flash('message', "El material fue registrado correctamente");
        return redirect('/client_listed_material?client='.$listed_material->client);
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

        $listed_material = ClientListedMaterial::find($id);

        if(!$listed_material){
            Session::flash('message', 'No se encontró el registro solicitado, revise la dirección e intente de nuevo por favor');
            return redirect()->back();
        }

        $stored_clients = ClientListedMaterial::select('client')->where('client', '<>', '')->groupBy('client')->get();

        return View::make('app.client_listed_material_form', ['listed_material' => $listed_material, 'client' => '',
            'stored_clients' => $stored_clients, 'user' => $user, 'service' => $service]);
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

        $v = \Validator::make(Request::all(), [
            'client'                => 'required',
            'other_client'          => 'required_if:client,Otro',
            'name'                  => 'required',
            'applies_to'            => 'required',
        ],
            [
                'client.required'               => 'Debe especificar un cliente!',
                'other_client.required_if'      => 'Debe especificar un cliente!',
                'name.required'                 => 'Debe especificar el nombre del item/material!',
                'applies_to.required'           => 'Debe especificar a que solución/escenario se aplica el material!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $listed_material = ClientListedMaterial::find($id);

        $old_name = $listed_material->name;

        $listed_material->fill(Request::all());

        $listed_material->client = $listed_material->client=='Otro' ? Request::input('other_client') : $listed_material->client;

        if(ClientListedMaterial::where('client', $listed_material->client)->where('name', $listed_material->name)->exists()
            &&$old_name!=$listed_material->name){
            Session::flash('message', 'Un material con el mismo nombre y cliente ya existe!');
            return redirect()->back();
        }

        $listed_material->save();

        Session::flash('message', "Datos modificados correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect('/client_listed_material?client='.$listed_material->client);
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

        $listed_material = ClientListedMaterial::find($id);
        $return_client = $listed_material->client;

        if ($listed_material) {
            $listed_material->delete();

            Session::flash('message', "El registro fue eliminado del sistema");
            if(Session::has('url'))
                return redirect(Session::get('url'));
            else
                return redirect('/client_listed_material?client='.$return_client);
        }
        else {
            Session::flash('message', "Error al borrar el registro, la información solicitada no fue encontrada en el sistema");
            return redirect()->back();
        }
    }
}
