<?php

namespace App\Http\Controllers;

// use Illuminate\Http\Request;
use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Session;
use View;
use Hash;
use Input;
use Exception;
use App\RendicionViatico;
use App\RendicionRespaldo;
use App\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;

class RendicionRespaldoController extends Controller
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
    public function create()
    {
        //
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
          'rendicion_id'            => 'required|exists:rendicion_viaticos,id',
          'fecha_respaldo'          => 'required|date',
          'tipo_respaldo'           => 'required',
          'nit'                     => 'required_if:tipo_respaldo,Factura',
          'nro_respaldo'            => 'required',
          'codigo_autorizacion'     => 'required_if:tipo_respaldo,Factura',
          'codigo_control'          => 'required_if:tipo_respaldo,Factura',
          'razon_social'            => 'required',
          'detalle'                 => 'required',
          'corresponde_a'           => 'required',
          'monto'                   => 'required|numeric'
      ],
        [
          'rendicion_id.required'      => 'Debe especificar la rendición a la que pertenece el respaldo!',
          'rendicion_id.exists'        => 'En número de rendición especificado no existe!',
          'fecha_respaldo.required'    => 'Debe especificar la fecha de emisión del documento!',
          'fecha_respaldo.date'        => 'La fecha introducida no es válida!',
          'tipo_respaldo.required'     => 'Debe especificar si el documento es un factura o un recibo!',
          'nit.required_if'            => 'Debe especificar el número de NIT si el documento es una factura!',
          'nro_respaldo.required'      => 'Debe especificar el número del documento!',
          'codigo_autorizacion.required_if' => 'Debe especificar el código de autorización si el documento es una factura!',
          'codigo_control.required_if' => 'Debe especificar el código de control si el documento es una factura!',
          'razon_social.required'      => 'Debe especificar la razón social del emisor del documento!',
          'detalle.required'           => 'Debe proporcionar un breve detalle del documento!',
          'corresponde_a.required'     => 'Debe indicar a qué tipo de gasto corresponde el documento!',
          'monto.required'             => 'Debe especificar el monto de la factura o recibo!',
          'monto.numeric'              => 'El monto indicado no es válido!'
        ]
      );

      if ($v->fails()) {
          Session::flash('message', $v->messages()->first());
          return redirect()->back()->withInput();
      }

      $respaldo = new RendicionRespaldo(Request::all());

      // $rendicion = RendicionViatico::find($respaldo->rendicion_id);

      $respaldo->usuario_creacion = $user->id;
      $respaldo->estado = 'Pendiente';

      $respaldo->save();

      $this->calcular_totales($respaldo->rendicion_id);
      
      // $rendicion->save();
      
      Session::flash('message', "El documento de respaldo fue registrado correctamente");
      // if (Session::has('url'))
          // return redirect(Session::get('url'));
      // else
          return redirect()->action('RendicionViaticoController@show', ['id' => $respaldo->rendicion_id]);
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
      $user = Session::get('user');
      if ((is_null($user)) || (!$user->id))
          return redirect()->route('root');
      
      $respaldo = RendicionRespaldo::find($id);
      // $monto_previo = $respaldo->monto;

      $v = \Validator::make(Request::all(), [
        'rendicion_id'            => 'required|exists:rendicion_viatico,id',
        'fecha_respaldo'          => 'required|date',
        'tipo_respaldo'           => 'required',
        'nit'                     => 'required_if:tipo_respaldo,Factura',
        'nro_respaldo'            => 'required',
        'codigo_autorizacion'     => 'required_if:tipo_respaldo,Factura',
        'codigo_control'          => 'required_if:tipo_respaldo,Factura',
        'razon_social'            => 'required',
        'detalle'                 => 'required',
        'corresponde_a'           => 'required',
        'monto'                   => 'required|numeric'
      ],
        [
          'rendicion_id.required'       => 'Debe especificar la rendición a la que pertenece el respaldo!',
          'rendicion_id.exists'         => 'En número de rendición especificado no existe!',
          'fecha_respaldo.required'    => 'Debe especificar la fecha de emisión del documento!',
          'fecha_respaldo.date'        => 'La fecha introducida no es válida!',
          'tipo_respaldo.required'     => 'Debe especificar si el documento es un factura o un recibo!',
          'nit.required_if'            => 'Debe especificar el número de NIT si el documento es una factura!',
          'nro_respaldo.required'      => 'Debe especificar el número del documento!',
          'codigo_autorizacion.required_if' => 'Debe especificar el código de autorización si el documento es una factura!',
          'codigo_control.required_if' => 'Debe especificar el código de control si el documento es una factura!',
          'razon_social.required'      => 'Debe especificar la razón social del emisor del documento!',
          'detalle.required'           => 'Debe proporcionar un breve detalle del documento!',
          'corresponde_a.required'     => 'Debe indicar a qué tipo de gasto corresponde el documento!',
          'monto.required'             => 'Debe especificar el monto de la factura o recibo!',
          'monto.numeric'              => 'El monto indicado no es válido!'
        ]
      );

      if ($v->fails()) {
        Session::flash('message', $v->messages()->first());
        return redirect()->back()->withInput();
      }

      $respaldo->fill(Request::all());

      $respaldo->usuario_modificacion = $user->id;

      $respaldo->save();

      $this->calcular_totales($respaldo->rendicion_id);

      // $rendicion = $respaldo->rendicion;

      /*
      if ($monto_previo != $respaldo->monto) {
        if ($respaldo->tipo_respaldo == 'Factura') {
          $rendicion->total_facturas_validas = $rendicion->total_facturas_validas + $respaldo->monto - $monto_previo;
        }
  
        if ($respaldo->tipo_respaldo == 'Recibo') {
          $rendicion->total_recibos_validos = $rendicion->total_recibos_validos + $respaldo->monto - $monto_previo;
        }
  
        if ($respaldo->corresponde_a == 'alimentacion') {
          $rendicion->subtotal_alimentacion = $rendicion->subtotal_alimentacion + $respaldo->monto - $monto_previo;
        }
  
        if ($respaldo->corresponde_a == 'combustible') {
          $rendicion->subtotal_combustible = $rendicion->subtotal_combustible + $respaldo->monto - $monto_previo;
        }
  
        if ($respaldo->corresponde_a == 'comunicaciones') {
          $rendicion->subtotal_comunicaciones = $rendicion->subtotal_comunicaciones + $respaldo->monto - $monto_previo;
        }
  
        if ($respaldo->corresponde_a == 'extras') {
          $rendicion->subtotal_extras = $rendicion->subtotal_extras + $respaldo->monto - $monto_previo;
        }
  
        if ($respaldo->corresponde_a == 'hotel') {
          $rendicion->subtotal_hotel = $rendicion->subtotal_hotel + $respaldo->monto - $monto_previo;
        }
  
        if ($respaldo->corresponde_a == 'materiales') {
          $rendicion->subtotal_materiales = $rendicion->subtotal_materiales + $respaldo->monto - $monto_previo;
        }
  
        if ($respaldo->corresponde_a == 'taxi') {
          $rendicion->subtotal_taxi = $rendicion->subtotal_taxi + $respaldo->monto - $monto_previo;
        }
  
        if ($respaldo->corresponde_a == 'transporte') {
          $rendicion->subtotal_transporte = $rendicion->subtotal_transporte + $respaldo->monto - $monto_previo;
        }
  
        $rendicion->total_rendicion = $rendicion->total_rendicion + $respaldo->monto - $monto_previo;
  
        $total_solicitado = $rendicion->solicitud->total_amount + $rendicion->solicitud->additional;
  
        if ($total_solicitado > $rendicion->total_rendicion) {
          $rendicion->monto_sobrante = $total_solicitado - $rendicion->total_rendicion;
          $rendicion->saldo_favor_empresa = $total_solicitado - $rendicion->total_rendicion;
          $rendicion->monto_excedente = 0;
          $rendicion->saldo_favor_persona = 0;
        } else {
          $rendicion->monto_sobrante = 0;
          $rendicion->saldo_favor_empresa = 0;
          $rendicion->monto_excedente = $rendicion->total_rendicion - $total_solicitado;
          $rendicion->saldo_favor_persona = $rendicion->total_rendicion - $total_solicitado;
        }
      }

      $rendicion->save();
      */
      
      Session::flash('message', "El documento de respaldo fue modificado correctamente");
      // if (Session::has('url'))
          // return redirect(Session::get('url'));
      // else
          return redirect()->action('RendicionViaticoController@show', ['id' => $respaldo->rendicion_id]);
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

      $respaldo = RendicionRespaldo::find($id);

      if ($respaldo) {
        $to_return_id = $respaldo->rendicion_id;

        /*
        $rendicion = $respaldo->rendicion;

        if ($respaldo->tipo_respaldo == 'Factura') {
          $rendicion->total_facturas_validas = $rendicion->total_facturas_validas - $respaldo->monto;
        }
  
        if ($respaldo->tipo_respaldo == 'Recibo') {
          $rendicion->total_recibos_validos = $rendicion->total_recibos_validos - $respaldo->monto;
        }
  
        if ($respaldo->corresponde_a == 'alimentacion') {
          $rendicion->subtotal_alimentacion = $rendicion->subtotal_alimentacion - $respaldo->monto;
        }
  
        if ($respaldo->corresponde_a == 'combustible') {
          $rendicion->subtotal_combustible = $rendicion->subtotal_combustible - $respaldo->monto;
        }
  
        if ($respaldo->corresponde_a == 'comunicaciones') {
          $rendicion->subtotal_comunicaciones = $rendicion->subtotal_comunicaciones - $respaldo->monto;
        }
  
        if ($respaldo->corresponde_a == 'extras') {
          $rendicion->subtotal_extras = $rendicion->subtotal_extras - $respaldo->monto;
        }
  
        if ($respaldo->corresponde_a == 'hotel') {
          $rendicion->subtotal_hotel = $rendicion->subtotal_hotel - $respaldo->monto;
        }
  
        if ($respaldo->corresponde_a == 'materiales') {
          $rendicion->subtotal_materiales = $rendicion->subtotal_materiales - $respaldo->monto;
        }
  
        if ($respaldo->corresponde_a == 'taxi') {
          $rendicion->subtotal_taxi = $rendicion->subtotal_taxi - $respaldo->monto;
        }
  
        if ($respaldo->corresponde_a == 'transporte') {
          $rendicion->subtotal_transporte = $rendicion->subtotal_transporte - $respaldo->monto;
        }
  
        $rendicion->total_rendicion = $rendicion->total_rendicion - $respaldo->monto;
  
        $total_solicitado = $rendicion->solicitud->total_amount + $rendicion->solicitud->additional;
  
        if ($total_solicitado > $rendicion->total_rendicion) {
          $rendicion->monto_sobrante = $total_solicitado - $rendicion->total_rendicion;
          $rendicion->saldo_favor_empresa = $total_solicitado - $rendicion->total_rendicion;
          $rendicion->monto_excedente = 0;
          $rendicion->saldo_favor_persona = 0;
        } else {
          $rendicion->monto_sobrante = 0;
          $rendicion->saldo_favor_empresa = 0;
          $rendicion->monto_excedente = $rendicion->total_rendicion - $total_solicitado;
          $rendicion->saldo_favor_persona = $rendicion->total_rendicion - $total_solicitado;
        }

        $rendicion->save();
        */
        
        $respaldo->delete();

        $this->calcular_totales($to_return_id);

        Session::flash('message', "El registro fue eliminado del sistema");

        // if (Session::has('url'))
          // return redirect(Session::get('url'));
        // else
          return redirect()->action('RendicionViaticoController@show', ['id' => $to_return_id]);
      } else {
        Session::flash('message', "Error al ejecutar el borrado, no se encontró el registro solicitado.");
        return redirect()->back();
      }
    }

    public function refrescar_totales ($id) {
      $response = $this->calcular_totales($id);

      Session::flash('message', "Se han actualizado los valores de la rendición");
      return redirect()->action('RendicionViaticoController@show', ['id' => $id]);
    }

    public function calcular_totales ($rendicion_id) {
      $rendicion = RendicionViatico::find($rendicion_id);

      if ($rendicion) {
        $rendicion->total_facturas_validas = 0;
        $rendicion->total_recibos_validos = 0;
        $rendicion->subtotal_alimentacion = 0;
        $rendicion->subtotal_combustible = 0;
        $rendicion->subtotal_comunicaciones = 0;
        $rendicion->subtotal_extras = 0;
        $rendicion->subtotal_hotel = 0;
        $rendicion->subtotal_materiales = 0;
        $rendicion->subtotal_taxi = 0;
        $rendicion->subtotal_transporte = 0;
        $rendicion->total_rendicion = 0;
        
        foreach ($rendicion->respaldos as $respaldo) {
          if ($respaldo->tipo_respaldo == 'Factura') {
            $rendicion->total_facturas_validas += $respaldo->monto;
          }
    
          if ($respaldo->tipo_respaldo == 'Recibo') {
            $rendicion->total_recibos_validos += $respaldo->monto;
          }
    
          if ($respaldo->corresponde_a == 'alimentacion') {
            $rendicion->subtotal_alimentacion += $respaldo->monto;
          }
    
          if ($respaldo->corresponde_a == 'combustible') {
            $rendicion->subtotal_combustible += $respaldo->monto;
          }
    
          if ($respaldo->corresponde_a == 'comunicaciones') {
            $rendicion->subtotal_comunicaciones += $respaldo->monto;
          }
    
          if ($respaldo->corresponde_a == 'extras') {
            $rendicion->subtotal_extras += $respaldo->monto;
          }
    
          if ($respaldo->corresponde_a == 'hotel') {
            $rendicion->subtotal_hotel += $respaldo->monto;
          }
    
          if ($respaldo->corresponde_a == 'materiales') {
            $rendicion->subtotal_materiales += $respaldo->monto;
          }
    
          if ($respaldo->corresponde_a == 'taxi') {
            $rendicion->subtotal_taxi += $respaldo->monto;
          }
    
          if ($respaldo->corresponde_a == 'transporte') {
            $rendicion->subtotal_transporte += $respaldo->monto;
          }
    
          $rendicion->total_rendicion += $respaldo->monto;
        }
    
        $total_solicitado = $rendicion->solicitud->total_amount + $rendicion->solicitud->additional;

        if ($total_solicitado > $rendicion->total_rendicion) {
          $rendicion->monto_sobrante = $total_solicitado - $rendicion->total_rendicion;
          $rendicion->saldo_favor_empresa = $total_solicitado - $rendicion->total_rendicion;
          $rendicion->monto_excedente = 0;
          $rendicion->saldo_favor_persona = 0;
        } else {
          $rendicion->monto_sobrante = 0;
          $rendicion->saldo_favor_empresa = 0;
          $rendicion->monto_excedente = $rendicion->total_rendicion - $total_solicitado;
          $rendicion->saldo_favor_persona = $rendicion->total_rendicion - $total_solicitado;
        }

        $rendicion->save();

        return true;
      } else {
        Session::flash('message', "Error al ejecutar el cálculo, no se encontró el registro solicitado.");
        return redirect()->back();
      }
    }

    public function cambiar_estado() {
      $user = Session::get('user');
      if ((is_null($user)) || (!$user->id))
        return redirect()->route('root');

      $service = Session::get('service');

      $id_respaldo = Input::get('id');
      $mode = Input::get('mode');

      if (!RendicionRespaldo::where('id', $id_respaldo)->exists()) {
        Session::flash('message', "No se ha encontrado el registro solicitado!");
        return redirect()->back()->withInput();
      }

      $respaldo = RendicionRespaldo::find($id_respaldo);

      $hoy = Carbon::now();
      
      if ($mode == 'aprobar') {
        $respaldo->estado = 'Aprobado';
      } elseif ($mode == 'observar') {
        return View::make('app.rendicion_respaldo_obs_form', ['respaldo' => $respaldo, 'user' => $user,
        'service' => $service, 'mode' => $mode]);
      }

      $respaldo->usuario_modificacion = $user->id;

      $respaldo->save();

      Session::flash('message', "Se ha actualizado el estado del documento de respaldo");
      return redirect()->action('RendicionViaticoController@show', ['id' => $respaldo->rendicion_id]);
    }

    public function cambiar_estado_obs (Request $request) {
      $user = Session::get('user');
      if ((is_null($user)) || (!$user->id))
        return redirect()->route('root');

      $id_respaldo = Input::get('id');
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

      $respaldo = RendicionRespaldo::find($id_respaldo);

      $respaldo->fill(Request::all());
      
      if ($mode == 'observar') {
        $respaldo->estado = 'Observado';
      }

      $respaldo->usuario_modificacion = $user->id;
      
      $respaldo->save();

      // TODO Send an email notification to Project Manager
      // $this->notify_request($rendicion, 0);

      /* TODO Register an event for the modification
          $this->add_event('created', $rendicion, '');
      */

      Session::flash('message', `El estado del documento de respaldo ha cambiado a $respaldo->estado`);
      return redirect()->action('RendicionViaticoController@show', ['id' => $respaldo->rendicion_id]);
    }
}
