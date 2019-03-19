<?php

namespace App\Http\Controllers;

// use Illuminate\Http\Request;
use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use View;
use Mail;
use Input;
use Exception;
use App\RendicionViatico;
use App\StipendRequest;
use App\Assignment;
use App\Site;
use App\User;
use App\Employee;
use App\Email;
use App\Event;
use App\ServiceParameter;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class RendicionViaticoController extends Controller
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
        return View('app.index', ['service'=>'project', 'user'=>null]);
      }
      if ($user->acc_project === 0)
        return redirect()->action('LoginController@logout', ['service' => 'project']);

      $service = Session::get('service');
      
      $rendiciones = RendicionViatico::where('id','>',0);

      if ($user->priv_level <= 1) {
        $rendiciones = $rendiciones->where(function ($query) use($user) {
            $query->where('usuario_creacion', $user->id)
                ->orwhere('usuario_modificacion', $user->id);
        });
      }

      $rendiciones = $rendiciones->orderBy('created_at', 'desc')->paginate(20);

      if ($user->priv_level >= 1) {
        $pendiente_aprobacion = RendicionViatico::where('estado', 'Presentado')->count();
        $observados = RendicionViatico::where('estado', 'Observados')->count();
      } else {
        $pendiente_aprobacion = 0;
        $observados = 0;
      }

      return View::make('app.rendicion_viatico_brief', ['rendiciones' => $rendiciones,
        'service' => $service, 'user' => $user, 'pendiente_aprobacion' => $pendiente_aprobacion,
        'observados' => $observados]);
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

      /*
      $solicitudes = StipendRequest::where('status', 'Completed')->where(function ($query) use($user) {
          $query->where('employee_id', $user->id);
      })->get();
      */

      if ($user->priv_level === 4) {
        $solicitudes = StipendRequest::where('status', 'Completed')->get();
      } else {
        // If not admin search only stipend requests corresponding to the logged user
        $solicitudes = StipendRequest::where('status', 'Completed')->where('user_id', $user->id)->get();
      }

      if (!$solicitudes || $solicitudes->count() === 0) {
        Session::flash('message', 'Usted no tiene ninguna solicitud de viáticos pendiente para rendición');
        return redirect()->back();
      }

      $ultima_rendicion = RendicionViatico::where('usuario_creacion', $user->id)->orderBy('created_at', 'desc')->first();

      $nro_rendicion = $ultima_rendicion ? $ultima_rendicion->nro_rendicion + 1 : 1;
      $tipos_gasto = ['alimentacion', 'combustible', 'comunicaciones', 'extras', 'hotel',
        'materiales', 'taxi', 'transporte'];

      return View::make('app.rendicion_viatico_form', ['rendicion' => 0, 'user' => $user,
        'service' => $service, 'solicitudes' => $solicitudes, 'nro_rendicion' => $nro_rendicion,
        'tipos_gasto' => $tipos_gasto]);
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

      $form_data = Request::all();
      // $form_data['date_to'] = $form_data['date_to'].' 23:59:59';

      $v = \Validator::make($form_data, [
        'stipend_request_id'    => 'required',
        'nro_rendicion'         => 'required',
        'fecha_deposito'        => 'required|date',
        'monto_deposito'        => 'required|numeric',
      ],
        [
          'required'                  => 'Este campo es obligatorio!',
          'date'                      => 'La fecha introducida es inválida!',
          'numeric'                   => 'Sólo puede ingresar números en este campo!',
        ]
      );

      if ($v->fails()) {
        Session::flash('message', 'Sucedió un error al enviar el formulario!');
        return redirect()->back()->withErrors($v)->withInput();
      }

      /*
      $stipend_request_id = Request::input('stipend_request_id');

      $stipend_request = StipendRequest::find($stipend_request_id);
      */

      $rendicion = new RendicionViatico(Request::all());

      $stipend_request = $rendicion->solicitud;

      if (!$stipend_request) {
        Session::flash('message', 'Error al cargar la información de la solicitud de viáticos, intente reenviar el formulario por favor');
        return redirect()->back()->withInput();
      }

      if ($user->priv_level < 4 && $user->id != $stipend_request->user_id) {
        Session::flash('message', 'Usted no tiene permisos para crear una rendición para la solicitud seleccionada');
        return redirect()->back()->withInput();
      }

      $rendicion->fecha_estado = Carbon::now();
      $rendicion->estado = 'Pendiente';
      $rendicion->usuario_creacion = $user->priv_level === 4 ? $stipend_request->user_id : $user->id;
      $rendicion->saldo_favor_empresa = $stipend_request->total_amount + $stipend_request->additional;
      
      $rendicion->save();

      $this->fill_code_column(); // Fill records' codes where empty

      // TODO Send an email notification to Project Manager
      // $this->notify_request($rendicion, 0);

      /* TODO Register an event for the creation
          $this->add_event('created', $rendicion, '');
      */

      Session::flash('message', "La estructura de rendición de viáticos fue creada en el sistema");
      if (Session::has('url'))
        return redirect(Session::get('url'));
      else
        return redirect('/rendicion_viatico');
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
      $rendicion = RendicionViatico::find($id);
      
      $rendicion->fecha_estado = Carbon::parse($rendicion->fecha_estado)->hour(0)->minute(0)->second(0);

      foreach($rendicion->respaldos as $respaldo) {
        $respaldo->fecha_respaldo = Carbon::parse($respaldo->fecha_respaldo)->hour(0)->minute(0)->second(0);
      }
      
      $cant_facturas = $rendicion->respaldos->where('tipo_respaldo', 'Factura')->count();
      $cant_recibos = $rendicion->respaldos->where('tipo_respaldo', 'Recibo')->count();
      
      $usuario_creacion_nombre = empty($rendicion->usuario_creacion) ? '' : User::find($rendicion->usuario_creacion)->name;
      $usuario_modificacion_nombre = empty($rendicion->usuario_modificacion) ? '' : User::find($rendicion->usuario_modificacion)->name;

      $tipos_gasto = ['alimentacion', 'combustible', 'comunicaciones', 'extras', 'hotel',
        'materiales', 'taxi', 'transporte'];

      return View::make('app.rendicion_viatico_info', ['rendicion' => $rendicion, 'service' => $service,
        'user' => $user, 'usuario_creacion_nombre' => $usuario_creacion_nombre,
        'usuario_modificacion_nombre' => $usuario_modificacion_nombre, 'tipos_gasto' => $tipos_gasto, 
        'cant_facturas' => $cant_facturas, 'cant_recibos' => $cant_recibos]);
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

      $rendicion = RendicionViatico::find($id);
      
      $solicitudes = StipendRequest::where('id', $rendicion->stipend_request_id)->get();
      
      if (!$solicitudes || $solicitudes->count() === 0) {
        Session::flash('message', 'Usted no tiene ninguna solicitud de viáticos pendiente para rendición');
        return redirect()->back();
      }
      
      $rendicion->fecha_deposito = Carbon::parse($rendicion->fecha_deposito);

      $nro_rendicion = $rendicion->nro_rendicion;

      $tipos_gasto = ['alimentacion', 'combustible', 'comunicaciones', 'extras', 'hotel',
        'materiales', 'taxi', 'transporte'];

      return View::make('app.rendicion_viatico_form', ['rendicion' => $rendicion, 'user' => $user,
        'service' => $service, 'solicitudes' => $solicitudes, 'nro_rendicion' => $nro_rendicion,
        'tipos_gasto' => $tipos_gasto]);
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

      $form_data = Request::all();

      $v = \Validator::make($form_data, [
        'stipend_request_id'    => 'required',
        'nro_rendicion'         => 'required',
        'fecha_deposito'        => 'required|date',
        'monto_deposito'        => 'required|numeric',
      ],
        [
          'required'                  => 'Este campo es obligatorio!',
          'date'                      => 'La fecha introducida es inválida!',
          'numeric'                   => 'Sólo puede ingresar números en este campo!',
        ]
      );

      if ($v->fails()) {
        Session::flash('message', 'Sucedió un error al enviar el formulario!');
        return redirect()->back()->withErrors($v)->withInput();
      }

      $rendicion = RendicionViatico::find($id);

      $rendicion->fill(Request::all());

      $stipend_request = $rendicion->solicitud;

      if (!$stipend_request) {
        Session::flash('message', 'Error al cargar la información de la solicitud de viáticos, intente reenviar el formulario por favor');
        return redirect()->back()->withInput();
      }

      if ($user->priv_level < 4 && $user->id != $stipend_request->user_id) {
        Session::flash('message', 'Usted no tiene permisos para modificar la rendición seleccionada');
        return redirect()->back()->withInput();
      }

      $rendicion->fecha_estado = Carbon::now();
      
      $rendicion->usuario_modificacion = $user->id;
      
      $rendicion->save();

      // TODO Send an email notification to Project Manager
      // $this->notify_request($rendicion, 0);

      /* TODO Register an event for the modification
          $this->add_event('created', $rendicion, '');
      */

      Session::flash('message', "La estructura de rendición de viáticos fue modificada en el sistema");
      if (Session::has('url'))
        return redirect(Session::get('url'));
      else
        return redirect('/rendicion_viatico');
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

    public function listar_pendientes() {
      $user = Session::get('user');
      if ((is_null($user)) || (!$user->id)) {
        $ref = $_SERVER['REQUEST_URI'];
        Session::put('url.intended', $ref);
        return redirect()->route('root');
      }

      $service = Session::get('service');

      // TODO agregar validacion segun permisos de usuario $user->action->...
      if (true) {
        $pendientes = RendicionViatico::where('estado', 'Pendiente')->orwhere('estado', 'Presentado')
          ->orderBy('id', 'desc')->get();
      } else {
        Session::flash('message', 'Usted no tiene permiso para ver la página solicitada!');
        return redirect()->back();
      }
      
      return View::make('app.rendicion_viatico_pendientes', ['pendientes' => $pendientes,
        'service' => $service, 'user' => $user]);
    }

    public function listar_observados() {
      $user = Session::get('user');
      if ((is_null($user)) || (!$user->id)) {
        $ref = $_SERVER['REQUEST_URI'];
        Session::put('url.intended', $ref);
        return redirect()->route('root');
      }

      $service = Session::get('service');

      // TODO agregar validacion segun permisos de usuario $user->action->...
      if (true) {
        $observados = RendicionViatico::where('estado', 'Observado')->orderBy('id', 'desc')->get();
      } else {
        Session::flash('message', 'Usted no tiene permiso para ver la página solicitada!');
        return redirect()->back();
      }

      return View::make('app.rendicion_viatico_observados', ['observados' => $observados,
        'service' => $service, 'user' => $user]);
    }

    public function cambiar_estado() {
      $user = Session::get('user');
      if ((is_null($user)) || (!$user->id))
        return redirect()->route('root');

      $service = Session::get('service');

      $id_rendicion = Input::get('id');
      $mode = Input::get('mode');

      if (!RendicionViatico::where('id', $id_rendicion)->exists()) {
        Session::flash('message', "No se ha encontrado el registro solicitado!");
        return redirect()->back()->withInput();
      }

      $rendicion = RendicionViatico::find($id_rendicion);

      $hoy = Carbon::now();

      $tiene_observados = false;
      $tiene_pendientes = false;
      foreach ($rendicion->respaldos as $respaldo) {
        if ($respaldo->estado == 'Observado') {
          $tiene_observados = true;
        }
        if ($respaldo->estado != 'Aprobado') {
          $tiene_pendientes = true;
        }
      }

      if ($mode == 'presentar') {
        if ($tiene_observados) {
          Session::flash('message', "Corrija las observaciones antes de presentar la rendición!");
          return redirect()->back()->withInput();
        }
        $rendicion->estado = 'Presentado';
        $rendicion->fecha_presentado = Carbon::parse($rendicion->fecha_presentado) < $hoy && $rendicion->fecha_presentado != '0000-00-00 00:00:00' ? $rendicion->fecha_presentado : $hoy;
      } elseif ($mode == 'aprobar') {
        if ($tiene_pendientes) {
          Session::flash('message', "Debe aprobar todos los respaldos antes de aprobar la rendición!");
          return redirect()->back()->withInput();
        }
        $rendicion->estado = 'Aprobado';
        if ($rendicion->solicitud) {
          $solicitud = $rendicion->solicitud;
          $solicitud->status = 'Documented';
          $solicitud->save();
        }
      } elseif ($mode == 'cancelar' || $mode == 'observar') {
        return View::make('app.rendicion_viatico_obs_form', ['rendicion' => $rendicion, 'user' => $user,
        'service' => $service, 'mode' => $mode]);
      }

      $rendicion->fecha_estado = $hoy;
      $rendicion->usuario_modificacion = $user->id;

      $rendicion->save();

      // return 'function cambiar_estado reached ' . $rendicion;
      Session::flash('message', "La estructura de rendición de viáticos fue modificada en el sistema");
      if (Session::has('url'))
        return redirect(Session::get('url'));
      else
        return redirect('/rendicion_viatico');
    }

    public function cambiar_estado_obs (Request $request) {
      $user = Session::get('user');
      if ((is_null($user)) || (!$user->id))
        return redirect()->route('root');

      $id_rendicion = Input::get('id');
      $mode = Input::get('mode');
      $form_data = Request::all();

      $v = \Validator::make($form_data, [
        'observaciones'         => 'required',
      ],
        [
          'required'                  => 'Este campo es obligatorio!',
        ]
      );

      if ($v->fails()) {
        Session::flash('message', 'Sucedió un error al enviar el formulario!');
        return redirect()->back()->withErrors($v)->withInput();
      }

      $rendicion = RendicionViatico::find($id_rendicion);

      $rendicion->fill(Request::all());

      if ($mode == 'cancelar') {
        $rendicion->estado = 'Cancelado';
      } elseif ($mode == 'observar') {
        $rendicion->estado = 'Observado';
      }

      $rendicion->fecha_estado = Carbon::now();
      $rendicion->usuario_modificacion = $user->id;
      
      $rendicion->save();

      // TODO Send an email notification to Project Manager
      // $this->notify_request($rendicion, 0);

      /* TODO Register an event for the modification
          $this->add_event('created', $rendicion, '');
      */

      Session::flash('message', `El estado de la rendición $rendicion->codigo ha cambiado a $rendicion->estado`);
      if (Session::has('url'))
        return redirect(Session::get('url'));
      else
        return redirect('/rendicion_viatico');
    }

    public function rendir_desde_solicitud ($id_solicitud) {
      $user = Session::get('user');
      if ((is_null($user)) || (!$user->id))
          return redirect()->route('root');

      $service = Session::get('service');

      if (!StipendRequest::where('id', $id_solicitud)->exists()) {
        Session::flash('message', "No se ha encontrado el registro de la solicitud de viáticos!");
        return redirect()->back()->withInput();
      }

      $solicitud = StipendRequest::find($id_solicitud);

      if ($solicitud->status != 'Completed') {
        Session::flash('message', "No se puede iniciar la rendición de esta solicitud porque el proceso de pago aún no se ha completado!");
        return redirect()->back()->withInput();
      }

      $ultima_rendicion = RendicionViatico::where('usuario_creacion', $user->id)->orderBy('created_at', 'desc')->first();

      $nro_rendicion = $ultima_rendicion ? $ultima_rendicion->nro_rendicion + 1 : 1;

      $rendicion = new RendicionViatico();
      $rendicion->stipend_request_id = $solicitud->id;
      $rendicion->nro_rendicion = $nro_rendicion;
      $rendicion->fecha_deposito = $solicitud->updated_at;
      $rendicion->monto_deposito = $solicitud->total_amount + $solicitud->additional;
      $rendicion->fecha_estado = Carbon::now();
      $rendicion->estado = 'Pendiente';
      $rendicion->usuario_creacion = $user->priv_level === 4 ? $solicitud->user_id : $user->id;
      
      $rendicion->save();

      $this->fill_code_column(); // Fill records' codes where empty

      // TODO Send an email notification to Project Manager
      // $this->notify_request($rendicion, 0);

      /* TODO Register an event for the creation
          $this->add_event('created', $rendicion, '');
      */

      Session::flash('message', "La estructura de rendición de viáticos fue creada en el sistema");
      if (Session::has('url'))
        return redirect(Session::get('url'));
      else
        return redirect('/rendicion_viatico');
    }

    function fill_code_column() {
      $rendiciones = RendicionViatico::where('codigo','')->get();

      foreach ($rendiciones as $rendicion) {
        $rendicion->codigo = 'RDV-'.str_pad($rendicion->id, 5, "0", STR_PAD_LEFT).'-'.
            Carbon::now()->format('y');

        $rendicion->save();
      }
    }
}
