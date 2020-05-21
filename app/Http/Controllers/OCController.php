<?php

namespace App\Http\Controllers;

use App\Assignment;
use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Session;
use View;
use Mail;
use Hash;
use Input;
use Exception;

use App\ClientSession;
use App\Email;
use App\Event;
use App\File;
use App\Invoice;
use App\OC;
use App\Provider;
use App\User;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
use App\Http\Traits\FilesTrait;
use App\Http\Traits\ProviderTrait;
use App\Http\Traits\UserTrait;

class OCController extends Controller
{
  use FilesTrait;
  use ProviderTrait;
  use UserTrait;
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
      //return redirect()->route('root');
    }
    if ($user->acc_oc == 0)
      return redirect()->action('LoginController@logout', ['service' => 'oc']);

    Session::put('service', 'oc');
    $service = Session::get('service');
      
    $this->trackService($user, $service);

    //if($user->priv_level>=3||$user->area=='Gerencia Tecnica'||$user->area=='Gerencia General')
    if ($user->priv_level >= 2 || ($user->priv_level == 1 && $user->area == 'Gerencia General')) {
      $ocs = OC::where('id', '>', 0)->orderBy('id', 'desc')->paginate(20);
      $db_query = OC::where('id', '>', 0)->orderBy('id', 'desc')->get();
    } else {
      $ocs = OC::where('user_id',$user->id)->orwhere('pm_id',$user->id)->orderBy('id','desc')->paginate(20);
      $db_query = OC::where('user_id',$user->id)->orwhere('pm_id',$user->id)->orderBy('id', 'desc')->get();
      /*
      $files = File::join('ocs', 'files.imageable_id', '=', 'ocs.id')
          ->select('files.id', 'files.name', 'files.path', 'files.type', 'files.size', 'files.imageable_id')
          ->where('user_id', $user->id)->orwhere('pm_id', '=', $user->id)
          ->where('imageable_type', 'App\OC')
          ->get();
      */
    }

    $ocs_waiting_approval = 0;

    $pending_ocs = OC::where('status','<>','Anulado')->where('payment_status','<>','Concluido')
                      ->where('status', '<>', 'Rechazada')->get();
    foreach ($pending_ocs as $oc) {
      if ((($oc->status == 'Aprobado Gerencia Tecnica' || $oc->status == 'Creado') && $user->action->oc_apv_gg /*$user->priv_level==4*/) ||
        ($oc->status == 'Creado' && $user->action->apv_tech /*$user->area=='Gerencia Tecnica'&&$user->priv_level==3*/) ||
        ($oc->status == 'Aprobado Gerencia Tecnica' && $user->action->apv_gg /*$user->area=='Gerencia General'&&$user->priv_level==3*/))
        $ocs_waiting_approval++;
    }

    if ($user->priv_level == 4)
      $rejected_ocs = OC::where('status', 'Rechazada')->count();
    else
      $rejected_ocs = OC::where('status', 'Rechazada')->where('user_id', $user->id)->count();

    $inv_waiting_approval = 0;

    /*
    $invoices = Invoice::where('flags','like','0%')->get();
    foreach($invoices as $invoice){
        if((($invoice->flags[1]==0||$invoice->flags[2]==0)&&$user->priv_level==4)||
            ($invoice->flags[2]==0&&$user->area=='Gerencia Tecnica'&&$user->priv_level==3)||
            ($invoice->flags[1]==0&&$invoice->flags[2]==1&&$user->area=='Gerencia General'&&$user->priv_level==3))
            $inv_waiting_approval++;
    }
    */

    // $incomplete_providers = Provider::whereNull('specialty')->orwhere('prov_name','')->orwhere('nit','=',0)->orwhere('phone_number','=',0)->orwhere('address','=','')->orwhere('bnk_account','=','')->orwhere('bnk_name','=','')->orwhere('contact_name','=','')->orwhere('contact_id','=',0)->orwhere('contact_id_place','=','')->orwhere('contact_phone','=',0)->count();
    $incomplete_providers = $this->incompleteProviderRecords()->count();

    //$incomplete_providers = $providers->count();

    Session::put('db_query', $db_query);

    return View::make('app.oc_brief', ['ocs' => $ocs, 'ocs_waiting_approval' => $ocs_waiting_approval,
        'inv_waiting_approval' => $inv_waiting_approval, 'incomplete_providers' => $incomplete_providers,
        'rejected_ocs' => $rejected_ocs, 'service' => $service, 'user' => $user /*'files' => $files,*/ ]);
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
    $action = Input::get('action');

    $last_stat = count(Assignment::$status_names) -1;

    $assignments = Assignment::select('id','name', 'literal_code')->whereNotIn('status',[0,$last_stat])->get();
    //$projects = OC::select('proy_name')->where('proy_name', '<>', '')->groupBy('proy_name')->get();
    $clients = OC::select('client')->where('client', '<>', '')->groupBy('client')->get();

    $pm_candidates = User::select('id', 'name')->where('status', 'Activo')->OrderBy('name')->get();

    /*
    $providers = Provider::select('id','prov_name')->where('prov_name','<>','')->where('nit','<>',0)->whereNotNull('specialty')
        ->where('phone_number','<>',0)->where('address','<>','')->where('bnk_account','<>','')->where('bnk_name','<>','')
        ->where('contact_name','<>','')->where('contact_id','<>',0)->where('contact_id_place','<>','')
        ->where('contact_phone','<>',0)->OrderBy('prov_name')->get();
        */
          
    $providers = $this->validProviderRecords();

    // $percentages = OC::select('percentages')->where('percentages', '<>', '')->groupBy('percentages')->get();
    $percentages = OC::select('percentages')->whereNotIn('percentages', ['', '100-0-0', '0-20-80'])->groupBy('percentages')->get();

    return View::make('app.oc_form', ['oc' => 0, 'assignments' => $assignments, 'clients' => $clients,
        'pm_candidates' => $pm_candidates, 'providers' => $providers, 'percentages' => $percentages, 
        'action' => $action, 'service' =>$service, 'user' => $user]);
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $user = Session::get('user');
    if ((is_null($user)) || (!$user->id))
      return redirect()->route('root');

    //Retrieve the value of the hidden field
    if(isset($_POST["oc_token"]))
      $secret = $_POST["oc_token"];
    else {
      Session::flash('message', 'Oops! Sucedió un error al enviar el formulario, intente de nuevo por favor');
      return redirect()->route('oc.create');
    }

    $session_token = Session::get('oc_token');

    $store = $this->check_oc_token($secret, $session_token);

    $oc = new OC(Request::all());

    $action = Request::input('action');

    /* Insert a complementary OC */
    if ($action == 'cmp') {
      return $this->insert_complementary($oc);
    }

    $v = \Validator::make(Request::all(), [
        'assignment_id'           => 'required|exists:assignments,id',
        //'proy_name'               => 'required',
        //'other_proy_name'         => 'required_if:proy_name,Otro',
        'proy_concept'            => 'required',
        'provider_id'             => 'required',
        'type'                    => 'required',
        'oc_amount'               => 'required',
        'percentages'             => 'required',
        'other_percentages'       => 'required_if:percentages,Otro|regex:[^(\d{1,3})-(\d{1,3})-(\d{1,3})$]',
        'client'                  => 'required',
        'other_client'            => 'required_if:client,Otro',
    ],
        [
            'assignment_id.required'          => 'Debe especificar un nombre de proyecto!',
            'assignment_id.exists'            => 'El proyecto seleccionado no ha sido encontrado en el sistema!',
            //'proy_name.required'              => 'Debe especificar un nombre de proyecto!',
            //'other_proy_name.required_if'     => 'Debe especificar un proyecto!',
            'proy_concept.required'           => 'Debe especificar el concepto de la OC!',
            'provider_id.required'            => 'Debe especificar el proveedor para la OC!',
            'type.required'                   => 'Debe especificar el tipo de orden!',
            'oc_amount.required'              => 'Debe especificar un monto para la OC!',
            'percentages.required'            => 'Debe especificar los porcentajes de pago!',
            'other_percentages.required_if'   => 'Debe especificar los porcentajes de pago!',
            'other_percentages.regex'         => 'El formato de porcentajes de pago debe coincidir con: xx-xx-xx',
            'client.required'                 => 'Debe especificar un cliente!',
            'other_client.required_if'        => 'Debe especificar un cliente!',
        ]
    );

    if ($v->fails()) {
      Session::flash('message', $v->messages()->first());
      return redirect()->back()->withInput();
    }

    $assignment = Assignment::find($oc->assignment_id);
    $oc->proy_name = $assignment->name;

    //$oc->proy_name = $oc->proy_name=="Otro" ? Request::input('other_proy_name') : $oc->proy_name;
    $oc->client = $oc->client == "Otro" ? Request::input('other_client') : $oc->client;
      
    if ($oc->oc_amount <= 0) {
      Session::flash('message', "El monto asignado a la OC debe ser mayor a 0!");
      return redirect()->back()->withInput();
    }

    $oc->percentages = $oc->percentages=="Otro" ? Request::input('other_percentages') : $oc->percentages;
      
    if (!empty($oc->percentages)) {
      $exploded_percentages = explode('-', $oc->percentages);
      if (($exploded_percentages[0] + $exploded_percentages[1] + $exploded_percentages[2]) != 100) {
        Session::flash('message', "Los porcentajes de pago deben sumar 100%!");
        return redirect()->back()->withInput();
      }
    }

    $provider = Provider::find($oc->provider_id);
    $oc->provider = $provider->prov_name;
      
    $oc->user_id = $user->id;

    if ($user->action->oc_apv_gg /*($user->priv_level==3&&$user->area=='Gerencia General')||$user->priv_level==4*/) {
      $oc->status = 'Aprobado Gerencia General';
      $oc->auth_tec_date = Carbon::now();
      $oc->auth_tec_code = $this->generateCode();
      $oc->auth_ceo_date = Carbon::now();
      $oc->auth_ceo_code = $this->generateCode();
    } elseif ($user->action->oc_apv_tech /*$user->priv_level==3&&$user->area=='Gerencia Tecnica'*/) {
      $oc->status = 'Aprobado Gerencia Tecnica';
      $oc->auth_tec_date = Carbon::now();
      $oc->auth_tec_code = $this->generateCode();
    } else
      $oc->status = 'Creado';

    /*
    $session_user = User::where('id', $user->id)->first();
    $date = Carbon::now()->format('Y');
    */

    if ($store) {
      $oc->save();

      $this->fill_code_column(); //Fill records' codes where empty

      /* Send a notification to inform on the creation of the OC */
      $this->send_email_notification($oc, 'new');
    }

    Session::flash('message', "La Orden de Compra fue agregada al sistema correctamente");
    if(Session::has('url'))
      return redirect(Session::get('url'));
    else
      return redirect()->route('oc.index');
  }

  /**
   * Display the specified resource.
   *
   * @param  int $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    $user = Session::get('user');
    if ((is_null($user)) || (!$user->id))
      return redirect()->route('root');

    $service = Session::get('service');
    $oc = OC::find($id);

    $oc->percentages = str_replace('-','% - ',$oc->percentages).'%';
    $exploded_percentages = explode('-', $oc->percentages);

    /*
    if(!empty($oc->percentages)){
        $percentages = explode('-',$oc->percentages);
        $oc->percentages = $percentages[0].'% - '.$percentages[1].'% - '.$percentages[2].'%';
    }
    */

    foreach ($oc->invoices as $invoice) {
      $invoice->updated_at = Carbon::parse($invoice->updated_at)->hour(0)->minute(0)->second(0);
    }

    /* old variables moved to OC class
    $file_org = File::where('imageable_id',$oc->id)->where('imageable_type','App\OC')
        ->where('name', 'like', 'oc_'.$oc->id.'_org%')->first();

    $file_sgn = File::where('imageable_id',$oc->id)->where('imageable_type','App\OC')
        ->where('name','oc_'.$oc->id.'_sgn.pdf')->first();

    $provider_id = Provider::where('prov_name',$oc->provider)->first();
    $pm_name = empty($oc->pm_id) ? '' : User::find($oc->pm_id)->name;
    */

    return View::make('app.oc_info', ['oc' => $oc, 'exploded_percentages' => $exploded_percentages, 'service' => $service, 'user' => $user]);
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int $id
   * @return \Illuminate\Http\Response
   */
  public function edit($id)
  {
    $user = Session::get('user');
    if ((is_null($user)) || (!$user->id))
      return redirect()->route('root');

    $service = Session::get('service');

    $action = input::get('action');

    $oc = OC::find($id);

    $last_stat = count(Assignment::$status_names) -1;

    $assignments = Assignment::select('id','name')->whereNotIn('status',[0,$last_stat])->get();
    //$projects = OC::select('proy_name')->where('proy_name', '<>', '')->groupBy('proy_name')->get();
    $clients = OC::select('client')->where('client', '<>', '')->groupBy('client')->get();

    $pm_candidates = User::select('id', 'name')->where('status', 'Activo')->OrderBy('name')->get();

    /*
    $providers = Provider::select('id','prov_name')->where('nit','<>',0)->whereNotNull('specialty')->where('phone_number','<>',0)
        ->where('address','<>','')->where('bnk_account','<>','')->where('bnk_name','<>','')
        ->where('contact_name','<>','')->where('contact_id','<>',0)
        ->where('contact_id_place','<>','')->where('contact_phone','<>',0)
        ->OrderBy('prov_name')->get();
        */
          
    $providers = $this->validProviderRecords();

    // $percentages = OC::select('percentages')->where('percentages', '<>', '')->groupBy('percentages')->get();
    $percentages = OC::select('percentages')->whereNotIn('percentages', ['', '100-0-0', '0-20-80'])->groupBy('percentages')->get();

    return View::make('app.oc_form', ['oc' => $oc, 'assignments' => $assignments, 'clients' => $clients,
        'providers' => $providers, 'pm_candidates' => $pm_candidates, 'percentages' => $percentages,
        'action' => $action, /*'action' => 0, */'service' => $service, 'user' => $user]);
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request $request
   * @param  int $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id)
  {
    $user = Session::get('user');
    if ((is_null($user)) || (!$user->id))
      return redirect()->route('root');

    $action = Request::input('action');

    $oc = OC::find($id);
    $status_flag = $oc->status;

    if ($oc->status != 'Anulado') {
      $v = \Validator::make(Request::all(), [
          'assignment_id'      => 'required|exists:assignments,id',
          //'proy_name'          => 'required',
          //'other_proy_name'    => 'required_if:proy_name,Otro',
          'proy_concept'       => 'required',
          'type'               => 'required',
          'oc_amount'          => 'required',
          'executed_amount'    => 'numeric',
          'other_percentages'  => 'regex:[^(\d{1,3})-(\d{1,3})-(\d{1,3})$]',
          'client'             => 'required',
          'other_client'       => 'required_if:client,Otro',
      ],
          [
              'assignment_id.required'          => 'Debe especificar un nombre de proyecto!',
              'assignment_id.exists'            => 'El proyecto seleccionado no ha sido encontrado en el sistema!',
              //'proy_name.required'           => 'Debe especificar un nombre de proyecto!',
              //'other_proy_name.required_if'  => 'Debe especificar un proyecto!',
              'proy_concept.required'        => 'Debe especificar el concepto de la OC!',
              'type.required'                => 'Debe especificar el tipo de orden!',
              'oc_amount.required'           => 'Debe especificar un monto para la OC!',
              'executed_amount.numeric'      => 'El monto ejecutado debe contener sólo números',
              'other_percentages.regex'      => 'El formato de porcentajes de pago debe coincidir con: xx-xx-xx',
              'client.required'              => 'Debe especificar un cliente!',
              'other_client.required_if'     => 'Debe especificar un cliente!',
          ]
      );

      if ($v->fails()) {
        Session::flash('message', $v->messages()->first());
        return redirect()->back()->withInput();
      }
    }

    $oc->fill(Request::all());

    $assignment = Assignment::find($oc->assignment_id);
    $oc->proy_name = $assignment->name;

    //$oc->proy_name = $oc->proy_name=="Otro" ? Request::input('other_proy_name') : $oc->proy_name;
    $oc->client = $oc->client == "Otro" ? Request::input('other_client') : $oc->client;
      
    if ($oc->oc_amount <= 0) {
      Session::flash('message', "El monto asignado a la OC debe ser mayor a 0!");
      return redirect()->back()->withInput();
    }
      
    if ($status_flag != 'Anulado') {
      if ($oc->percentages == "Otro" || $oc->percentages == "") {
        $oc->percentages = Request::input('other_percentages');
        if ($oc->percentages == "") {
          Session::flash('message', "Debe especificar los porcentajes de pago!");
          return redirect()->back()->withInput();
        } else {
          $exploded_percentages = explode('-', $oc->percentages);
          if (($exploded_percentages[0] + $exploded_percentages[1] + $exploded_percentages[2]) != 100) {
            Session::flash('message', "Los porcentajes de pago deben sumar 100%!");
            return redirect()->back()->withInput();
          }
        }
      }
    }

    $provider = Provider::find($oc->provider_id);
    $oc->provider = $provider ? $provider->prov_name : 'N/E';

    if ($oc->status == 'Anulado') {
      foreach ($oc->files as $file) {
        $this->blockFile($file);
      }
    } elseif ($oc->status != 'Anulado' && $status_flag == 'Anulado') {
      $oc->status = 'Creado';
      
      foreach ($oc->files as $file) {
        $this->unblockFile($file);
      }
    }

    if ($action == 'reject_disable') {
      $oc->status = 'Creado';
      $oc->observations = '';
    }
      
    $oc->save();

    Session::flash('message', "Datos actualizados correctamente");
    if(Session::has('url'))
      return redirect(Session::get('url'));
    else
      return redirect()->route('oc.index');
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    Session::flash('message', "Esta función esta deshabilitada, intente anular la OC");
    return redirect()->route('oc.index');
  }

  public function approve_form()
  {
    $user = Session::get('user');
    if ((is_null($user)) || (!$user->id)) {
      // $ref = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
      $ref = $_SERVER['REQUEST_URI'];
      Session::put('url.intended', $ref);
      return redirect()->route('root');
    }

    $service = Session::get('service');
      
    // To stress an OC of origin
    $oc_code = Input::get('code');

    if ($user->priv_level == 4) {
      $ocs = OC::whereIn('status', ['Aprobado Gerencia Tecnica', 'Creado'])->where('status', '<>', 'Rechazada')->orderBy('id', 'desc')->get();
    } elseif ($user->action->oc_apv_gg /*$user->priv_level==3&&$user->area=='Gerencia General'*/) {
      $ocs = OC::where('status', 'Aprobado Gerencia Tecnica')->where('status', '<>', 'Rechazada')->orderBy('id', 'desc')->get();
    } elseif ($user->action->oc_apv_tech /*$user->priv_level==3&&$user->area=='Gerencia Tecnica'*/) {
      $ocs = OC::where('status', 'Creado')->where('status', '<>', 'Rechazada')->orderBy('id', 'desc')->get();
    } else {
      Session::flash('message', 'Usted no tiene permiso para ver la página solicitada!');
      return redirect()->back();
    }

    return View::make('app.oc_approve_form', ['ocs' => $ocs, 'service' => $service, 'user' => $user, 'oc_code' => $oc_code]);
  }
  
  public function approve_action(Request $request)
  {
    $user = Session::get('user');
    if ((is_null($user)) || (!$user->id))
      return redirect()->route('root');

    //$service = Session::get('service');

    if (!Hash::check(Request::input('password'), $user->password)) {
      Session::flash('message', "Contraseña incorrecta, intente de nuevo por favor");
      return redirect()->back();
    }
      
    $results = Request::all();
    $count = $results['count']; //Request::input('count');
    $comments = Request::input('add_comments') == 1 ? Request::input('comments') : '';

    $num_approved = 0;
    $approved = "";

    for ($i = 0; $i < $count; $i++) {
      if (!empty($results[$i])) {
        $oc = OC::find($results[$i]);

        if ($user->priv_level == 4 || $user->action->oc_apv_gg /*($user->priv_level==3&&$user->area=='Gerencia General')*/) {
          /*
          if ($oc->flags[1] == 0 && $oc->flags[2] == 0)
              $oc->flags = str_pad($oc->flags+1100000, 8, "0", STR_PAD_LEFT);
          elseif ($oc->flags[1] == 0 && $oc->flags[2] == 1)
              $oc->flags = str_pad($oc->flags+1000000, 8, "0", STR_PAD_LEFT);
              */
          $oc->status = 'Aprobado Gerencia General';

          foreach ($oc->files as $file) {
            $file->status = 1;
            $file->save();
          }

          $oc->auth_ceo_date = Carbon::now();
          $oc->auth_ceo_code = $this->generateCode();
        } elseif ($user->action->oc_apv_tech /*$user->priv_level==3&&$user->area=='Gerencia Tecnica'*/) {
          // $oc->flags = str_pad($oc->flags+100000, 8, "0", STR_PAD_LEFT);
          $oc->status = 'Aprobado Gerencia Tecnica';

          $oc->auth_tec_date = Carbon::now();
          $oc->auth_tec_code = $this->generateCode();
        }

        $oc->save();
        $approved .= ($approved == "" ? '' : ', ').$oc->code;
        $num_approved++;

        /* A new event is recorded to register the approval of the OC */
        $this->add_event('approve', $oc, $comments);
      }
    }

    /* Send notification to inform on the approval of one or more OCs */
    if ($num_approved > 0) {
      if ($user->action->oc_apv_tech /*$user->priv_level==3&&$user->area=='Gerencia Tecnica'*/) {
        $this->send_email_notification($approved, 'approved');
      }
    }

    if ($num_approved == 0)
      $message = "No seleccionó ninguna Orden";
    elseif ($num_approved == 1)
      $message = "La orden $approved ha sido aprobada";
    else
      $message = "Las ordenes $approved han sido aprobadas";

    Session::flash('message', $message);
    if (Session::has('url'))
      return redirect(Session::get('url'));
    else
      return redirect()->route('oc.index');
  }

  public function cancel_form($id)
  {
    $user = Session::get('user');
    if ((is_null($user)) || (!$user->id))
      return redirect()->route('root');

    $service = Session::get('service');

    $oc = OC::find($id);
    
    $action = 'anular';

    return View::make('app.oc_form', ['oc' => $oc, 'proyectos' => 0, 'clients' => 0, 'providers' => 0,
        'pm_candidates' => 0, 'percentages' => 0, 'action' => $action, 'service' => $service, 'user' => $user]);
  }

  public function cancel_oc(Request $request, $id)
  {
    $user = Session::get('user');
    if ((is_null($user)) || (!$user->id))
      return redirect()->route('root');

    $oc = OC::find($id);

    $v = \Validator::make(Request::all(), [
        'observations'       => 'required',
    ],
        [
            'observations.required'      => 'Debe especificar el motivo para anular la OC!',
        ]
    );

    if ($v->fails()) {
      Session::flash('message', $v->messages()->first());
      return redirect()->back();
    }

    $oc->fill(Request::all());

    $oc->status = 'Anulado';

    foreach ($oc->files as $file) {
      $file->status = 1; //Block file modifications
      $file->save();
    }

    $oc->save();

    /* A new event is recorded to register the cancellation of the OC */
    $this->add_event('cancel', $oc, '');

    Session::flash('message', "La Orden de Compra ha sido anulada");
    if (Session::has('url'))
      return redirect(Session::get('url'));
    else
      return redirect()->route('oc.index');
    // return redirect()->action('OCController@show', ['id' => $id]);
  }

  public function reject_form() {
    $user = Session::get('user');
    if ((is_null($user)) || (!$user->id))
      return redirect()->route('root');

    $id = Input::get('id');

    $service = Session::get('service');

    $oc = OC::find($id);

    $action = 'reject';

    return View::make('app.oc_form', ['oc' => $oc, 'proyectos' => 0, 'clients' => 0, 'providers' => 0,
        'pm_candidates' => 0, 'percentages' => 0, 'action' => $action, 'service' => $service, 'user' => $user]);
  }

  public function reject_oc(Request $request) {
    $user = Session::get('user');
    if ((is_null($user)) || (!$user->id))
      return redirect()->route('root');

    $v = \Validator::make(Request::all(), [
        'observations'       => 'required',
    ],
      [ 'observations.required'      => 'Debe especificar el motivo para rechazar la OC!' ]
    );

    if ($v->fails()) {
      Session::flash('message', $v->messages()->first());
      return redirect()->back()->withInput();
    }

    $id = Request::input('id');

    $oc = OC::find($id);

    $oc->observations = Request::input('observations');
    $oc->status = 'Rechazada';

    $oc->save();

    /* Send a notification to the OC's creator */
    $this->send_email_notification($oc, 'rejected');

    /* An event is recorded to register the rejection of the OC */
    $this->add_event('reject',$oc,'');

    Session::flash('message', "La Orden de Compra $oc->code ha sido rechazada");
    if (Session::has('url'))
      return redirect(Session::get('url'));
    else
      return redirect()->route('oc.index');
  }

  public function rejected_ocs_list()
  {
    $user = Session::get('user');
    if ((is_null($user)) || (!$user->id))
      return redirect()->route('root');

    $service = Session::get('service');
      
    if ($user->priv_level == 4) {
      $ocs = OC::where('status', 'Rechazada')->orderBy('id', 'desc')->get();
    } else {
      $ocs = OC::where('status', 'Rechazada')->where('user_id', $user->id)->orderBy('id', 'desc')->get();
    }

    return View::make('app.oc_rejected_list', ['ocs' => $ocs, 'service' => $service, 'user' => $user]);
  }
  
  public function insert_complementary($oc)
  {
    $user = Session::get('user');

    $oc_array = array();
    $oc_array['link_id'] = $oc->link_id;
    $oc_array['oc_amount'] = $oc->oc_amount;

    $v = \Validator::make($oc_array, [
        'link_id'                 => 'required|exists:o_c_s,id',
        'oc_amount'               => 'required',
    ],
        [
            'link_id.required'                => 'Debe especificar la OC a la que se relacionará!',
            'link_id.exists'                  => 'La OC indicada no existe en el sistema!',
            'oc_amount.required'              => 'Debe especificar un monto para la OC!',
        ]
    );

    if ($v->fails()) {
      Session::flash('message', $v->messages()->first());
      return redirect()->back()->withInput();
    }

    $link_data = OC::find($oc->link_id);

    $oc->pm_id = $link_data->pm_id;
    $oc->provider_id = $link_data->provider_id;
    $oc->provider = $link_data->provider;
    $oc->assignment_id = $link_data->assignment_id;
    $oc->proy_name = $link_data->proy_name;
    $oc->proy_concept = $link_data->proy_concept;
    $oc->proy_description = $link_data->proy_description;
    $oc->percentages = $link_data->percentages;
    $oc->client = $link_data->client;
    $oc->client_oc = $link_data->client_oc;
    $oc->client_ad = $link_data->client_ad;
    $oc->delivery_place = $link_data->delivery_place;
    $oc->delivery_term = $link_data->delivery_term;
    $oc->observations = 'Orden complementaria a '.$link_data->code;
    $oc->user_id = $user->id;

    if ($user->action->oc_apv_gg /*($user->priv_level==3&&$user->area=='Gerencia General')*/ || $user->priv_level == 4) {
      $oc->status = 'Aprobado Gerencia General';
      $oc->auth_tec_date = Carbon::now();
      $oc->auth_tec_code = $this->generateCode();
      $oc->auth_ceo_date = Carbon::now();
      $oc->auth_ceo_code = $this->generateCode();
    } elseif ($user->action->oc_apv_tech /*$user->priv_level==3&&$user->area=='Gerencia Tecnica'*/) {
      $oc->status = 'Aprobado Gerencia Tecnica';
      $oc->auth_tec_date = Carbon::now();
      $oc->auth_tec_code = $this->generateCode();
    } else {
      $oc->status = 'Creado';
    }

    $oc->save();

    $this->fill_code_column(); //Fill records' codes where empty

    /* Send notification to inform on the creation of the complementary OC */
    $this->send_email_notification($oc, 'new');

    Session::flash('message', "La Orden de Compra fue agregada al sistema correctamente");
    if (Session::has('url'))
      return redirect(Session::get('url'));
    else
      return redirect()->route('oc.index');
  }

  public function add_event($type, $oc, $comments)
  {
    $user = Session::get('user');

    $event = new Event;
    $event->user_id = $user->id;
    $event->date = Carbon::now();

    $prev_number = Event::select('number')->where('eventable_id',$oc->id)
        ->where('eventable_type','App\OC')->orderBy('number','desc')->first();

    $event->number = $prev_number ? $prev_number->number+1 : 1;

    if ($type == 'approve') {
      $event->description = 'Orden aprobada';
      $event->detail = $user->name.' aprueba la orden de compra '.$oc->code.'. '.$comments;
    } elseif ($type == 'cancel') {
      $event->description = 'Orden anulada';
      $event->detail = $user->name.' anula la orden de compra '.$oc->code;
    } elseif ($type == 'reject') {
      $event->description = 'Orden rechazada';
      $event->detail = $user->name.' rechazó la orden de compra '.$oc->code.' por motivo de: '.$oc->observations;
    }

    $event->responsible_id = $user->id;
    $event->eventable()->associate($oc/*OC::find($oc->id)*/);
    $event->save();
  }

  public function generateCode()
  {
    //A string with all the possible characters
    $str = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
    //The string's length
    $length = strlen($str);

    //variable to store the code
    $code = "";
    //define the length of the code
    $lengthCode = 10;

    for ($i = 1; $i <= $lengthCode ; $i++) {
      //generate a random number indicating the position of the character on the string from 0 to length-1
      $pos = rand(0,$length-1);

      //put the character obtained on the code variable
      $code .= substr($str,$pos,1);
    }

    return $code;
  }

  public function fill_code_column()
  {
    $ocs = OC::where('code','')->get();

    foreach ($ocs as $oc) {
      $oc->code = 'OC-'.str_pad($oc->id, 5, "0", STR_PAD_LEFT);
      $oc->save();
    }
  }

  function send_email_notification($oc, $type)
  {
    $user = Session::get('user');
    $mail_structure = '';
    $subject = '';
    $data = array();
    $recipient = $user; //Temporary assigned to avoid empty collection errors
    $cc = '';

    if ($type == 'new' && $oc->status == 'Aprobado Gerencia Tecnica') {
      $oc = OC::find($oc->id); //Retrieve code

      //$recipient = User::where('area','Gerencia General')->where('priv_level',3)->first();
      $recipient = User::whereHas('action', function ($query) {
          $query->where('oc_apv_gg', 1);
      })->where('name', '<>', 'Administrador')->first();
      //->where('priv_level','<','4')->first();

      $cc = $user->email;
      $approved = $oc->code; //'OC-'.str_pad($oc->id, 5, "0", STR_PAD_LEFT);
      $data = array('recipient' => $recipient, 'approved' => $approved);

      $mail_structure = 'emails.oc_approved';
      $subject = 'Ordenes de compra pendientes de aprobación';
    } elseif ($type == 'new' && $oc->status == 'Creado') {
      //$recipient = User::where('area','Gerencia Tecnica')->where('priv_level',3)->first();
      $recipient = User::whereHas('action', function ($query) {
          $query->where('oc_apv_tech', 1);
      })->where('priv_level','<','4')->first();

      $cc = $user->email;
      $data = array('recipient' => $recipient, 'oc' => $oc);

      $mail_structure = 'emails.oc_added';
      $subject = 'Nueva órden de compra agregada al sistema';
    } elseif ($type == 'approved') {
      $approved = $oc; // In this case $oc is a string not a collection (see approve_action function)
      //$recipient = User::where('area','Gerencia General')->where('priv_level',3)->first();

      $recipient = User::whereHas('action', function ($query) {
          $query->where('oc_apv_gg', 1);
      })->where('name', '<>', 'Administrador')->first();
      //->where('priv_level','<','4')->first();

      $cc = $user->email;
      $data = array('recipient' => $recipient, 'approved' => $approved);

      $mail_structure = 'emails.oc_approved';
      $subject = 'Ordenes de compra pendientes de aprobación';
    } elseif ($type == 'rejected') {
      $recipient = $oc->user ?: $user;
      $cc = $user->email;
      $data = array('recipient' => $recipient, 'oc' => $oc, 'user' => $user);

      $mail_structure = 'emails.oc_rejected';
      $subject = 'Orden de compra rechazada';
    }

    if ($mail_structure != '') {
      // If one condition is true then send email

      $view = View::make($mail_structure, $data);
      $content = (string) $view;
      $success = 1;

      try {
        Mail::send($mail_structure, $data, function($message) use($recipient, $cc, $subject) {
          $message->to($recipient->email, $recipient->name)
              ->cc($cc)
              ->subject($subject);
          $message->from('postmaster@gerteabros.com', 'postmaster@gerteabros.com');
        });
      } catch (Exception $ex) {
        $success = 0;
      }

      $email = new Email;
      $email->sent_cc = $cc;
      $email->subject = $subject;
      $email->sent_by = 'postmaster@gerteabros.com';
      $email->sent_to = $recipient->email;
      $email->content = $content;
      $email->success = $success;
      $email->save();
    }
  }

  function check_oc_token($secret, $session_token)
  {
    Session::forget('oc_token'); //unset($_SESSION["oc_token"]);

    if ($session_token != '' && $secret != '' /*isset($_SESSION["oc_token"])*/) {
      if (strcasecmp($secret, $session_token) === 0) {
        $store = true; //new form
      } else {
        $store = false; //Invalid secret key
      }
    } else {
      $store = false; //Secret key missing
    }

    return $store;
  }
}
