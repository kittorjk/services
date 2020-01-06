<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 30/11/2017
 * Time: 02:54 PM
 */
?>

@extends('layouts.master')

@section('header')
  @parent
  <link rel="stylesheet" href="{{ asset("app/css/custom_autocomplete.css") }}">
  <script src="{{ asset('https://cdnjs.cloudflare.com/ajax/libs/jquery.devbridge-autocomplete/1.2.27/jquery.autocomplete.js') }}"></script>

  <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

<div id="loginbox" class="mg-tp-px-50 mg-btm-px-40 col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1">

    <div class="panel panel-sky">
      <div class="panel-heading" align="center">
        <div class="panel-title">
            {{ 'Seleccione un proyecto' }}
        </div>
      </div>
      <div class="panel-body">
        <div class="mg20">
            <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                <i class="fa fa-arrow-left"></i>
            </a>
            <a href="{{ '/stipend_request' }}" class="btn btn-warning"
                title="Volver a la tabla de solicitudes de viáticos">
                <i class="fa fa-arrow-up"></i>
            </a>
        </div>

        @include('app.session_flashed_messages', array('opt' => 1))

        <form novalidate="novalidate" action="{{ '/stipend_request' }}" method="post" class="form-horizontal">
        
            <input type="hidden" name="_token" value="{{ csrf_token() }}">

            <fieldset>
              <legend class="col-md-10">Proyecto</legend>
              
              <div class="row">
                <div class="col-sm-12 col-md-12">
                  <p>
                      Nota.- Sólo se listan los proyectos que no se encuentran concluídos y que tienen sus fechas de inicio y fin configuradas
                  </p>
                </div>
                <div class="col-sm-12 col-md-12">
                  <div class="form-group{{ $errors->has('assignment_id') ? ' has-error' : '' }}">
                    <label for="assignment_id" class="col-md-4 control-label">(*) Proyecto</label>

                    <div class="col-md-6">
                      <select id="assignment_id" name="assignment_id" class="form-control">
                        <option value="" hidden="hidden">Seleccione una asignación</option>
                        @foreach($assignments as $assignment)
                          <option value="{{ $assignment->id }}" title="{{ $assignment->name }}"
                              {{ (old('assignment_id') && $assignment->id == old('assignment_id')) ? 'selected="selected"' : '' }}>
                              {{ str_limit($assignment->name,200) }}
                          </option>
                        @endforeach
                      </select>

                      @if ($errors->has('assignment_id'))
                        <span class="help-block">
                          <strong>{{ $errors->first('assignment_id') }}</strong>
                        </span>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
            </fieldset>

            @include('app.loader_gif')

            <div class="form-group" align="center">
                <a href="#" class="btn btn-primary" title="Continuar" id="botonContinuar" onclick="$('#wait').show();">
                    Continuar <i class="fa fa-arrow-right"></i>
                </a>
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

    $('#botonContinuar').click(function(event) {
      var assignment_id = $("#assignment_id").val();
      
      if (!assignment_id || assignment_id === '') {
          event.preventDefault();
          console.log('tried to click without selecting first');
      }
    });

    $(document).ready(function() {
      $("#wait").hide();
      $('#botonContinuar').attr('disabled', 'disabled').hide();
    });
    
    var $assignment_id = $('#assignment_id'), $botonContinuar = $('#botonContinuar');
    $assignment_id.change(function () {
        if ($assignment_id.val() && $assignment_id.val() !== '') {
            $botonContinuar.attr('href', '/stipend_request/create?asg='+$assignment_id.val());
            $botonContinuar.removeAttr('disabled').show();
        } else {
            $botonContinuar.attr('href', '#');
            $botonContinuar.attr('disabled', 'disabled').hide();
        }
    }).trigger('change');
    </script>
@endsection
