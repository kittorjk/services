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
use App\Bill;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;

class BillController extends Controller
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
            return View('app.index', ['service' => 'project', 'user' => null]);
        }
        if($user->acc_project==0)
            return redirect()->action('LoginController@logout', ['service' => 'project']);

        $service = Session::get('service');

        $stat = Input::get('stat');

        $bills = Bill::where('id','>',0);

        if(!is_null($stat))
            $bills = $bills->where('status', $stat);

        $bills = $bills->orderBy('id', 'desc')->paginate(20);

        foreach($bills as $bill){
            /*
            $status_determiner = 1;
            $count_orders = 0;

            foreach($bill->orders as $order){
                if($order->pivot->status==0){
                    $status_determiner = 0;
                }
                $count_orders++;
            }

            if($bill->status!=$status_determiner&&$count_orders!=0){
                $bill->status = $status_determiner;
                $bill->save();
            }
            */
            $bill->date_issued = Carbon::parse($bill->date_issued)->hour(0)->minute(0)->second(0);
        }
        /*
        $current_date = Carbon::now();
        $current_date->hour = 0;
        $current_date->minute = 0;
        $current_date->second = 0;
        */
        return View::make('app.bill_brief', ['bills' => $bills, 'service' => $service, 'user' => $user]);
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

        return View::make('app.bill_form', ['bill' => 0, 'service' => $service, 'user' => $user]);
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

        $bill = new Bill(Request::all());

        $v = \Validator::make(Request::all(), [
            'code'                    => 'required',
            'date_issued'             => 'required',
            'billed_price'            => 'required',
        ],
            [
                'code.required'                  => 'Debe especificar el número de la factura!',
                'date_issued.required'           => 'Debe especificar la fecha de emisión de la factura!',
                'billed_price.required'          => 'Debe especificar el monto facturado!',
            ]
        );
        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back();
        }
        
        $bill->user_id = $user->id;

        $bill->save();

        Session::flash('message', "La factura fue agregada al sistema");
        return redirect()->route('bill.index');
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

        $bill = Bill::find($id);

        return View::make('app.bill_info', ['bill' => $bill, 'service' => $service, 'user' => $user]);
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

        $bill = Bill::find($id);

        $bill->date_issued = Carbon::parse($bill->date_issued)->format('Y-m-d');

        return View::make('app.bill_form', ['bill' => $bill, 'service' => $service, 'user' => $user]);
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
            'code'                      => 'required',
            'date_issued'               => 'required',
            'billed_price'              => 'required',
        ],
            [
                'code.required'                  => 'Debe especificar el número de la factura!',
                'date_issued.required'           => 'Debe especificar la fecha de emisión de la factura!',
                'billed_price.required'          => 'Debe especificar el monto facturado!',
            ]
        );
        
        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back();
        }

        $bill = Bill::find($id);

        $bill->fill(Request::all());
        
        $bill->save();
        
        Session::flash('message', "Datos modificados correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('bill.index');
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

    public function update_status($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        //$service = Session::get('service');

        $bill = Bill::find($id);

        $bill->status=1;
        $bill->date_charged = Carbon::now();
        $bill->save();

        Session::flash('message', "La factura $bill->code fue marcada como cobrada");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('bill.index');
    }
}
