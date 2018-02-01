<?php

namespace App\Http\Controllers;

use App\ServiceParameter;
use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use View;
use Mail;
use Input;
use Exception;
use App\RbsViatic;
use App\RbsViaticRequest;
use App\Assignment;
use App\Site;
use App\User;
use App\Email;
use App\Event;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class RbsViaticController extends Controller
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
        
        $viatics = RbsViatic::where('id','>',0);

        if($user->priv_level<=1){
            $viatics = $viatics->where('user_id', $user->id);
        }

        $viatics = $viatics->orderBy('created_at', 'desc')->paginate(20);

        $waiting_approval = RbsViatic::where('status', 0)->orwhere('status', '=', 2)->count();
        $observed = RbsViatic::where('status', 1)->count();

        return View::make('app.rbs_viatic_brief', ['viatics' => $viatics, 'service' => $service, 'user' => $user,
            'waiting_approval' => $waiting_approval, 'observed' => $observed]);
    }

    public function pending_approval_list()
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        if($user->priv_level>=2){
            $viatics = RbsViatic::where('status', 0)->orwhere('status', '=', 2)->orderBy('created_at', 'desc')->get();

            return View::make('app.rbs_viatic_pending_approval', ['viatics' => $viatics, 'service' => $service,
                'user' => $user]);
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

        $viatics = RbsViatic::where('status', 1);
        
        if($user->priv_level<2){
            $viatics = $viatics->where('user_id', $user->id);
        }
        
        $viatics = $viatics->orderBy('created_at', 'desc')->get();
        
        return View::make('app.rbs_viatic_observed', ['viatics' => $viatics, 'service' => $service, 'user' => $user]);
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

        $last_stat = count(Assignment::$status_names) - 1;
        //$last_stat = Assignment::first()->last_stat();
        
        $assignments = Assignment::select('id','name')->where('type','Radiobases')
            ->whereNotIn('status', [$last_stat/*'Concluído'*/, 0/*'No asignado'*/])->get();

        return View::make('app.rbs_viatic_form', ['viatic' => 0, 'user' => $user, 'service' => $service,
            'assignments' => $assignments]);
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
            'type'                  => 'required',
            'work_description'      => 'required',
            'date_from'             => 'required|date',
            'date_to'               => 'required|date|after:date_from',
            'type_transport'        => 'required',
            'vehicle_rent_days'     => 'required_if:type_transport,Vehículo alquilado|numeric',
            'vehicle_rent_cost_day' => 'required_if:type_transport,Vehículo alquilado|numeric',
            'num_technicians'       => 'required',
            'num_sites'             => 'required',
            'materials_cost'        => 'numeric',
            //'tech.*.name'           => 'required|exists:users,name',
        ],
            [
                //'tech.*.name.required'      => 'Uno o más campos de nombres de técnicos están vacíos!',
                'required'                  => 'Este campo es obligatorio!',
                'date'                      => 'La fecha introducida es inválida!',
                'after'                     => 'La fecha de fin debe ser posterior a la fecha de inicio!',
                'numeric'                   => 'Este campo sólo puede contener números!',
                'required_if'               => 'Este campo debe estar completo!',
            ]
        );

        if ($v->fails())
        {
            //Session::flash('message', $v->messages()->first());
            Session::flash('message', 'Sucedió un error al enviar el formulario!');
            return redirect()->back()->withErrors($v)->withInput();
        }

        $technicians = Request::input('tech');
        $sites = Request::input('site');
        $fail = false;
        $message = '';

        foreach($technicians as $key => $technician){
            if($technician['name']==''||!(User::where('name', $technician['name'])->exists())){
                $message = 'Uno o más nombres de personal técnico no fueron encontrados en el sistema!
                    Asegúrese de que todos los campos estén llenos y correspondan a personal registrado en el sistema.';
                $fail = true;
            }
        }

        $check_tech = array_column($technicians, 'name');

        if($this->duplicated($check_tech)){
            $message = 'Uno o más nombres de técnicos son repetidos!';
            $fail = true;
        }

        foreach($sites as $key => $site){
            if($site['name']==''||!(Site::where('code', 'like', '%'.substr($site['name'], -11))->exists())){
                $message = 'Uno o más nombres de sitios no fueron encontrados en el sistema!
                    Asegúrese de que todos los campos estén llenos y correspondan a sitios registrados en el sistema.';
                $fail = true;
            }
        }

        $check_site = array_column($sites, 'name');

        if($this->duplicated($check_site)){
            $message = 'Uno o más nombres de sitios son repetidos!';
            $fail = true;
        }

        if(count($technicians)!=Request::input('num_technicians')){
            $message = 'El número de técnicos indicado no concuerda con la cantidad de nombres encontrada!';
            $fail = true;
        }

        if(count($sites)!=Request::input('num_sites')){
            $message = 'El número de sitios indicado no concuerda con la cantidad de nombres encontrada!';
            $fail = true;
        }

        if($fail){
            Session::flash('message', $message);
            return redirect()->back()->withInput();
        }

        // End of form validation

        $rbs_viatic = new RbsViatic(Request::all());

        $rbs_viatic->user_id = $user->id;

        $days = Carbon::parse($rbs_viatic->date_from)->diffInDays(Carbon::parse($rbs_viatic->date_to)) + 1; // Both extremes count

        $departure_count = 0;
        $return_count = 0;

        $parameters = $this->get_parameters();
        /*
        $pm_load = ServiceParameter::where('name', 'pm_work_load')->first();
        $pm_load = $pm_load ? $pm_load->numeric_content : 0;
        $sb_load = ServiceParameter::where('name', 'social_benefits_load')->first();
        $sb_load = $sb_load ? $sb_load->numeric_content : 0;
        $ws_load = ServiceParameter::where('name', 'work_supplies_load')->first();
        $ws_load = $ws_load ? $ws_load->numeric_content : 0;
        $mtu_load = ServiceParameter::where('name', 'minor_tools_use_load')->first();
        $mtu_load = $mtu_load ? $mtu_load->numeric_content : 0;
        */
        foreach($technicians as $technician){
            if(array_key_exists('departure', $technician)&&$technician['departure']!='')
                $departure_count++;
            if(array_key_exists('return', $technician)&&$technician['return']!='')
                $return_count++;

            $rbs_viatic->extra_expenses += $technician['extras'];

            $record = User::where('name', $technician['name'])->first();

            $rbs_viatic->sub_total_workforce += ($record->cost_day*$days);
            $rbs_viatic->sub_total_viatic += ($technician['viatic']*$days);
            if($rbs_viatic->type_transport=='Aéreo'||$rbs_viatic->type_transport=='Terrestre')
                $rbs_viatic->sub_total_transport += $technician['departure'] + $technician['return'];
        }

        $rbs_viatic->departure_qty = $departure_count;
        $rbs_viatic->return_qty = $return_count;

        $rbs_viatic->status = 0; // new record

        $rbs_viatic->pm_cost = ($rbs_viatic->sub_total_workforce*$parameters['pm_load'])/100;
        $rbs_viatic->social_benefits = (($rbs_viatic->sub_total_workforce+$rbs_viatic->pm_cost)*$parameters['sb_load'])/100;
        $rbs_viatic->work_supplies = (($rbs_viatic->sub_total_workforce+$rbs_viatic->pm_cost)*$parameters['ws_load'])/100;

        $rbs_viatic->total_workforce = $rbs_viatic->sub_total_workforce + $rbs_viatic->sub_total_viatic +
            $rbs_viatic->pm_cost + $rbs_viatic->social_benefits + $rbs_viatic->work_supplies;

        if($rbs_viatic->type_transport=='Vehículo alquilado')
            $rbs_viatic->sub_total_transport += ($rbs_viatic->vehicle_rent_days*$rbs_viatic->vehicle_rent_cost_day);
        else{
            $rbs_viatic->vehicle_rent_days = 0;
            $rbs_viatic->vehicle_rent_cost_day = 0;
        }

        if($rbs_viatic->type_transport=='Vehículo de la empresa'){
            $dev_cost = 0;
            foreach($sites as $site){
                $record = Site::where('code', 'like', '%'.substr($site['name'], -11))->first();
                $dev_cost += $record->vehicle_dev_cost;
            }

            $dev_cost = $dev_cost/(Request::input('num_sites')==0 ? 1 : Request::input('num_sites'));

            $rbs_viatic->sub_total_transport += ($days*$dev_cost);
        }

        $rbs_viatic->minor_tools_cost = ($rbs_viatic->total_workforce*$parameters['mtu_load'])/100;

        $rbs_viatic->total_cost = $rbs_viatic->minor_tools_cost + $rbs_viatic->extra_expenses +
            $rbs_viatic->sub_total_transport + $rbs_viatic->materials_cost + $rbs_viatic->total_workforce;

        // End of cost calculation

        $cost_per_site = $rbs_viatic->total_cost/($rbs_viatic->num_sites>0 ? $rbs_viatic->num_sites : 1);

        foreach($sites as $site){
            $amount_used = 0;
            $record = Site::where('code', 'like', '%'.substr($site['name'], -11))->first();

            foreach($record->rbs_viatics as $viatic){
                if($viatic->pivot->cost_applied==1)
                    $amount_used += $viatic->pivot->cost_applied;
            }

            $amount_available = $record->budget - $amount_used;

            if($cost_per_site>$amount_available){
                $message = 'El costo calculado excede el presupuesto de uno o más sitios! Por favor revise los
                    montos introducidos o elija técnicos con menor experiencia. ';

                if($user->priv_level>=2)
                    $message .= "Monto excedente del primer sitio: ".($cost_per_site-$amount_available)." Bs";

                Session::flash('message', $message);
                return redirect()->back()->withInput();
            }
        }

        // End of site budget verification

        $rbs_viatic->save();

        foreach($technicians as $technician){
            $record = User::where('name', $technician['name'])->first();

            $store = new RbsViaticRequest();
            $store->rbs_viatic_id = $rbs_viatic->id;
            $store->technician_id = $record->id;
            $store->num_days = $days;
            $store->viatic_amount = $technician['viatic'];
            $store->departure_cost = array_key_exists('departure', $technician) ? $technician['departure'] : '';
            $store->return_cost = array_key_exists('return', $technician) ? $technician['return'] : '';
            $store->extra_expenses = $technician['extras'];
            $store->total_deposit = ($store->viatic_amount*$days) + $store->departure_cost + $store->return_cost +
                $store->extra_expenses;
            $store->status = 0;

            $store->save();
        }

        foreach($sites as $site){
            $record = Site::where('code', 'like', '%'.substr($site['name'], -11))->first();

            $record->rbs_viatics()->attach($rbs_viatic->id, ['cost_applied' => $cost_per_site, 'status' => 0]);
        }

        //Email notifications disabled on testing
        /* Send an email notification to Project Manager
        $this->viatic_notification($rbs_viatic->id);

        /* Register an event for the creation
        $this->add_event('created', $rbs_viatic, '');
        */

        Session::flash('message', "La solicitud de viáticos fue registrada en el sistema");
        return redirect()->route('rbs_viatic.index');
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

        $rbs_viatic = RbsViatic::find($id);

        $parameters = $this->get_parameters();

        $cost_per_site = $rbs_viatic->num_sites>=1 ? $rbs_viatic->total_cost/$rbs_viatic->num_sites : 0;

        $budgets = array();

        foreach($rbs_viatic->sites as $site){

            $amount_used = 0;
            $temp = array();

            foreach($site->rbs_viatics as $viatic){
                if($viatic->pivot->status==1)
                    $amount_used += $viatic->pivot->cost_applied;
            }
            
            $temp['name'] = $site->name;
            $temp['amount_available'] = $site->budget - $amount_used;

            $temp['flag'] = $cost_per_site>$temp['amount_available'] ? 'error' : 'success';

            $budgets[] = $temp;
        }

        return View::make('app.rbs_viatic_info', ['rbs_viatic' => $rbs_viatic, 'service' => $service, 'user' => $user,
            'parameters' => $parameters, 'budgets' => $budgets]);
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

        $rbs_viatic = RbsViatic::find($id);

        $rbs_viatic->date_from = Carbon::parse($rbs_viatic->date_from)->format('Y-m-d');
        $rbs_viatic->date_to = Carbon::parse($rbs_viatic->date_to)->format('Y-m-d');

        $technicians = array();
        $counter = 0;

        foreach($rbs_viatic->technician_requests as $technician_request){
            $technicians[$counter]['name'] = $technician_request->technician ? $technician_request->technician->name : '';
            $technicians[$counter]['viatic'] = $technician_request->viatic_amount;
            $technicians[$counter]['extras'] = $technician_request->extra_expenses;
            $technicians[$counter]['departure'] = $technician_request->departure_cost;
            $technicians[$counter]['return'] = $technician_request->return_cost;

            $counter++;
        }

        $sites = array();
        $i = 0;

        foreach($rbs_viatic->sites as $site){
            $sites[$i] = $site->name.' - '.$site->code;

            $i++;
        }

        $last_stat = count(Assignment::$status_names) - 1;
        //$last_stat = Assignment::first()->last_stat();
        
        $assignments = Assignment::select('id','name')->where('type','Radiobases')
            ->whereNotIn('status', [$last_stat/*'Concluído'*/, 0/*'No asignado'*/])->get();

        return View::make('app.rbs_viatic_form', ['viatic' => $rbs_viatic, 'user' => $user, 'service' => $service,
            'assignments' => $assignments, 'technicians' => $technicians, 'sites' => $sites]);
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

        $rbs_viatic = RbsViatic::find($id);
        $old_technicians = $rbs_viatic->technician_requests;

        $v = \Validator::make(Request::all(), [
            'type'                  => 'required',
            'work_description'      => 'required',
            'date_from'             => 'required|date',
            'date_to'               => 'required|date|after:date_from',
            'type_transport'        => 'required',
            'vehicle_rent_days'     => 'required_if:type_transport,Vehículo alquilado|numeric',
            'vehicle_rent_cost_day' => 'required_if:type_transport,Vehículo alquilado|numeric',
            'num_technicians'       => 'required',
            'num_sites'             => 'required',
            'materials_cost'        => 'numeric',
            //'tech.*.name'           => 'required|exists:users,name',
        ],
            [
                //'tech.*.name.required'      => 'Uno o más campos de nombres de técnicos están vacíos!',
                'required'                  => 'Este campo es obligatorio!',
                'date'                      => 'La fecha introducida es inválida!',
                'after'                     => 'La fecha de fin debe ser posterior a la fecha de inicio!',
                'numeric'                   => 'Este campo sólo puede contener números!',
                'required_if'               => 'Este campo debe estar completo!',
            ]
        );

        if ($v->fails())
        {
            //Session::flash('message', $v->messages()->first());
            Session::flash('message', 'Sucedió un error al enviar el formulario!');
            return redirect()->back()->withErrors($v)->withInput();
        }

        $technicians = Request::input('tech');
        $sites = Request::input('site');
        $fail = false;
        $message = '';

        foreach($technicians as $key => $technician){
            if($technician['name']==''||!(User::where('name', $technician['name'])->exists())){
                $message = 'Uno o más nombres de personal técnico no fueron encontrados en el sistema!
                    Asegúrese de que todos los campos estén llenos y correspondan a personal registrado en el sistema.';
                $fail = true;
            }
        }

        $check_tech = array_column($technicians, 'name');

        if($this->duplicated($check_tech)){
            $message = 'Uno o más nombres de técnicos son repetidos!';
            $fail = true;
        }

        foreach($sites as $key => $site){
            if($site['name']==''||!(Site::where('code', 'like', '%'.substr($site['name'], -11))->exists())){
                $message = 'Uno o más nombres de sitios no fueron encontrados en el sistema!
                    Asegúrese de que todos los campos estén llenos y correspondan a sitios registrados en el sistema.';
                $fail = true;
            }
        }

        $check_site = array_column($sites, 'name');

        if($this->duplicated($check_site)){
            $message = 'Uno o más nombres de sitios son repetidos!';
            $fail = true;
        }

        if(count($technicians)!=Request::input('num_technicians')){
            $message = 'El número de técnicos indicado no concuerda con la cantidad de nombres encontrada!';
            $fail = true;
        }

        if(count($sites)!=Request::input('num_sites')){
            $message = 'El número de sitios indicado no concuerda con la cantidad de nombres encontrada!';
            $fail = true;
        }

        if($fail){
            Session::flash('message', $message);
            return redirect()->back()->withInput();
        }

        // End of form validation

        $rbs_viatic->fill(Request::all());

        $days = Carbon::parse($rbs_viatic->date_from)->diffInDays(Carbon::parse($rbs_viatic->date_to)) + 1; //Both extremes count

        $departure_count = 0;
        $return_count = 0;

        $parameters = $this->get_parameters();
        
        $rbs_viatic->extra_expenses = 0;
        $rbs_viatic->sub_total_workforce = 0;
        $rbs_viatic->sub_total_viatic = 0;
        $rbs_viatic->sub_total_transport = 0;

        foreach($technicians as $technician){
            if(array_key_exists('departure', $technician)&&$technician['departure']!='')
                $departure_count++;
            if(array_key_exists('return', $technician)&&$technician['return']!='')
                $return_count++;

            $rbs_viatic->extra_expenses += $technician['extras'];

            $record = User::where('name', $technician['name'])->first();

            $rbs_viatic->sub_total_workforce += ($record->cost_day*$days);
            $rbs_viatic->sub_total_viatic += ($technician['viatic']*$days);
            if($rbs_viatic->type_transport=='Aéreo'||$rbs_viatic->type_transport=='Terrestre')
                $rbs_viatic->sub_total_transport += $technician['departure'] + $technician['return'];
        }

        $rbs_viatic->departure_qty = $departure_count;
        $rbs_viatic->return_qty = $return_count;

        $rbs_viatic->pm_cost = ($rbs_viatic->sub_total_workforce*$parameters['pm_load'])/100;
        $rbs_viatic->social_benefits = (($rbs_viatic->sub_total_workforce+$rbs_viatic->pm_cost)*$parameters['sb_load'])/100;
        $rbs_viatic->work_supplies = (($rbs_viatic->sub_total_workforce+$rbs_viatic->pm_cost)*$parameters['ws_load'])/100;

        $rbs_viatic->total_workforce = $rbs_viatic->sub_total_workforce + $rbs_viatic->sub_total_viatic +
            $rbs_viatic->pm_cost + $rbs_viatic->social_benefits + $rbs_viatic->work_supplies;

        if($rbs_viatic->type_transport=='Vehículo alquilado')
            $rbs_viatic->sub_total_transport += ($rbs_viatic->vehicle_rent_days*$rbs_viatic->vehicle_rent_cost_day);
        else{
            $rbs_viatic->vehicle_rent_days = 0;
            $rbs_viatic->vehicle_rent_cost_day = 0;
        }

        if($rbs_viatic->type_transport=='Vehículo de la empresa'){
            $dev_cost = 0;
            foreach($sites as $site){
                $record = Site::where('code', 'like', '%'.substr($site['name'], -11))->first();
                $dev_cost += $record->vehicle_dev_cost;
            }

            $dev_cost = $dev_cost/(Request::input('num_sites')==0 ? 1 : Request::input('num_sites'));

            $rbs_viatic->sub_total_transport += ($days*$dev_cost);
        }

        $rbs_viatic->minor_tools_cost = ($rbs_viatic->total_workforce*$parameters['mtu_load'])/100;

        $rbs_viatic->total_cost = $rbs_viatic->minor_tools_cost + $rbs_viatic->extra_expenses +
            $rbs_viatic->sub_total_transport + $rbs_viatic->materials_cost + $rbs_viatic->total_workforce;

        // End of cost calculation

        $cost_per_site = $rbs_viatic->total_cost/($rbs_viatic->num_sites>0 ? $rbs_viatic->num_sites : 1);

        foreach($sites as $site){
            $amount_used = 0;
            $record = Site::where('code', 'like', '%'.substr($site['name'], -11))->first();

            foreach($record->rbs_viatics as $viatic){
                if($viatic->pivot->cost_applied==1)
                    $amount_used += $viatic->pivot->cost_applied;
            }

            $amount_available = $record->budget - $amount_used;

            if($cost_per_site>$amount_available){
                $message = 'El costo calculado excede el presupuesto de uno o más sitios! Por favor revise los
                    montos introducidos o elija técnicos con menor experiencia. ';

                if($user->priv_level>=2)
                    $message .= "Monto excedente del primer sitio: ".($cost_per_site-$amount_available)." Bs";

                Session::flash('message', $message);
                return redirect()->back()->withInput();
            }
        }

        // End of site budget verification

        $rbs_viatic->status = 2; // Modified record

        $rbs_viatic->save();

        /* Modify technician requests and rbs_viatic_site pivot records, delete if necessary */

        $to_insert_names = array_column($technicians, 'name');
        $existing_ids = array();

        foreach($old_technicians as $old_technician){
            $name = $old_technician->technician ? $old_technician->technician->name : '';

            if(!in_array($name, $to_insert_names))
                $old_technician->delete();
            else
                $existing_ids[] = $old_technician->technician_id;
        }

        foreach($technicians as $technician){
            $record = User::where('name', $technician['name'])->first();

            if(in_array($record->id, $existing_ids)){
                // Already exists, then edit
                $update = RbsViaticRequest::where('technician_id', $record->id)->where('rbs_viatic_id', $rbs_viatic->id)
                    ->first();

                if($update){
                    $update->num_days = $days;
                    $update->viatic_amount = $technician['viatic'];
                    $update->departure_cost = array_key_exists('departure', $technician) ? $technician['departure'] : '';
                    $update->return_cost = array_key_exists('return', $technician) ? $technician['return'] : '';
                    $update->extra_expenses = $technician['extras'];
                    $update->total_deposit = ($update->viatic_amount*$days) + $update->departure_cost + $update->return_cost +
                        $update->extra_expenses;
                    $update->status = 2; //modified record

                    $update->save();
                }
            }
            else{
                // Add a new record

                $store = new RbsViaticRequest();
                $store->rbs_viatic_id = $rbs_viatic->id;
                $store->technician_id = $record->id;
                $store->num_days = $days;
                $store->viatic_amount = $technician['viatic'];
                $store->departure_cost = array_key_exists('departure', $technician) ? $technician['departure'] : '';
                $store->return_cost = array_key_exists('return', $technician) ? $technician['return'] : '';
                $store->extra_expenses = $technician['extras'];
                $store->total_deposit = ($store->viatic_amount*$days) + $store->departure_cost + $store->return_cost +
                    $store->extra_expenses;
                $store->status = 2; //modified record

                $store->save();
            }
        }

        /*
        foreach($old_sites as $old_site){
            if(!in_array($old_site->name.' - '.$old_site->code, $sites, true))
                $rbs_viatic->sites()->detach($old_site->id);
        }
        */

        $to_sync = array();

        foreach($sites as $site){
            $record = Site::where('code', 'like', '%'.substr($site['name'], -11))->first();

            $to_sync[$record->id] = ['cost_applied' => $cost_per_site, 'status' => 0];
        }

        $rbs_viatic->sites()->sync($to_sync);

        /* Send an email notification to Project Manager */
        $this->viatic_notification($rbs_viatic->id);

        /* Register an event for the modification */
        $this->add_event('modified', $rbs_viatic, '');

        Session::flash('message', "Datos modificados correctamente");
        return redirect()->route('rbs_viatic.index');
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

    public function approve($id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $rbs_viatic = RbsViatic::find($id);

        if($rbs_viatic&&$user->priv_level>=2){
            $rbs_viatic->status = 3;
            $rbs_viatic->save();

            //$this->generate_xls($rbs_viatic->id); // Fill the excel model to attach to the notification email

            //$this->viatic_notification($rbs_viatic->id); // Notify the viatic request to Administration for payment
            $this->add_event('approved', $rbs_viatic, '');

            $message = "La solicitud de viáticos ha sido aprobada";
        }
        else
            $message = "Usted no tiene permiso para acceder a esta función!";

        Session::flash('message', $message);
        return redirect()->route('rbs_viatic.index');
    }

    public function change_status_form($id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $action = Input::get('action');
        $rbs_viatic = RbsViatic::find($id);

        return View::make('app.rbs_viatic_stat_form', ['viatic' => $rbs_viatic, 'user' => $user, 'service' => $service,
            'action' => $action]);
    }

    public function change_status(Request $request, $id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        //$service = Session::get('service');

        $action = Input::get('action');
        $rbs_viatic = RbsViatic::find($id);

        $message = 'No se ralizó ningún cambio';

        if($action=='observe'){
            $rbs_viatic->status = 1;
            $message = 'La solicitud ha sido observada, un email fue enviado a su creador';
        }
        elseif($action=='reject'){
            $rbs_viatic->status = 4;
            $message = 'La solicitud ha sido rechazada, se ha enviado un email de notificación a su creador';
        }
        elseif($action=='complete'){
            $rbs_viatic->status = 5;

            foreach($rbs_viatic->sites as $site){
                $rbs_viatic->sites()->updateExistingPivot($site->id, ['status' => 1]);
            }

            $message = 'La solicitud ha sido completada. No se requiere ninuna acción adicional';
        }
        elseif($action=='cancel'){
            $rbs_viatic->status = 6;
            $message = 'La solicitud ha sido cancelada, se ha notificado de este cambio al Project Manager';
        }

        $rbs_viatic->save();

        /*Disabled for testing purposes
        if($rbs_viatic->status==1||$rbs_viatic->status==4||$rbs_viatic->status==6)
            $this->viatic_notification($rbs_viatic->id);
        */

        $reason = Request::input('comments');

        $this->add_event('status_changed', $rbs_viatic, $reason);

        Session::flash('message', $message);
        return redirect()->route('rbs_viatic.index');
    }

    function duplicated(array $input_array) {
        return count($input_array) !== count(array_flip($input_array));
    }

    function viatic_notification($id)
    {
        //Function disabled for testing purposes
        /*
        $user = Session::get('user');

        $rbs_viatic = RbsViatic::find($id);
        $recipient = '';
        $subject = '';
        
        $project_manager = User::where('area','Gerencia Tecnica')->where('work_type', 'Radiobases')
            ->where('priv_level',2)->first();
        $creator = $rbs_viatic->user;
        $administrator = User::where('area', 'Gerencia Administrativa')->where('priv_level', 3)->first();
        
        if($rbs_viatic->status==0||$rbs_viatic->status==2||$rbs_viatic->status==6)
            $recipient = $project_manager;
        elseif($rbs_viatic->status==1||$rbs_viatic->status==4)
            $recipient = $creator;
        elseif($rbs_viatic->status==3)
            $recipient = $administrator;

        $data = array('recipient' => $recipient, 'rbs_viatic' => $rbs_viatic);
        
        $mail_structure = 'emails.rbs_viatic_status_changed';
        
        if($rbs_viatic->status==0){
            $mail_structure = 'emails.rbs_viatic_new';
            $subject = 'Nueva solicitud de viáticos agregada al sistema';
        }
        elseif($rbs_viatic->status==1){
            $subject = 'Una solicitud de viáticos ha sido observada';
        }
        elseif($rbs_viatic->status==2){
            $subject = 'Solicitud de viáticos modificada en el sistema';
        }
        elseif($rbs_viatic->status==3){
            $mail_structure = 'emails.rbs_viatic_approved';
            $subject = 'Solicitud de viáticos - Radiobases';
        }
        elseif($rbs_viatic->status==4){
            $subject = 'Una solicitud de viáticos ha sido rechazada';
        }
        elseif($rbs_viatic->status==6){
            $subject = 'Una solicitud de viáticos ha sido cancelada';
        }

        if($mail_structure!=''){
            $view = View::make($mail_structure, $data/*['recipient' => $recipient, 'rbs_viatic' => $rbs_viatic]);
            $content = (string) $view;
            $success = 1;

            if($rbs_viatic->status==3){
                try {
                    Mail::send($mail_structure, $data, function($message) use($recipient, $user, $subject) {
                        $message->to($recipient->email, $recipient->name)
                            ->cc($user->email, $user->name)
                            ->subject($subject)
                            ->attach(public_path('files/viatics/rbs_viatic_model.xlsx'))
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
            $email->sent_to = $recipient->email;
            $email->sent_cc = $user->email;
            $email->subject = $subject;
            $email->content = $content;
            $email->success = $success;
            $email->save();
        }
        */
    }

    public function add_event($type, $model, $reason)
    {
        $user = Session::get('user');

        $event = new Event;
        $event->user_id = $user->id;
        $event->date = Carbon::now();

        $prev_number = Event::select('number')->where('eventable_id',$model->id)
            ->where('eventable_type','App\RbsViatic')->orderBy('number','desc')->first();

        $event->number = $prev_number ? $prev_number->number+1 : 1;

        if($type=='status_changed'){
            $statuses = array();
            $statuses[0] = 'Nueva';
            $statuses[1] = 'Observada';
            $statuses[2] = 'Modificada';
            $statuses[3] = 'Aprobada';
            $statuses[4] = 'Rechazada';
            $statuses[5] = 'Completada';
            $statuses[6] = 'Cancelada';

            $event->description = 'Cambio de estado de la solicitud';
            $event->detail = 'La solicitud cambió de estado a '.$statuses[$model->status].
                ($reason ? '<br>Con el siguiente detalle: '.$reason : '');
        }
        elseif($type=='created'){
            $event->description = 'Solicitud agregada al sistema';
            $event->detail = 'Se agrega la solicitud número '.$model->id.' al sistema';
        }
        elseif($type=='modified'){
            $event->description = 'Solicitud modificada';
            $event->detail = 'La solicitud con número '.$model->id.' ha sido modificada';
        }
        elseif($type=='approved'){
            $event->description = 'Solicitud aprobada';
            $event->detail = 'La solicitud número '.$model->id.' ha sido aprobada por el Porject manager a cargo';
        }

        $event->responsible_id = $user->id;
        $event->eventable()->associate($model);
        $event->save();
    }

    function get_parameters()
    {
        $parameters = array();

        $pm_load = ServiceParameter::where('name', 'pm_work_load')->first();
        $parameters['pm_load'] = $pm_load ? $pm_load->numeric_content : 0;

        $sb_load = ServiceParameter::where('name', 'social_benefits_load')->first();
        $parameters['sb_load'] = $sb_load ? $sb_load->numeric_content : 0;

        $ws_load = ServiceParameter::where('name', 'work_supplies_load')->first();
        $parameters['ws_load'] = $ws_load ? $ws_load->numeric_content : 0;

        $mtu_load = ServiceParameter::where('name', 'minor_tools_use_load')->first();
        $parameters['mtu_load'] = $mtu_load ? $mtu_load->numeric_content : 0;

        return $parameters;
    }

    function generate_xls($id)
    {
        $rbs_viatics = RbsViatic::find($id);

        $technicians = $rbs_viatics->technician_requests;
        $sites = $rbs_viatics->sites;

        Excel::load('/public/file_layouts/rbs_viatic_model.xlsx', function($reader)
        use($rbs_viatics, $technicians, $sites)
        {
            foreach($reader->get() as $key => $sheet) {
                $sheetTitle = $sheet->getTitle();
                $sheetTochange = $reader->setActiveSheetIndex($key);

                if($sheetTitle === 'General') {

                    $sheetTochange->setCellValue('C2', $rbs_viatics->id)
                        ->setCellValue('C3', $rbs_viatics->work_description)
                        ->setCellValue('C4', $sites->first()->assignment->client)
                        ->setCellValue('C5', wordwrap($sites->first()->assignment->name, 50, "\n", false))
                        ->setCellValue('C12', Carbon::parse($rbs_viatics->date_from)->format('d/m/Y'))
                        ->setCellValue('C13', Carbon::parse($rbs_viatics->date_to)->format('d/m/Y'))
                        ->setCellValue('F17', $rbs_viatics->type_transport)
                        ->setCellValue('C18', $rbs_viatics->sub_total_transport)
                        ->setCellValue('C20', $rbs_viatics->extra_expenses)
                        ->setCellValue('E18', $rbs_viatics->vehicle_rent_days);

                    $i = 12;
                    foreach($technicians as $technician){
                        if($technician->technician)
                            $sheetTochange->setCellValue('F'.$i, $technician->technician->name);

                        $i++;
                    }
                }

                if($sheetTitle === 'Viaticos') {
                    $i = 5;
                    foreach($technicians as $technician){
                        $sheetTochange->setCellValue('E'.$i, $technician->departure_cost+$technician->return_cost)
                            ->setCellValue('F'.$i, $technician->num_days)
                            ->setCellValue('G'.$i, $technician->viatic_amount)
                            ->setCellValue('I'.$i, $technician->extra_expenses);

                        $i++;
                    }

                    $sheetTochange->setCellvalue('A4', Carbon::now()->format('d/m/Y'));
                }
            }
        })->store('xlsx', public_path('files/viatics'));
    }
}
