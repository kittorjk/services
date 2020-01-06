<?php
/**
 * User: Admininstrador
 * Date: 26/08/2018
 * Time: 09:07 PM
 */
?>

@extends('layouts.master')

@section('header')
  @parent
  <style>
    input[type=date]:before {  right: 10px;  }
  </style>
  <link rel="stylesheet" href="{{ asset("app/css/custom_autocomplete.css") }}">
  <script src="{{ asset('https://cdnjs.cloudflare.com/ajax/libs/jquery.devbridge-autocomplete/1.2.27/jquery.autocomplete.js') }}"></script>

  <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

  <div id="loginbox" class="mg-tp-px-50 mg-btm-px-40 col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1">

    <div class="panel panel-sky">
      <div class="panel-heading" align="center">
        <div class="panel-title">
          {{ $rendicion ? 'Modificar rendición de viáticos' : 'Registrar rendición de viáticos' }}
        </div>
      </div>
      <div class="panel-body">
        <div class="mg20">
          <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
            <i class="fa fa-arrow-left"></i>
          </a>
          <a href="{{ '/rendicion_viatico' }}" class="btn btn-warning"
            title="Volver a la tabla de rendiciones de viáticos">
            <i class="fa fa-arrow-up"></i>
          </a>
        </div>

        @include('app.session_flashed_messages', array('opt' => 1))

        @if($rendicion)
          <form id="delete" action="{{ '/rendicion_viatico/'.$rendicion->id }}" method="post">
            <input type="hidden" name="_method" value="delete">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
          </form>
          <form novalidate="novalidate" action="{{ '/rendicion_viatico/'.$rendicion->id }}" method="post" class="form-horizontal">
            <input type="hidden" name="_method" value="put">
        @else
          <form novalidate="novalidate" action="{{ '/rendicion_viatico' }}" method="post" class="form-horizontal">
        @endif
            <input type="hidden" name="_token" value="{{ csrf_token() }}">

            <fieldset>
              <legend class="col-md-10">Datos generales</legend>

              <div class="row">
                <div class="col-md-12 col-sm-12">

                  <div class="form-group{{ $errors->has('stipend_request_id') ? ' has-error' : '' }}">
                    <label for="stipend_request_id" class="col-md-4 control-label">(*) Solicitud</label>

                    <div class="col-md-6">
                      <select id="stipend_request_id" name="stipend_request_id" class="form-control">
                        <option value="" hidden="hidden">Seleccione una solicitud</option>
                        @foreach ($solicitudes as $solicitud)
                          <option value="{{ $solicitud->id }}" title="{{ $solicitud->reason }}" 
                            {{ ($rendicion && $rendicion->stipend_request_id === $solicitud->id) ||
                              old('stipend_request_id') === $solicitud->id ? 'selected="selected"' : '' }}>
                            {{ str_limit($solicitud->code, 200) }}
                          </option>
                        @endforeach
                      </select>

                      @if ($errors->has('stipend_request_id'))
                        <span class="help-block">
                          <strong>{{ $errors->first('stipend_request_id') }}</strong>
                        </span>
                      @endif
                    </div>
                  </div>

                  <div class="form-group{{ $errors->has('nro_rendicion') ? ' has-error' : '' }}">
                    <label for="nro_rendicion" class="col-md-4 control-label">(*) Número de rendición</label>

                    <div class="col-md-6">
                      <input id="nro_rendicion" type="text"
                        class="form-control"
                        name="nro_rendicion" placeholder="Número de rendición"
                        value="{{ $rendicion && $rendicion->nro_rendicion ?
                          $rendicion->nro_rendicion : (old('nro_rendicion') ?: $nro_rendicion) }}" 
                        required readonly="readonly">

                      @if ($errors->has('nro_rendicion'))
                        <span class="help-block">
                          <strong>{{ $errors->first('nro_rendicion') }}</strong>
                        </span>
                      @endif
                    </div>
                  </div>

                  <div class="form-group{{ $errors->has('fecha_deposito') ? ' has-error' : '' }}">
                    <label for="fecha_deposito" class="col-md-4 control-label">(*) Fecha depósito</label>

                    <div class="col-md-3">
                      <input id="fecha_deposito" type="date" class="form-control" name="fecha_deposito"
                        step="1" max="{{ date('Y-m-d') }}"
                        value="{{ $rendicion ? $rendicion->fecha_deposito->format('Y-m-d') : old('fecha_deposito') /*?: date('Y-m-d')*/ }}">

                      @if($errors->has('fecha_deposito'))
                        <span class="help-block">
                          <strong>{{ $errors->first('fecha_deposito') }}</strong>
                        </span>
                      @endif
                    </div>
                  </div>

                  <div class="form-group{{ $errors->has('monto_deposito') ? ' has-error' : '' }}">
                    <label for="monto_deposito" class="col-md-4 control-label">(*) Monto depositado [Bs]</label>

                    <div class="col-md-6">
                      {{--
                      <input id="monto_deposito" type="text"
                        class="form-control"
                        name="monto_deposito" placeholder="Monto depositado según solicitud"
                        value="{{ $rendicion && $rendicion->monto_deposito ?
                          $rendicion->monto_deposito : old('monto_deposito') }}"
                        required>--}}
                      <input required="required" type="number" class="form-control" name="monto_deposito"
                        id="monto_deposito" step="any" min="0" placeholder="0.00"
                        value="{{ $rendicion && $rendicion->monto_deposito ?
                          $rendicion->monto_deposito : old('monto_deposito') /*?: 0*/ }}">
                      {{--<span class="input-group-addon" style="width:55px">Bs</span>--}}

                      @if ($errors->has('monto_deposito'))
                        <span class="help-block">
                          <strong>{{ $errors->first('monto_deposito') }}</strong>
                        </span>
                      @endif
                    </div>
                  </div>

                  <div class="form-group{{ $errors->has('observaciones') ? ' has-error' : '' }}">
                    <label for="observaciones" class="col-md-4 control-label">Observaciones</label>

                    <div class="col-md-6">
                      <textarea rows="4" class="form-control" id="observaciones" placeholder="Escriba aquí..."
                          name="observaciones">{{ old('observaciones') }}</textarea>

                      @if($errors->has('observaciones'))
                        <span class="help-block">
                          <strong>{{ $errors->first('observaciones') }}</strong>
                        </span>
                      @endif
                    </div>
                  </div>

                </div>
              </div>
            </fieldset>

            {{--
            <fieldset>
              <legend class="col-md-10">Facturas</legend>

              <div class="row">
                <div class="col-md-12 col-sm-12">

                  <table>
                    <thead>
                      <tr>
                        <th>Fecha</th>
                        <th>NIT</th>
                        <th>Número</th>
                        <th>Autorización</th>
                        <th>Código control</th>
                        <th>Razón social</th>
                        <th>Detalle</th>
                        <th>Corresponde a</th>
                        <th>Monto [Bs]</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>
                          <input id="fecha_respaldo[0]" type="date" class="form-control" name="fecha_respaldo[0]"
                            step="1" value="{{ $rendicion && $rendicion->facturas ? $rendicion->facturas[0]->fecha_respaldo->format('Y-m-d') : (old('fecha_respaldo') ?: date('Y-m-d')) }}">
                        </td>
                        <td>
                          <input id="nit[0]" type="text" class="form-control"
                            name="nit[0]" placeholder="NIT"
                            value="{{ $rendicion && $rendicion->facturas ? $rendicion->facturas[0]->nit : old('nit') }}" required>
                        </td>
                        <td>
                          <input id="nro_respaldo[0]" type="text" class="form-control"
                            name="nro_respaldo[0]" placeholder="#"
                            value="{{ $rendicion && $rendicion->facturas ? $rendicion->facturas[0]->nro_respaldo : old('nro_respaldo') }}" required>
                        </td>
                        <td>
                          <input id="codigo_autorizacion[0]" type="text" class="form-control"
                            name="codigo_autorizacion[0]" placeholder="código"
                            value="{{ $rendicion && $rendicion->facturas ? $rendicion->facturas[0]->codigo_autorizacion : old('codigo_autorizacion') }}" required>
                        </td>
                        <td>
                          <input id="codigo_control[0]" type="text" class="form-control"
                            name="codigo_control[0]" placeholder="#"
                            value="{{ $rendicion && $rendicion->facturas ? $rendicion->facturas[0]->codigo_control : old('codigo_control') }}" required>
                        </td>
                        <td>
                          <input id="razon_social[0]" type="text" class="form-control"
                            name="razon_social[0]" placeholder="#"
                            value="{{ $rendicion && $rendicion->facturas ? $rendicion->facturas[0]->razon_social : old('razon_social') }}" required>
                        </td>
                        <td>
                          <input id="detalle[0]" type="text" class="form-control"
                            name="detalle[0]" placeholder="#"
                            value="{{ $rendicion && $rendicion->facturas ? $rendicion->facturas[0]->detalle : old('detalle') }}" required>
                        </td>
                        <td>
                          <select id="corresponde_a[0]" name="corresponde_a[0]" class="form-control">
                            <option value="" hidden="hidden">Seleccionar</option>
                            {{--
                            @foreach($tipos_gasto as $tipo_gasto)
                              <option value="{{ $tipo_gasto }}"
                                {{ ($rendicion && $rendicion->facturas ? $rendicion->facturas[0]->corresponde_a === $tipo_gasto) ||
                                  old('corresponde_a') === $tipo_gasto ? 'selected="selected"' : '' }}>
                                {{ $tipo_gasto }}
                              </option>
                            @endforeach
                            
                          </select>
                        </td>
                        <td>
                          <input id="monto[0]" type="number" class="form-control"
                            name="monto[0]" step="any" min="0"
                            value="{{ $rendicion && $rendicion->facturas ? $rendicion->facturas[0]->monto : old('monto') }}"
                            placeholder="Bs" required>
                        </td>
                      </tr>

                      <div id="contenedor_facturas">
                        {{-- Contenedor para campos dinámicos
                      </div>
                    </tbody>
                  </table>

                  <div class="form-group">
                    <div class="col-md-10">
                      <a href="#" id="agregarFactura" class="pull-right">
                        <i class="fa fa-plus"></i> Agregar factura
                      </a>
                    </div>
                  </div>

                  <div id="clonableFacturas" style="display: none">
                    <tr class="dynamic">
                      <td>
                        <input id="fecha_respaldo[*]" type="date" class="form-control dynamic" 
                          name="fecha_respaldo[*]"
                          step="1" value="{{ (old('fecha_respaldo') ?: date('Y-m-d')) }}">
                      </td>
                      <td>
                        <input id="nit[*]" type="text" class="form-control dynamic"
                          name="nit[*]" placeholder="NIT"
                          value="{{ old('nit') }}" required>
                      </td>
                      <td>
                        <input id="nro_respaldo[*]" type="text" class="form-control dynamic"
                          name="nro_respaldo[*]" placeholder="#"
                          value="{{ old('nro_respaldo') }}" required>
                      </td>
                      <td>
                        <input id="codigo_autorizacion[*]" type="text" class="form-control dynamic"
                          name="codigo_autorizacion[*]" placeholder="código"
                          value="{{ old('codigo_autorizacion') }}" required>
                      </td>
                      <td>
                        <input id="codigo_control[*]" type="text" class="form-control dynamic"
                          name="codigo_control[*]" placeholder="#"
                          value="{{ old('codigo_control') }}" required>
                      </td>
                      <td>
                        <input id="razon_social[*]" type="text" class="form-control dynamic"
                          name="razon_social[*]" placeholder="#"
                          value="{{ old('razon_social') }}" required>
                      </td>
                      <td>
                        <input id="detalle[*]" type="text" class="form-control dynamic"
                          name="detalle[*]" placeholder="#"
                          value="{{ old('detalle') }}" required>
                      </td>
                      <td>
                        <select id="corresponde_a[*]" name="corresponde_a[*]" class="form-control dynamic">
                          <option value="" hidden="hidden">Seleccionar</option>
                          @foreach ($tipos_gasto as $tipo_gasto)
                            <option value="{{ $tipo_gasto }}"
                              {{ old('corresponde_a') === $tipo_gasto ? 'selected="selected"' : '' }}>
                              {{ $tipo_gasto }}
                            </option>
                          @endforeach
                        </select>
                      </td>
                      <td>
                        <input id="monto[*]" type="number" class="form-control dynamic"
                          name="monto[*]" step="any" min="0"
                          value="{{ old('monto') }}" placeholder="Bs" required>
                      </td>
                    </tr>
                  </div>

                </div>
              </div>
            </fieldset>
            --}}

            {{--
            <fieldset>
              <legend class="col-md-10">Montos</legend>

              <div class="row">
                <div class="col-md-12 col-sm-12">
                  <div class="form-group {{ $errors->has('per_day_amount') || $errors->has('hotel_amount') ? ' has-error' : '' }}">
                    <label for="per_day_amount" class="col-md-4 control-label"
                      title="Incluye: Desayuno, almuerzo y/o cena.">
                      Alimentación
                    </label>
                    <div class="col-md-2">
                      <input id="per_day_amount" type="number" class="form-control viatico dynamic"
                            name="per_day_amount" step="any" min="0"
                            value="{{ $rendicion && $rendicion->per_day_amount!=0 ? $rendicion->per_day_amount : old('per_day_amount') }}"
                            placeholder="Bs" required>

                      @if($errors->has('per_day_amount'))
                        <span class="help-block">
                          <strong>{{ $errors->first('per_day_amount') }}</strong>
                        </span>
                      @endif
                    </div>

                    <label for="hotel_amount" class="col-md-2 control-label">Alojamiento</label>
                    <div class="col-md-2">
                      <input id="hotel_amount" type="number" class="form-control viatico dynamic"
                            name="hotel_amount" step="any" min="0"
                            value="{{ $rendicion && $rendicion->hotel_amount!=0 ? $rendicion->hotel_amount : old('hotel_amount') }}"
                            placeholder="Bs" required>

                      @if($errors->has('hotel_amount'))
                        <span class="help-block">
                          <strong>{{ $errors->first('hotel_amount') }}</strong>
                        </span>
                      @endif
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12 col-sm-12">
                  <div class="form-group {{ $errors->has('viatico_dia') ?' has-error' : '' }}">
                    <label for="viatico_dia" class="col-md-4 control-label"
                        title="Suma de alimentaciòn y alojamiento">
                        Viático por día 
                    </label>
                    <div class="col-md-2">
                      <input id="viatico_dia" type="number" class="form-control dynamic"
                            name="viatico_dia" step="any" min="0" readonly="readonly"
                            value="{{ old('additional') }}" placeholder="Bs" required>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12 col-sm-12">
                  <div class="form-group {{ $errors->has('transport_amount')||$errors->has('gas_amount') ? ' has-error' : '' }}">
                    <label for="transport_amount" class="col-md-4 control-label">Transporte (Pasajes)</label>
                    <div class="col-md-2">
                      <input id="transport_amount" type="number" class="form-control amount dynamic"
                            name="transport_amount" step="any" min="0"
                            value="{{ $rendicion && $rendicion->transport_amount!=0 ?
                                        $rendicion->transport_amount : old('transport_amount') }}"
                            placeholder="Bs" required>

                      @if($errors->has('transport_amount'))
                        <span class="help-block">
                          <strong>{{ $errors->first('transport_amount') }}</strong>
                        </span>
                      @endif
                    </div>

                    <label for="gas_amount" class="col-md-2 control-label">Combustible</label>
                    <div class="col-md-2">
                      <input id="gas_amount" type="number" class="form-control amount dynamic"
                            name="gas_amount" step="any" min="0"
                            value="{{ $rendicion && $rendicion->gas_amount!=0 ?
                                        $rendicion->gas_amount : old('gas_amount') }}"
                            placeholder="Bs" required>

                      @if($errors->has('gas_amount'))
                        <span class="help-block">
                          <strong>{{ $errors->first('gas_amount') }}</strong>
                        </span>
                      @endif
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12 col-sm-12">
                  <div class="form-group {{ $errors->has('taxi_amount')||$errors->has('comm_amount') ? ' has-error' : '' }}">
                    <label for="taxi_amount" class="col-md-4 control-label">Taxi (Pasajes)</label>
                    <div class="col-md-2">
                      <input id="taxi_amount" type="number" class="form-control amount dynamic"
                            name="taxi_amount" step="any" min="0"
                            value="{{ $rendicion && $rendicion->taxi_amount!=0 ?
                                    $rendicion->taxi_amount : old('taxi_amount') }}"
                            placeholder="Bs" required>

                      @if($errors->has('taxi_amount'))
                        <span class="help-block">
                          <strong>{{ $errors->first('taxi_amount') }}</strong>
                        </span>
                      @endif
                    </div>

                    <label for="comm_amount" class="col-md-2 control-label"
                        title="Incluye: tarjetas de recarga de crédito, llamadas desde cabinas, etc.">
                        Comunicaciones
                    </label>
                    <div class="col-md-2">
                      <input id="comm_amount" type="number" class="form-control amount dynamic"
                            name="comm_amount" step="any" min="0"
                            value="{{ $rendicion && $rendicion->comm_amount!=0 ?
                                    $rendicion->comm_amount : old('comm_amount') }}"
                            placeholder="Bs" required>

                      @if($errors->has('comm_amount'))
                        <span class="help-block">
                          <strong>{{ $errors->first('comm_amount') }}</strong>
                        </span>
                      @endif
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12 col-sm-12">
                  <div class="form-group {{ $errors->has('materials_amount')||$errors->has('extras_amount') ? ' has-error' : '' }}">
                    <label for="materials_amount" class="col-md-4 control-label">Compra de materiales</label>
                    <div class="col-md-2">
                      <input id="materials_amount" type="number" class="form-control amount dynamic"
                            name="materials_amount" step="any" min="0"
                            value="{{ $rendicion && $rendicion->materials_amount!=0 ?
                                    $rendicion->materials_amount : old('materials_amount') }}"
                            placeholder="Bs" required>

                      @if($errors->has('materials_amount'))
                        <span class="help-block">
                          <strong>{{ $errors->first('materials_amount') }}</strong>
                        </span>
                      @endif
                    </div>

                    <label for="extras_amount" class="col-md-2 control-label">Extras</label>
                    <div class="col-md-2">
                      <input id="extras_amount" type="number" class="form-control amount dynamic"
                            name="extras_amount" step="any" min="0"
                            value="{{ $rendicion && $rendicion->extras_amount!=0 ?
                                    $rendicion->extras_amount : old('extras_amount') }}"
                            placeholder="Bs" required>

                      @if($errors->has('extras_amount'))
                        <span class="help-block">
                          <strong>{{ $errors->first('extras_amount') }}</strong>
                        </span>
                      @endif
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12 col-sm-12">
                  <div class="form-group {{ $errors->has('additional') ?' has-error' : '' }}">
                    <label for="additional" class="col-md-4 control-label"
                        title="Incluye: transporte, combustible, recargas de celular entre otros">
                        Adicionales
                    </label>
                    <div class="col-md-2">
                      <input id="additional" type="number" class="form-control dynamic"
                            name="additional" step="any" min="0" readonly="readonly"
                            value="{{ $rendicion ? $rendicion->additional : old('additional') }}"
                            placeholder="Bs" required>

                      @if($errors->has('additional'))
                        <span class="help-block">
                          <strong>{{ $errors->first('additional') }}</strong>
                        </span>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
            </fieldset>

            <fieldset>
              <legend class="col-md-10">Información adicional</legend>

              <div class="row">
                <div class="col-md-12 col-sm-12">
                  <div class="form-group{{ $errors->has('reason') ? ' has-error' : '' }}">
                    <label for="reason" class="col-md-4 control-label">(*) Trabajo a realizar</label>
                    <div class="col-md-6">
                      <textarea rows="3" class="form-control" id="reason"
                                name="reason">{{ $rendicion ? $rendicion->reason : old('reason') }}</textarea>

                      @if($errors->has('reason'))
                        <span class="help-block">
                          <strong>{{ $errors->first('reason') }}</strong>
                        </span>
                      @endif
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12 col-sm-12">
                  <div class="form-group{{ $errors->has('work_area') ? ' has-error' : '' }}">
                    <label for="work_area" class="col-md-4 control-label">Área de trabajo</label>
                    <div class="col-md-6">
                      <select id="work_area" name="work_area" class="form-control">
                        <option value="" hidden="hidden">Seleccione un área</option>
                        <option value="FO" {{ ($rendicion && $rendicion->work_area=='FO') ||
                            old('work_area')=='FO' ? 'selected="selected"' : '' }}>FO - Fibra óptica</option>
                        <option value="RBS" {{ ($rendicion && $rendicion->work_area=='RBS') ||
                            old('work_area')=='RBS' ? 'selected="selected"' : '' }}>RBS - Radiobases</option>
                      </select>

                      @if ($errors->has('work_area'))
                        <span class="help-block">
                          <strong>{{ $errors->first('work_area') }}</strong>
                        </span>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
            </fieldset>
            --}}

            @include('app.loader_gif')

            <div class="form-group" align="center">
              <button type="submit" class="btn btn-success" onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                <i class="fa fa-save"></i> Registrar rendición
              </button>

              {{--
                @if($rendicion && $user->priv_level==4)
                  <button type="submit" form="delete" class="btn btn-danger">
                    <i class="fa fa-trash-o"></i> Eliminar
                  </button>
                @endif
              --}}
            </div>
            {{ csrf_field() }}
          </form>

      </div>
    </div>
  </div>

@endsection

@section('footer')
@endsection

@section('javascript')
  <script src="{{ asset('app/js/prevent_enter_form_submit.js') }}"></script> {{-- Avoid submitting form on enter press --}}
  <script>
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    /*
    $('#agregarFacturas').click(function(e) {
      e.preventDefault();

      var nroNuevaFactura = $('.nueva_factura').length + 1;

      var clonableFacturas = $('#clonableFacturas').clone(true).removeAttr('id').removeAttr('style');

      var NuevaFilaFactura = $('<div>').data('nuevaFacturaNro', nroNuevaFactura).
        addClass('nueva_factura').append(clonableFacturas);

        $('.dynamic', NuevaFilaFactura).each(function() {
          var oldName = $(this).attr('name');
          var oldId = $(this).attr('id');

          $(this).attr('name', oldName.replace('*', nroNuevaFactura));
          $(this).attr('id', oldId.replace('*', nroNuevaFactura));
        });

        NuevaFilaFactura.appendTo('#contenedor_facturas');
    });
    */

    $(document).ready(function() {
      $("#wait").hide();
    });

    /*
    $('.amount').keyup(function() {
      update_amount()
    });

    function update_amount() {
      var additional = $("#additional"), sum = 0;

      $('.amount').each(function() {
        sum += Number($(this).val());
      });

      additional.val(sum);
    }

    $('.viatico').keyup(function() {
      actualizar_viatico()
    });

    function actualizar_viatico() {
      var viatico_dia = $("#viatico_dia"), suma = 0;

      $('.viatico').each(function() {
        suma += Number($(this).val());
      });

      viatico_dia.val(suma);
    }

    $(document).ready(function() {
      $("#wait").hide();
      update_amount();
      actualizar_viatico();
    });

    function autocomplete_employee(e){
      $(e).autocomplete({
        type: 'post',
        serviceUrl:'/autocomplete/employees',
        dataType: 'JSON'
      });
    }
    */
  </script>
@endsection
