<?php

namespace App\Http\Controllers;

use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use View;
use Mail;
use Exception;
use App\User;
use App\Warehouse;
use App\WarehouseEntry;
use App\WarehouseOutlet;
use App\Email;
use App\Event;
use App\Material;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;

class WarehouseController extends Controller
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
            return View('app.index', ['service'=>'warehouse', 'user'=>null]);
        }
        if($user->acc_warehouse==0)
            return redirect()->action('LoginController@logout', ['service' => 'warehouse']);

        $service = Session::get('service');
        
        $warehouses = Warehouse::orderBy('name')->paginate(20);
        
        return View::make('app.warehouse_brief', ['warehouses' => $warehouses, 'service' => $service,
            'user' => $user]);
    }

    public function materials_per_warehouse($id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');
        
        $service = Session::get('service');

        $wh_info = Warehouse::find($id);
        $wh_materials = Warehouse::find($id)->materials()->paginate(20);

        return View::make('app.warehouse_materials_brief', ['wh_materials' => $wh_materials, 'wh_info' => $wh_info,
            'service' => $service, 'user' => $user]);
    }

    public function warehouse_events($id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $events = Event::where('eventable_type','like','%Warehouse%')->paginate(20);

        return View::make('app.warehouse_events_brief', ['events' => $events, 'service' => $service, 'user' => $user]);
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

        return View::make('app.warehouse_form', ['warehouse' => 0, 'service' => $service, 'user' => $user]);
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

        //$service = Session::get('service');

        $warehouse = new Warehouse(Request::all());

        $v = \Validator::make(Request::all(), [
            'name'          => 'required',
            'location'      => 'required',
        ],
            [
                'name.required'                   => 'Debe indicar un nombre para el almacén!',
                'location.required'               => 'Debe especificar la dirección del almacén!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back();
        }

        $warehouse->save();

        Session::flash('message', "Se agregó un nuevo almacén al sistema");
        return redirect()->route('warehouse.index');
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
        $warehouse = Warehouse::find($id);
        
        return View::make('app.warehouse_info', ['warehouse' => $warehouse, 'service' => $service, 'user' => $user]);
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
        $warehouse = Warehouse::find($id);

        return View::make('app.warehouse_form', ['warehouse' => $warehouse, 'service' => $service, 'user' => $user]);
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

        $v = \Validator::make(Request::all(), [
            'name'          => 'required',
            'location'      => 'required',
        ],
            [
                'name.required'                   => 'Debe indicar un nombre para el almacén!',
                'location.required'               => 'Debe especificar la dirección del almacén!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back();
        }

        $warehouse = Warehouse::find($id);
        $warehouse->fill(Request::all());

        $warehouse->save();

        Session::flash('message', "Datos modificados exitosamente!");
        return redirect()->route('warehouse.index');
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

        $warehouse = Warehouse::find($id);

        if($warehouse){
            $deletable = true;

            foreach($warehouse->materials as $material){
                if($material->pivot->qty>0)
                    $deletable = false;
            }

            if($deletable){
                foreach($warehouse->files as $file){
                    $success = true;

                    try {
                        \Storage::disk('local')->delete($file->name);
                    } catch (ModelNotFoundException $ex) {
                        $success = false;
                    }

                    if($success)
                        $file->delete();
                }

                $warehouse->delete();

                Session::flash('message', "Se eliminó el registro con éxito");
                return redirect()->route('warehouse.index');
            }

            Session::flash('message', "No se pudo eliminar el almacén porque aún tiene materiales registrados en el sistema!");
            return redirect()->back();
        }
        else {
            Session::flash('message', "Error al borrar el registro, intente de nuevo por favor.");
            return redirect()->back();
        }
    }

    public function transfer_form()
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $warehouses = Warehouse::where('name','<>','')->orderBy('name')->get();
        
        return View::make('app.warehouse_transfer_form', ['warehouses' => $warehouses,
            'service' => $service, 'user' => $user]);
    }

    public function transfer_materials(Request $request)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $transfer = Request::all();

        $v = \Validator::make(Request::all(), [
            'warehouse_out_id'      => 'required',
            'warehouse_in_id'       => 'required|different:warehouse_out_id',
            'material_name'         => 'required|exists:materials,name',
            'qty'                   => 'required',
            'delivered_by'          => 'required',
            'received_by'           => 'required',
        ],
            [
                'warehouse_out_id.required' => 'Debe seleccionar el almacén de salida del material!',
                'warehouse_in_id.required'  => 'Debe seleccionar el almacén de ingreso del amterial!',
                'warehouse_in_id.different' => 'El almacén de ingreso no puede ser el mismo que el de salida de material!',
                'material_name.required'    => 'Debe indicar el material que ingresa!',
                'material_name.exists'      => 'El material especificado no está registrado en el sistema!',
                'qty.required'              => 'Debe especificar la cantidad del material que se transfiere',
                'delivered_by.required'     => 'Debe especificar la persona que entrega el material!',
                'received_by.required'      => 'Debe especificar la persona que recibe el material!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back();
        }

        /* Record outlet of warehouse */
        $outlet = new WarehouseOutlet;

        $outlet->user_id = $user->id;
        $outlet->warehouse_id = Request::input('warehouse_out_id');
        $outlet->date = Carbon::now();
        $outlet->reason = Request::input('reason');
        $outlet->outlet_type = 'Traspaso';

        $material = Material::select('id')->where('name', Request::input('material_name'))->first();
        $deliverer = User::select('id')->where('name', Request::input('delivered_by'))->first();
        $receiver = User::select('id')->where('name', Request::input('received_by'))->first();

        $outlet->material_id = $material->id;
        $outlet->delivered_by = Request::input('delivered_by');
        $outlet->delivered_id = $deliverer ? $deliverer->id : 0;
        $outlet->received_by = Request::input('received_by');
        $outlet->received_id = $receiver ? $receiver->id : 0;

        $outlet->qty = Request::input('qty');

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

        /* Record entry to warehouse */
        $entry = new WarehouseEntry;

        $entry->user_id = $user->id;
        $entry->warehouse_id = Request::input('warehouse_in_id');
        $entry->date = Carbon::now();
        $entry->reason = Request::input('reason');
        $entry->entry_type = 'Traspaso';

        $material = Material::select('id')->where('name', Request::input('material_name'))->first();
        $deliverer = User::select('id')->where('name', Request::input('delivered_by'))->first();
        $receiver = User::select('id')->where('name', Request::input('received_by'))->first();

        $entry->material_id = $material->id;
        $entry->delivered_by = Request::input('delivered_by');
        $entry->delivered_id = $deliverer ? $deliverer->id : 0;
        $entry->received_by = Request::input('received_by');
        $entry->received_id = $receiver ? $receiver->id : 0;

        $entry->qty = Request::input('qty');

        $entry->save();

        $warehouse = $entry->warehouse;
        $exists = $warehouse->materials->contains($entry->material_id); //Determine if a relation material-warehouse exists

        /* associate material quantity to warehouse */
        $prev_total = $entry->warehouse->materials()->where('material_id', $entry->material_id)->first();

        if($exists){
            $new_total = $prev_total->pivot->qty + $entry->qty;
            $entry->warehouse->materials()->updateExistingPivot($entry->material_id, ['qty' => $new_total]);
        }
        else
            $entry->warehouse->materials()->attach($entry->material_id, ['qty' => $entry->qty]);

        /* record a new event for the entrance */
        $this->add_event('new entrance',$entry);

        Session::flash('message', "El traspaso de material fue registrado correctamente");
        return redirect()->route('warehouse.index');
    }

    public function add_event($type, $transfer)
    {
        $user = Session::get('user');

        $event = new Event;
        $event->user_id = $user->id;
        $event->date = Carbon::now();

        $prev_number = Event::select('number')->where('eventable_id',$transfer->id);

        if($type=='new_entrance'){
            $prev_number = $prev_number->where('eventable_type','App\WarehouseEntry')->orderBy('number','desc')->first();
            $event->number = $prev_number ? $prev_number->number+1 : 1;

            $event->description = 'Traspaso de material';
            $event->detail = ($transfer->received_by ? $transfer->received_by : 'Se').' recibe '.$transfer->qty.' '.
                $transfer->material->units.' de '.$transfer->material->name.' en el almacén '.$transfer->warehouse->name.
                ($transfer->reason ? ' por concepto de: '.$transfer->reason : '');

            $event->responsible_id = $transfer->received_id;
            $event->eventable()->associate(WarehouseEntry::find($transfer->id));
        }
        if($type=='new outlet'){
            $prev_number = $prev_number->where('eventable_type','App\WarehouseOutlet')->orderBy('number','desc')->first();
            $event->number = $prev_number ? $prev_number->number+1 : 1;

            $event->description = 'Traspaso de material';
            $event->detail = ($transfer->delivered_by ? $transfer->delivered_by : 'Se').' entregó '.$transfer->qty.' '.
                $transfer->material->units.' de '.$transfer->material->name.' del almacén '.$transfer->warehouse->name.
                ' a '.$transfer->received_by.($transfer->reason ? ' por concepto de: '.$transfer->reason : '');

            $event->responsible_id = $transfer->delivered_id;
            $event->eventable()->associate(WarehouseOutlet::find($transfer->id));
        }

        $event->save();
    }
}
