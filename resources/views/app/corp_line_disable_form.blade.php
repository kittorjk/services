<?php
/**
 * User: Admininstrador
 * Date: 19/08/2018
 * Time: 22:12 PM
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
        <div class="panel-title">{{ 'Dar de baja línea corporativa' }}</div>
      </div>
      <div class="panel-body">
        <div class="mg20">
          <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
            <i class="fa fa-arrow-circle-left"></i>
          </a>
          <a href="{{ '/corporate_line' }}" class="btn btn-warning" title="Volver a tabla de líneas corporativas">
            <i class="fa fa-arrow-circle-up"></i>
          </a>
        </div>

        @include('app.session_flashed_messages', array('opt' => 1))

        @if($line)
          <form novalidate="novalidate" action="{{ '/corporate_line/disable' }}" method="post">
            <input type="hidden" name="_method" value="put">
        @endif
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="line_id" value="{{ $line->id }}">

            <div class="form-group">
              <div class="input-group">
                <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                  <div class="input-group" style="width: 100%">
                    <label for="number" class="input-group-addon" style="width: 23%;text-align: left">
                        Número:
                    </label>

                    <input required="required" type="text" class="form-control" name="number"
                            id="number" value="{{ $line ? $line->number : old('number') }}"
                            readonly="readonly">
                  </div>

                  <div class="input-group" style="width: 100%">
                    <label for="service_area" class="input-group-addon" style="width: 23%;text-align: left">
                        Área de servicio:
                    </label>

                    <input required="required" type="text" class="form-control" name="service_area" id="service_area"
                            value="{{ $line ? $line->service_area : old('service_area') }}" readonly="readonly">
                  </div>

                  <div class="input-group" style="width: 100%">
                    <label for="observations" class="input-group-addon" style="width: 23%;text-align: left">
                        Motivo de baja: <span class="pull-right">(*)</span>
                    </label>

                    <textarea rows="5" required="required" class="form-control" name="observations"
                            id="observations"
                            placeholder="Especifique el motivo para dar de baja esta línea">{{
                            $line ? $line->observations : old('observations') }}</textarea>
                  </div>

                </div>
              </div>
            </div>

            @include('app.loader_gif')

            <div class="form-group" align="center">
              <button type="submit" class="btn btn-danger"
                    onclick="this.disabled=true; $('#wait').show(); this.form.submit()"
                    title="Una vez que se dé de baja esta línea ya no podrá modificar su información">
                <i class="fa fa-warning"></i> Dar de baja
              </button>
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
    $(document).ready(function() {
      $("#wait").hide();
    });
  </script>
@endsection
