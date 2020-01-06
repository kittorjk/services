<?php

namespace App\Http\Controllers;

use App\VehicleHistory;
use App\VehicleRequirement;
use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use View;
use Mail;
use Input;
use Exception;
use App\VhcFailureReport;
use App\Driver;
use App\User;
use App\Vehicle;
use App\Email;
use Carbon\Carbon;
use App\Http\Traits\FilesTrait;

class VhcFailureReportController extends Controller
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

        $vhc = Input::get('vhc');

        $vehicle = Vehicle::find($vhc);
        
        if(!$vehicle){
            Session::flash('message', 'No se pudo cargar la página solicitada!, error al recuperar la información solicitada');
            return redirect()->back();
        }
        
        $reports = VhcFailureReport::where('vehicle_id', $vhc);

        if(!(($user->priv_level>=2&&$user->area=='Gerencia Tecnica')||$user->priv_level>=3||$user->work_type=='Transporte'))
            $reports = $reports->where('user_id', $user->id);
        
        $reports = $reports->orderBy('created_at', 'desc')->paginate(20);

        return View::make('app.vehicle_failure_report_brief', ['reports' => $reports, 'service' => $service,
            'user' => $user, 'vehicle' => $vehicle]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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

        $report = VhcFailureReport::find($id);

        $report->date_stat = Carbon::parse($report->date_stat);

        return View::make('app.vehicle_failure_report_info', ['report' => $report, 'service' => $service,
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
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $report = VhcFailureReport::find($id);

        if(!$report){
            Session::flash('message', "Ocurrió un error al recuperar los datos del servidor, intente de nuevo por favor.");
            return redirect()->back();
        }

        return View::make('app.vehicle_failure_report_form', ['report' => $report, 'service' => $service,
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
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $v = \Validator::make(Request::all(), [
            'vehicle_id'            => 'required|exists:vehicles,id',
            'reason'                => 'required',
        ],
            [
                'vehicle_id.required'       => 'Debe seleccionar un vehículo!',
                'vehicle_id.exists'         => 'El vehículo seleccionado no fue encontrado en el sistema!',
                'reason.required'           => 'Debe detallar la falla encontrada en el vehículo!',
            ]
        );

        if ($v->fails()) {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $report = VhcFailureReport::find($id);

        $report->fill(Request::all());

        $report->save();

        Session::flash('message', "Registro de reporte de falla modificado correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect('/vehicle_failure_report?vhc='.$report->vehicle_id);
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

    public function move_stat()
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $stat = Input::get('stat');
        $rep = Input::get('rep');

        $report = VhcFailureReport::find($rep);

        if(!$report){
            Session::flash('message', 'Error al recuperar la información solicitada del servidor!');
            return redirect()->back();
        }

        if($stat=='in_process'){
            $report->status = 1;
            $report->date_stat = Carbon::now();

            $report->save();
        }
        elseif($stat=='solved'){
            $report->status = 2;
            $report->date_stat = Carbon::now();

            $report->save();

            foreach($report->files as $file){
                $this->blockFile($file);
            }

            $vehicle = $report->vehicle;

            if($vehicle->failure_reports()->where('status','<>',2)->count()==0){
                if($vehicle->flags[1]==1){
                    $vehicle->flags = str_pad($vehicle->flags-100, 4, "0", STR_PAD_LEFT);
                    //Remove orange alert flag

                    if($vehicle->flags[2]==1)
                        $vehicle->status = 'Activo';
                    if($vehicle->flags[3]==1)
                        $vehicle->status = 'Disponible';

                    $vehicle->save();
                }
            }
        }
        else{
            Session::flash('message', 'No se reconoce la acción solicitada!');
            return redirect()->back();
        }

        Session::flash('message', 'El estado del reporte de falla ha cambiado');
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect('/vehicle_failure_report?vhc='.$report->vehicle_id);
    }
}
