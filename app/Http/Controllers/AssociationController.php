<?php

namespace App\Http\Controllers;

use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Session;
use View;
use App\Assignment;
use App\User;
use App\Site;
use App\Order;
use App\Bill;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;

class AssociationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function join_form($model, $id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        //$current_date = Carbon::now();
        $to_join = 0;
        $options = 0;
        $total_available = 0;

        if($model=='site-to-order'){
            $to_join = Site::find($id);
            $options = Order::where('status', 'Pendiente')->where('client', $to_join->assignment->client)->get();
        }
        if($model=='order-to-site'){
            $to_join = Order::find($id);
            
            $last_stat = Assignment::first()->last_stat();
            $options = Assignment::whereNotIn('status', [$last_stat/*'Concluído'*/,0/*'No asignado'*/])
                ->where('client', $to_join->client)
                ->get();

            $total_available = $to_join->assigned_price;

            foreach($to_join->sites as $site){
                $total_available = $total_available - $site->pivot->assigned_amount;
            }

            if($total_available<0)
                $total_available=0;
        }
        if($model=='order-to-bill'){
            $to_join = Order::find($id);
            //$options = Bill::where('date_issued', '>=', Carbon::now()->startOfMonth())->get();
            $options = Bill::where('status', 0)->get();
        }
        if($model=='bill-to-order'){
            $to_join = Bill::find($id);
            $options = Order::where('status', 'Pendiente')->get();
        }
        
        return view::make('app.join_form', ['id' => $id, 'model' => $model, 'to_join' => $to_join, 'service' => $service,
            'options' => $options, 'total_available' => $total_available]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function joiner(Request $request, $model, $id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        //$service = Session::get('service');

        $join_id = Request::input('id');

        if($model=='order-to-bill'){

            if(!$join_id){
                Session::flash('message', "Debe seleccionar una factura!");
                return redirect()->back();
            }

            $order = Order::find($id);
            $bill = Bill::find($join_id);
            $amount = Request::input('amount');

            $hasBill = $order->bills()->where('bill_id', $bill->id)->exists();

            if($hasBill){
                Session::flash('message', "La factura seleccionada ya está asociada a esta orden!");
                return redirect()->back();
            }

            $order->bills()->attach($join_id, ['charged_amount' => $amount]);

            Session::flash('message', "Se asoció la orden $order->code a la factura $bill->code");
            if(Session::has('url'))
                return redirect(Session::get('url'));
            else
                return redirect()->route('order.index');
        }
        if($model=='order-to-site'){
            $order = Order::find($id);
            $sites = Request::input('site');

            if($sites){

                if($order->sites->count()>=$order->number_of_sites){
                    Session::flash('message', "Ya no puede asociar mas sitios a esta orden!");
                    return redirect()->back();
                }

                foreach($sites as $key => $site){

                    //$site = Site::find($join_id);
                    if($site['site_id']!=''){
                        $hasSite = $order->sites()->where('site_id', $site['site_id'])->exists();

                        if(!$hasSite){
                            $order->sites()->attach($site['site_id'], ['assigned_amount' => $site['amount']]);

                            //$site->assigned_price = Request::input('site_assigned_price');
                            //$site->status = 'Cobro';
                            //$site->save();
                        }
                    }
                }

                Session::flash('message', "Los sitios seleccionados fueron asociados a la orden $order->code");
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->route('order.index');
            }
            else{
                Session::flash('message', "No se asoció ningún sitio a esta orden");
                return redirect()->back();
            }

            //return Request::input('site.1.amount').' '.Request::input('site.2.amount');
            /*
            $assignment_id = Request::input('assignment_id');

            if($join_id==0){
                $sites = Site::where('assignment_id','=',$assignment_id)->get();
                $assignment = Assignment::find($assignment_id);
                
                foreach($sites as $site){
                    $hasSite = $order->sites()->where('site_id', $site->id)->exists();

                    if(!$hasSite){
                        $order->sites()->attach($site->id);
                        $site->status = 'Cobro';
                        $site->save();
                    }
                }
                
                $assignment->status = 'Cobro';
                $assignment->save();
                
                Session::flash('message', " Se asoció la orden $order->code al proyecto $assignment->name");
            }
            else{
                $site = Site::find($join_id);

                $hasSite = $order->sites()->where('site_id', $site->id)->exists();

                if($hasSite){
                    Session::flash('message', " El sitio seleccionado ya está asociado a esta orden ");
                    return redirect()->back();
                }

                $order->sites()->attach($join_id);
                $site->assigned_price = Request::input('site_assigned_price');
                $site->status = 'Cobro';
                $site->save();

                Session::flash('message', " Se asoció la orden $order->code al sitio $site->name");
            }
            return redirect()->route('order.index');
            */
        }
        if($model=='bill-to-order'){
            $bill = Bill::find($id);
            $orders = Request::input('order');

            if($orders){

                foreach($orders as $key => $order){

                    if($order['order_id']!=''){

                        $hasOrder = $bill->orders()->where('order_id', $order['order_id'])->exists();

                        if(!$hasOrder){
                            $bill->orders()->attach($order['order_id'], ['charged_amount' => $order['amount']]);
                        }
                    }
                }

                Session::flash('message', "Las órdenes seleccionadas fueron asociadas a la factura $bill->code");
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->route('bill.index');
            }
            else{
                Session::flash('message', "No se asoció ninguna orden a esta factura");
                return redirect()->back();
            }

            //$order = Order::find($join_id);
            //$amount = Request::input('amount');
        }
        if($model=='site-to-order'){

            if(!$join_id){
                Session::flash('message', "Debe seleccionar una Orden!");
                return redirect()->back();
            }

            $site = Site::find($id);
            $order = Order::find($join_id);
            $amount = Request::input('amount');

            $hasOrder = $site->orders()->where('order_id', $order->id)->exists();

            if($hasOrder){
                Session::flash('message', "La orden seleccionada ya está asociada a este sitio!");
                return redirect()->back();
            }

            $site->orders()->attach($join_id, ['assigned_amount' => $amount]);
            /*
            if($order->charged_price>$site->assigned_price)
                $site->charged_price = $site->assigned_price;
            else
                $site->charged_price = $site->charged_price + $order->charged_price;

            $site->status = 'Cobro';
            $site->save();
            */
            Session::flash('message', "Se asoció el sitio $site->name a la orden $order->code");
            if(Session::has('url'))
                return redirect(Session::get('url'));
            else
                return redirect()->action('SiteController@sites_per_project', ['id' => $site->assignment_id]);
        }

        /* Default redirection if no match is found */
        Session::flash('message', "Sucedió un error al cargar el formulario, intente de nuevo por favor");
        return redirect()->back();
    }

    public function detach($page, $from_id, $id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        //$service = Session::get('service');

        $order = Order::find($id);
        $from_exploded = explode('-',$from_id);

        if($from_exploded[0]=='st'){
            $order->sites()->detach($from_exploded[1]);
            $message = "Se eliminó la asociación correctamente";
        }
        elseif($from_exploded[0]=='bl'){
            $order->bills()->detach($from_exploded[1]);
            $message = "Se eliminó la asociación correctamente";
        }
        else{
            $message = "No se pudo completar la operación solicitada. Intente de nuevo por favor";
        }

        Session::flash('message', $message);

        if(Session::has('url'))
            return redirect(Session::get('url'));
        elseif($page=='order')
            return redirect()->route('order.index');
        elseif($page=='site')
            return redirect()->action('SiteController@show', ['id' => $from_exploded[1]]);
        elseif($page=='bill')
            return redirect()->action('BillController@show', ['id' => $from_exploded[1]]);
        else
            return redirect()->back();
    }
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
