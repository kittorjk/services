<?php

namespace App\Http\Controllers;

use App\Project;
use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use View;
use Mail;
use Input;
use App\Guarantee;
use App\User;
use App\Assignment;
use Carbon\Carbon;
use App\Http\Traits\FilesTrait;

class GuaranteeController extends Controller
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
            return View('app.index', ['service' => 'project', 'user' => null]);
        }
        if($user->acc_project==0)
            return redirect()->action('LoginController@logout', ['service' => 'project']);

        $service = Session::get('service');

        $arch = Input::get('arch');

        $guarantees = Guarantee::where('id','>',0);

        if(!is_null($arch))
            $guarantees = $guarantees->where('closed', $arch);

        //$guarantees = Guarantee::where('expiration_date','>',Carbon::now())->paginate(20);
        $guarantees = $guarantees->orderBy('closed', 'asc')->orderBy('expiration_date')->paginate(20);

        foreach($guarantees as $guarantee){
            $guarantee->start_date = Carbon::parse($guarantee->start_date);
            $guarantee->expiration_date = Carbon::parse($guarantee->expiration_date);
        }

        return View::make('app.guarantee_brief', ['guarantees' => $guarantees, 'service' => $service,
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

        $companies = Guarantee::select('company')->where('company','<>','')->groupBy('company')->get();
        //$assignments = Assignment::where('id', '>', 0)->whereNotIn('status', ['Concluído','No asignado'])
        //        ->orderBy('name')->get();

        return View::make('app.guarantee_form', ['guarantee' => 0, 'companies' => $companies,
            /*'assignments' => $assignments, */'service' => $service, 'user' => $user]);
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

        $guarantee = new Guarantee(Request::all());

        $v = \Validator::make(Request::all(), [
            'code'              => 'required',
            'company'           => 'required',
            'other_company'     => 'required_if:company,Otro',
            'type'              => 'required',
            'to_morph_id'       => 'required_if:type,Garantía (proyectos),Garantía (asignaciones)',
            'applied_to'        => 'required_unless:type,Garantía (proyectos),Garantía (asignaciones)',
            'start_date'        => 'required|date',
            'expiration_date'   => 'required|date|after:start_date',
        ],
            [
                'code.required'               => 'Debe especificar el número de poliza!',
                'company.required'            => 'Debe especificar la empresa emisora de la poliza!',
                'other_company.required_if'   => 'Debe especificar la empresa emisora de la poliza!',
                'type.required'               => 'Debe especificar el tipo de poliza!',
                'to_morph_id.required_if'     => 'No seleccionó el item al que corresponde la poliza',
                'applied_to.required_unless'  => 'Debe especificar el objeto de la poliza!',
                'start_date.required'         => 'Debe especificar la fecha de inicio de la poliza!',
                'start_date.date'             => 'Debe introducir una fecha de inicio válida',
                'expiration_date.required'    => 'Debe especificar la fecha de vencimiento de la poliza!',
                'expiration_date.date'        => 'Debe introducir una fecha de vencimiento válida',
                'expiration_date.after'       => 'La fecha de vencimiento no puede ser anterior a la fecha de inicio!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back();
        }

        $guarantee->company = $guarantee->company=='Otro' ? Request::input('other_company') : $guarantee->company;

        $to_morph_id = Request::input('to_morph_id');

        if($guarantee->type=='Garantía (proyectos)'){
            $project = Project::find($to_morph_id);
            $guarantee->guaranteeable()->associate($project);
            $guarantee->applied_to = 'Aplica a proyecto: '.$project->name;
        }
        elseif($guarantee->type=='Garantía (asignaciones)'){
            $assignment = Assignment::find($to_morph_id);
            $guarantee->guaranteeable()->associate($assignment);
            $guarantee->applied_to = 'Aplica a asignación: '.$assignment->name;
        }

        $guarantee->user_id = $user->id;
        $guarantee->save();

        Session::flash('message', "La poliza fue agregada al sistema correctamente");
        return redirect()->route('guarantee.index');
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

        $guarantee = Guarantee::find($id);
        $guarantee->expiration_date = Carbon::parse($guarantee->expiration_date);
        $guarantee->start_date = Carbon::parse($guarantee->start_date);

        return View::make('app.guarantee_info', ['guarantee' => $guarantee, 'service' => $service, 'user' => $user]);
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
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $guarantee = Guarantee::find($id);

        $companies = Guarantee::select('company')->where('company','<>','')->groupBy('company')->get();
        //$assignments = Assignment::where('id', '>', 0)->whereNotIn('status', ['Concluído','No asignado'])
        //    ->orderBy('name')->get();

        $guarantee->start_date = Carbon::parse($guarantee->start_date)->format('Y-m-d');
        $guarantee->expiration_date = Carbon::parse($guarantee->expiration_date)->format('Y-m-d');

        return View::make('app.guarantee_form', ['guarantee' => $guarantee, 'companies' => $companies,
            /*'assignments' => $assignments,*/ 'service' => $service, 'user' => $user]);
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

        $guarantee = Guarantee::find($id);

        $v = \Validator::make(Request::all(), [
            'code'              => 'required',
            'company'           => 'required',
            'other_company'     => 'required_if:company,Otro',
            'type'              => 'required',
            'to_morph_id'       => 'required_if:type,Garantía (proyectos),Garantía (asignaciones)',
            'applied_to'        => 'required_unless:type,Garantía (proyectos),Garantía (asignaciones)',
            'start_date'        => 'required|date',
            'expiration_date'   => 'required|date|after:start_date',
        ],
            [
                'code.required'               => 'Debe especificar el número de poliza!',
                'company.required'            => 'Debe especificar la empresa emisora de la poliza!',
                'other_company.required_if'   => 'Debe especificar la empresa emisora de la poliza!',
                'type.required'               => 'Debe especificar el tipo de poliza!',
                'to_morph_id.required_if'     => 'No seleccionó el item al que corresponde la poliza',
                'applied_to.required_unless'  => 'Debe especificar el objeto de la poliza!',
                'start_date.required'         => 'Debe especificar la fecha de inicio de la poliza!',
                'start_date.date'             => 'Debe introducir una fecha de inicio válida',
                'expiration_date.required'    => 'Debe especificar la fecha de vencimiento de la poliza!',
                'expiration_date.date'        => 'Debe introducir una fecha de vencimiento válida',
                'expiration_date.after'       => 'La fecha de vencimiento no puede ser anterior a la fecha de inicio!',
            ]
        );
        
        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back();
        }

        $guarantee->fill(Request::all());

        $guarantee->company = $guarantee->company=='Otro' ? Request::input('other_company') : $guarantee->company;

        $to_morph_id = Request::input('to_morph_id');
        
        if($guarantee->type=='Garantía (proyectos)'){
            $project = Project::find($to_morph_id);
            $guarantee->guaranteeable()->associate($project);
            $guarantee->applied_to = 'Aplica a proyecto: '.$project->name;
        }
        elseif($guarantee->type=='Garantía (asignaciones)'){
            $assignment = Assignment::find($to_morph_id);
            $guarantee->guaranteeable()->associate($assignment);
            $guarantee->applied_to = 'Aplica a asignación: '.$assignment->name;
        }

        $guarantee->save();

        Session::flash('message', "Poliza modificada correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('guarantee.index');

        //return redirect()->action('AssignmentController@show', ['id' => $guarantee->assignment_id]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        /* No record can be deleted, only marked as closed */
        Session::flash('message', "Esta función está deshabilitada! Intente archivar el registro en su lugar.");
        return redirect()->back();
    }

    public function close_guarantee($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        //$service = Session::get('service');

        $guarantee = Guarantee::find($id);

        $guarantee->closed = 1;
        $guarantee->save();

        foreach($guarantee->files as $file){
            $this->blockFile($file);
        }

        Session::flash('message', "La poliza $guarantee->code ha sido marcada como 'No renovable' y archivada");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('guarantee.index');
    }
}
