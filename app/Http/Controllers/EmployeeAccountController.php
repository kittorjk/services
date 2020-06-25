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
use App\Email;
use App\Employee;
use App\Event;
use App\RendicionViatico;
use App\ServiceParameter;
use App\StipendRequest;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeAccountController extends Controller
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
            return View('app.index', ['service'=>'project', 'user'=>null]);
        }
        if ($user->acc_project == 0)
            return redirect()->action('LoginController@logout', ['service' => 'project']);

        $service = Session::get('service');

        if ($user->priv_level <= 2 && $user->area != 'Gerencia Administrativa') {
            Session::flash('message', 'Usted no tiene permisos para ver la página solicitada');
            return redirect()->back();
        }

        $employees = Employee::where('active', 1)->orderBy('last_name')->paginate(20); //Only active employees

        // TODO count requests without reports
        // $waiting_expense_report = StipendRequest::where('status', 'Sent')->count();
        
        /*
        foreach ($stipend_requests as $request) {
            $request->date_from = Carbon::parse($request->date_from);
            $request->date_to = Carbon::parse($request->date_to);
        }
        */

        return View::make('app.employee_account_brief', ['employees' => $employees, 'service' => $service, 'user' => $user]);
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
        if ((is_null($user))||(!$user->id)) {
            return View('app.index', ['service'=>'project', 'user'=>null]);
        }
        if ($user->acc_project == 0)
            return redirect()->action('LoginController@logout', ['service' => 'project']);

        $service = Session::get('service');

        if ($user->priv_level <= 2 && $user->area != 'Gerencia Administrativa') {
            Session::flash('message', 'Usted no tiene permisos para ver la página solicitada');
            return redirect()->back();
        }

        $employee_record = Employee::find($id);

        if ($employee_record) {
            $stipend_requests = $employee_record->stipend_requests()->whereNotIn('status', ['Observed', 'Rejected']);
        } else {
            Session::flash('message', 'No se encontraron registros para el empleado seleccionado');
            return redirect()->back();
        }

        $all_stipend_requests = $stipend_requests->orderBy('created_at', 'desc')->get(); // For calculation purposes
        $stipend_requests = $stipend_requests->orderBy('created_at', 'desc')->paginate(20);

        $total_solicitudes = 0;
        $total_rendiciones = 0;
        $saldo_global_abros = 0;
        $saldo_global_empleado = 0;

        foreach ($all_stipend_requests as $request) {
            if ($request->status == 'Completed' || $request->status == 'Documented') {
                $total_solicitudes += $request->total_amount + $request->additional;
            }
            
            if ($request->rendicion_viatico) {
                $total_rendiciones += $request->rendicion_viatico->total_rendicion;
            }
        }

        foreach ($stipend_requests as $request) {
            $request->date_from = Carbon::parse($request->date_from);
            $request->date_to = Carbon::parse($request->date_to);
        }

        $saldo_global_abros = $total_solicitudes - $total_rendiciones;
        $saldo_global_empleado = $total_rendiciones - $total_solicitudes;

        return View::make('app.employee_account_info', ['stipend_requests' => $stipend_requests, 'service' => $service, 'user' => $user, 
            'employee_record' => $employee_record, 'total_solicitudes' => $total_solicitudes, 'total_rendiciones' => $total_rendiciones, 
            'saldo_global_abros' => $saldo_global_abros, 'saldo_global_empleado' => $saldo_global_empleado]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
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
