<?php

namespace App\Http\Controllers;

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

class CorpLineController extends Controller
{
    use FilesTrait;
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

        $lines = CorpLine::where('id', '>', 0);

        if(!($user->priv_level>=1))
            $lines = $lines->where('responsible_id', $user->id);

        /*
        $db_query = $lines->orderBy('number')->get();

        Session::put('db_query', $db_query);
        */

        $lines = $lines->orderBy('number')->paginate(20);

        return View::make('app.corp_line_brief', ['lines' => $lines, 'service' => $service, 'user' => $user]);
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
        
        $technologies = CorpLine::select('technology')->where('technology', '<>', '')->groupBy('technology')->get();

        return View::make('app.corp_line_form', ['line' => 0, 'technologies' => $technologies, 'service' => $service,
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

        $v = \Validator::make(Request::all(), [
            'number'            => 'required|numeric||digits_between:7,8|unique:corp_lines',
            'service_area'      => 'required',
            'avg_consumption'   => 'numeric',
            'credit_assigned'   => 'numeric',
        ],
            [
                'number.unique'                   => 'Este número de línea ya está registrado!',
                'number.required'                 => 'Debe especificar el número de línea que desea registrar!',
                'number.numeric'                  => 'El número de línea introducido no es válido!',
                'number.digits_between'           => 'El número de línea introducido no es válido!',
                'service_area.required'           => 'Debe especificar el área donde se utilizará la línea!',
                'avg_consumption.numeric'         => 'El campo "Consumo promedio" contiene caracteres no válidos!',
                'credit_assigned.numeric'         => 'El campo "Crédito asignado" contiene caracteres no válidos!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $line = new CorpLine(Request::all());

        $line->technology = $line->technology=="Otro" ? Request::input('other_technology') : $line->technology;

        $responsible = User::where('area', 'Gerencia General')->where('priv_level', 2)->first();

        $line->responsible_id = $responsible ? $responsible->id : $user->id;
        $line->status = 'Disponible';
        $line->flags = '0001';

        $line->save();

        Session::flash('message', "Línea corporativa registrada correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('corporate_line.index');
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

        $line = CorpLine::find($id);

        return View::make('app.corp_line_info', ['line' => $line, 'service' => $service, 'user' => $user]);
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

        $line = CorpLine::find($id);

        $technologies = CorpLine::select('technology')->where('technology','<>','')->groupBy('technology')->get();

        return View::make('app.corp_line_form', ['line' => $line, 'technologies' => $technologies, 'service' => $service,
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

        $line = CorpLine::find($id);

        if(Request::input('number')!=$line->number){
            $v = \Validator::make(Request::all(), [
                'number'            => 'required|numeric||digits_between:7,8|unique:corp_lines',
            ],
                [
                    'number.unique'                   => 'Este número de línea ya está registrado!',
                    'number.required'                 => 'Debe especificar el número de línea que desea registrar!',
                    'number.numeric'                  => 'El número de línea introducido no es válido!',
                    'number.digits_between'           => 'El número de línea introducido no es válido!',
                ]
            );

            if ($v->fails())
            {
                Session::flash('message', $v->messages()->first());
                return redirect()->back()->withInput();
            }
        }

        $v = \Validator::make(Request::all(), [
            'service_area'      => 'required',
            'avg_consumption'   => 'numeric',
            'credit_assigned'   => 'numeric',
        ],
            [
                'service_area.required'           => 'Debe especificar el área donde se utilizará la línea!',
                'avg_consumption.numeric'         => 'El campo "Consumo promedio" contiene caracteres no válidos!',
                'credit_assigned.numeric'         => 'El campo "Crédito asignado" contiene caracteres no válidos!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $line->fill(Request::all());

        $line->technology = $line->technology=="Otro" ? Request::input('other_technology') : $line->technology;

        $line->save();

        Session::flash('message', "Información de línea corporativa modificada correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('corporate_line.index');
    }

    public function disable_form() {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $cl_id = Input::get('cl_id');
        
        $line = CorpLine::find($cl_id);

        if (!$line) {
            Session::flash('message', "Ocurrió un error al recuperar el registro de la línea, intente de nuevo por favor.");
            return redirect()->back();
        }

        return View::make('app.corp_line_disable_form', ['line' => $line, 'service' => $service, 'user' => $user]);
    }

    public function disable_record(Request $request) {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $v = \Validator::make(Request::all(), [
            'observations'             => 'required|filled',
        ],
            [
                'observations.required'        => 'Debe especificar el motivo para dar de baja esta línea!',
                'observations.filled'          => 'El campo "Motivo de baja" no puede estar vacío!',
            ]
        );

        if ($v->fails()) {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $id = Request::input('line_id');

        $line = CorpLine::find($id);

        $line->fill(Request::all());

        $line->status = 'Baja';
        $line->flags = '0000';
        $line->save();

        foreach ($line->files as $file) {
            $this->blockFile($file);
        }

        Session::flash('message', "La línea corporativa número $line->number ha sido dada de baja");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('corporate_line.index');
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
