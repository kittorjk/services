<?php

namespace App\Http\Controllers;

use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use View;
use Input;
use App\Vehicle;
use App\VehicleCondition;
use App\Driver;
use App\Maintenance;
use App\ServiceParameter;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;

class VehicleConditionController extends Controller
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

        return redirect()->back();
    }

    public function vehicle_records($id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $vehicle_info = Vehicle::find($id);

        if(!$vehicle_info){
            Session::flash('message', 'No se encontró la página solicitada, revise la dirección 
                e intente de nuevo por favor');
            return redirect()->back();
        }

        $condition_records = VehicleCondition::where('vehicle_id',$id)->orderBy('created_at', 'desc')->paginate(20);

        foreach($condition_records as $condition_record)
        {
            $condition_record->last_maintenance = Carbon::parse($condition_record->last_maintenance);
        }

        $current_date = Carbon::now()->hour(0)->minute(0)->second(0);

        return View::make('app.vehicle_condition_brief', ['vehicle_info' => $vehicle_info, 'service' => $service,
            'condition_records' => $condition_records, 'current_date' => $current_date, 'user' => $user]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $vehicle = Vehicle::find($id);

        if(!$vehicle){
            Session::flash('message', "Ocurrió un error al recuperar el registro del vehículo, intente de nuevo por favor.");
            return redirect()->back();
        }

        $mode = Input::get('mode');
        
        $current_date = Carbon::now()->format('Y-m-d');

        return View::make('app.vehicle_condition_form', ['vehicle_condition' => 0, 'user' => $user, 'service' => $service,
            'vehicle' => $vehicle, 'current_date' => $current_date, 'mode' => $mode]);
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
            'vehicle_id'            => 'required',
            //'mileage_start'         => 'required|numeric',
            'mileage_end'           => 'required|numeric',
            'gas_level'             => 'required|numeric',
            'gas_filled'            => 'required_if:gas_added,1|numeric',
            'gas_bill'              => 'required_if:gas_added,1'
        ],
            [
                'vehicle_id.required'       => 'Debe especificar el vehiculo al cual pertenece este registro!',
                //'mileage_start.required'    => 'Debe especificar el kilometraje de inicio!',
                //'mileage_start.numeric'     => 'El kilometraje de inicio solo puede contener números!',
                'mileage_end.required'      => 'Debe especificar el kilometraje actual del vehículo!',
                'mileage_end.numeric'       => 'El valor introducido de kilometraje actual contiene caracteres no válidos!',
                'gas_level.required'        => 'Debe especificar el nivel de combustible actual!',
                'gas_level.numeric'         => 'El valor de nivel de combustible contiene caracteres no válidos!',
                'gas_filled.required_if'    => 'Debe especificar la cantidad de combustible cargado!',
                'Gas_filled.numeric'        => 'El valor de combustible cargado contiene caracteres no válidos!',
                'gas_bill.required_if'      => 'Debe registrar el número de la factura que hace referencia a la carga de combustible!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $mode = Request::input('mode');

        $vehicle_condition = new VehicleCondition(Request::all());

        $vehicle = Vehicle::find($vehicle_condition->vehicle_id);

        /*
        if($vehicle_condition->mileage_start<$vehicle->mileage){
            Session::flash('message', "El kilometraje de inicio no puede ser menor que el kilometraje actual de este vehículo!");
            return redirect()->back()->withInput();
        }
        */

        $vehicle_condition->mileage_start = $vehicle->mileage;

        if($vehicle_condition->mileage_end<=$vehicle_condition->mileage_start){
            Session::flash('message', "El kilometraje actual debe ser mayor que el último kilometraje registrado!");
            return redirect()->back()->withInput();
        }

        if($vehicle_condition->gas_level>$vehicle->gas_capacity){
            Session::flash('message', "El nivel de combustible no puede ser mayor que la capacidad del tanque del vehículo!");
            return redirect()->back()->withInput();
        }

        if($vehicle_condition->gas_filled>$vehicle->gas_capacity){
            Session::flash('message', "La cantidad de combustible cargado no puede ser mayor que la capacidad
                del tanque del vehículo!");
            return redirect()->back()->withInput();
        }

        $vehicle_condition->user_id = $user->id;

        //$last_maintenance = Maintenance::where('vehicle_id',$vehicle_condition->vehicle_id)
        //    ->orderBy('created_at','desc')->first();

        if($vehicle->last_maintenance){
            $vehicle_condition->maintenance_id = $vehicle->last_maintenance->id;
            $vehicle_condition->last_maintenance = $vehicle->last_maintenance->created_at;
        }

        if($mode=='refill'/*Request::input('gas_added')==1*/){
            //if(Request::input('gas_full')==1)
            //    $vehicle_condition->gas_filled = $vehicle->gas_capacity;
            if($vehicle->gas_type=='diesel')
                $service_parameter = ServiceParameter::where('name','diesel_price')->first();
            elseif($vehicle->gas_type=='gnv')
                $service_parameter = ServiceParameter::where('name','gnv_price')->first();
            else
                $service_parameter = ServiceParameter::where('name','gas_price')->first(); // Gas cost (bs/lts) defined in parameters
            
            $vehicle_condition->gas_cost = $vehicle_condition->gas_filled*$service_parameter->numeric_content;
        }

        $vehicle_condition->save();

        /* Update mileage on vehicle record */
        $vehicle->mileage = $vehicle_condition->mileage_end;
        $vehicle->save();

        Session::flash('message', "El registro fue agregado correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->action('VehicleConditionController@vehicle_records', ['id' => $vehicle_condition->vehicle_id]);
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
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $condition_record = VehicleCondition::find($id);

        return View::make('app.vehicle_condition_info', ['condition_record' => $condition_record, 
            'service' => $service, 'user' => $user]);
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

        $vehicle_condition = VehicleCondition::find($id);

        $vehicle = $vehicle_condition->vehicle; //Vehicle::find($vehicle_condition->vehicle_id);

        if(!$vehicle){
            Session::flash('message', "Ocurrió un error al recuperar el registro del vehículo, intente de nuevo por favor.");
            return redirect()->back();
        }

        $mode = $vehicle_condition->gas_cost!=0 ? 'refill' : 'travel';

        $current_date = Carbon::now()->format('Y-m-d');

        //$vehicle_condition->last_maintenance = Carbon::parse($vehicle_condition->last_maintenance)->format('Y-m-d');

        return View::make('app.vehicle_condition_form', ['vehicle_condition' => $vehicle_condition, 'user' => $user,
            'service' => $service, 'vehicle' => $vehicle, 'current_date' => $current_date, 'mode' => $mode]);
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
            'vehicle_id'            => 'required',
            //'mileage_start'         => 'required|numeric',
            'mileage_end'           => 'required|numeric',
            'gas_level'             => 'required|numeric',
            'gas_filled'            => 'required_if:gas_added,1|numeric',
            'gas_bill'              => 'required_if:gas_added,1'
        ],
            [
                'vehicle_id.required'       => 'Debe especificar el vehiculo al cual pertenece este registro!',
                //'mileage_start.required'    => 'Debe especificar el kilometraje de inicio!',
                //'mileage_start.numeric'     => 'El kilometraje de inicio solo puede contener números!',
                'mileage_end.required'      => 'Debe especificar el kilometraje actual del vehículo!',
                'mileage_end.numeric'       => 'El valor introducido de kilometraje actual contiene caracteres no válidos!',
                'gas_level.required'        => 'Debe especificar el nivel de combustible actual!',
                'gas_level.numeric'         => 'El valor de nivel de combustible contiene caracteres no válidos!',
                'gas_filled.required_if'    => 'Debe especificar la cantidad de combustible cargado!',
                'Gas_filled.numeric'        => 'El valor de combustible cargado contiene caracteres no válidos!',
                'gas_bill.required_if'      => 'Debe registrar el número de la factura que hace referencia a la carga de combustible!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $mode = Request::input('mode');

        $vehicle_condition = VehicleCondition::find($id);
        //$prev_data = VehicleCondition::find($id);

        $vehicle_condition->fill(Request::all());

        $vehicle = Vehicle::find($vehicle_condition->vehicle_id);

        /*
        if($vehicle_condition->mileage_start<$prev_data->mileage_start){
            Session::flash('message', "El kilometraje de inicio no puede ser menor que el ya registrado!");
            return redirect()->back()->withInput();
        }
        */

        if($vehicle_condition->mileage_end<=$vehicle_condition->mileage_start){
            Session::flash('message', "El kilometraje actual debe ser mayor que el último kilometraje registrado!");
            return redirect()->back()->withInput();
        }

        if($vehicle_condition->gas_level>$vehicle->gas_capacity){
            Session::flash('message', "El nivel de combustible no puede ser mayor que la capacidad del tanque del vehículo!");
            return redirect()->back()->withInput();
        }

        if($vehicle_condition->gas_filled>$vehicle->gas_capacity){
            Session::flash('message', "La cantidad de combustible cargado no puede ser mayor que la capacidad
                del tanque del vehículo!");
            return redirect()->back()->withInput();
        }

        //$last_maintenance = Maintenance::where('vehicle_id',$vehicle_condition->vehicle_id)
        //    ->orderBy('created_at','desc')->first();

        /*
        if($vehicle->last_maintenance){
            $vehicle_condition->maintenance_id = $vehicle->last_maintenance->id;
            $vehicle_condition->last_maintenance = $vehicle->last_maintenance->created_at;
        }
        */

        if($mode=='refill'/*Request::input('gas_added')==1*/){
            //if(Request::input('gas_full')==1)
            //    $vehicle_condition->gas_filled = $vehicle->gas_capacity;

            $service_parameter = ServiceParameter::where('name','gas_price')->first(); // Gas cost (bs/lts) defined in parameters
            $vehicle_condition->gas_cost = $vehicle_condition->gas_filled*$service_parameter->numeric_content;
        }
        else{
            $vehicle_condition->gas_filled = 0;
            $vehicle_condition->gas_cost = 0;
            $vehicle_condition->gas_bill = '';
        }

        $vehicle_condition->save();

        /* Update mileage on vehicle record if it is lower than the new value recorded */
        if($vehicle->mileage<$vehicle_condition->mileage_end){
            $vehicle->mileage = $vehicle_condition->mileage_end;
            $vehicle->save();
        }

        Session::flash('message', "El registro fue modificado correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->action('VehicleConditionController@vehicle_records', ['id' => $vehicle_condition->vehicle_id]);
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

        $record = VehicleCondition::find($id);
        $vehicle_id = $record->vehicle_id;

        if($record) {
            $record->delete();

            Session::flash('message', "El registro ha sido eliminado");
            if(Session::has('url'))
                return redirect(Session::get('url'));
            else
                return redirect()->action('VehicleConditionController@vehicle_records', ['id' => $vehicle_id]);
        }
        else {
            Session::flash('message', "Error al borrar el registro, intente de nuevo por favor.");
            return redirect()->back();
        }
    }
}
