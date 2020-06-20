@extends('layouts.projects_structure')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/info_tabs.css") }}">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
  @if ($user->priv_level > 0)
    @include('app.project_navigation_button', array('user'=>$user))
  @endif
  <div class="btn-group">
    <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
      <i class="fa fa-money"></i> Rendiciones de viáticos <span class="caret"></span>
    </button>
    <ul class="dropdown-menu dropdown-menu-prim">
      <li><a href="{{ '/rendicion_viatico' }}"><i class="fa fa-refresh fa-fw"></i> Ver lista de rendiciones </a></li>
      <li><a href="{{ '/rendicion_viatico/create' }}"><i class="fa fa-plus fa-fw"></i> Registrar rendición </a></li>
      {{--@if($user->action->aprobar_rendicion)--}}
      @if ($user->priv_level > 0)
        <li>
          <a href="{{ '/rendicion_viatico/pendientes' }}">
            <i class="fa fa-check fa-fw"></i> Pendientes de aprobación
          </a>
        </li>
        <li>
          <a href="{{ '/rendicion_viatico/observados' }}">
            <i class="fa fa-eye fa-fw"></i> Observadas
          </a>
        </li>
      @endif
      {{-- @endif --}}
      @if(/*$user->action->prj_vtc_exp*/ $user->priv_level == 4)
        <li class="divider"></li>
        <li class="dropdown-submenu">
          <a href="#" data-toggle="dropdown"><i class="fa fa-file-excel-o"></i> Exportar a Excel</a>
          <ul class="dropdown-menu dropdown-menu-prim">
            <li>
              <a href="{{ '/excel/rendicion_viatico' }}">
                <i class="fa fa-file-excel-o fa-fw"></i> Tabla de rendiciones
              </a>
            </li>
          </ul>
        </li>
      @endif
    </ul>
  </div>
  <a href="{{ '/stipend_request' }}" class="btn btn-primary" title="Ver solicitudes de viáticos">
    <i class="fa fa-file"></i> Solicitudes
  </a>
  @if($user->priv_level >= 2)
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchBox">
      <i class="fa fa-search"></i> Buscar
    </button>
  @endif
@endsection

@section('content')

<div id="loginbox" class="mg-tp-px-50 col-md-10 col-md-offset-1 col-sm-12 mg-btm-px-40">
  <div class="panel panel-sky">
    <div class="panel-heading" align="center">
      <div class="panel-title">
        <div class="pull-left">
          <ul class="nav nav-tabs">
              <li class="{{ $tab && $tab == "main" ? "active" : "" }}"><a href="#main" data-toggle="tab"> Datos Generales</a></li>
              <li class="{{ $tab && $tab == "respaldos" ? "active" : "" }}"><a href="#respaldos" data-toggle="tab"> Respaldos</a></li>
              {{--@if($oc->status <> 'Anulado' || $user->priv_level == 4)
                <li><a href="#payments" data-toggle="tab"> Estado de pagos</a></li>
              @endif--}}
          </ul>
        </div>
        <div class="clearfix"></div>
      </div>
    </div>

    <div class="panel-body">
      <div class="tab-content">

        <div class="tab-pane fade {{ $tab && $tab == "main" ? "in active" : "" }}" id="main">

          <div class="col-lg-5 mg20">
            <a href="#" onclick="history.back();" class="btn btn-warning">
              <i class="fa fa-arrow-circle-left"></i> Volver
            </a>
            <a href="{{ '/rendicion_viatico' }}" class="btn btn-warning" title="Ir a la tabla de rendiciones">
              <i class="fa fa-arrow-circle-up"></i> Rendiciones
            </a>
          </div>

          <div class="col-lg-7" align="right">
            @if($rendicion->estado <> 'Cancelado')
              {{--@if($user->action->oc_ctf_add)
                  <a href="{{ '/oc_certificate/create?id='.$oc->id }}" class="btn btn-success"
                      title="Agregar certificado de aceptación para ésta OC">
                      <i class="fa fa-file-text-o"></i> Emitir certificado
                  </a>
              @endif--}}
              @if($user->priv_level == 4)
                <a href="/excel/rendicion_viatico/{{ $rendicion->id }}" class="btn btn-success" title="Descargar Rendición">
                  <i class="fa fa-file-excel-o"></i> Descargar rendición
                </a>
              @endif
            @endif
          </div>

          <div class="col-sm-12 mg10">
            @include('app.session_flashed_messages', array('opt' => 0))
          </div>

          <div class="col-sm-12 mg10 mg-tp-px-10">
            <table class="table table-striped table-hover table-bordered">
              <thead>
                <tr>
                  <th width="25%">Código:</th>
                  <td width="25%">{{ $rendicion->codigo }}</td>
                  <th width="25%">Estado:</th>
                  <td>
                      {{ $rendicion->estado }}
                      @if ($rendicion->estado === 'Cancelado' && ($user->priv_level === 4 || $user->id === $rendicion->usuario_creacion))
                        <a href="/rendicion_viatico/estado/?mode=reabrir&id={{ $rendicion->id }}" class="btn btn-primary pull-right">
                          Reabrir rendición
                        </a>
                      @endif
                  </td>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td colspan="2" rowspan="3"></td>
                  <th>Última actualización:</th>
                  <td>{{ date_format($rendicion->fecha_estado, 'd-m-Y') }}</td>
                </tr>
                <tr>
                  <th>Monto solicitado:</th>
                    {{--<td>{{ number_format($rendicion->monto_deposito, 2).' Bs' }}</td>--}}
                    <td>{{ number_format($rendicion->solicitud->total_amount + $rendicion->solicitud->additional, 2).' Bs' }}</td>
                  </tr>
                <tr>
                  <th>Monto rendición:</th>
                  <td>{{ number_format($rendicion->total_rendicion, 2).' Bs' }}</td>
                </tr>
                <tr>
                  <th>Creado por:</th>
                  <td colspan="3">{{ $rendicion->creadoPor->name }}</td>
                </tr>
                <tr>
                  <th>Código de solicitud:</th>
                  <td colspan="3">
                    @if ($rendicion->solicitud)
                      <a href="/stipend_request/{{ $rendicion->solicitud->id }}">
                        {{ $rendicion->solicitud->code }}
                      </a>
                    @else
                      {{ 'N/E' }}
                    @endif
                  </td>
                </tr>
                @if ($rendicion->observaciones && $rendicion->estado !== 'Cancelado')
                  <tr>
                    <th>Observaciones:</th>
                    <td colspan="3">{{ $rendicion->observaciones }}</td>
                  </tr>
                @endif

                @if($rendicion->estado != 'Cancelado')
                  <tr><td colspan="4"></td></tr>
                  <tr>
                    <th colspan="4">
                      Comparativa solicitud / rendición [Bs]:
                      @if ($rendicion->estado == 'Pendiente' || $rendicion->estado == 'Observado')
                        @if ($user->id == $rendicion->usuario_creacion || $user->priv_level == 4)
                          <div class="pull-right">
                            <a href="/rendicion_respaldo/refrescar_totales/{{ $rendicion->id }}" class="btn btn-success" title="Actualizar totales">
                              <i class="fa fa-refresh"></i>
                            </a>
                            &ensp;
                            <button type="button" 
                              class="btn btn-success open-rowBox"
                              title="Agregar respaldo (factura / recibo)"
                              data-toggle="modal" 
                              data-target="#rowBox"
                              data-id="0"
                              data-fecha=""
                              data-tipo=""
                              data-nit=""
                              data-nrorespaldo=""
                              data-codigoautorizacion=""
                              data-codigocontrol=""
                              data-razonsocial=""
                              data-detalle=""
                              data-correspondea=""
                              data-monto="">
                              <i class="fa fa-plus"></i>
                            </button>
                            &ensp;
                            <a href="/import/rendicion_respaldos/{{ $rendicion->id }}" class="btn btn-success" title="Importar lista de respaldos desde un archivo excel">
                              <i class="fa fa-upload"></i>
                            </a>
                          </div>
                        @endif
                      @endif
                    </th>
                  </tr>
                  <tr>
                    <td colspan="2">Solicitud</td>
                    <td colspan="2">Rendición</td>
                  </tr>
                  @if ($rendicion->solicitud->per_day_amount > 0 || $rendicion->subtotal_alimentacion > 0)
                    <tr>
                      <td>Alimentación</td>
                      <td>{{ $rendicion->solicitud->per_day_amount * $rendicion->solicitud->in_days }}</td>
                      <td>Alimentación</td>
                      <td>{{ $rendicion->subtotal_alimentacion }}</td>
                    </tr>
                  @endif
                  @if ($rendicion->solicitud->transport_amount > 0 || $rendicion->subtotal_transporte > 0)
                    <tr>
                      <td>Transporte</td>
                      <td>{{ $rendicion->solicitud->transport_amount }}</td>
                      <td>Transporte</td>
                      <td>{{ $rendicion->subtotal_transporte }}</td>
                    </tr>
                  @endif
                  @if ($rendicion->solicitud->hotel_amount > 0 || $rendicion->subtotal_hotel > 0)
                    <tr>
                      <td>Alojamiento</td>
                      <td>{{ $rendicion->solicitud->hotel_amount * $rendicion->solicitud->in_days }}</td>
                      <td>Alojamiento</td>
                      <td>{{ $rendicion->subtotal_hotel }}</td>
                    </tr>
                  @endif
                  @if ($rendicion->solicitud->gas_amount > 0 || $rendicion->subtotal_combustible > 0)
                    <tr>
                      <td>Combustible</td>
                      <td>{{ $rendicion->solicitud->gas_amount }}</td>
                      <td>Combustible</td>
                      <td>{{ $rendicion->subtotal_combustible }}</td>
                    </tr>
                  @endif
                  @if ($rendicion->solicitud->taxi_amount > 0 || $rendicion->subtotal_taxi > 0)
                    <tr>
                      <td>Taxi</td>
                      <td>{{ $rendicion->solicitud->taxi_amount }}</td>
                      <td>Taxi</td>
                      <td>{{ $rendicion->subtotal_taxi }}</td>
                    </tr>
                  @endif
                  @if ($rendicion->solicitud->comm_amount > 0 || $rendicion->subtotal_comunicaciones > 0)
                    <tr>
                      <td>Comunicaciones</td>
                      <td>{{ $rendicion->solicitud->comm_amount }}</td>
                      <td>Comunicaciones</td>
                      <td>{{ $rendicion->subtotal_comunicaciones }}</td>
                    </tr>
                  @endif
                  @if ($rendicion->solicitud->materials_amount > 0 || $rendicion->subtotal_materiales > 0)
                    <tr>
                      <td>Materiales</td>
                      <td>{{ $rendicion->solicitud->materials_amount }}</td>
                      <td>Materiales</td>
                      <td>{{ $rendicion->subtotal_materiales }}</td>
                    </tr>
                  @endif
                  @if ($rendicion->solicitud->extras_amount > 0 || $rendicion->subtotal_extras > 0)
                    <tr>
                      <td>Extras</td>
                      <td>{{ $rendicion->solicitud->extras_amount }}</td>
                      <td>Extras</td>
                      <td>{{ $rendicion->subtotal_extras }}</td>
                    </tr>
                  @endif
                  <tr><td colspan="4"></td></tr>
                  <tr>
                    <td colspan="2">Total en facturas válidas [Bs]</td>
                    <td colspan="2">{{ $rendicion->total_facturas_validas }}</td>
                  </tr>
                  <tr>
                    <td colspan="2">Total en recibos válidos [Bs]</td>
                    <td colspan="2">{{ $rendicion->total_recibos_validos }}</td>
                  </tr>

                  <tr><td colspan="4"></td></tr>
                  <tr>
                    <td colspan="2">Saldo favor empresa [Bs]</td>
                    <td colspan="2">{{ $rendicion->saldo_favor_empresa }}</td>
                  </tr>
                  <tr>
                    <td colspan="2">Saldo favor persona [Bs]</td>
                    <td colspan="2">{{ $rendicion->saldo_favor_persona }}</td>
                  </tr>

                  @if($rendicion->events->count() > 0)
                    <tr>
                      <th>Eventos:</th>
                      <td colspan="3">
                        <a href="/event/rendicion_viatico/{{ $rendicion->id }}">{{ 'Ver eventos' }}</a>
                      </td>
                    </tr>
                  @endif
                @else
                  <tr><td colspan="4"></td></tr>
                  <tr>
                    <th colspan="4">Motivo de anulación</th>
                  </tr>
                  <tr>
                    <td colspan="4">
                      {{ $rendicion->observaciones }}
                    </td>
                  </tr>
                @endif
              </tbody>
            </table>
          </div>
          
          @if($rendicion->estado != 'Cancelado' && $rendicion->estado != 'Aprobado')
            <div class="col-sm-12 mg20" align="center">
              @if(($rendicion->usuario_creacion == $user->id || $user->priv_level == 4) && ($rendicion->estado == 'Pendiente' || $rendicion->estado == 'Observado'))
                <a href="{{ '/rendicion_viatico/estado?mode=cancelar&id='.$rendicion->id }}"
                  title="Cancelar registro de rendición de viáticos" class="btn btn-danger">
                  <i class="fa fa-times"></i> Cancelar
                </a>
                <a href="/rendicion_viatico/{{ $rendicion->id }}/edit" title="Modificar rendición" class="btn btn-primary">
                  <i class="fa fa-pencil-square-o"></i> Modificar
                </a>
                <a href="{{ '/rendicion_viatico/estado?mode=presentar&id='.$rendicion->id }}"
                  title="Presentar rendición para su aprobación" class="btn btn-primary">
                  <i class="fa fa-send"></i> Presentar
                </a>
              @endif
              @if($rendicion->estado === 'Presentado' && (($user->priv_level >= 2 && $user->area == 'Gerencia Administrativa') || $user->priv_level == 4))
                <a href="#" id="boton_observar" title="Observar rendición" class="btn btn-warning botonObservar">
                  <i class="fa fa-eye"></i> Observar
                </a>
                <a href="{{ '/rendicion_viatico/estado?mode=aprobar&id='.$rendicion->id }}"
                  title="Aprobar rendición" class="confirm_close btn btn-success">
                  <i class="fa fa-check"></i> Aprobar
                </a>
              @endif
            </div>
          @endif

          @if($rendicion->estado === 'Presentado' && (($user->priv_level >= 2 && $user->area == 'Gerencia Administrativa') || $user->priv_level == 4))
            <div class="col-sm-12 mg10">
              <form method="post" action="{{ '/rendicion_viatico/estado?mode=observar&id='.$rendicion->id }}" id="observar_rendicion"
                  accept-charset="UTF-8" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                {{--<input type="hidden" name="id" value="{{ $rendicion->id }}">--}}

                <div class="col-sm-8 col-sm-offset-2" id="container" align="center">
                  <div class="input-group">
                    <span class="input-group-addon">
                      <textarea rows="3" class="form-control" name="observaciones" id="observaciones"
                        placeholder="Indique el motivo de observar esta rendición" disabled="disabled"></textarea>
                    </span>
                  </div>
                  <br>

                  @include('app.loader_gif')

                  <div class="form-group" align="center">
                    <button type="submit" id="submit_button" class="btn btn-warning"
                      onclick="this.disabled=true; $('#wait').show(); this.form.submit()" disabled="disabled">
                      <i class="fa fa-eye"></i> Observar
                    </button>
                  </div>
                </div>
              </form>
            </div>
          @endif
        </div>

        <div class="tab-pane fade {{ $tab && $tab == "respaldos" ? "in active" : "" }}" id="respaldos">
          <div class="col-lg-5 mg20">
            <a href="#" onclick="history.back();" class="btn btn-warning">
              <i class="fa fa-arrow-circle-left"></i> Volver
            </a>
            <a href="{{ '/rendicion_viatico' }}" class="btn btn-warning" title="Ir a la tabla de rendiciones">
              <i class="fa fa-arrow-circle-up"></i> Rendiciones
            </a>
          </div>

          <div class="col-sm-12 mg10">
            @include('app.session_flashed_messages', array('opt' => 0))
          </div>

          <div class="col-sm-12 mg10 mg-tp-px-10">
            <table class="table table-striped table-hover table-bordered">
              <thead>
                <tr>
                  <th width="25%">Código:</th>
                  <td width="25%">{{ $rendicion->codigo }}</td>
                  <th width="25%">Estado:</th>
                  <td>{{ $rendicion->estado }}</td>
                </tr>
              </thead>
              <tbody>
                @if ($rendicion->respaldos->count() > 0)
                  {{-- Facturas --}}
                  <tr><th colspan="4"></th></tr>
                  <tr>
                    <th colspan="4">Facturas:</th>
                  </tr>
                  <tr>
                    @if ($cant_facturas > 0)
                    <th colspan="4">
                      <table class="table table-bordered">
                        <tr>
                          <td>#</td>
                          <td width="10%">Fecha</td>
                          <td>NIT</td>
                          <td width="8%">Número</td>
                          <td title="Código de autorización">Cod. Autorización</td>
                          <td>Cod. Control</td>
                          <td>Razón Social</td>
                          <td width="20%">Detalle</td>
                          <td>Corresponde a</td>
                          <td>Monto [Bs]</td>
                          <td>Estado</td>
                          <td>Acciones</td>
                        </tr>
                        <?php $i = 0; ?>
                        @foreach($rendicion->respaldos as $respaldo)
                          @if ($respaldo->tipo_respaldo == 'Factura')
                            <?php $i++ ?>
                            <tr @if ($respaldo->estado == 'Observado') style="background-color: #d98c8c" {{--title="{{ $respaldo->observaciones }}"--}} @endif>
                              <td>{{ $i }}</td>
                              <td>
                                {{ $respaldo->fecha_respaldo!='0000-00-00 00:00:00' ?
                                  \Carbon\Carbon::parse($respaldo->fecha_respaldo)->format('d-m-Y') :
                                  'N/E' }}
                              </td>
                              <td>{{ $respaldo->nit }}</td>
                              <td>{{ $respaldo->nro_respaldo }}</td>
                              <td>{{ $respaldo->codigo_autorizacion }}</td>
                              <td>{{ $respaldo->codigo_control }}</td>
                              <td>{{ $respaldo->razon_social }}</td>
                              <td>{{ $respaldo->detalle }}</td>
                              <td>{{ $respaldo->corresponde_a }}</td>
                              <td align="right">{{ number_format($respaldo->monto, 2) }}</td>
                              <td>
                                @if ($respaldo->estado == "Observado")
                                  <div data-popover="true" data-html=true data-content="
                                    {{
                                        '<i class="fa fa-question-circle" title="Observación"></i>
                                            &ensp;
                                            '.($respaldo->observaciones ?: $respaldo->estado)
                                    }}
                                  ">
                                    {{ $respaldo->estado }}
                                  </div>
                                @else
                                  {{ $respaldo->estado }}
                                @endif
                              </td>
                              <td>
                                @if (($respaldo->estado == 'Pendiente' || $respaldo->estado == 'Observado') && ($rendicion->estado == 'Pendiente' || $rendicion->estado == 'Observado'))
                                  @if ($user->id == $respaldo->usuario_creacion || $user->priv_level == 4)
                                    <a data-toggle="modal" 
                                      data-id="{{ $respaldo->id }}"
                                      data-fecha="{{ $respaldo->fecha_respaldo }}"
                                      data-tipo="{{ $respaldo->tipo_respaldo }}"
                                      data-nit="{{ $respaldo->nit }}"
                                      data-nrorespaldo="{{ $respaldo->nro_respaldo }}"
                                      data-codigoautorizacion="{{ $respaldo->codigo_autorizacion }}"
                                      data-codigocontrol="{{ $respaldo->codigo_control }}"
                                      data-razonsocial="{{ $respaldo->razon_social }}"
                                      data-detalle="{{ $respaldo->detalle }}"
                                      data-correspondea="{{ $respaldo->corresponde_a }}"
                                      data-monto="{{ $respaldo->monto }}"
                                      title="Modificar item"
                                      class="open-rowBox"
                                      href="#rowBox"
                                      style="text-decoration: none">
                                      <i class="fa fa-pencil-square"></i>
                                    </a>
                                    &ensp;
                                    <a href="javascript:;" class="confirm_remove removeRow" data-id="{{ $respaldo->id }}" 
                                      title="Eliminar item" style="text-decoration: none">
                                      <i class="fa fa-trash"></i>
                                    </a>
                                    &ensp;
                                  @endif
                                  @if ($respaldo->files->count() === 0)
                                    <a href="/files/rendicion_respaldo/{{ $respaldo->id }}" title="Subir imagen o pdf" style="text-decoration: none">
                                      <i class="fa fa-upload"></i>
                                    </a>
                                    &ensp;
                                  @endif
                                @endif
                                @if ($respaldo->files->count() > 0)
                                  @foreach ($respaldo->files as $file)
                                    <a href="/display_file/{{ $file->id }}" target="_blank"
                                        title="Mostrar archivo en una nueva pestaña del navegador" style="text-decoration: none">
                                      <i class="fa fa-file"></i>
                                    </a>
                                    &ensp;
                                    @if ($respaldo->estado == 'Observado' || $user->priv_level === 4)
                                      <a href="/files/replace/{{ $file->id }}" title="Reemplazar archivo" style="text-decoration: none">
                                        <i class="fa fa-refresh"></i>
                                      </a>
                                      &ensp;
                                    @endif
                                    {{--
                                    <a href="/download/{{ $file->id }}" title="Descargar archivo" style="text-decoration: none">
                                      <i class="fa fa-download"></i>
                                    </a>
                                    --}}
                                  @endforeach
                                @endif
                                @if ($respaldo->estado == 'Pendiente' && $rendicion->estado == 'Presentado')
                                  @if(($user->priv_level >= 2 && $user->area == 'Gerencia Administrativa') || $user->priv_level == 4)
                                    <a href="{{ '/rendicion_respaldo/estado?mode=observar&id='.$respaldo->id }}"
                                      title="Observar" style="text-decoration: none">
                                      <i class="fa fa-eye"></i>
                                    </a>
                                    &ensp;
                                    <a href="{{ '/rendicion_respaldo/estado?mode=aprobar&id='.$respaldo->id }}"
                                      title="Aprobar" style="text-decoration: none">
                                      <i class="fa fa-check"></i>
                                    </a>
                                    &ensp;
                                  @endif
                                @endif
                              </td>
                            </tr>
                          @endif
                        @endforeach
                        @if($i == 0)
                          <tr>
                            <td colspan="12" align="center">
                              No se ha registrado ninguna factura para esta rendición
                            </td>
                          </tr>
                        @endif
                      </table>
                    </th>
                    @else
                    <td colspan="4" align="center">No se ha registrado ninguna factura para esta rendición</td>
                    @endif
                  </tr>

                  {{-- Recibos --}}
                  <tr><th colspan="4"></th></tr>
                  <tr>
                    <th colspan="4">Recibos:</th>
                  </tr>
                  <tr>
                    @if ($cant_recibos > 0)
                    <th colspan="4">
                      <table class="table table-bordered">
                        <tr>
                          <td>#</td>
                          <td width="10%">Fecha</td>
                          <td width="8%">Número</td>
                          <td>Razón Social</td>
                          <td width="20%">Detalle</td>
                          <td>Corresponde a</td>
                          <td>Monto [Bs]</td>
                          <td>Estado</td>
                          <td>Acciones</td>
                        </tr>
                        <?php $j = 0; ?>
                        @foreach($rendicion->respaldos as $respaldo)
                          @if ($respaldo->tipo_respaldo == 'Recibo')
                            <?php $j++ ?>
                            <tr @if ($respaldo->estado == 'Observado') style="background-color: #d98c8c" {{--title="{{ $respaldo->observaciones }}"--}} @endif>
                              <td>{{ $j }}</td>
                              <td>
                                {{ $respaldo->fecha_respaldo!='0000-00-00 00:00:00' ?
                                  \Carbon\Carbon::parse($respaldo->fecha_respaldo)->format('d-m-Y') :
                                  'N/E' }}
                              </td>
                              <td>{{ $respaldo->nro_respaldo }}</td>
                              <td>{{ $respaldo->razon_social }}</td>
                              <td>{{ $respaldo->detalle }}</td>
                              <td>{{ $respaldo->corresponde_a }}</td>
                              <td align="right">{{ number_format($respaldo->monto, 2) }}</td>
                              <td>
                                @if ($respaldo->estado == "Observado")
                                  <div data-popover="true" data-html=true data-content="
                                    {{
                                        '<i class="fa fa-question-circle" title="Observación"></i>
                                            &ensp;
                                            '.($respaldo->observaciones ?: $respaldo->estado)
                                    }}
                                  ">
                                    {{ $respaldo->estado }}
                                  </div>
                                @else
                                  {{ $respaldo->estado }}
                                @endif
                              </td>
                              <td>
                                @if (($respaldo->estado == 'Pendiente' || $respaldo->estado == 'Observado') && ($rendicion->estado == 'Pendiente' || $rendicion->estado == 'Observado'))
                                  @if ($user->id == $respaldo->usuario_creacion || $user->priv_level == 4)
                                    <a data-toggle="modal" 
                                      data-id="{{ $respaldo->id }}"
                                      data-fecha="{{ $respaldo->fecha_respaldo }}"
                                      data-tipo="{{ $respaldo->tipo_respaldo }}"
                                      data-nit="{{ $respaldo->nit }}"
                                      data-nrorespaldo="{{ $respaldo->nro_respaldo }}"
                                      data-codigoautorizacion="{{ $respaldo->codigo_autorizacion }}"
                                      data-codigocontrol="{{ $respaldo->codigo_control }}"
                                      data-razonsocial="{{ $respaldo->razon_social }}"
                                      data-detalle="{{ $respaldo->detalle }}"
                                      data-correspondea="{{ $respaldo->corresponde_a }}"
                                      data-monto="{{ $respaldo->monto }}"
                                      title="Modificar item"
                                      class="open-rowBox"
                                      href="#rowBox"
                                      style="text-decoration: none">
                                      <i class="fa fa-pencil-square"></i>
                                    </a>
                                    &ensp;
                                    <a href="javascript:;" class="confirm_remove removeRow" data-id="{{ $respaldo->id }}" 
                                      title="Eliminar item" style="text-decoration: none">
                                      <i class="fa fa-trash"></i>
                                    </a>
                                    &ensp;
                                    @if ($respaldo->files->count() === 0)
                                      <a href="/files/rendicion_respaldo/{{ $respaldo->id }}" title="Subir imagen o pdf" style="text-decoration: none">
                                        <i class="fa fa-upload"></i>
                                      </a>
                                      &ensp;
                                    @endif
                                  @endif
                                @endif
                                @if ($respaldo->files->count() > 0)
                                  @foreach ($respaldo->files as $file)
                                    <a href="/display_file/{{ $file->id }}" target="_blank"
                                        title="Mostrar archivo en una nueva pestaña del navegador" style="text-decoration: none">
                                      <i class="fa fa-file"></i>
                                    </a>
                                    &ensp;
                                    @if ($respaldo->estado == 'Observado' || $user->priv_level === 4)
                                      <a href="/files/replace/{{ $file->id }}" title="Reemplazar archivo" style="text-decoration: none">
                                        <i class="fa fa-refresh"></i>
                                      </a>
                                      &ensp;
                                    @endif
                                    {{--
                                    <a href="/download/{{ $file->id }}" title="Descargar archivo" style="text-decoration: none">
                                      <i class="fa fa-download"></i>
                                    </a>
                                    --}}
                                  @endforeach
                                @endif
                                @if ($respaldo->estado == 'Pendiente' && $rendicion->estado == 'Presentado')
                                  @if(($user->priv_level >= 2 && $user->area == 'Gerencia Administrativa') || $user->priv_level == 4)
                                    <a href="{{ '/rendicion_respaldo/estado?mode=observar&id='.$respaldo->id }}"
                                      title="Observar" style="text-decoration: none">
                                      <i class="fa fa-eye"></i>
                                    </a>
                                    &ensp;
                                    <a href="{{ '/rendicion_respaldo/estado?mode=aprobar&id='.$respaldo->id }}"
                                      title="Aprobar" style="text-decoration: none">
                                      <i class="fa fa-check"></i>
                                    </a>
                                    &ensp;
                                  @endif
                                @endif
                              </td>
                            </tr>
                          @endif
                        @endforeach
                        @if($j == 0)
                          <tr>
                            <td colspan="9" align="center">
                              No se ha registrado ningún recibo para esta rendición
                            </td>
                          </tr>
                        @endif
                      </table>
                    </th>
                    @else
                    <td colspan="4" align="center">No se ha registrado ningún recibo para esta rendición</td>
                    @endif
                  </tr>
                @else
                  <tr>
                    <td colspan="4" align="center">No se han cargado respaldos a esta rendición</td>
                  </tr>
                @endif
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- Row Modal -->
<div id="rowBox" class="modal fade" role="dialog">
    @include('app.rendicion_respaldo_modal', array('user' => $user, 'service' => $service, 'rendicion' => $rendicion, 'tipos_gasto' => $tipos_gasto))
</div>

 <!-- Search Modal -->
<div id="searchBox" class="modal fade" role="dialog">
  @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'rendicion_viaticos','id'=>0))
</div>

<form id="removeRow" action="{{ '/rendicion_respaldo' }}" method="post">
  <input type="hidden" name="_method" value="delete">
  <input type="hidden" name="_token" value="{{ csrf_token() }}">
</form>

@endsection

@section('footer')
@endsection

@section('javascript')
  <script src="{{ asset('app/js/set_current_url.js') }}"></script> {{-- For recording current url --}}
  <script>
    // TODO convert alert from id to class, all alerts should have the same delay
    $('#alert').delay(2000).fadeOut('slow');

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    /*
    var data = '', oc_id = '';
    function replace(element, id) {
      data = $.trim($(element).text());
      oc_id = id;
      var arr = data.split(' ');
      arr[0] = arr[0].replace(/,/g, '');
      $(element).html("<input type=\"text\" value=\"" + arr[0] + "\" id=\"editable\" />");
      $(element).find('input').focus();
      $(element).off();
    }
    */

    $(document).ready(function() {
    });

    $(document).on("click", ".open-rowBox", function () {
      var respaldoId = $(this).data('id');
      var respaldoFecha = new Date($(this).data('fecha')).toJSON().substring(0,10);
      console.log(respaldoFecha);
      var respaldoTipo = $(this).data('tipo');
      var respaldoNit = $(this).data('nit');
      var respaldoNroRespaldo = $(this).data('nrorespaldo');
      var respaldoCodigoAutorizacion = $(this).data('codigoautorizacion');
      var respaldoCodigoControl = $(this).data('codigocontrol');
      var respaldoRazonSocial = $(this).data('razonsocial');
      var respaldoDetalle = $(this).data('detalle');
      var respaldoCorrespondeA = $(this).data('correspondea');
      var respaldoMonto = $(this).data('monto');

      $('#rowBox .modal-body #respaldoForm').attr('action', respaldoId > 0 ? '/rendicion_respaldo/'+respaldoId : '/rendicion_respaldo');
      $("#rowBox .modal-body #_method").val( respaldoId > 0 ? 'put' : 'post' );
      $("#rowBox .modal-body #fecha_respaldo").val( respaldoFecha );
      $("#rowBox .modal-body #tipo_respaldo").val( respaldoTipo );
      $("#rowBox .modal-body #nit").val( respaldoNit );
      $("#rowBox .modal-body #nro_respaldo").val( respaldoNroRespaldo );
      $("#rowBox .modal-body #codigo_autorizacion").val( respaldoCodigoAutorizacion );
      $("#rowBox .modal-body #codigo_control").val( respaldoCodigoControl );
      $("#rowBox .modal-body #razon_social").val( respaldoRazonSocial );
      $("#rowBox .modal-body #detalle").val( respaldoDetalle );
      $("#rowBox .modal-body #corresponde_a").val( respaldoCorrespondeA );
      $("#rowBox .modal-body #monto").val( respaldoMonto );
    });

    $(document).on("click", ".removeRow", function () {
      var respaldoId = $(this).data('id');
      if (respaldoId > 0) {
        $('#removeRow').attr('action', '/rendicion_respaldo/'+respaldoId);
        $('#removeRow').submit();
      }
    });

    $("#wait").hide();
    $("#container").hide();

    var $submit_button = $('#submit_button'), $container = $('#container'), $observaciones = $('#observaciones');
    $(document).on("click", ".botonObservar", function () {
      //console.log('called');
      $container.show();
      $observaciones.removeAttr('disabled').show();
      $submit_button.removeAttr('disabled').show();
      $("html, body").animate({ scrollTop: $("#container").offset().top }, 1000);
    });

    $('.confirm_close').on('click', function () {
        return confirm('Está seguro de que desea aprobar esta rendición?');
    });

    $('.confirm_remove').on('click', function () {
        return confirm('Está seguro de que desea eliminar este respaldo?');
    });
  </script>
@endsection
