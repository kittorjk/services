<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 01/12/2017
 * Time: 04:31 PM
 */
?>

@extends('layouts.master')

@section('header')
    @parent

    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')
  <div id="loginbox" class="mg-tp-px-50 col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1">
    <div class="panel panel-sky">
      <div class="panel-heading" align="center">
        <div class="panel-title">
          {{ 'Respaldo de rendición de viáticos '.($respaldo->rendicion ? $respaldo->rendicion->codigo : '') }}
        </div>
      </div>
      <div class="panel-body">
        <div class="mg20">
          <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
            <i class="fa fa-arrow-left"></i>
          </a>
        </div>

        @include('app.session_flashed_messages', array('opt' => 1))

        <form novalidate="novalidate" action="{{ '/rendicion_respaldo/estado?mode='.$mode.'&id='.$respaldo->id }}"
              method="post" class="form-horizontal">

          <input type="hidden" name="_token" value="{{ csrf_token() }}">

          <fieldset>
            <legend class="col-md-10">
              {{ $mode=='observar' ? 'Observar' : '' }}
            </legend>

            <div class="row">
              <div class="col-md-12 col-sm-12">
                <div class="form-group{{ $errors->has('observaciones') ? ' has-error' : '' }}">
                  <label for="observations" class="col-md-4 control-label">
                    {{ $mode == 'observar' ? '(*) Indique el motivo' : '' }}
                    {{-- $mode=='complete'||$mode=='approve' ? 'Información adicional (opcional)' : '' --}}
                  </label>

                  <div class="col-md-6">
                    <textarea rows="5" class="form-control" id="observaciones" placeholder="Escriba aquí..."
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

          @include('app.loader_gif')

          <div class="form-group" align="center">
            <button type="submit"
              onclick="this.disabled=true; $('#wait').show(); this.form.submit()"
              class="{{ ($mode=='observar' ? 'btn btn-warning' : '') }}">
              @if($mode == 'observar')
                <i class="fa fa-eye"></i> Observar
              @endif
            </button>
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

    $(document).ready(function() {
      $("#wait").hide();
    });
  </script>
@endsection
