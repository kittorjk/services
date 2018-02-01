<?php

namespace App\Http\Controllers;

use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use View;
use Mail;
use Input;
use App\User;
use App\Contract;
use App\Assignment;
use App\Order;
use Carbon\Carbon;
use App\Http\Traits\FilesTrait;

class ContractController extends Controller
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

        $contracts = Contract::where('id','>',0);

        if(!is_null($arch))
            $contracts = $contracts->where('closed', $arch);

        //$contracts = Contract::where('expiration_date','>',Carbon::now())->orderBy('created_at','desc')->paginate(20);
        $contracts = $contracts->orderBy('created_at','desc')->paginate(20);

        foreach($contracts as $contract){
            $contract->expiration_date = Carbon::parse($contract->expiration_date);
        }
        
        return View::make('app.contract_brief', ['contracts' => $contracts, 'service' => $service,
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

        $clients = Contract::select('client')->where('client', '<>', '')->groupBy('client')->get();

        return View::make('app.contract_form', ['contract' => 0, 'clients' => $clients,
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

        $contract = new Contract(Request::all());

        $v = \Validator::make(Request::all(), [
            'client_code'       => 'required',
            'client'            => 'required',
            'other_client'      => 'required_if:client,Otro',
            'objective'         => 'required',
            'start_date'        => 'required|date',
            'expiration_date'   => 'required|date|after:start_date',
        ],
            [
                'client_code.required'        => 'Debe especificar el código de contrato del cliente!',
                'client.required'             => 'Debe especificar el cliente!',
                'other_client.required_if'    => 'Debe especificar el cliente!',
                'objective.required'          => 'Debe especificar el objeto del contrato!',
                'start_date.required'         => 'Debe especificar la fecha de inicio del contrato!',
                'start_date.date'             => 'Debe introducir una fecha de inicio válida',
                'expiration_date.required'    => 'Debe especificar la fecha de vencimiento del contrato!',
                'expiration_date.date'        => 'Debe introducir una fecha de vencimiento válida',
                'expiration_date.after'       => 'La fecha de vencimiento no puede ser anterior a la fecha de inicio!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back();
        }

        $contract->client = $contract->client=="Otro" ? Request::input('other_client') : $contract->client;

        $contract->user_id = $user->id;
        $contract->save();

        $this->fill_code_column();

        Session::flash('message', "El contrato fue agregado al sistema correctamente");
        return redirect()->route('contract.index');
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

        $contract = Contract::find($id);
        $contract->expiration_date = Carbon::parse($contract->expiration_date);
        $contract->start_date = Carbon::parse($contract->start_date);

        return View::make('app.contract_info', ['contract' => $contract, 'service' => $service, 'user' => $user]);
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

        $contract = Contract::find($id);

        $clients = Contract::select('client')->where('client', '<>', '')->groupBy('client')->get();

        $contract->start_date = Carbon::parse($contract->start_date)->format('Y-m-d');
        $contract->expiration_date = Carbon::parse($contract->expiration_date)->format('Y-m-d');

        return View::make('app.contract_form', ['contract' => $contract, 'clients' => $clients,
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

        $contract = Contract::find($id);

        $v = \Validator::make(Request::all(), [
            'client_code'       => 'required',
            'client'            => 'required',
            'other_client'      => 'required_if:client,Otro',
            'objective'         => 'required',
            'start_date'        => 'required|date',
            'expiration_date'   => 'required|date|after:start_date',
        ],
            [
                'client_code.required'        => 'Debe especificar el código de contrato del cliente!',
                'client.required'             => 'Debe especificar el cliente!',
                'other_client.required_if'    => 'Debe especificar el cliente!',
                'objective.required'          => 'Debe especificar el objeto del contrato!',
                'start_date.required'         => 'Debe especificar la fecha de inicio del contrato!',
                'start_date.date'             => 'Debe introducir una fecha de inicio válida',
                'expiration_date.required'    => 'Debe especificar la fecha de vencimiento del contrato!',
                'expiration_date.date'        => 'Debe introducir una fecha de vencimiento válida',
                'expiration_date.after'       => 'La fecha de vencimiento no puede ser anterior a la fecha de inicio!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back();
        }

        $contract->fill(Request::all());

        $contract->client = $contract->client=="Otro" ? Request::input('other_client') : $contract->client;

        $contract->user_id = $user->id;
        $contract->save();

        Session::flash('message', "El contrato ha sido modificado!");
        return redirect()->route('contract.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Session::flash('message', 'Esta función está deshabilitada! Intente archivar el registro en su lugar');
        return redirect()->back();
    }

    public function close_contract($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        //$service = Session::get('service');

        $contract = Contract::find($id);

        $contract->closed = 1;
        $contract->save();

        foreach($contract->files as $file){
            $this->blockFile($file);
        }

        Session::flash('message', "El contrato ".$contract->code." ha sido marcado como 'No renovable' y archivado");
        return redirect()->route('contract.index');
    }
    
    public function fill_code_column()
    {
        $contracts = Contract::where('code','')->get();
        
        foreach($contracts as $contract){
            $contract->code = 'CTO-'.date_format($contract->created_at,'y').
                str_pad($contract->id, 3, "0", STR_PAD_LEFT);
            
            $contract->save();
        }
    }
}
