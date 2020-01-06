<?php

namespace App\Http\Controllers;

use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use View;
use App\ServiceParameter;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;

class ServiceParameterController extends Controller
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
            return View('app.index', ['service' => 'active', 'user' => null]);
        }
        if($user->priv_level<4)
            return redirect()->action('LoginController@logout', ['service' => 'active']);

        $service = Session::get('service');
        
        $service_parameters = ServiceParameter::where('id', '>', 0)->orderBy('group')->paginate(20);
        
        return View::make('app.service_parameter_brief', ['service_parameters' => $service_parameters, 
            'service' => $service, 'user' => $user]);
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

        $groups = ServiceParameter::select('group')->where('group', '<>', '')->groupBy('group')->get();

        return View::make('app.service_parameter_form', ['service_parameter' => 0, 'groups' => $groups,
            'service' => $service, 'user' => $user]);
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

        $service_parameter = new ServiceParameter(Request::all());

        $v = \Validator::make(Request::all(), [
            'name'              => 'unique:service_parameters|required',
            'group'             => 'required',
            'other_group'       => 'required_if:group,Otro',
            'content_type'      => 'required',
            'numeric_content'   => 'required_if:content_type,numeric|numeric',
            'units'             => 'required_if:content_type,numeric',
            'literal_content'   => 'required_if:content_type,literal',
        ],
            [
                'unique'                          => 'Un parámetro con este nombre ya existe!',
                'name.required'                   => 'Debe especificar el nombre del parámetro!',
                'group.required'                  => 'Debe especificar el grupo al que pertenece el parámetro!',
                'other_group.required_if'         => 'Debe especificar el grupo al que pertenece el parámetro!',
                'content_type.required'           => 'Debe especificar el tipo de contenido!',
                'numeric_content.required_if'     => 'Debe especificar el valor del parámetro si es numérico!',
                'numeric_content.numeric'         => 'El campo "valor" sólo puede contener números!',
                'units.required_if'               => 'Debe especificar las unidades del parámetro si es numérico',
                'literal_content.required_if'     => 'Debe especificar el contenido del parámetro si es literal',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $service_parameter->group = $service_parameter->group=="Otro" ? Request::input('other_group') :
            $service_parameter->group;

        $service_parameter->user_id = $user->id;

        $service_parameter->save();

        Session::flash('message', "Se ha agregado un nuevo parámetro al sistema, por favor modifique el código para
            poder utilizarlo");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('service_parameter.index');
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

        $service_parameter = ServiceParameter::find($id);

        return View::make('app.service_parameter_info', ['service_parameter' => $service_parameter, 
            'service' => $service, 'user' => $user]);
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

        $service_parameter = ServiceParameter::find($id);

        $groups = ServiceParameter::select('group')->where('group', '<>', '')->groupBy('group')->get();

        return View::make('app.service_parameter_form', ['service_parameter' => $service_parameter, 'groups' => $groups,
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

        $service_parameter = ServiceParameter::find($id);

        $v = \Validator::make(Request::all(), [
            'other_group'       => 'required_if:group,Otro',
            'content_type'      => 'required',
            'numeric_content'   => 'required_if:content_type,numeric|numeric',
            'units'             => 'required_if:content_type,numeric',
            'literal_content'   => 'required_if:content_type,literal',
        ],
            [
                'other_group.required_if'         => 'Debe especificar el grupo al que pertenece el parámetro!',
                'content_type.required'           => 'Debe especificar el tipo de contenido!',
                'numeric_content.required_if'     => 'Debe especificar el valor del parámetro si es numérico!',
                'numeric_content.numeric'         => 'El campo "valor" sólo puede contener números!',
                'units.required_if'               => 'Debe especificar las unidades del parámetro si es numérico',
                'literal_content.required_if'     => 'Debe especificar el contenido del parámetro si es literal',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $service_parameter->fill(Request::all());
        
        $service_parameter->group = $service_parameter->group=="Otro" ? Request::input('other_group') :
            $service_parameter->group;

        $service_parameter->user_id = $user->id;

        $service_parameter->save();

        Session::flash('message', "El parámetro fue modificado, por favor verifique su correcto funcionamiento");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('service_parameter.index');
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
