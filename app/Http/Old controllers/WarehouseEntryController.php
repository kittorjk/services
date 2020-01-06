<?php

namespace App\Http\Controllers;

use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use View;
use Mail;
use Exception;
use App\WarehouseEntry;
use App\User;
use App\Warehouse;
use App\Material;
use App\Event;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class WarehouseEntryController extends Controller
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
        
        $entries = WarehouseEntry::where('id', '>', 0)->orderBy('date','desc')->paginate(20);
        
        return View::make('app.warehouse_entry_brief', ['entries' => $entries, 'service' => $service, 'user' => $user]);
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
        $entry_types = WarehouseEntry::select('entry_type')->where('entry_type','<>','')->groupBy('entry_type')->get();

        return View::make('app.warehouse_entry_form', ['entry' => 0, 'warehouses' => $warehouses,
            'entry_types' => $entry_types, 'service' => $service, 'user' => $user]);
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

        $entry = new WarehouseEntry(Request::all());

        $v = \Validator::make(Request::all(), [
            'warehouse_id'          => 'required',
            'material_name'         => 'required|exists:materials,name',
            'qty'                   => 'required',
            'delivered_by'          => 'required',
            'received_by'           => 'required',
            'entry_type'            => 'required',
            'other_entry_type'      => 'required_if:entry_type,Otro',
        ],
            [
                'warehouse_id.required'     => 'Debe seleccionar un almacén!',
                'material_name.required'    => 'Debe indicar el material que ingresa!',
                'material_name.exists'      => 'El material especificado no está registrado en el sistema!',
                'qty.required'              => 'Debe especificar la cantidad del material que ingresa',
                'delivered_by.required'     => 'Debe especificar la persona que entrega el material!',
                'received_by.required'      => 'Debe especificar la persona que recibe el material!',
                'entry_type.required'       => 'Debe especificar el tipo de ingreso del material!',
                'entry_type.required_if'    => 'Debe especificar el tipo de ingreso del material!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back();
        }

        $entry->user_id = $user->id;
        $entry->date = Carbon::now();
        $entry->entry_type = $entry->entry_type=='Otro' ? Request::input('other_entry_type') : $entry->entry_type;

        $material = Material::select('id')->where('name', Request::input('material_name'))->first();
        $deliverer = User::select('id')->where('name', Request::input('delivered_by'))->first();
        $receiver = User::select('id')->where('name', Request::input('received_by'))->first();

        $entry->material_id = $material->id;
        $entry->delivered_id = $deliverer ?  $deliverer->id : 0;
        $entry->received_id = $receiver ? $receiver->id : 0;

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

        Session::flash('message', "El ingreso de material fue registrado correctamente");
        return redirect()->route('wh_entry.index');
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
        $entry = WarehouseEntry::find($id);
        
        $entry->date = Carbon::parse($entry->date);

        return View::make('app.warehouse_entry_info', ['entry' => $entry, 'service' => $service, 'user' => $user]);
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

        $entry = WarehouseEntry::find($id);

        $warehouses = Warehouse::where('name','<>','')->orderBy('name')->get();
        $entry_types = WarehouseEntry::select('entry_type')->where('entry_type','<>','')->groupBy('entry_type')->get();

        //$entry->date = Carbon::parse($entry->date)->format('Y-m-d');

        return View::make('app.warehouse_entry_form', ['entry' => $entry, 'warehouses' => $warehouses,
            'entry_types' => $entry_types, 'service' => $service, 'user' => $user]);
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

        $entry = WarehouseEntry::find($id);
        $prev_qty = $entry->qty;

        $v = \Validator::make(Request::all(), [
            'qty'                   => 'required',
            'delivered_by'          => 'required',
            'received_by'           => 'required',
            'entry_type'            => 'required',
            'other_entry_type'      => 'required_if:entry_type,Otro',
        ],
            [
                'qty.required'              => 'Debe especificar la cantidad del material que ingresa',
                'delivered_by.required'     => 'Debe especificar la persona que entrega el material!',
                'received_by.required'      => 'Debe especificar la persona que recibe el material!',
                'entry_type.required'       => 'Debe especificar el tipo de ingreso del material!',
                'entry_type.required_if'    => 'Debe especificar el tipo de ingreso del material!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back();
        }

        $entry->fill(Request::all());

        $entry->entry_type = $entry->entry_type=='Otro' ? Request::input('other_entry_type') : $entry->entry_type;

        $deliverer = User::select('id')->where('name', Request::input('delivered_by'))->first();
        $receiver = User::select('id')->where('name', Request::input('received_by'))->first();

        $entry->delivered_id = $deliverer ?  $deliverer->id : 0;
        $entry->received_id = $receiver ? $receiver->id : 0;

        $entry->save();

        if($prev_qty!=$entry->qty){
            /* associate material quantity to warehouse */
            $prev_total = $entry->warehouse->materials()->where('material_id', $entry->material_id)->first();

            $new_total = $prev_total->pivot->qty + $entry->qty - $prev_qty;
            $entry->warehouse->materials()->updateExistingPivot($entry->material_id, ['qty' => $new_total]);
        }

        /* record a new event for the modification */
        $this->add_event('corrected entrance',$entry);

        Session::flash('message', "Datos modificados correctamente");
        return redirect()->route('wh_entry.index');
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

        $entry = WarehouseEntry::find($id);

        if($entry){

            /* substract the entry's quantity from the total in the relation warehouse-material */
            $prev_total = $entry->warehouse->materials()->where('material_id', $entry->material_id)->first();

            $new_total = $prev_total->pivot->qty - $entry->qty;
            $new_total = $new_total<0 ? 0 : $new_total;
            $entry->warehouse->materials()->updateExistingPivot($entry->material_id, ['qty' => $new_total]);

            /* record a new event for the deletion of the entrance */
            $this->add_event('deleted entrance',$entry);

            foreach($entry->files as $file){
                $success = true;

                try {
                    \Storage::disk('local')->delete($file->name);
                } catch (ModelNotFoundException $ex) {
                    $success = false;
                }

                if($success)
                    $file->delete();
            }

            $entry->delete();

            Session::flash('message', "El registro ha sido eliminado");
            return redirect()->route('wh_entry.index');
        }
        else {
            Session::flash('message', "Error al borrar el registro, revise la dirección e intente de nuevo por favor.");
            return redirect()->back();
        }
    }

    public function add_event($type, $entry)
    {
        $user = Session::get('user');

        $event = new Event;
        $event->user_id = $user->id;
        $event->date = Carbon::now();

        $prev_number = Event::select('number')->where('eventable_id',$entry->id)
            ->where('eventable_type','App\WarehouseEntry')->orderBy('number','desc')->first();

        $event->number = $prev_number ? $prev_number->number+1 : 1;

        if($type=='new entrance'){
            $event->description = 'Ingreso de material';
            $event->detail = ($entry->received_by ? $entry->received_by : 'Se').' recibe '.$entry->qty.' '.
                $entry->material->units.' de '.$entry->material->name.' en el almacén '.$entry->warehouse->name.
                ($entry->reason ? ' por concepto de: '.$entry->reason : '');
        }
        if($type=='corrected entrance'){
            $event->description = 'Corrección de ingreso';
            $event->detail = 'Se corrige el ingreso de material de fecha '.Carbon::parse($entry->date)->format('d-m-Y').
                ' con el siguiente detalle: '.($entry->received_by ? $entry->received_by : 'Se').' recibe '.$entry->qty.
                ' '.$entry->material->units.' de '.$entry->material->name.' en el almacén '.$entry->warehouse->name.
                ($entry->reason ? ' por concepto de: '.$entry->reason : '');
        }
        if($type=='deleted entrance'){
            $event->description = 'Registro de ingreso eliminado';
            $event->detail = 'Se elimina del sistema el registro '.$entry->id.' de fecha '.
                Carbon::parse($entry->date)->format('d-m-Y');
        }

        $event->responsible_id = $entry->received_id;
        $event->eventable()->associate(WarehouseEntry::find($entry->id));
        $event->save();
    }
}
