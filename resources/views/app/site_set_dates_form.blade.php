<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 29/01/2018
 * Time: 12:44 PM
 */
?>

@extends('layouts.master')

@section('header')
    @parent
    <style>
        input[type=date]:before {  right: 10px;  }
    </style>
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 mg-btm-px-40 col-md-6 col-md-offset-3 col-sm-10 col-sm-offset-1">

        <div class="panel panel-sky">
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    {{ $assignment ? 'Fijar fechas de los sitios - Asignación: '.$assignment->name : 'Fijar fechas' }}
                </div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-undo"></i>
                    </a>
                    <a href="/site{{ $assignment&&$assignment->id!=0 ? '/'.$assignment->id : '' }}"
                       class="btn btn-warning" title="Volver a la tabla de sitios de ésta signación">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <p><em>Nota.- los campos con * son obligatorios</em></p>

                    <form novalidate="novalidate" action="{{ '/site/set_global_dates/'.($assignment ? $assignment->id : '') }}"
                          method="post" class="form-horizontal">
                        <input type="hidden" name="_method" value="put">

                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="interval" value="{{ $interval }}">
                        <input type="hidden" name="mode" value="{{ $mode }}">

                        @if($mode=='add'||$mode=='sub')
                            <fieldset>
                                <legend class="col-md-12">
                                    {{ 'Recorrer intervalo de fechas '.($interval=='exec' ? 'de ejecución' :
                                        'asignado por el cliente').($mode=='add' ? ' (Agregar días)' : ' (Restar días)') }}
                                </legend>

                                <div class="row">
                                    <div class="col-md-12 col-sm-12">

                                        <div class="form-group{{ $errors->has('diff_days') ? ' has-error' : '' }}">
                                            <label for="diff_days" class="col-md-4 control-label">
                                                (*) Cantidad de días
                                            </label>

                                            <div class="col-md-6">
                                                <input id="diff_days" type="number" class="form-control"
                                                       name="diff_days" step="any" min="0"
                                                       value="{{ old('diff_days') }}"
                                                       placeholder="días a recorrer" required>

                                                @if($errors->has('diff_days'))
                                                    <span class="help-block">
                                                        <strong>{{ $errors->first('diff_days') }}</strong>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                    </div>
                                </div>

                            </fieldset>
                        @else
                            <fieldset>
                                <legend class="col-md-12">
                                    {{ $interval=='exec' ? 'Establecer las fechas de inicio y fin de ejecución' : '' }}
                                    {{ $interval=='asg' ? 'Establecer las fechas de inicio y fin asignadas por el cliente' : '' }}
                                </legend>

                                <div class="row">
                                    <div class="col-md-12 col-sm-12">

                                        <div class="form-group{{ $errors->has('from') ? ' has-error' : '' }}">
                                            <label for="from" class="col-md-4 control-label">(*) Desde</label>

                                            <div class="col-md-4">
                                                <input id="from" type="date" class="form-control" name="from" step="1"
                                                       value="{{ old('from') ?: ($interval=='exec' ? $assignment->start_date :
                                                       $assignment->start_line) }}">

                                                @if($errors->has('from'))
                                                    <span class="help-block">
                                                        <strong>{{ $errors->first('from') }}</strong>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="form-group{{ $errors->has('to') ? ' has-error' : '' }}">

                                            <label for="to" class="col-md-4 control-label">(*) Hasta</label>

                                            <div class="col-md-4">
                                                <input id="to" type="date" class="form-control" name="to" step="1"
                                                       value="{{ old('to') ?: ($interval=='exec' ? $assignment->end_date :
                                                       $assignment->deadline) }}">

                                                @if($errors->has('to'))
                                                    <span class="help-block">
                                                        <strong>{{ $errors->first('to') }}</strong>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </fieldset>
                        @endif

                        @include('app.loader_gif')

                        <div class="form-group" align="center">
                            <button type="submit" class="btn btn-success"
                                    onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                                <i class="fa fa-arrow-right"></i> Establecer fechas
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
        $(document).ready(function(){
            $("#wait").hide();
        });
    </script>
@endsection
