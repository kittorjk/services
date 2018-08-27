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
use App\StipendRequest;
use App\Assignment;
use App\Site;
use App\User;
use App\Employee;
use App\Email;
use App\Event;
use App\ServiceParameter;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class StipendRequestController extends Controller
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
        
        $asg = Input::get('asg');

        $stipend_requests = StipendRequest::where('id','>',0);

        if($user->priv_level<=1){
            $stipend_requests = $stipend_requests->where(function ($query) use($user){
                $query->where('employee_id', $user->id)->orwhere('user_id', $user->id);
            });
        }

        $stipend_requests = $stipend_requests->orderBy('created_at', 'desc')->paginate(20);

        $waiting_payment = StipendRequest::where('status', 'Sent')->count();
        $waiting_approval = StipendRequest::where('status', 'Pending')->count();
        $observed = StipendRequest::where('status', 'Observed')->count();
        
        foreach($stipend_requests as $request){
            $request->date_from = Carbon::parse($request->date_from);
            $request->date_to = Carbon::parse($request->date_to);
        }

        return View::make('app.stipend_request_brief', ['stipend_requests' => $stipend_requests, 'service' => $service,
            'user' => $user, 'waiting_payment' => $waiting_payment, 'waiting_approval' => $waiting_approval,
            'observed' => $observed, 'asg' => $asg]);
    }

    public function pending_approval_list()
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        if($user->action->prj_vtc_mod /*$user->priv_level>=3*/){
            $stipend_requests = StipendRequest::where('status', 'Pending')->orderBy('created_at', 'desc')->get();

            return View::make('app.stipend_request_pending_approval', ['stipend_requests' => $stipend_requests,
                'service' => $service, 'user' => $user]);
        }
        else{
            Session::flash('message', 'Usted no tiene permiso para ver la página solicitada');
            return redirect()->back();
        }
    }

    public function observed_list()
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $stipend_requests = StipendRequest::where('status', 'Observed');

        if($user->priv_level<3){
            $stipend_requests = $stipend_requests->where('user_id', $user->id);
        }

        $stipend_requests = $stipend_requests->orderBy('created_at', 'desc')->get();

        return View::make('app.stipend_request_observed', ['stipend_requests' => $stipend_requests,
            'service' => $service, 'user' => $user]);
    }

    public function pending_payment_list()
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        if($user->action->prj_vtc_pmt /*$user->priv_level>=3*/){
            // $stipend_requests = StipendRequest::where('status', 'Approved_tech')->orderBy('created_at', 'desc')->get();
            $stipend_requests = StipendRequest::where('status', 'Sent')->orderBy('created_at', 'desc')->get();

            return View::make('app.stipend_request_pending_payment', ['stipend_requests' => $stipend_requests,
                'service' => $service, 'user' => $user]);
        }
        else{
            Session::flash('message', 'Usted no tiene permiso para ver la página solicitada');
            return redirect()->back();
        }
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

        $asg = Input::get('asg');
        
        $assignment = Assignment::find($asg);

        if(!$assignment){
            Session::flash('message', 'Error al cargar la página solicitada. Recargue la página e intente de nuevo por favor');
            return redirect()->back();
        }

        if(($assignment->start_date!='0000-00-00 00:00:00'&&$assignment->end_date!='0000-00-00 00:00:00')||
            ($assignment->quote_from!='0000-00-00 00:00:00'&&$assignment->quote_to!='0000-00-00 00:00:00')){
            $assignment->start_date = Carbon::parse($assignment->start_date);
            $assignment->end_date = Carbon::parse($assignment->end_date);
            $assignment->quote_from = Carbon::parse($assignment->quote_from);
            $assignment->quote_to = Carbon::parse($assignment->quote_to);
        }
        else{
            Session::flash('message', 'Debe especificar las fechas de ejecución o relevamiento de la asignación
                para poder solicitar viáticos!');
            return redirect()->back();
        }
        
        $last_stat = count(Site::$status_options) - 1;

        $sites = $assignment->sites()->whereNotIn('status', [$last_stat/*'Concluído'*/, 0/*'No asignado'*/])
            ->where('start_date','<>','0000-00-00 00:00:00')->where('end_date','<>','0000-00-00 00:00:00')->get();

        return View::make('app.stipend_request_form', ['stipend' => 0, 'user' => $user, 'service' => $service,
            'assignment' => $assignment, 'sites' => $sites, 'asg' => $asg]);
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

        $form_data = Request::all();

        $form_data['date_to'] = $form_data['date_to'].' 23:59:59';

        $v = \Validator::make($form_data, [
            'employee_name'         => 'required',
            'assignment_id'         => 'required',
            'date_from'             => 'required|date',
            'date_to'               => 'required|date|after:date_from',
            'per_day_amount'        => 'required_if:additional,',
            'additional'            => 'required_if:per_day_amount,',
            'reason'                => 'required',
        ],
            [
                'required'                  => 'Este campo es obligatorio!',
                'required_if'               => 'Debe indicar un monto para viáticos o adicionales para esta solicitud!',
                'date'                      => 'La fecha introducida es inválida!',
                'after'                     => 'La fecha "Hasta" debe ser posterior a la fecha "Desde"!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', 'Sucedió un error al enviar el formulario!');
            return redirect()->back()->withErrors($v)->withInput();
        }

        $site_ids = Request::input('site_ids');

        $employee_name = Request::input('employee_name');

        $employee = Employee::where(function ($query) use($employee_name){
            $query->where(DB::raw("CONCAT(`first_name`, ' ', `last_name`)"), 'like', "%$employee_name%");
        })->first();

        if (!$employee) {
            Session::flash('message', 'El nombre de empleado especificado no fue encontrado en la lista de empleados!');
            return redirect()->back()->withInput();
        }

        $stipend = new StipendRequest(Request::all());
        
        if (($stipend->per_day_amount && $stipend->per_day_amount > 0) || ($stipend->hotel_amount && $stipend->hotel_amount > 0)) {
            if(StipendRequest::where('employee_id', $employee->id)->where('status', '<>', 'Rejected')->where('total_amount', '>', 0)
                ->where(function ($query) use($stipend) {
                    $query->whereBetween('date_to', [$stipend->date_from, $stipend->date_to])
                        ->orwhereBetween('date_from', [$stipend->date_from, $stipend->date_to])
                        ->orWhere(function ($query1) use($stipend) {
                            $query1->where('date_from', '<', $stipend->date_from)->where('date_to', '>', $stipend->date_to);
                        });
                })->exists()) {
                Session::flash('message', 'La persona indicada en el formulario ya tiene una solicitud de viáticos dentro del
                rango de fechas especificadas');
                return redirect()->back()->withInput();
            }
        }

        $assignment = $stipend->assignment;

        if (!$assignment) {
            Session::flash('message', 'Error al cargar la información de la asignación, intente reenviar el formulario por favor');
            return redirect()->back()->withInput();
        }

        $assignment->start_date = Carbon::parse($assignment->start_date);
        $assignment->end_date = Carbon::parse($assignment->end_date);
        $assignment->quote_from = Carbon::parse($assignment->quote_from);
        $assignment->quote_to = Carbon::parse($assignment->quote_to);

        $stipend->date_from = Carbon::parse($stipend->date_from);
        $stipend->date_to = Carbon::parse($stipend->date_to);

        if (!(($stipend->date_from->between($assignment->start_date, $assignment->end_date) &&
            $stipend->date_to->between($assignment->start_date, $assignment->end_date)) ||
            ($stipend->date_from->between($assignment->quote_from, $assignment->quote_to) &&
            $stipend->date_to->between($assignment->quote_from, $assignment->quote_to)))) {
            Session::flash('message', 'Las fechas desde y hasta de la solicitud deben estar dentro del intervalo de
                tiempo de relevamiento o de ejecución de la asignación!');
            return redirect()->back()->withInput();
        }

        $stipend->user_id = $user->id;
        $stipend->employee_id = $employee->id;
        $stipend->in_days = Carbon::parse($stipend->date_to)->diffInDays(Carbon::parse($stipend->date_from)) + 1; //Extremes count

        $hotel_cost = $stipend->hotel_amount ? $stipend->hotel_amount : 0;
        $stipend->total_amount = ($stipend->per_day_amount + $hotel_cost) * $stipend->in_days;

        $stipend->status = 'Pending';
        $stipend->save();

        $this->fill_code_column(); // Fill records' codes where empty

        if (count($site_ids) > 0) {
            $stipend->sites()->attach($site_ids);
        }

        // Send an email notification to Project Manager
        $this->notify_request($stipend, 0);

        /* Register an event for the creation
            $this->add_event('created', $rbs_viatic, '');
        */

        Session::flash('message', "La solicitud de viáticos fue registrada en el sistema");
        return redirect('/stipend_request?asg='.$stipend->assignment_id);
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

        $stipend = StipendRequest::find($id);

        return View::make('app.stipend_request_info', ['stipend' => $stipend, 'service' => $service, 'user' => $user]);
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

        $stipend = StipendRequest::find($id);
        
        $stipend->date_from = Carbon::parse($stipend->date_from);
        $stipend->date_to = Carbon::parse($stipend->date_to);

        $asg = $stipend->assignment_id;

        $assignment = $stipend->assignment;

        if(!$assignment){
            Session::flash('message', 'Error al cargar la página solicitada. Recargue la página e intente de nuevo por favor');
            return redirect()->back();
        }

        if($assignment->start_date!='0000-00-00 00:00:00'&&$assignment->end_date!='0000-00-00 00:00:00'){
            $assignment->start_date = Carbon::parse($assignment->start_date);
            $assignment->end_date = Carbon::parse($assignment->end_date);
        }
        else{
            Session::flash('message', 'Debe especificar las fechas de ejecución de la asignación antes de modificar
                solicitudes de viáticos!');
            return redirect()->back();
        }

        $last_stat = count(Site::$status_options) - 1;

        $sites = $assignment->sites()->whereNotIn('status', [$last_stat/*'Concluído'*/, 0/*'No asignado'*/])
            ->where('start_date','<>','0000-00-00 00:00:00')->where('end_date','<>','0000-00-00 00:00:00')->get();

        $selected_sites = $stipend->sites;
        $site_ids = array();

        foreach($selected_sites as $selected_site){
            $site_ids[] = $selected_site->id;
        }

        $stipend->site_ids = $site_ids;

        return View::make('app.stipend_request_form', ['stipend' => $stipend, 'user' => $user, 'service' => $service,
            'assignment' => $assignment, 'sites' => $sites, 'asg' => $asg]);
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

        $form_data = Request::all();

        $form_data['date_to'] = $form_data['date_to'].' 23:59:59';

        $v = \Validator::make($form_data, [
            'employee_name'         => 'required',
            'assignment_id'         => 'required',
            'date_from'             => 'required|date',
            'date_to'               => 'required|date|after:date_from',
            'per_day_amount'        => 'required_if:additional,0',
            'additional'            => 'required_if:per_day_amount,',
            'reason'                => 'required',
        ],
            [
                'required'                  => 'Este campo es obligatorio!',
                'required_if'               => 'Debe indicar un monto para viáticos o adicionales para esta solicitud!',
                'date'                      => 'La fecha introducida es inválida!',
                'after'                     => 'La fecha "Hasta" debe ser posterior a la fecha "Desde"!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', 'Sucedió un error al enviar el formulario!');
            return redirect()->back()->withErrors($v)->withInput();
        }

        $stipend = StipendRequest::find($id);
        $old_employee = $stipend->employee_id;

        $site_ids = Request::input('site_ids') ?: array();

        $employee_name = Request::input('employee_name');

        $employee = Employee::where(function ($query) use($employee_name){
            $query->where(DB::raw("CONCAT(`first_name`, ' ', `last_name`)"), 'like', "%$employee_name%");
        })->first();

        if (!$employee) {
            Session::flash('message', 'El nombre de empleado especificado no fue encontrado en la lista de empleados!
                Asegúrese de que el nombre ingresado corresponda a personal registrado en el sistema.');
            return redirect()->back()->withInput();
        }

        $stipend->fill(Request::all());

        if ($old_employee != $employee->id) {
            //The name of the employee has changed
            if ($stipend->per_day_amount != 0 && $stipend->per_day_amount != '') {
                if (StipendRequest::where('employee_id', $employee->id)->where('total_amount', '>', 0)->where('date_to', '>=', $stipend->date_from)->exists()) {
                    Session::flash('message', 'La persona indicada en el formulario ya tiene una solicitud de viáticos dentro del
                    rango de fechas especificadas');
                    return redirect()->back()->withInput();
                }
            }
        }

        $assignment = $stipend->assignment;

        if (!$assignment) {
            Session::flash('message', 'Error al cargar la información de la asignación, intente reenviar el formulario por favor');
            return redirect()->back()->withInput();
        }

        $assignment->start_date = Carbon::parse($assignment->start_date);
        $assignment->end_date = Carbon::parse($assignment->end_date);
        $assignment->quote_from = Carbon::parse($assignment->quote_from);
        $assignment->quote_to = Carbon::parse($assignment->quote_to);

        $stipend->date_from = Carbon::parse($stipend->date_from);
        $stipend->date_to = Carbon::parse($stipend->date_to);

        if (!(($stipend->date_from->between($assignment->start_date, $assignment->end_date) &&
                $stipend->date_to->between($assignment->start_date, $assignment->end_date)) ||
            ($stipend->date_from->between($assignment->quote_from, $assignment->quote_to) &&
                $stipend->date_to->between($assignment->quote_from, $assignment->quote_to)))) {
            Session::flash('message', 'Las fechas desde y hasta de la solicitud deben estar dentro del intervalo de
                tiempo de relevamiento o de ejecución de la asignación!');
            return redirect()->back()->withInput();
        }

        $stipend->employee_id = $employee->id;
        $stipend->in_days = Carbon::parse($stipend->date_to)->diffInDays(Carbon::parse($stipend->date_from)) + 1; //Extremes count

        $hotel_cost = $stipend->hotel_amount ? $stipend->hotel_amount : 0;
        $stipend->total_amount = ($stipend->per_day_amount + $hotel_cost) * $stipend->in_days;

        $stipend->status = 'Pending';
        $stipend->save();

        $stipend->sites()->sync($site_ids);

        // Send an email notification to Project Manager
        $this->notify_request($stipend, 0);

        /* Register an event for the modification
        $this->add_event('modified', $rbs_viatic, '');
        */

        Session::flash('message', "Datos modificados correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect('/stipend_request?asg='.$stipend->assignment_id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Session::flash('message', "Esta función está deshabilitada!");
        return redirect()->back();
    }

    public function change_status_form()
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $mode = Input::get('mode');
        $id = Input::get('id');
        
        $service = Session::get('service');
        
        $stipend = StipendRequest::find($id);
        
        if(!$stipend){
            Session::flash('message', 'No se encontró el registro solicitado en el servidor!');
            return redirect()->back();
        }

        return View::make('app.stipend_request_stat_form', ['stipend' => $stipend, 'mode' => $mode, 'id' => $id, 
            'user' => $user, 'service' => $service]);
    }

    public function change_status(Request $request)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $mode = Input::get('mode');
        $id = Input::get('id');

        if($mode=='observe'||$mode=='reject'){
            $v = \Validator::make(Request::all(), [
                'observations'          => 'required',
            ],
                [
                    'required'                  => 'Este campo es obligatorio!',
                ]
            );

            if ($v->fails())
            {
                Session::flash('message', 'Sucedió un error al enviar el formulario!');
                return redirect()->back()->withErrors($v)->withInput();
            }
        }

        $stipend = StipendRequest::find($id);

        $stipend->fill(Request::all());

        $message = 'No se ralizó ningún cambio';

        if($mode=='observe'){
            $stipend->status = 'Observed';
            $message = 'La solicitud ha sido observada, se ha enviado un email de notificación a su creador';
        }
        elseif($mode=='reject'){
            $stipend->status = 'Rejected';
            $message = 'La solicitud ha sido rechazada, se ha enviado un email de notificación a su creador';
        }
        /* Moved to a separate function
        elseif($mode=='complete'){
            $stipend->status = 'Completed';
            $message = 'La solicitud ha sido registrada como completada. No se requiere ninuna acción adicional';
        }
        */
        elseif($mode=='approve'){
            $stipend->status = 'Approved_tech';
            $message = 'La solicitud ha sido aprobada';
        }

        $stipend->save();

        if($stipend->status=='Observed'||$stipend->status=='Rejected')
            $this->notify_request($stipend,0);

        /*
        $reason = Request::input('comments');

        $this->add_event('status_changed', $rbs_viatic, $reason);
        */

        Session::flash('message', $message);
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect('/stipend_request?asg='.$stipend->assignment_id);
    }

    public function close_request()
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $mode = Input::get('mode');
        $id = Input::get('id');

        $stipend = StipendRequest::find($id);

        if(!$stipend){
            Session::flash('message', 'No se encontró el registro solicitado en el servidor!');
            return redirect()->back();
        }

        $message = 'No se ralizó ningún cambio';

        if($mode=='complete'){
            if($stipend->xls_gen!=''){
                $grouped_records = StipendRequest::where('xls_gen',$stipend->xls_gen)->get();
                $count = 0;

                foreach($grouped_records as $record){
                    $record->status = 'Completed';
                    $record->save();
                    $count++;
                }

                if($count==1)
                    $message = '1 solicitud ha sido registrada como completada. No se requiere ninuna acción adicional';
                else
                    $message = "$count solicitudes han sido registradas como completadas. No se requiere ninuna acción adicional";
            }
            else{
                $stipend->status = 'Completed';
                $stipend->save();

                $message = 'La solicitud ha sido registrada como completada. No se requiere ninuna acción adicional';
            }
        }

        Session::flash('message', $message);
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect('/stipend_request?asg='.$stipend->assignment_id);
    }

    public function request_adm()
    {
        $id = Input::get('id');
        //$type = input::get('type');

        $stipend = StipendRequest::find($id);

        $requests = $this->generate_request_file();

        $this->notify_request($stipend, $requests);

        Session::flash('message', 'Se ha generado y enviado la solicitud de viáticos al encargado administrativo');
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect('/stipend_request?asg='.$stipend->assignment_id);
    }

    public function generate_request_file()
    {
        $content = collect();

        //$stipend = StipendRequest::find($id);

        $requests = StipendRequest::where('status', 'Approved_tech')->get();

        foreach($requests as $request)
        {
            if($request->total_amount>0){
                $content->push(
                    [   'Cuenta'            => $request->employee->bnk_account,
                        'Monto'             => $request->total_amount+1-1, //Add and substract to convert to a number
                        'Dias'              => $request->in_days==1 ? '1 dia de viatico' : $request->in_days.' dias de viatico ',
                        'Tipo DocId'        => 'Q -CEDULA DE IDENTIDAD',
                        'DocId'             => $request->employee->id_card,
                        'Extensión'         => $request->employee->id_extension,
                        'Cliente'           => $this->normalize_name($request->employee->last_name).' '.
                            $this->normalize_name($request->employee->first_name),
                        'Motivo'            => $this->normalize_name($request->reason),
                        'Proyecto'          => $request->assignment ? ($request->assignment->project ?
                            $this->normalize_name($request->assignment->project->name) : 'N E') : 'N E',
                    ]);
            }

            if($request->additional>0){
                $description = '';

                if($request->transport_amount>0)
                    $description .= $description=='' ? 'TR' : ' TR';
                if($request->gas_amount>0)
                    $description .= $description=='' ? 'COMB' : ' COMB';
                if($request->taxi_amount>0)
                    $description .= $description=='' ? 'TX' : ' TX';
                if($request->comm_amount>0)
                    $description .= $description=='' ? 'COM' : ' COM';
                if($request->hotel_amount>0)
                    $description .= $description=='' ? 'AL' : ' AL';
                if($request->materials_amount>0)
                    $description .= $description=='' ? 'MAT' : ' MAT';
                if($request->extras_amount>0)
                    $description .= $description=='' ? 'EX' : ' EX';

                $content->push(
                    [   'Cuenta'            => $request->employee->bnk_account,
                        'Monto'             => $request->additional+1-1, //Add and substract to convert to a number
                        'Dias'              => $description,
                        'Tipo DocId'        => 'Q -CEDULA DE IDENTIDAD',
                        'DocId'             => $request->employee->id_card,
                        'Extensión'         => $request->employee->id_extension,
                        'Cliente'           => $this->normalize_name($request->employee->last_name).' '.
                            $this->normalize_name($request->employee->first_name),
                        'Motivo'            => $this->normalize_name($request->reason),
                        'Proyecto'          => $request->assignment ? ($request->assignment->project ?
                            $this->normalize_name($request->assignment->project->name) : 'N E') : 'N E',
                    ]);
            }
        }

        Excel::load('/public/file_layouts/stipend_request_model.xlsx', function($reader) use($content)
        {
            //foreach($reader->get() as $key => $sheet) {
                //$sheetTitle = $sheet->getTitle();
            $reader->sheet('Planilla', function($sheet) use($content) {

                //$sheetToChange = $reader->setActiveSheetIndex($key);

                //if($sheetTitle === 'Planilla') {

                    $sheet->fromArray($content, null, 'A2', true, false);

                    $i = count($content)+1;

                    $sheet->setBorder('A1:I'.$i, 'thin');

                //}
            });
        })->store('xlsx', public_path('files/stipend_requests'));

        $timestamp = Carbon::now()->format('ymdhis');

        foreach($requests as $request){
            $request->status = 'Sent';
            $request->xls_gen = 'XLS_'.$timestamp;
            $request->save();
        }

        return $requests;
    }

    function notify_request($stipend, $requests)
    {
        $user = Session::get('user');
        
        $recipient = '';
        $subject = '';
        $mail_structure = '';

        $creator = $stipend->user;
        $administrator = User::where('area', 'Gerencia Administrativa')->where('priv_level', 3)->first();
        $technical_manager = User::where('priv_level',3)->where('area', 'Gerencia Tecnica')->first();
        
        if($stipend->status=='Pending'){
            $recipient = $technical_manager;
            $subject = 'Tiene una solicitud de viáticos pendiente de aprobación';
            $mail_structure = 'emails.stipend_request_new';
        }
        elseif($stipend->status=='Observed'){
            $recipient = $creator;
            $subject = "La solicitud de viáticos $stipend->id ha sido observada";
            $mail_structure = 'emails.stipend_request_observed';
        }
        elseif($stipend->status=='Rejected'){
            $recipient = $creator;
            $subject = "La solicitud de viáticos $stipend->id ha sido rechazada";
            $mail_structure = 'emails.stipend_request_rejected';
        }
        elseif($stipend->status=='Approved_tech'){
            $recipient = $administrator;
            
            //$copies = User::where('area', 'Gerencia Administrativa')->where('work_type', 'Administrativo')->get();
            $copies = User::where(function ($query){
                $query->where('area', 'Gerencia Administrativa')->where('work_type', 'Administrativo')->where('status', 'Activo');
            })->orwhere(function ($query1){
                $query1->where('area', 'Gerencia Tecnica')->where('priv_level', 3);
            })->get();
            
            $cc = [];
            
            foreach($copies as $copy){
                $cc[] = $copy->email;
            }
            
            $subject = 'Solicitudes de viáticos pendientes de pago';
            $mail_structure = 'emails.stipend_request_payment';
        }
        
        $data = array('recipient' => $recipient, 'stipend' => $stipend, 'requests' => $requests);
        
        if($mail_structure!=''){
            $view = View::make($mail_structure, $data);
            $content = (string) $view;
            $success = 1;

            if($stipend->status=='Approved_tech'){
                try {
                    Mail::send($mail_structure, $data, function($message) use($recipient, $cc, $user, $subject) {
                        $message->to($recipient->email, $recipient->name)
                            ->cc($cc)
                            ->subject($subject)
                            ->attach(public_path('files/stipend_requests/stipend_request_model.xlsx'))
                            ->from('postmaster@gerteabros.com', 'postmaster@gerteabros.com');
                    });
                } catch (Exception $ex) {
                    $success = 0;
                }
            }
            else{
                try {
                    Mail::send($mail_structure, $data, function($message) use($recipient, $user, $subject) {
                        $message->to($recipient->email, $recipient->name)
                            ->cc($user->email, $user->name)
                            ->subject($subject)
                            ->from('postmaster@gerteabros.com', 'postmaster@gerteabros.com');
                    });
                } catch (Exception $ex) {
                    $success = 0;
                }
            }

            // Store a record for the email in the DB
            $email = new Email;
            $email->sent_by = 'postmaster@gerteabros.com';
            $email->sent_to = $recipient ? $recipient->email : '';
            $email->sent_cc = $stipend->status=='Approved_tech'&&$cc ? implode(",", $cc) : ($user ? $user->email : '');
            $email->subject = $subject;
            $email->content = $content;
            $email->success = $success;
            $email->save();
        }
    }

    function fill_code_column()
    {
        $requests = StipendRequest::where('code','')->get();

        foreach($requests as $request){
            $request->code = 'STP-'.str_pad($request->id, 4, "0", STR_PAD_LEFT).'-'.
                Carbon::now()->format('y');

            $request->save();
        }
    }

    function normalize_name($name)
    {
        $name_no_bars = str_replace(array('/', '\\', '*', '?', ':', '[', ']'), '-', $name);
        
        $replace = array(
            'ъ'=>'-', 'Ь'=>'-', 'Ъ'=>'-', 'ь'=>'-',
            'Ă'=>'A', 'Ą'=>'A', 'À'=>'A', 'Ã'=>'A', 'Á'=>'A', 'Æ'=>'A', 'Â'=>'A', 'Å'=>'A', 'Ä'=>'Ae',
            'Þ'=>'B',
            'Ć'=>'C', 'ץ'=>'C', 'Ç'=>'C',
            'È'=>'E', 'Ę'=>'E', 'É'=>'E', 'Ë'=>'E', 'Ê'=>'E',
            'Ğ'=>'G',
            'İ'=>'I', 'Ï'=>'I', 'Î'=>'I', 'Í'=>'I', 'Ì'=>'I',
            'Ł'=>'L',
            'Ñ'=>'N', 'Ń'=>'N',
            'Ø'=>'O', 'Ó'=>'O', 'Ò'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'Oe',
            'Ş'=>'S', 'Ś'=>'S', 'Ș'=>'S', 'Š'=>'S',
            'Ț'=>'T',
            'Ù'=>'U', 'Û'=>'U', 'Ú'=>'U', 'Ü'=>'Ue',
            'Ý'=>'Y',
            'Ź'=>'Z', 'Ž'=>'Z', 'Ż'=>'Z',
            'â'=>'a', 'ǎ'=>'a', 'ą'=>'a', 'á'=>'a', 'ă'=>'a', 'ã'=>'a', 'Ǎ'=>'a', 'а'=>'a', 'А'=>'a', 'å'=>'a', 'à'=>'a', 'א'=>'a', 'Ǻ'=>'a', 'Ā'=>'a', 'ǻ'=>'a', 'ā'=>'a', 'ä'=>'ae', 'æ'=>'ae', 'Ǽ'=>'ae', 'ǽ'=>'ae',
            'б'=>'b', 'ב'=>'b', 'Б'=>'b', 'þ'=>'b',
            'ĉ'=>'c', 'Ĉ'=>'c', 'Ċ'=>'c', 'ć'=>'c', 'ç'=>'c', 'ц'=>'c', 'צ'=>'c', 'ċ'=>'c', 'Ц'=>'c', 'Č'=>'c', 'č'=>'c', 'Ч'=>'ch', 'ч'=>'ch',
            'ד'=>'d', 'ď'=>'d', 'Đ'=>'d', 'Ď'=>'d', 'đ'=>'d', 'д'=>'d', 'Д'=>'D', 'ð'=>'d',
            'є'=>'e', 'ע'=>'e', 'е'=>'e', 'Е'=>'e', 'Ə'=>'e', 'ę'=>'e', 'ĕ'=>'e', 'ē'=>'e', 'Ē'=>'e', 'Ė'=>'e', 'ė'=>'e', 'ě'=>'e', 'Ě'=>'e', 'Є'=>'e', 'Ĕ'=>'e', 'ê'=>'e', 'ə'=>'e', 'è'=>'e', 'ë'=>'e', 'é'=>'e',
            'ф'=>'f', 'ƒ'=>'f', 'Ф'=>'f',
            'ġ'=>'g', 'Ģ'=>'g', 'Ġ'=>'g', 'Ĝ'=>'g', 'Г'=>'g', 'г'=>'g', 'ĝ'=>'g', 'ğ'=>'g', 'ג'=>'g', 'Ґ'=>'g', 'ґ'=>'g', 'ģ'=>'g',
            'ח'=>'h', 'ħ'=>'h', 'Х'=>'h', 'Ħ'=>'h', 'Ĥ'=>'h', 'ĥ'=>'h', 'х'=>'h', 'ה'=>'h',
            'î'=>'i', 'ï'=>'i', 'í'=>'i', 'ì'=>'i', 'į'=>'i', 'ĭ'=>'i', 'ı'=>'i', 'Ĭ'=>'i', 'И'=>'i', 'ĩ'=>'i', 'ǐ'=>'i', 'Ĩ'=>'i', 'Ǐ'=>'i', 'и'=>'i', 'Į'=>'i', 'י'=>'i', 'Ї'=>'i', 'Ī'=>'i', 'І'=>'i', 'ї'=>'i', 'і'=>'i', 'ī'=>'i', 'ĳ'=>'ij', 'Ĳ'=>'ij',
            'й'=>'j', 'Й'=>'j', 'Ĵ'=>'j', 'ĵ'=>'j', 'я'=>'ja', 'Я'=>'ja', 'Э'=>'je', 'э'=>'je', 'ё'=>'jo', 'Ё'=>'jo', 'ю'=>'ju', 'Ю'=>'ju',
            'ĸ'=>'k', 'כ'=>'k', 'Ķ'=>'k', 'К'=>'k', 'к'=>'k', 'ķ'=>'k', 'ך'=>'k',
            'Ŀ'=>'l', 'ŀ'=>'l', 'Л'=>'l', 'ł'=>'l', 'ļ'=>'l', 'ĺ'=>'l', 'Ĺ'=>'l', 'Ļ'=>'l', 'л'=>'l', 'Ľ'=>'l', 'ľ'=>'l', 'ל'=>'l',
            'מ'=>'m', 'М'=>'m', 'ם'=>'m', 'м'=>'m',
            'ñ'=>'n', 'н'=>'n', 'Ņ'=>'n', 'ן'=>'n', 'ŋ'=>'n', 'נ'=>'n', 'Н'=>'n', 'ń'=>'n', 'Ŋ'=>'n', 'ņ'=>'n', 'ŉ'=>'n', 'Ň'=>'n', 'ň'=>'n',
            'о'=>'o', 'О'=>'o', 'ő'=>'o', 'õ'=>'o', 'ô'=>'o', 'Ő'=>'o', 'ŏ'=>'o', 'Ŏ'=>'o', 'Ō'=>'o', 'ō'=>'o', 'ø'=>'o', 'ǿ'=>'o', 'ǒ'=>'o', 'ò'=>'o', 'Ǿ'=>'o', 'Ǒ'=>'o', 'ơ'=>'o', 'ó'=>'o', 'Ơ'=>'o', 'œ'=>'oe', 'Œ'=>'oe', 'ö'=>'oe',
            'פ'=>'p', 'ף'=>'p', 'п'=>'p', 'П'=>'p',
            'ק'=>'q',
            'ŕ'=>'r', 'ř'=>'r', 'Ř'=>'r', 'ŗ'=>'r', 'Ŗ'=>'r', 'ר'=>'r', 'Ŕ'=>'r', 'Р'=>'r', 'р'=>'r',
            'ș'=>'s', 'с'=>'s', 'Ŝ'=>'s', 'š'=>'s', 'ś'=>'s', 'ס'=>'s', 'ş'=>'s', 'С'=>'s', 'ŝ'=>'s', 'Щ'=>'sch', 'щ'=>'sch', 'ш'=>'sh', 'Ш'=>'sh', 'ß'=>'ss',
            'т'=>'t', 'ט'=>'t', 'ŧ'=>'t', 'ת'=>'t', 'ť'=>'t', 'ţ'=>'t', 'Ţ'=>'t', 'Т'=>'t', 'ț'=>'t', 'Ŧ'=>'t', 'Ť'=>'t', '™'=>'tm',
            'ū'=>'u', 'у'=>'u', 'Ũ'=>'u', 'ũ'=>'u', 'Ư'=>'u', 'ư'=>'u', 'Ū'=>'u', 'Ǔ'=>'u', 'ų'=>'u', 'Ų'=>'u', 'ŭ'=>'u', 'Ŭ'=>'u', 'Ů'=>'u', 'ů'=>'u', 'ű'=>'u', 'Ű'=>'u', 'Ǖ'=>'u', 'ǔ'=>'u', 'Ǜ'=>'u', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'У'=>'u', 'ǚ'=>'u', 'ǜ'=>'u', 'Ǚ'=>'u', 'Ǘ'=>'u', 'ǖ'=>'u', 'ǘ'=>'u', 'ü'=>'ue',
            'в'=>'v', 'ו'=>'v', 'В'=>'v',
            'ש'=>'w', 'ŵ'=>'w', 'Ŵ'=>'w',
            'ы'=>'y', 'ŷ'=>'y', 'ý'=>'y', 'ÿ'=>'y', 'Ÿ'=>'y', 'Ŷ'=>'y',
            'Ы'=>'y', 'ž'=>'z', 'З'=>'z', 'з'=>'z', 'ź'=>'z', 'ז'=>'z', 'ż'=>'z', 'ſ'=>'z', 'Ж'=>'zh', 'ж'=>'zh'
        );
        
        return strtr($name_no_bars, $replace);
    }
}
