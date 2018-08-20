<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 20/09/2017
 * Time: 11:09 AM
 */
?>

@extends('layouts.master')

@section('header')
  @parent
@endsection

@section('content')

  <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">
    <div class="panel panel-brown">
      <div class="panel-heading" align="center">
        <div class="panel-title">
            {{ $line ? 'Modificar información de línea corporativa' : 'Registrar una nueva línea corporativa' }}
        </div>
      </div>
      <div class="panel-body">
        <div class="mg20">
          <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
            <i class="fa fa-undo"></i>
          </a>
          <a href="{{ '/corporate_line' }}" class="btn btn-warning" title="Volver al listado de líneas corporativas">
            <i class="fa fa-arrow-up"></i>
          </a>
        </div>

        @include('app.session_flashed_messages', array('opt' => 1))

        @if($line)
          <form id="delete" action="/corporate_line/{{ $line->id }}" method="post">
            <input type="hidden" name="_method" value="delete">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
          </form>
          <form novalidate="novalidate" action="{{ '/corporate_line/'.$line->id }}" method="post">
            <input type="hidden" name="_method" value="put">
        @else
          <form novalidate="novalidate" action="{{ '/corporate_line' }}" method="post">
        @endif
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <div class="form-group">
              <div class="input-group">

                <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                  <div class="input-group" style="width: 100%">
                    <label for="number" class="input-group-addon" style="width: 23%;text-align: left">
                        Número: <span class="pull-right">*</span>
                    </label>

                    <input required="required" type="number" class="form-control" name="number"
                            id="number" min="1111111" max="99999999"
                            value="{{ $line ? $line->number : old('number') }}"
                            placeholder="Número corporativo">
                  </div>

                  <div class="input-group" style="width: 100%">
                    <label for="service_area" class="input-group-addon" style="width: 23%;text-align: left">
                        Área de servicio: <span class="pull-right">*</span>
                    </label>

                    <input required="required" type="text" class="form-control" name="service_area"
                            id="service_area"
                            value="{{ $line ? $line->service_area : old('service_area') }}"
                            placeholder="Oficina o ciudad a la que se destina la línea">
                  </div>

                  <div class="input-group" style="width: 75%">
                    <span class="input-group-addon" style="width:31%; text-align: left">
                        Consumo prom.:
                    </span>

                    <input required="required" type="number" class="form-control" name="avg_consumption"
                            step="any" min="0"
                            value="{{ $line && $line->avg_consumption != 0 ? $line->avg_consumption :
                                old('avg_consumption') }}" placeholder="0.00">
                    <span class="input-group-addon">Bs</span>
                  </div>

                  <div class="input-group" style="width: 75%">
                    <span class="input-group-addon" style="width:31%; text-align: left">
                        Crédito asignado:
                    </span>

                    <input required="required" type="number" class="form-control" name="credit_assigned"
                            step="any" min="0"
                            value="{{ $line && $line->credit_assigned != 0 ? $line->credit_assigned :
                                old('credit_assigned') }}" placeholder="0.00">
                    <span class="input-group-addon">Bs</span>
                  </div>

                  <div class="input-group" style="width: 100%">
                    <label for="technology" class="input-group-addon" style="width: 23%;text-align: left">
                        Tecnología:
                    </label>

                    <select required="required" class="form-control" name="technology" id="technology">
                      <option value="" hidden>Seleccione la tecnología habilitada en el chip</option>
                      @foreach($technologies as $technology)
                        <option value="{{ $technology->technology }}"
                          {{ ($line && $line->technology==$technology->technology) ||
                                old('technology')== $technology->technology ? 'selected="selected"' : '' }}
                          >{{ $technology->technology }}</option>
                      @endforeach
                      <option value="Otro">Otra</option>
                    </select>
                  </div>
                  <input required="required" type="text" class="form-control" name="other_technology"
                        id="other_technology" placeholder="Indique la tecnología habilitada en el chip"
                        disabled="disabled">

                  <div class="input-group" style="width: 100%">
                    <label for="pin" class="input-group-addon" style="width: 23%;text-align: left">
                        PIN:
                    </label>

                    <input required="required" type="number" class="form-control" name="pin"
                            id="pin" min="0000" max="99999"
                            value="{{ $line && $line->pin != 0 ? $line->pin : old('pin') }}"
                            placeholder="Código PIN del chip">
                  </div>

                  <div class="input-group" style="width: 100%">
                    <label for="puk" class="input-group-addon" style="width: 23%;text-align: left">
                        PUK:
                    </label>

                    <input required="required" type="text" class="form-control" name="puk"
                            id="puk" value="{{ $line ? $line->puk : old('puk') }}"
                            placeholder="Código PUK del chip">
                  </div>

                  <div class="input-group" style="width: 100%">
                    <label for="observations" class="input-group-addon" style="width: 23%;text-align: left">
                        Observaciones:
                    </label>

                    <textarea rows="3" required="required" class="form-control" name="observations"
                            id="observations"
                            placeholder="Observaciones de la línea o de su asignación">{{ $line ?
                            $line->observations : old('observations') }}</textarea>
                  </div>

                </div>

              </div>
            </div>

            @include('app.loader_gif')

            <div class="form-group" align="center">
              <button type="submit" class="btn btn-success"
                    onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                <i class="fa fa-floppy-o"></i> Guardar
              </button>

              {{--
              @if($line && $user->priv_level == 4)
                <button type="submit" form="delete" class="btn btn-danger">
                  <i class="fa fa-trash-o"></i> Eliminar
                </button>
              @endif
              --}}
            </div>
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
    var $technology = $('#technology'), $other_technology = $('#other_technology');

    $technology.change(function () {
      if ($technology.val() === 'Otro') {
        $other_technology.removeAttr('disabled').show();
      } else {
        $other_technology.attr('disabled', 'disabled').val('').hide();
      }
    }).trigger('change');

    $(document).ready(function() {
      $("#wait").hide();
    });
  </script>
@endsection
