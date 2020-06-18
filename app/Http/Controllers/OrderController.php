<?php

namespace App\Http\Controllers;

use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Session;
use View;
use Input;
use App\File;
use App\User;
use App\Site;
use App\Order;
use App\ServiceParameter;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
use App\Http\Traits\FilesTrait;

class OrderController extends Controller
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
        if ($user->acc_project == 0)
            return redirect()->action('LoginController@logout', ['service' => 'project']);

        $service = Session::get('service');

        $stat = Input::get('stat');

        $orders = Order::where('id','>',0);

        if (!is_null($stat))
            $orders = $orders->where('status', $stat);

        $orders = $orders->orderBy('date_issued')->paginate(20);
        /*
        $files = File::join('orders', 'files.imageable_id', '=', 'orders.id')
            ->select('files.id', 'files.name', 'files.imageable_id', 'files.created_at')
            ->where('imageable_type', '=', 'App\Order')
            ->get();
        */
        foreach ($orders as $order) {
            /*
            if($order->charged_price>=$order->assigned_price){
                $order->status = 'Cobrado';
                $order->save();
            }
            */
            $order->date_issued = Carbon::parse($order->date_issued)->hour(0)->minute(0)->second(0);
            $order->updated_at = Carbon::parse($order->updated_at)->hour(0)->minute(0)->second(0);
            /*
            if($order->status!='Cobrado'&&$order->status!='Anulado'){
                $total_billed = 0;
                foreach($order->bills as $bill){
                    $total_billed = $total_billed+$bill->billed_price;
                }
                if($total_billed>=$order->assigned_price){
                    $order->charged_price = $order->assigned_price;
                    $order->status = 'Cobrado';
                }
                else
                    $order->charged_price = $total_billed;
                $order->save();
            }
            */
            foreach ($order->files as $file) {
                $file->created_at = Carbon::parse($file->created_at)->hour(0)->minute(0)->second(0);
            }
        }
        
        $current_date = Carbon::now();
        $current_date->hour = 0;
        $current_date->minute = 0;
        $current_date->second = 0;

        $qccs = File::where('name','like','%qcc%')->where('created_at','>=',Carbon::now()->subDays(7))->get();

        $recent_qcc = $qccs->count();

        return View::make('app.order_brief', ['orders' => $orders, 'service' => $service, 'user' => $user,
            'recent_qcc' => $recent_qcc, 'current_date' => $current_date]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $payment_percentages = Order::select('payment_percentage')->where('payment_percentage', '<>', '')
            ->groupBy('payment_percentage')->get();
        $clients = Order::select('client', 'type')->where('client','<>','')->groupBy('client', 'type')->get();

        return View::make('app.order_form', ['order' => 0, 'payment_percentages' => $payment_percentages,
            'clients' => $clients, 'service' => $service, 'user' => $user]);
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
            'code'                      => 'required',
            'client'                    => 'required',
            'other_client'              => 'required_if:client,Otro',
            'other_type'                => 'required_if:client,Otro',
            'payment_percentage'        => 'required',
            'other_payment_percentage'  => 'required_if:payment_percentage,Otro|regex:[^(\d{1,3})-(\d{1,3})-(\d{1,3})$]',
            'number_of_sites'           => 'numeric',
            'date_issued'               => 'required',
            'assigned_price'            => 'required',
        ],
            [
                'code.required'                  => 'Debe especificar el código de orden!',
                'client.required'                => 'Debe especificar un cliente!',
                'other_client.required_if'       => 'Debe especificar un cliente!',
                'other_type.required_if'         => 'Debe especificar el tipo de orden!',
                'payment_percentage.required'    => 'Debe especificar los porcentajes de pago!',
                'other_payment_percentage.required_if' => 'Debe especificar los porcentajes de pago!',
                'other_payment_percentage.regex' => 'El formato de porcentajes de pago debe coincidir con: xx-xx-xx',
                'number_of_sites.numeric'        => 'Introdujo un valor no válido en número de sitios!',
                'date_issued.required'           => 'Debe especificar la fecha de emisión de la orden!',
                'assigned_price.required'        => 'Debe especificar el monto asignado a la orden!',
            ]
        );

        if ($v->fails()) {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $order = new Order(Request::all());

        if ($order->client == "Otro") {
            $order->client = Request::input('other_client');
            $order->type = Request::input('other_type');
        } else {
            $client_type = explode('-', Request::input('client'));
            $order->client = $client_type[0];
            $order->type = $client_type[1];
        }

        if ($order->payment_percentage == "Otro") {
            $order->payment_percentage = Request::input('other_payment_percentage');

            $exploded_percentages = explode('-', $order->payment_percentage);
            if (($exploded_percentages[0] + $exploded_percentages[1] + $exploded_percentages[2]) != 100) {
                Session::flash('message', "Los porcentajes de pago deben sumar 100%!");
                return redirect()->back()->withInput();
            }
        }

        $order->number_of_sites = $order->number_of_sites == '' ? 1 : $order->number_of_sites;

        $order->status = 'Pendiente';
        $order->user_id = $user->id;

        $dollar_to_bs = ServiceParameter::where('name','dollar_to_bs')->first(); //Convert dollars and store only Bs

        if (Request::input('currency') == '$us') {
            $order->assigned_price = $order->assigned_price * $dollar_to_bs->numeric_content;
        }

        if (Request::input('currency_charged') == '$us') {
            $order->charged_price = $order->charged_price * $dollar_to_bs->numeric_content;
        }

        $order->save();

        Session::flash('message', "La orden de compra del cliente fue agregada al sistema");
        if (Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('order.index');
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

        $order = Order::find($id);
        /*
        $files = File::select('id','user_id','name','updated_at')->where('imageable_id', $id)
            ->where('imageable_type', 'App\Order')->get();
        */
        $percentages = explode('-', $order->payment_percentage);

        foreach ($order->bills as $bill) {
            $order->billed_price = $order->billed_price + $bill->pivot->charged_amount;
        }
        
        return View::make('app.order_info', ['order' => $order, /*'files' => $files,*/ 'percentages' => $percentages,
            'service' => $service, 'user' => $user]);
    }
    /*
    public function show_order_associations($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $order = Order::find($id);

        $sites_count = 0;

        foreach($order->sites as $site){
            $sites_count++;
        }

        return View::make('app.order_associations', ['order' => $order, 'sites_count' => $sites_count,
            'service' => $service, 'user' => $user]);
    }
    */
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

        $order = Order::find($id);

        $order->date_issued = Carbon::parse($order->date_issued)->format('Y-m-d');

        $payment_percentages = Order::select('payment_percentage')->where('payment_percentage', '<>', '')
            ->groupBy('payment_percentage')->get();
        $clients = Order::select('client', 'type')->where('client','<>','')->groupBy('client', 'type')->get();

        return View::make('app.order_form', ['order' => $order, 'payment_percentages' => $payment_percentages,
            'clients' => $clients, 'service' => $service, 'user' => $user]);
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

        $order = Order::find($id);

        $v = \Validator::make(Request::all(), [
            'code'                      => 'required',
            'status'                    => 'required',
            'client'                    => 'required',
            'other_client'              => 'required_if:client,Otro',
            'other_type'                => 'required_if:client,Otro',
            'payment_percentage'        => 'required',
            'other_payment_percentage'  => 'required_if:payment_percentage,Otro|regex:[^(\d{1,3})-(\d{1,3})-(\d{1,3})$]',
            'number_of_sites'           => 'numeric',
            'date_issued'               => 'required',
            'assigned_price'            => 'required',
        ],
            [
                'code.required'                  => 'Debe especificar el código de orden!',
                'status.required'                => 'Seleccione el estado de la orden!',
                'client.required'                => 'Debe especificar un cliente!',
                'other_client.required_if'       => 'Debe especificar un cliente!',
                'other_type.required_if'         => 'Debe especificar el tipo de orden!',
                'payment_percentage.required'    => 'Debe especificar los porcentajes de pago!',
                'other_payment_percentage.required_if' => 'Debe especificar los porcentajes de pago!',
                'other_payment_percentage.regex' => 'El formato de porcentajes de pago debe coincidir con: xx-xx-xx',
                'number_of_sites.numeric'        => 'Introdujo un valor no válido en número de sitios!',
                'date_issued.required'           => 'Debe especificar la fecha de emisión de la orden!',
                'assigned_price.required'        => 'Debe especificar el monto asignado a la orden!',
            ]
        );
        
        if ($v->fails()) {
            Session::flash('message', $v->messages()->first());
            return redirect()->back();
        }

        $order->fill(Request::all());

        if ($order->client == "Otro") {
            $order->client = Request::input('other_client');
            $order->type = Request::input('other_type');
        } else {
            $client_type = explode('-', Request::input('client'));
            $order->client = $client_type[0];
            $order->type = $client_type[1];
        }

        if ($order->payment_percentage == "Otro") {
            $order->payment_percentage = Request::input('other_payment_percentage');

            $exploded_percentages = explode('-', $order->payment_percentage);
            
            if (($exploded_percentages[0] + $exploded_percentages[1] + $exploded_percentages[2]) != 100) {
                Session::flash('message', "Los porcentajes de pago deben sumar 100%!");
                return redirect()->back()->withInput();
            }
        }

        if ($order->status == 'Cobrado' || $order->status == 'Anulado') {
            if ($order->status == 'Cobrado')
                $order->date_charged = Carbon::now();

            foreach ($order->files as $file) {
                $this->blockFile($file);
            }
        }

        $order->number_of_sites = $order->number_of_sites == '' ? 1 : $order->number_of_sites;

        $dollar_to_bs = ServiceParameter::where('name','dollar_to_bs')->first(); //Convert dollars and store only Bs

        if (Request::input('currency') == '$us') {
            $order->assigned_price = $order->assigned_price * $dollar_to_bs->numeric_content;
        }

        if (Request::input('currency_charged') == '$us') {
            $order->charged_price = $order->charged_price * $dollar_to_bs->numeric_content;
        }

        $order->save();

        Session::flash('message', "Datos modificados correctamente");
        if (Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('order.index');
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
    /*    
    public function detach_from_order($type_id, $order_id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $order = Order::find($order_id);
        
        $selector = explode('-',$type_id);
        
        $id = $selector[1];
        
        if($selector[0]=='st')
            $order->sites()->detach($id);
        elseif($selector[0]=='bl')
            $order->bills()->detach($id);
        else
            Session::flash('message', " No se pudo completar la operación solicitada. Intente de nuevo por favor ");
        
        return redirect()->action('OrderController@show', ['id' => $order->id]);
    }
    */
    public function update_status($param)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        //$service = Session::get('service');

        $exploded = explode('-',$param);
        $type = $exploded[0];
        $id = $exploded[1];

        $order = Order::find($id);

        if ($type == 'ch') {
            $order->status = 'Cobrado';
            $order->date_charged = Carbon::now();
        } elseif ($type == 'nl') {
            $order->status='Anulado';
        }

        $order->save();

        foreach ($order->files as $file) {
            $this->blockFile($file);
        }

        Session::flash('message', "La orden $order->type - $order->code fue marcada como $order->status");
        if (Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('order.index');
    }
    
    public function recent_qcc()
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');
        
        $service = Session::get('service');

        $qccs = File::where('name','like','%qcc%')->where('created_at','>=',Carbon::now()->subDays(7))->get();
        
        return View::make('app.order_qcc_list', ['qccs' => $qccs, 'service' => $service, 'user' => $user]);
    }
}
