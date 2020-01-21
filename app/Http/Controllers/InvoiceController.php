<?php

namespace App\Http\Controllers;

use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use View;
use Hash;
use Mail;
use Input;
use Exception;
use App\Invoice;
use App\User;
use App\Provider;
use App\OC;
use App\Email;
use App\Event;
use Carbon\Carbon;
use App\Http\Traits\FilesTrait;

class InvoiceController extends Controller
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
        return View('app.index', ['service' => 'oc', 'user' => null]);
    }
    if ($user->acc_oc == 0)
        return redirect()->action('LoginController@logout', ['service' => 'oc']);
    
    $service = Session::get('service');

    //$inv_waiting_approval = Invoice::where('flags','like','0%')->count(); //0;
    //$pending_invoices = Invoice::where('flags','like','0%')->get();

    /*
    foreach($pending_invoices as $invoice){
        if((($invoice->flags[1]==0||$invoice->flags[2]==0)&&$user->priv_level==4)||
            ($invoice->flags[2]==0&&$user->area=='Gerencia Tecnica'&&$user->priv_level==3)||
            ($invoice->flags[1]==0&&$invoice->flags[2]==1&&$user->area=='Gerencia General'&&$user->priv_level==3)||
            ($invoice->flags[1]==1&&$invoice->flags[2]==1&&$user->area=='Gerencia Administrativa'&&$user->priv_level>=2))
            $inv_waiting_approval++;
    }
    */

    if ($user->priv_level >= 3) {
      // $invoices = Invoice::where('created_at', '>=', Carbon::now()->subDays(30))->orWhere('flags','like','0%');
      $invoices = Invoice::where('created_at', '>=', Carbon::now()->subDays(30))
                          ->orWhere('status','<>','Pagado');
    }
    /*
    elseif($user->priv_level==3&&$user->area=='Gerencia General'){
        $invoices = Invoice::where('flags','like','001%')->orwhere('user_id','=',$user->id)
            ->orwhere(function ($query) {
                $query->where('flags','like','01%');})
            ->orwhere(function ($query) {
                $query->where('flags','like','1%')->where('created_at', '>=', Carbon::now()->subDays(30));
            });
    }
    elseif($user->priv_level==3&&$user->area=='Gerencia Tecnica'){
        $invoices = Invoice::where('flags','like','0001%')->orwhere('user_id','=',$user->id)
            ->orwhere(function ($query) {
                $query->where('flags','like','001%');})
            ->orwhere(function ($query) {
                $query->where('flags','like','01%');})
            ->orwhere(function ($query) {
                $query->where('flags','like','1%')->where('created_at', '>=', Carbon::now()->subDays(30));
            });
    }
    */
    elseif ($user->action->oc_inv_pmt /*$user->area=='Gerencia Administrativa'*/) {
      /* $invoices = Invoice::where('flags','like','01%')->orwhere('user_id','=',$user->id)
          ->orwhere(function ($query) {
              $query->where('flags','like','1%')->where('created_at', '>=', Carbon::now()->subDays(30));
          }); */
      $invoices = Invoice::where('status', 'Aprobado Gerencia General')
          ->orwhere('user_id','=',$user->id)
          ->orwhere(function ($query) {
            $query->where('status','Pagado')->where('created_at', '>=', Carbon::now()->subDays(30));
          });
    } else {
      /* $invoices = Invoice::where('user_id', $user->id)
          ->where(function ($query) {
              $query->where('flags','like','0%')->orwhere('created_at', '>=', Carbon::now()->subDays(30));
          }); */
      $invoices = Invoice::where('user_id', $user->id)
          ->where(function ($query) {
            $query->where('status','<>','Pagado')->orwhere('created_at', '>=', Carbon::now()->subDays(30));
          });
    }

    $invoices = $invoices->orderBy('updated_at','desc')->paginate(20);

    foreach ($invoices as $invoice) {
      $invoice->date_issued = Carbon::parse($invoice->date_issued)->hour(0)->minute(0)->second(0);
      if ($invoice->transaction_date != '0000-00-00 00:00:00')
        $invoice->transaction_date = Carbon::parse($invoice->transaction_date)->hour(0)->minute(0)->second(0);
    }

    //Session::put('db_query', $invoices);

    return View::make('app.invoice_brief', ['invoices' => $invoices, 'inv_waiting_approval' => 0 /*$inv_waiting_approval*/,
        'service' => $service, 'user' => $user]);
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

    $ps_id = Input::get('oc'); // Previously selected OC id

    $ocs = OC::where('status','Aprobado Gerencia General')->where('payment_status','<>','Concluido')->where(function ($query) {
                $query->where('executed_amount','<>',0)->orwhere('payment_status','Sin pagos')->orwhere('payment_status', '');
            })->get();
    //$bank_options = Provider::select('bnk_name')->where('bnk_name', '<>', '')->groupBy('bnk_name')->get();

    return View::make('app.invoice_form', ['invoice' => 0, 'ocs' => $ocs, 'service' => $service,
        'user' => $user, 'ps_id' => $ps_id]);
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
        'oc_id'                  => 'required',
        'number'                 => 'required',
        'amount'                 => 'required',
        'date_issued'            => 'required',
        'concept'                => 'required',
        'oc_certification_id'    => 'required_unless:concept,Adelanto'
    ],
        [
            'oc_id.required'             => 'Debe especificar la orden a la que pertenece la factura!',
            'number.required'            => 'Debe especificar el número de factura!',
            'amount.required'            => 'Debe especificar el monto de la factura!',
            'date_issued.required'       => 'Debe especificar la fecha de emisión de la factura!',
            'billed_price.required'      => 'Debe especificar el monto facturado!',
            'concept.required'           => 'Debe seleccionar el motivo de la factura!',
            'oc_certification_id.required_unless' => 'Debe seleccionar un certificado si la factura no es por adelanto!'
        ]
    );

    if ($v->fails()) {
        Session::flash('message', $v->messages()->first());
        return redirect()->back()->withInput();
    }

    $invoice = new Invoice(Request::all());
    // $invoice_reason = Request::input('invoice_reason');
    /*
      if ($invoice_reason == 'adelanto')
          $invoice->flags = '00010100';
      elseif ($invoice_reason == 'avance')
          $invoice->flags = '00010010';
      elseif ($invoice_reason == 'final')
          $invoice->flags = '00010001';
      else {
          Session::flash('message', "Debe seleccionar el motivo de la factura de la lista!");
          return redirect()->back()->withInput();
      }
    */

    if ($invoice->concept != 'Adelanto' && $invoice->oc->executed_amount == 0) {
      Session::flash('message', "Debe especificar el monto ejecutado de la OC si la factura no es por adelanto!
          Por favor cargue un certificado de aceptación parcial o total");
      return redirect()->back()->withInput();
    }

    // Validación de porcentajes de pago según OC
    $percentages = explode('-', $invoice->oc->percentages);
    
    if (($invoice->concept == 'Adelanto' && $percentages[0] == 0) ||
        ($invoice->concept == 'Avance' && $percentages[1] == 0) ||
        ($invoice->concept == 'Entrega' && $percentages[2] == 0)) {
      $message = "No puede cargar una factura por ".$invoice->concept." para la OC seleccionada, revise los porcentajes de pago de la OC!";
      Session::flash('message', $message);
      return redirect()->back()->withInput();
    }

    $similar_invoices = Invoice::where('number', $invoice->number)->get();
    foreach ($similar_invoices as $similar_invoice) {
      if ($similar_invoice->oc->provider_id == $invoice->oc->provider_id) {
        Session::flash('message', "El proveedor de ésta OC ya tiene registrada una factura con el mismo número!");
        return redirect()->back()->withInput();
      }
    }

    $invoice->user_id = $user->id;

    if ($invoice->concept != 'Adelanto') {
      // Certificado firmado
      $signed_file_exists = false;
      foreach ($invoice->oc_certification->files as $file) {
        if (substr($file->name, 0, 4) == 'CTDF') {
          $signed_file_exists = true;
        }
      }

      if (!$signed_file_exists) {
        Session::flash('message', "El certificado seleccionado no cuenta con el archivo firmado en el sistema!");
        return redirect()->back()->withInput();
      }

      // Facturas en certificado, no se toma en cuenta la factura actual
      $existing_amount = 0;
      foreach ($invoice->oc_certification->invoices as $inv) {
        if ($inv->id != $invoice->id) {
          $existing_amount += $inv->amount;
        }
      }

      if (($existing_amount + $invoice->amount) > $invoice->oc_certification->amount) {
        Session::flash('message', "El monto indicado excede el monto disponible para la certificación seleccionada!");
        return redirect()->back()->withInput();
      }
    }

    if ($invoice->concept == 'Adelanto') {
      foreach ($invoice->oc->invoices as $inv) {
        if ($inv->concept == 'Adelanto') {
          Session::flash('message', "La orden seleccionada ya tiene una factura por adelanto!");
          return redirect()->back()->withInput();
        }
      }
    }

    // Commented lines regarding approval, all invoices are now recorded as approved
    /*
    if (($user->priv_level == 3 && $user->area == 'Gerencia General') || $user->priv_level == 4) {
      if ($invoice->flags[1] == 0)
        $invoice->flags = str_pad($invoice->flags+1000000, 8, "0", STR_PAD_LEFT);
      if ($invoice->flags[2] == 0)
        $invoice->flags = str_pad($invoice->flags+100000, 8, "0", STR_PAD_LEFT);
    } elseif ($user->priv_level == 3 && $user->area == 'Gerencia Tecnica') {
      if ($invoice->flags[2] == 0)
        $invoice->flags = str_pad($invoice->flags+100000, 8, "0", STR_PAD_LEFT);
    }
    */
    
    // All invoices are recorded as approved by GG after a file with the scanned bill is uploaded
    $invoice->status = 'Creado';
      
    $invoice->save();

    /* Send email notification */
    if (empty(Request::input('transaction_code'))) {
      $approved = array();
      $approved[] = array('number' => $invoice->number, 'provider' => $invoice->oc->provider_record->prov_name);

      $this->send_notification($invoice, $approved);

      /*Moved to a separate function
      $approved = array();
      $approved[] = array('number' => $invoice->number, 'provider' => $invoice->oc->provider_record->prov_name);

      if(($user->priv_level==3&&$user->area=='Gerencia General')||$user->priv_level==4){
          $recipient = User::where('area','Gerencia Administrativa')->where('priv_level',3)->first();
          $data = array('recipient' => $recipient, 'approved' => $approved); //$invoice->number);

          $mail_structure = 'emails.invoice_waiting_payment';
          $subject = 'Facturas de proveedor pendientes de pago';
      }
      elseif($user->priv_level==3&&$user->area=='Gerencia Tecnica'){
          $recipient = User::where('area','Gerencia General')->where('priv_level',3)->first();
          $data = array('recipient' => $recipient, 'approved' => $approved); //$invoice->number);

          $mail_structure = 'emails.invoice_approved';
          $subject = 'Facturas de proveedor pendientes de aprobación';
      }
      else{
          $recipient = User::where('area','Gerencia Tecnica')->where('priv_level',3)->first();
          $data = array('recipient' => $recipient, 'invoice' => $invoice);

          $mail_structure = 'emails.invoice_added';
          $subject = 'Nueva factura de proveedor agregada al sistema';
      }

      if($mail_structure!=''){
          $view = View::make($mail_structure, $data);
          $content = (string) $view;

          $success = 1;

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

    Session::flash('message', "La factura fue agregada al sistema");
    if (Session::has('url'))
      return redirect(Session::get('url'));
    else
      return redirect()->route('invoice.index');
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
    if ((is_null($user)) || (!$user->id)) {
      //$ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
      //$ref = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
      $ref = $_SERVER['REQUEST_URI'];
      Session::put('url.intended', $ref);
      return redirect()->route('root');
    }

    $service = Session::get('service');

    $invoice = Invoice::find($id);
    
    //$flags = substr($invoice->flags, -3);

    return View::make('app.invoice_info', ['invoice' => $invoice, 'service' => $service, 'user' => $user]);
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

    $invoice = Invoice::find($id);

    $invoice->date_issued = Carbon::parse($invoice->date_issued)->format('Y-m-d');
    $invoice->transaction_date = Carbon::parse($invoice->transaction_date)->format('Y-m-d');

    //$ocs = OC::where('flags','like','0111%')->where('flags','<>','01110111')->get();
    $ocs = OC::where('status','Aprobado Gerencia General')->where('payment_status','<>','Concluido')->where(function ($query) {
          $query->where('executed_amount','<>',0)->orwhere('payment_status','Sin pagos')->orwhere('payment_status', '');
      })
      ->orwhere('id', $invoice->oc_id)
      ->get();

    return View::make('app.invoice_form', ['invoice' => $invoice, 'ocs' => $ocs, 'service' => $service,
        'user' => $user, 'ps_id' => 0 /*Id already selected in invoice variable*/]);
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
        'number'                 => 'required',
        'amount'                 => 'required',
        'date_issued'            => 'required',
        'concept'                => 'required',
        'oc_certification_id'    => 'required_unless:concept,Adelanto'
    ],
        [
            'number.required'               => 'Debe especificar el número de factura!',
            'amount.required'               => 'Debe especificar el monto de la factura!',
            'date_issued.required'          => 'Debe especificar la fecha de emisión de la factura!',
            'concept.required'              => 'Debe seleccionar el motivo de la factura!',
            'oc_certification_id.required_unless' => 'Debe seleccionar un certificado si la factura no es por adelanto!'
        ]
    );

    if ($v->fails()) {
      Session::flash('message', $v->messages()->first());
      return redirect()->back()->withInput();
    }

    // TODO duplicate value without reference
    $old_invoice = Invoice::find($id);
    $invoice = Invoice::find($id);

    // $invoice_reason = Request::input('invoice_reason');

    $invoice->fill(Request::all());

    if ($old_invoice->status != 'Pagado') {
      /*
      if ($invoice_reason == 'adelanto')
        $invoice->flags = '00010100';
      elseif ($invoice_reason == 'avance')
        $invoice->flags = '00010010';
      elseif ($invoice_reason == 'final')
        $invoice->flags = '00010001';
      else {
        Session::flash('message', "Seleccione el motivo de la factura de la lista!");
        return redirect()->back()->withInput();
      }
      */

      if ($invoice->concept != 'Adelanto' && $invoice->oc->executed_amount == 0) {
        Session::flash('message', "Debe especificar el monto ejecutado de la OC si la factura no es por adelanto! 
            Por favor cargue un certificado de aceptación parcial o total");
        return redirect()->back()->withInput();
      }

          // Validación de porcentajes de pago según OC
      $percentages = explode('-', $invoice->oc->percentages);
      
      if (($invoice->concept == 'Adelanto' && $percentages[0] == 0) ||
          ($invoice->concept == 'Avance' && $percentages[1] == 0) ||
          ($invoice->concept == 'Entrega' && $percentages[2] == 0)) {
        $message = "No puede cargar una factura por ".$invoice->concept." para la OC seleccionada, revise los porcentajes de pago de la OC!";
        Session::flash('message', $message);
        return redirect()->back()->withInput();
      }

      if ($invoice->concept != 'Adelanto') {
        // Facturas en certificado, no se toma en cuenta la factura actual
        $existing_amount = 0;
        foreach ($invoice->oc_certification->invoices as $inv) {
          if ($inv->id != $invoice->id) {
            $existing_amount += $inv->amount;
          }
        }
  
        if (($existing_amount + $invoice->amount) > $invoice->oc_certification->amount) {
          Session::flash('message', "El monto indicado excede el monto disponible para la certificación seleccionada!");
          return redirect()->back()->withInput();
        }
      }

      if ($invoice->concept == 'Adelanto') {
        $count = 0;
        foreach ($invoice->oc->invoices as $inv) {
          if ($inv->concept == 'Adelanto') {
            $count++;
          }
        }
        if ($count > 1) {
          Session::flash('message', "La orden seleccionada ya tiene una factura por adelanto!");
          return redirect()->back()->withInput();
        }
      }
    }

    if ($old_invoice->number != $invoice->number) {
      $similar_invoices = Invoice::where('number',$invoice->number)->get();
      foreach ($similar_invoices as $similar_invoice) {
        if ($similar_invoice->oc->provider_id == $invoice->oc->provider_id) {
          Session::flash('message', "El proveedor de ésta OC ya tiene registrada una factura con el mismo número!");
          return redirect()->back()->withInput();
        }
      }
    }

    /* Set digits of approval (obsolete: invoices no longer request approval)
    if(($user->priv_level==3&&$user->area=='Gerencia General')||$user->priv_level==4){
        if($invoice->flags[1]==0)
            $invoice->flags = str_pad($invoice->flags+1000000, 8, "0", STR_PAD_LEFT);
        if($invoice->flags[2]==0)
            $invoice->flags = str_pad($invoice->flags+100000, 8, "0", STR_PAD_LEFT);
    }
    elseif($user->priv_level==3&&$user->area=='Gerencia Tecnica'){
        if($invoice->flags[2]==0)
            $invoice->flags = str_pad($invoice->flags+100000, 8, "0", STR_PAD_LEFT);
    }
    */

    $invoice->save();

    Session::flash('message', "Factura actualizada correctamente");
    if (Session::has('url'))
      return redirect(Session::get('url'));
    else
      return redirect()->route('invoice.index');
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

  public function payment_form($id)
  {
    $user = Session::get('user');
    if ((is_null($user)) || (!$user->id))
      return redirect()->route('root');

    $service = Session::get('service');

    $invoice = Invoice::find($id);
    
    if (!$invoice) {
      Session::flash('message', 'Sucedió un error al recuperar la información del servidor. Revise la dirección e
        intente de nuevo por favor');
      return redirect()->back();
    }

    $invoice->date_issued = Carbon::parse($invoice->date_issued)->format('Y-m-d');
    $invoice->transaction_date = Carbon::parse($invoice->transaction_date)->format('Y-m-d');

    return View::make('app.invoice_payment_form', ['invoice' => $invoice, 'service' => $service, 'user' => $user]);
  }

  public function record_payment(Request $request, $id)
  {
    $user = Session::get('user');
    if ((is_null($user)) || (!$user->id))
      return redirect()->route('root');

    $v = \Validator::make(Request::all(), [
        'transaction_code'        => 'required',
        'transaction_date'        => 'required', //required_with:transaction_code
    ],
        [
          'transaction_code.required' => 'Debe especificar el código de transacción como evidencia del pago',
          'transaction_date.required' => 'Debe especificar la fecha en que se realizó la transacción!',
        ]
    );

    if ($v->fails()) {
      Session::flash('message', $v->messages()->first());
      return redirect()->back()->withInput();
    }

    // TODO duplicate value without reference
    $old_invoice = Invoice::find($id);
    $invoice = Invoice::find($id);

    $invoice->fill(Request::all());
    $invoice->detail = $old_invoice->detail.'<br>'.Request::input('detail');

    /* Set digits of payment */
    // if ($invoice->flags[0] == 0)
    //  $invoice->flags = str_pad($invoice->flags+10000000, 8, "0", STR_PAD_LEFT);
    $invoice->status = 'Pagado';

    $oc = OC::find($invoice->oc_id);

    if ($invoice->concept == 'Adelanto') {
      //if ($oc->flags[5] == 0)
      //  $oc->flags = str_pad($oc->flags+100, 8, "0", STR_PAD_LEFT);
      $oc->payment_status = 'Adelanto';
      $oc->executed_amount += $invoice->amount;
    } elseif ($invoice->concept == 'Avance') {
      //if ($oc->flags[6] == 0)
      //  $oc->flags = str_pad($oc->flags+10, 8, "0", STR_PAD_LEFT);
      $oc->payment_status = 'Avance';
    } elseif ($invoice->concept == 'Entrega') {
      //if ($oc->flags[7] == 0)
      //  $oc->flags = str_pad($oc->flags+1, 8, "0", STR_PAD_LEFT);
      $oc->payment_status = 'Concluido';
    }

    $oc->payed_amount += $invoice->amount;

    $oc->save();
    $invoice->save();

    foreach ($invoice->files as $file) {
      $this->blockFile($file);
    }

    // Add payment event
    $this->add_event('payment', $invoice, '');

    Session::flash('message', "Se registró el pago de la factura $invoice->number correctamente");
    if (Session::has('url'))
      return redirect(Session::get('url'));
    else
      return redirect()->route('invoice.index');
  }

  // Obsolete: invoices no longer require approval
  /*
  public function approve_invoice_form()
  {
      $user = Session::get('user');
      if ((is_null($user)) || (!$user->id))
          return redirect()->route('root');

      $service = Session::get('service');

      if($user->priv_level==4){
          $invoices = Invoice::where('flags', 'like', '00%')->orderBy('id', 'desc')->get();
      }
      elseif ($user->priv_level==3&&$user->area=='Gerencia General') {
          $invoices = Invoice::where('flags', 'like', '0011%')->orderBy('id', 'desc')->get();
      }
      elseif($user->priv_level==3&&$user->area=='Gerencia Tecnica'){
          $invoices = Invoice::where('flags', 'like', '0001%')->orderBy('id', 'desc')->get();
      }
      else {
          $invoices = 0;
      }

      return View::make('app.invoice_approve_form', ['invoices' => $invoices, 'service' => $service, 'user' => $user]);
  }

  public function approve_invoice(Request $request)
  {
      $user = Session::get('user');
      if ((is_null($user)) || (!$user->id))
          return redirect()->route('root');
      
      //$service = Session::get('service');

      if (!Hash::check(Request::input('password'), $user->password)){
          Session::flash('message', "Contraseña incorrecta, intente de nuevo por favor");
          return redirect()->back();
      }

      $results = Request::all();
      $count = Request::input('count');

      $num_approved = 0;

      $approved = array(); //$approved = "";

      for($i=0;$i<$count;$i++){
          if(!empty($results[$i])){
              $invoice = Invoice::find($results[$i]);

              if($user->priv_level==4||($user->priv_level==3&&$user->area=='Gerencia General')){
                  if($invoice->flags[1]==0&&$invoice->flags[2]==0)
                      $invoice->flags = str_pad($invoice->flags+1100000, 8, "0", STR_PAD_LEFT);
                  elseif($invoice->flags[1]==0&&$invoice->flags[2]==1)
                      $invoice->flags = str_pad($invoice->flags+1000000, 8, "0", STR_PAD_LEFT);
              }
              elseif($user->priv_level==3&&$user->area=='Gerencia Tecnica')
                  $invoice->flags = str_pad($invoice->flags+100000, 8, "0", STR_PAD_LEFT);

              $invoice->save();

              $approved[] = array('number' => $invoice->number, 'provider' => $invoice->oc->provider_record->prov_name);

              //$approved .= ' '.$invoice->number; //Concatenate the numbers of all approved invoices
              $num_approved++;

              // A new event is recorded to register the invoice's approval
              if(Request::input('add_comments')==1)
                  $this->add_event('approve',$invoice,Request::input('comments'));
              else
                  $this->add_event('approve',$invoice,'');
          }
      }

      // send email notification
      if($num_approved>0){
          $this->send_notification(0, $approved);

          /* Moved to a separate function
          $mail_structure = '';
          $recipient = '';
          $subject = '';
          $data = '';

          if($user->priv_level==3&&$user->area=='Gerencia General'){
              $recipient = User::where('area','Gerencia Administrativa')->where('priv_level',3)->first();
              $data = array('recipient' => $recipient, 'approved' => $approved);

              $mail_structure = 'emails.invoice_waiting_payment';
              $subject = 'Facturas de proveedor pendientes de pago';
          }
          elseif($user->priv_level==3&&$user->area=='Gerencia Tecnica'){
              $recipient = User::where('area','Gerencia General')->where('priv_level',3)->first();
              $data = array('recipient' => $recipient, 'approved' => $approved);

              $mail_structure = 'emails.invoice_approved';
              $subject = 'Facturas de proveedor pendientes de aprobación';
          }
          
          if($mail_structure!=''){
              $view = View::make($mail_structure, $data);
              $content = (string) $view;

              $success = 1;

              try {
                  Mail::send($mail_structure, $data, function($message) use($recipient, $subject) {
                      $message->to($recipient->email, $recipient->name)
                          ->subject($subject)
                          ->from('postmaster@gerteabros.com', 'postmaster@gerteabros.com');
                  });
              } catch (Exception $ex) {
                  $success = 0;
              }

              $email = new Email;
              $email->sent_by = 'postmaster@gerteabros.com';
              $email->sent_to = $recipient->email;
              $email->subject = $subject;
              $email->content = $content;
              $email->success = $success;
              $email->save();
          }
      }

      if($num_approved==0)
          $message = "No seleccionó ninguna factura";
      elseif($num_approved==1)
          $message = "1 factura aprobada"; //"La factura $approved fue aprobada";
      else
          $message = "$num_approved facturas aprobadas"; //"Las facturas $approved fueron aprobadas";

      Session::flash('message', $message);
      return redirect()->route('invoice.index');
  }
  */

  public function add_event($type, $invoice, $comments)
  {
    $user = Session::get('user');

    $event = new Event;
    $event->user_id = $user->id;
    $event->date = Carbon::now();

    $prev_number = Event::select('number')->where('eventable_id',$invoice->id)
        ->where('eventable_type','App\Invoice')->orderBy('number','desc')->first();

    $event->number = $prev_number ? $prev_number->number + 1 : 1;

    /*
    if($type=='approve'){
        $event->description = 'Factura de proveedor aprobada';
        $event->detail = $user->name.' aprueba la factura de proveedor '.$invoice->number.'. '.$comments;
    }
    */
    if ($type == 'payment') {
      $event->description = 'Factura de proveedor pagada';
      $event->detail = $user->name.' registró el pago de la factura '.$invoice->number.
          ', transacción efectuada el '.
          Carbon::parse($invoice->transaction_date)->format('d-m-Y');
    }

    $event->responsible_id = $user->id;
    $event->eventable()->associate(Invoice::find($invoice->id));
    $event->save();
  }

  function send_notification($invoice, $approved)
  {
    //$user = Session::get('user');

    /*
    $mail_structure = '';
    $recipient = '';
    $subject = '';
    $data = '';

    if(($user->priv_level==3&&$user->area=='Gerencia General')||$user->priv_level==4){
        $recipient = User::where('area','Gerencia Administrativa')->where('priv_level',3)->first();
        $data = array('recipient' => $recipient, 'approved' => $approved); //$invoice->number);

        $mail_structure = 'emails.invoice_waiting_payment';
        $subject = 'Facturas de proveedor pendientes de pago';
    }
    elseif($user->priv_level==3&&$user->area=='Gerencia Tecnica'){
        $recipient = User::where('area','Gerencia General')->where('priv_level',3)->first();
        $data = array('recipient' => $recipient, 'approved' => $approved); //$invoice->number);

        $mail_structure = 'emails.invoice_approved';
        $subject = 'Facturas de proveedor pendientes de aprobación';
    }
    elseif($invoice){
    */
      $recipient = User::where('area','Gerencia Administrativa')->where('priv_level',3)->first();
      //$recipient = User::where('area','Gerencia Tecnica')->where('priv_level',3)->first();
      $data = array('recipient' => $recipient, 'invoice' => $invoice);

      $email_copies = User::select('email')->whereHas('action', function ($query) {
          $query->where('oc_inv_pmt', 1);
          })->get();

      $cc = array();

      foreach ($email_copies as $copy) {
        if ($copy->email != '')
          $cc[] = $copy->email;
      }

      $mail_structure = 'emails.invoice_added';
      $subject = 'Nueva factura de proveedor agregada al sistema';
    //}

    if ($mail_structure != '') {
      $view = View::make($mail_structure, $data);
      $content = (string) $view;

      $success = 1;

      try {
        Mail::send($mail_structure, $data, function($message) use($recipient, $cc /*$user*/, $subject) {
          $message->to($recipient->email, $recipient->name)
              ->cc($cc /*$user->email, $user->name*/)
              ->subject($subject)
              ->from('postmaster@gerteabros.com', 'postmaster@gerteabros.com');
        });
      } catch (Exception $ex) {
        $success = 0;
      }

      $email = new Email;
      $email->sent_by = 'postmaster@gerteabros.com';
      $email->sent_to = $recipient->email;
      $email->sent_cc = implode(',',$cc); //$user->email;
      $email->subject = $subject;
      $email->content = $content;
      $email->success = $success;
      $email->save();
    }
  }
}
