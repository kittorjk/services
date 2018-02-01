<?php

namespace App\Http\Controllers;

use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use View;
use Mail;
use Exception;
use App\WarehouseOutlet;
use App\User;
use App\Warehouse;
use App\Material;
use App\Event;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class WarehouseOutletController extends Controller
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
        if($user->acc_warehouse==0)
            return redirect()->action('LoginController@logout', ['service' => 'warehouse']);

        $service = Session::get('service');

        $outlets = WarehouseOutlet::where('id', '>', 0)->orderBy('date','desc')->paginate(20);

        return View::make('app.warehouse_outlet_brief', ['outlets' => $outlets, 'service' => $service, 'user' => $user]);
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

        $warehouses = Warehouse::where('name','<>','')->orderBy('name')->get();
        $outlet_types = WarehouseOutlet::select('outlet_type')->where('outlet_type','<>','')
            ->groupBy('outlet_type')->get();

        return View::make('app.warehouse_outlet_form', ['outlet' => 0, 'warehouses' => $warehouses,
            'outlet_types' => $outlet_types, 'service' => $service, 'user' => $user]);
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

        $outlet = new WarehouseOutlet(Request::all());

        $v = \Validator::make(Request::all(), [
            'warehouse_id'          => 'required',
            'material_name'         => 'required|exists:materials,name',
            'qty'                   => 'required',
            'delivered_by'          => 'required',
            'received_by'           => 'required',
            'outlet_type'           => 'required',
            'other_outlet_type'     => 'required_if:outlet_type,Otro',
        ],
            [
                'warehouse_id.required'     => 'Debe seleccionar un almacén!',
                'material_name.required'    => 'Debe indicar el material que ingresa!',
                'material_name.exists'      => 'El material especificado no está registrado en el sistema!',
                'qty.required'              => 'Debe especificar la cantidad del material que ingresa',
                'delivered_by.required'     => 'Debe especificar la persona que entrega el material!',
                'received_by.required'      => 'Debe especificar la persona que recibe el material!',
                'outlet_type.required'      => 'Debe especificar el tipo de salida del material!',
                'outlet_type.required_if'   => 'Debe especificar el tipo de salida del material!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back();
        }

        $outlet->user_id = $user->id;
        $outlet->date = Carbon::now();
        $outlet->outlet_type = $outlet->outlet_type=='Otro' ? Request::input('other_outlet_type') : $outlet->outlet_type;

        $material = Material::select('id')->where('name', Request::input('material_name'))->first();
        $deliverer = User::select('id')->where('name', Request::input('delivered_by'))->first();
        $receiver = User::select('id')->where('name', Request::input('received_by'))->first();

        $outlet->material_id = $material->id;
        $outlet->delivered_id = $deliverer ?  $deliverer->id : 0;
        $outlet->received_id = $receiver ? $receiver->id : 0;

        $warehouse = $outlet->warehouse;
        $exists = $warehouse->materials->contains($outlet->material_id); //Determine if a relation material-warehouse exists

        if(!$exists){
            Session::flash('message', "El almacén seleccionado no cuenta con el material indicado!");
            return redirect()->back();
        }

        $prev_total = $outlet->warehouse->materials()->where('material_id', $outlet->material_id)->first();
        $new_total = $prev_total->pivot->qty - $outlet->qty;

        if($new_total<0){
            Session::flash('message', "El almacén seleccionado no cuenta con la cantidad de material indicada!");
            return redirect()->back();
        }

        /* update the quantity of material in the warehouse */
        $outlet->warehouse->materials()->updateExistingPivot($outlet->material_id, ['qty' => $new_total]);

        $outlet->save();

        /* record a new event for the outlet */
        $this->add_event('new outlet',$outlet);

        Session::flash('message', "La salida de material fue registrada correctamente");
        return redirect()->route('wh_outlet.index');
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
        $outlet = WarehouseOutlet::find($id);

        $outlet->date = Carbon::parse($outlet->date);

        return View::make('app.warehouse_outlet_info', ['outlet' => $outlet, 'service' => $service, 'user' => $user]);
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

        $outlet = WarehouseOutlet::find($id);

        $warehouses = Warehouse::where('name','<>','')->orderBy('name')->get();
        $outlet_types = WarehouseOutlet::select('outlet_type')->where('outlet_type','<>','')
            ->groupBy('outlet_type')->get();

        return View::make('app.warehouse_outlet_form', ['outlet' => $outlet, 'warehouses' => $warehouses,
            'outlet_types' => $outlet_types, 'service' => $service, 'user' => $user]);
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

        $outlet = WarehouseOutlet::find($id);
        $prev_qty = $outlet->qty;

        $v = \Validator::make(Request::all(), [
            'qty'                   => 'required',
            'delivered_by'          => 'required',
            'received_by'           => 'required',
            'outlet_type'           => 'required',
            'other_outlet_type'     => 'required_if:outlet_type,Otro',
        ],
            [
                'qty.required'              => 'Debe especificar la cantidad del material que ingresa',
                'delivered_by.required'     => 'Debe especificar la persona que entrega el material!',
                'received_by.required'      => 'Debe especificar la persona que recibe el material!',
                'outlet_type.required'      => 'Debe especificar el tipo de ingreso del material!',
                'outlet_type.required_if'   => 'Debe especificar el tipo de ingreso del material!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back();
        }

        $outlet->fill(Request::all());

        $outlet->outlet_type = $outlet->outlet_type=='Otro' ? Request::input('other_outlet_type') : $outlet->outlet_type;

        $deliverer = User::select('id')->where('name', Request::input('delivered_by'))->first();
        $receiver = User::select('id')->where('name', Request::input('received_by'))->first();

        $outlet->delivered_id = $deliverer ?  $deliverer->id : 0;
        $outlet->received_id = $receiver ? $receiver->id : 0;

        if($prev_qty!=$outlet->qty){
            /* associate material quantity to warehouse */
            $prev_total = $outlet->warehouse->materials()->where('material_id', $outlet->material_id)->first();

            $new_total = $prev_total->pivot->qty - $outlet->qty + $prev_qty;

            if($new_total<0){
                Session::flash('message', "El almacén seleccionado no cuenta con la cantidad de material indicada!");
                return redirect()->back();
            }

            $outlet->warehouse->materials()->updateExistingPivot($outlet->material_id, ['qty' => $new_total]);
        }

        $outlet->save();

        /* record a new event for the modification */
        $this->add_event('corrected outlet',$outlet);

        Session::flash('message', "Datos modificados correctamente");
        return redirect()->route('wh_outlet.index');
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

        $outlet = WarehouseOutlet::find($id);

        if($outlet){
            /* add the outlet's quantity to the total in the relation warehouse-material */
            $prev_total = $outlet->warehouse->materials()->where('material_id', $outlet->material_id)->first();

            $new_total = $prev_total->pivot->qty + $outlet->qty;
            $outlet->warehouse->materials()->updateExistingPivot($outlet->material_id, ['qty' => $new_total]);

            /* record a new event for the deletion of the outlet */
            $this->add_event('deleted outlet',$outlet);

            foreach($outlet->files as $file){
                $success = true;

                try {
                    \Storage::disk('local')->delete($file->name);
                } catch (ModelNotFoundException $ex) {
                    $success = false;
                }

                if($success)
                    $file->delete();
            }

            $outlet->delete();

            Session::flash('message', "El registro ha sido eliminado");
            return redirect()->route('wh_outlet.index');
        }
        else {
            Session::flash('message', "Error al borrar el registro, revise la dirección e intente de nuevo por favor.");
            return redirect()->back();
        }
    }

    public function add_event($type, $outlet)
    {
        $user = Session::get('user');

        $event = new Event;
        $event->user_id = $user->id;
        $event->date = Carbon::now();

        $prev_number = Event::select('number')->where('eventable_id',$outlet->id)
            ->where('eventable_type','App\WarehouseOutlet')->orderBy('number','desc')->first();

        $event->number = $prev_number ? $prev_number->number+1 : 1;

        if($type=='new outlet'){
            $event->description = 'Salida de material';
            $event->detail = ($outlet->delivered_by ? $outlet->delivered_by : 'Se').' entregó '.$outlet->qty.' '.
                $outlet->material->units.' de '.$outlet->material->name.' del almacén '.$outlet->warehouse->name.
                ' a '.$outlet->received_by.($outlet->reason ? ' por concepto de: '.$outlet->reason : '');
        }
        if($type=='corrected outlet'){
            $event->description = 'Corrección de registro de salida';
            $event->detail = 'Se corrige el registro de salida de material de fecha '.
                Carbon::parse($outlet->date)->format('d-m-Y').' con el siguiente detalle: '.
                ($outlet->delivered_by ? $outlet->delivered_by : 'Se').' entregó '.$outlet->qty.
                ' '.$outlet->material->units.' de '.$outlet->material->name.' del almacén '.$outlet->warehouse->name.
                ' a '.$outlet->received_by.($outlet->reason ? ' por concepto de: '.$outlet->reason : '');
        }
        if($type=='deleted outlet'){
            $event->description = 'Registro de salida eliminado';
            $event->detail = 'Se elimina del sistema el registro '.$outlet->id.' de fecha '.
                Carbon::parse($outlet->date)->format('d-m-Y');
        }

        $event->responsible_id = $outlet->delivered_id;
        $event->eventable()->associate(WarehouseOutlet::find($outlet->id));
        $event->save();
    }
}
