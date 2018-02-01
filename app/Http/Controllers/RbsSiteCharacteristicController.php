<?php

namespace App\Http\Controllers;

use App\RbsSiteCharacteristic;
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
use App\TechGroup;
use Illuminate\Support\Facades\Input;

class RbsSiteCharacteristicController extends Controller
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
        $site_id = Input::get('site_id');

        $site = Site::find($site_id);
        
        if(!$site){
            Session::flash('message', 'No se encontró la información solicitada, revise la dirección e intente de nuevo
                por favor');
            return redirect()->back();
        }
        
        $tech_groups = TechGroup::where('status', 0)->orderBy('group_number')->get();

        return View::make('app.rbs_site_characteristics_form', ['rbs_char' => 0, 'user' => $user, 'service' => $service,
            'site' => $site, 'tech_groups' => $tech_groups]);
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
            //'site_name'             => 'required',
            'site_id'               => 'required',
            'type_station'          => 'required',
            'solution'              => 'required',
            'type_rbs'              => 'required',
            'height'                => 'required_if:type_rbs,Torre',
            'number_floors'         => 'required_if:type_rbs,Roof top',
            'tech_group_id'         => 'required',
        ],
            [
                //'site_name.required'          => 'El nombre del sitio debe estar presente en el formulario!',
                'site_id.required'            => 'El nombre del sitio debe estar presente en el formulario!',
                'type_station.required'       => 'Seleccione el tipo de estación!',
                'solution.required'           => 'Debe especificar el tipo de solución aplicable al sitio!',
                'type_rbs.required'           => 'Seleccione el tipo de radiobase!',
                'height.required_if'          => 'Debe especificar la altura si el sitio es Torre!',
                'number_floors.required_if'   => 'Debe especificar el número de pisos si el sitio es Roof top!',
                'tech_group_id.required'      => 'Seleccione el grupo asignado a este sitio!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $rbs_site_char = new RbsSiteCharacteristic(Request::all());

        $rbs_site_char->user_id = $user->id;

        $rbs_site_char->save();

        Session::flash('message', "El formulario fue guardado correctamente");
        return redirect()->action('SiteController@sites_per_project', ['id' => $rbs_site_char->site->assignment_id]);
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

        $rbs_char = RbsSiteCharacteristic::find($id);

        $site = $rbs_char->site;

        if(!$rbs_char||!$site){
            Session::flash('message', 'No se encontró el registro solicitado, revise la dirección e intente de nuevo por favor');
            return redirect()->back();
        }

        $tech_groups = TechGroup::where('status', 0)->orwhere('id', '=', $rbs_char->tech_group->id)
            ->orderBy('group_number')->get();

        return View::make('app.rbs_site_characteristics_form', ['rbs_char' => $rbs_char, 'user' => $user,
            'service' => $service, 'site' => $site, 'tech_groups' => $tech_groups]);
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
            //'site_name'             => 'required',
            'site_id'               => 'required',
            'type_station'          => 'required',
            'solution'              => 'required',
            'type_rbs'              => 'required',
            'height'                => 'required_if:type_rbs,Torre',
            'number_floors'         => 'required_if:type_rbs,Roof top',
            'tech_group_id'         => 'required',
        ],
            [
                //'site_name.required'          => 'El nombre del sitio debe estar presente en el formulario!',
                'site_id.required'            => 'El nombre del sitio debe estar presente en el formulario!',
                'type_station.required'       => 'Seleccione el tipo de estación!',
                'solution.required'           => 'Debe especificar el tipo de solución aplicable al sitio!',
                'type_rbs.required'           => 'Seleccione el tipo de radiobase!',
                'height.required_if'          => 'Debe especificar la altura si el sitio es Torre!',
                'number_floors.required_if'   => 'Debe especificar el número de pisos si el sitio es Roof top!',
                'tech_group_id.required'      => 'Seleccione el grupo asignado a este sitio!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $rbs_site_char = RbsSiteCharacteristic::find($id);

        $rbs_site_char->fill(Request::all());

        $rbs_site_char->save();

        Session::flash('message', "Datos actualizados correctamente");
        return redirect()->action('SiteController@sites_per_project', ['id' => $rbs_site_char->site->assignment_id]);
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

        $rbs_site_char = RbsSiteCharacteristic::find($id);
        $return_id = $rbs_site_char->site->assignment_id;

        if ($rbs_site_char) {
            $rbs_site_char->delete();

            Session::flash('message', "El registro fue eliminado del sistema");
            return redirect()->action('SiteController@sites_per_project', ['id' => $return_id]);
        }
        else {
            Session::flash('message', "Error al borrar el registro, la información solicitada no fue encontrada en el sistema");
            return redirect()->back();
        }
    }
}
