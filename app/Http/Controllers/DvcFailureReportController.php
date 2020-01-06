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
use App\DvcFailureReport;
use App\Operator;
use App\User;
use App\Device;
use App\Email;
use App\DeviceHistory;
use App\DeviceRequirement;
use Carbon\Carbon;
use App\Http\Traits\FilesTrait;

class DvcFailureReportController extends Controller
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

        $dvc = Input::get('dvc');

        $device = Device::find($dvc);

        if(!$device){
            Session::flash('message', 'No se pudo cargar la página solicitada!, error al recuperar la información solicitada');
            return redirect()->back();
        }

        $reports = DvcFailureReport::where('device_id', $dvc);

        if(!(($user->priv_level>=2&&$user->area=='Gerencia Tecnica')||$user->priv_level>=3||$user->work_type=='Almacén'))
            $reports = $reports->where('user_id', $user->id);

        $reports = $reports->orderBy('created_at', 'desc')->paginate(20);

        return View::make('app.device_failure_report_brief', ['reports' => $reports, 'service' => $service,
            'user' => $user, 'device' => $device]);
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

        $report = DvcFailureReport::find($id);

        $report->date_stat = Carbon::parse($report->date_stat);

        return View::make('app.device_failure_report_info', ['report' => $report, 'service' => $service,
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

        $report = DvcFailureReport::find($id);

        if(!$report){
            Session::flash('message', "Ocurrió un error al recuperar los datos del servidor, intente de nuevo por favor.");
            return redirect()->back();
        }

        return View::make('app.device_failure_report_form', ['report' => $report, 'service' => $service,
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
            'device_id'             => 'required|exists:devices,id',
            'reason'                => 'required',
        ],
            [
                'device_id.required'        => 'Debe seleccionar un equipo!',
                'device_id.exists'          => 'El equipo seleccionado no fue encontrado en el sistema!',
                'reason.required'           => 'Debe detallar la falla encontrada en el equipo!',
            ]
        );

        if ($v->fails()) {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $report = DvcFailureReport::find($id);

        $report->fill(Request::all());

        $report->save();

        Session::flash('message', "Registro de reporte de falla modificado correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect('/device_failure_report?dvc='.$report->device_id);
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

        $report = DvcFailureReport::find($rep);

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

            $device = $report->device;

            if($device->failure_reports()->where('status','<>',2)->count()==0){
                if($device->flags[1]==1){
                    $device->flags = str_pad($device->flags-100, 4, "0", STR_PAD_LEFT);
                    //Remove orange alert flag

                    if($device->flags[2]==1)
                        $device->status = 'Activo';
                    if($device->flags[3]==1)
                        $device->status = 'Disponible';

                    $device->save();
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
            return redirect('/device_failure_report?dvc='.$report->device_id);
    }
}
