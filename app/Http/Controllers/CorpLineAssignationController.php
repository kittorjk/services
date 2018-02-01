<?php

namespace App\Http\Controllers;

use App\CorpLineRequirement;
use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use View;
use Mail;
use Input;
use Exception;
use App\User;
use App\CorpLine;
use App\CorpLineAssignation;
use App\Email;
use Carbon\Carbon;
use App\Http\Traits\FilesTrait;

class CorpLineAssignationController extends Controller
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
        if($user->acc_active==0)
            return redirect()->action('LoginController@logout', ['service' => 'active']);

        $service = Session::get('service');

        $ln = Input::get('ln');

        $assignations = CorpLineAssignation::where('id', '>', 0);

        if(!is_null($ln))
            $assignations = $assignations->where('corp_line_id', $ln);

        if($user->priv_level<1){
            $assignations = $assignations->where(function ($query) use($user) {
                $query->where('resp_before_id', $user->id)->orwhere('resp_after_id','=',$user->id);
            });
        }

        $assignations = $assignations->orderBy('created_at','desc')->paginate(20);

        return View::make('app.line_assignation_brief', ['assignations' => $assignations, 'service' => $service,
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

        $ln = Input::get('ln');
        $req = Input::get('req');
        
        $requirements = CorpLineRequirement::where('status',1)->get();
        
        $lines = CorpLine::where('number','<>','')->where('status', 'Disponible')->orderBy('number')->get();
        
        return View::make('app.line_assignation_form', ['assignation' => 0, 'ln' => $ln, 'requirements' => $requirements,
            'req' => $req, 'lines' => $lines, 'service' => $service, 'user' => $user]);
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
            //'corp_line_requirement_id' => 'required|exists:corp_line_requirements,id',
            'corp_line_id'          => 'required|exists:corp_lines,id',
            'service_area'          => 'required',
            'resp_after_name'       => 'required|exists:users,name',
        ],
            [
                //'corp_line_requirement_id.required' => 'Debe seleccionar una opción en el campo "requerimiento"!',
                //'corp_line_requirement_id.exists'   => 'No se encontró en el sistema el requerimiento indicado!',
                'corp_line_id.required'     => 'Debe seleccionar una línea de la lista de opciones!',
                'corp_line_id.exists'       => 'No se encontró en el sistema la línea seleccionada!',
                'service_area.required'     => 'Debe indicar el área de servicio a la que se destina la línea!',
                'resp_after_name.required'  => 'Debe especificar el nombre de la persona a la que se asigna la línea!',
                'resp_after_name.exists'    => 'El nombre de la persona a la que se asigna la línea no está registrado en el sistema!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $assignation = new CorpLineAssignation(Request::all());

        $assignation->user_id = $user->id;
        $line = CorpLine::find($assignation->corp_line_id);

        $assignation->resp_before_id = $line->responsible_id;

        $resp_after = User::where('name', Request::input('resp_after_name'))->first();

        if($resp_after==''){
            Session::flash('message', "El nombre de la persona a la que se asigna la línea no está registrado en el sistema!");
            return redirect()->back()->withInput();
        }
        else
            $assignation->resp_after_id = $resp_after->id;

        $assignation->type = 'Entrega';

        $assignation->save();

        /* Update requirement status */
        if($assignation->requirement){
            $requirement = $assignation->requirement;

            $this->touch_req($requirement, 'store');
        }

        /* Update line information */
        $this->touch_line($line, $assignation, $resp_after, $user, 'store');

        Session::flash('message', "El cambio de responsable de la línea corporativa fue registrado correctamente");
        return redirect()->route('line_assignation.index');
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

        $assignation = CorpLineAssignation::find($id);

        return View::make('app.line_assignation_info', ['assignation' => $assignation, 'service' => $service,
            'user' => $user]);
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

        $assignation = CorpLineAssignation::find($id);

        $requirements = CorpLineRequirement::where('status',1)->orwhere('id', $assignation->corp_line_requirement_id)->get();

        $lines = CorpLine::where('number','<>','')->where(function ($query) use($assignation){
                $query->where('status', 'Disponible')->orwhere('id', $assignation->corp_line_id);
            })->orderBy('number')->get();

        return View::make('app.line_assignation_form', ['assignation' => $assignation, 'ln' => 0, 'lines' => $lines,
            'requirements' => $requirements, 'req' => 0, 'service' => $service, 'user' => $user]);
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
            //'corp_line_requirement_id' => 'required|exists:corp_line_requirements,id',
            'corp_line_id'          => 'required|exists:corp_lines,id',
            'service_area'          => 'required',
            'resp_after_name'       => 'required|exists:users,name',
        ],
            [
                //'corp_line_requirement_id.required' => 'Debe seleccionar una opción en el campo "requerimiento"!',
                //'corp_line_requirement_id.exists'   => 'No se encontró en el sistema el requerimiento indicado!',
                'corp_line_id.required'     => 'Debe seleccionar una línea de la lista de opciones!',
                'corp_line_id.exists'       => 'No se encontró en el sistema la línea seleccionada!',
                'service_area.required'     => 'Debe indicar el área de servicio a la que se destina la línea!',
                'resp_after_name.required'  => 'Debe especificar el nombre de la persona a la que se asigna la línea!',
                'resp_after_name.exists'    => 'El nombre de la persona a la que se asigna la línea no está registrado en el sistema!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $assignation = CorpLineAssignation::find($id);

        $assignation->fill(Request::all());

        $resp_after = User::where('name', Request::input('resp_after_name'))->first();

        if($resp_after==''){
            Session::flash('message', "El nombre de la persona a la que se asigna la línea no está registrado en el sistema!");
            return redirect()->back()->withInput();
        }
        else
            $assignation->resp_after_id = $resp_after->id;

        $assignation->save();

        /* Update requirement status */
        if($assignation->requirement){
            $requirement = $assignation->requirement;

            $this->touch_req($requirement, 'update');
        }
        
        /* Update line information */
        $line = $assignation->line;

        if($line){
            $this->touch_line($line, $assignation, $resp_after, $user, 'update');
        }

        Session::flash('message', "La asignación ha sido modificada correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('line_assignation.index');
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

    public function devolution_form()
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');
        
        $ln_id = Input::get('ln_id');
        
        $line = CorpLine::find($ln_id);

        $service = Session::get('service');

        return View::make('app.line_devolution_form', ['assignation' => 0, 'line' => $line, 'service' => $service,
            'user' => $user]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register_devolution(Request $request)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $v = \Validator::make(Request::all(), [
            'corp_line_id'          => 'required|exists:corp_lines,id',
        ],
            [
                'corp_line_id.required'     => 'Debe seleccionar una línea de la lista de opciones!',
                'corp_line_id.exists'       => 'No se encontró en el sistema la línea seleccionada!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $devolution = new CorpLineAssignation(Request::all());

        $devolution->user_id = $user->id;
        $line = CorpLine::find($devolution->corp_line_id);

        $devolution->resp_before_id = $line->responsible_id;

        $devolution->resp_after_id = $user->id;

        $devolution->type = 'Devolución';

        $devolution->save();

        /* Update line information */
        $this->touch_line($line, 0, 0, $user, 'update');

        Session::flash('message', "Se registró correctamente la devolución de la línea $line->number");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('corporate_line.index');
    }

    function touch_line($line, $assignation, $resp_after, $user, $mode)
    {
        if($mode=='store'){
            $line->status = 'En uso';
            $line->flags = '0010';
            $line->service_area = $assignation->service_area;
            $line->responsible_id = $resp_after->id;
        }
        elseif($mode=='update'){
            $line->service_area = $assignation->service_area;
            $line->responsible_id = $resp_after->id;
        }
        elseif($mode=='devolution'){
            $line->status = 'Disponible';
            $line->flags = '0001';
            $line->service_area = 'Oficina '.$user->branch;
            $line->responsible_id = $user->id;
        }

        $line->save();
    }

    function touch_req($requirement, $mode)
    {
        $requirement->status = 2; //Requirement completed
        $requirement->stat_change = Carbon::now();

        $requirement->save();
    }
}
