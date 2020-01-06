<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 01/08/2017
 * Time: 12:55 PM
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
                    {{ 'Solicitud de viáticos #'.$viatic->id }}
                </div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-undo"></i>
                    </a>
                    <a href="{{ '/rbs_viatic' }}" class="btn btn-warning" title="Volver a resumen de solicitudes">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <form novalidate="novalidate" action="{{ '/rbs_viatic/status/'.$viatic->id.'?action='.$action }}"
                      method="post" class="form-horizontal">

                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    <fieldset>

                        <legend class="col-md-10">
                            {{ $action=='observe' ? 'Observar solicitud' : '' }}
                            {{ $action=='complete' ? 'Confirmar pago de solicitud' : '' }}
                            {{ $action=='reject' ? 'Rechazar solicitud' : '' }}
                            {{ $action=='cancel' ? 'Cancelar solicitud' : '' }}
                        </legend>

                        <div class="row">
                            <div class="col-md-12 col-sm-12">

                                <div class="form-group{{ $errors->has('comments') ? ' has-error' : '' }}">
                                    <label for="comments" class="col-md-4 control-label">
                                        {{ $action=='complete' ? 'Información adicional' : 'Indique el motivo' }}
                                    </label>

                                    <div class="col-md-6">
                                        <textarea rows="5" class="form-control" id="comments" placeholder="Escriba aquí..."
                                                  name="comments">{{ old('extra_expenses_detail') }}</textarea>

                                        @if($errors->has('comments'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('comments') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                            </div>
                        </div>

                    </fieldset>

                    @include('app.loader_gif')

                    <div class="form-group" align="center">
                        <button type="submit" class="btn btn-primary"
                                onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                            <i class="fa fa-arrow-right"></i> Cambiar estado
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

        $(document).ready(function(){
            $("#wait").hide();
        });
    </script>
@endsection
