<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 03/03/2018
 * Time: 11:17 AM
 */
?>

@extends('layouts.master')

@section('header')
    @parent
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
        <div class="panel panel-info" >
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    @if($type=='per-assignment-progress'){{ 'Generar reporte de avance - '.($place ? $place->name : '') }}
                    @endif
                </div>
            </div>
            <div class="panel-body">
                <div class="col-lg-4 mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                    @if($type=='per-assignment-progress')
                        <a href="/site/{{ $place->id }}" class="btn btn-warning" title="Ir a la tabla de sitios">
                            <i class="fa fa-arrow-up"></i>
                        </a>
                    @endif
                </div>

                <div class="col-sm-12">
                    @include('app.session_flashed_messages', array('opt' => 0))
                </div>

                <div class="col-sm-12 mg10 mg-tp-px-10">
                    <form method="post" action="/excel/report/{{ $type }}/{{ $id }}" accept-charset="UTF-8">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="form-group">
                            <div class="input-group">

                                <span class="input-group-addon"><i class="fa fa-cloud-upload"></i></span>

                                <div class="input-group" style="width: 100%;text-align: center">
                                    <span class="input-group-addon" style="width: 23%/*133px*/;text-align: left">
                                        Intervalo de fechas:
                                    </span>

                                    <span class="input-group-addon">
                                        <label for="date_from" style="font-weight: normal; margin-bottom: 0">Desde:</label>
                                        <input type="date" name="date_from" id="date_from" step="1"
                                               min="{{ $place ? $place->start_date : '2015-01-01' }}"
                                               max="{{ $place ? $place->end_date : '' }}"
                                               value="{{ old('date_from') ?: ($place ? $place->start_date : date('Y-m-d')) }}">

                                        <label for="date_to" style="font-weight: normal; margin-bottom: 0">Hasta:</label>
                                        <input type="date" name="date_to" id="date_to" step="1"
                                               min="{{ $place ? $place->start_date : '2015-01-01' }}"
                                               max="{{ $place ? $place->end_date : '' }}"
                                               value="{{ old('date_to') ?: ($place ? $place->end_date : date('Y-m-d')) }}">
                                    </span>
                                </div>

                            </div>
                        </div>

                        {{-- @include('app.loader_gif') --}}
                        <div id="waitMessage">
                            <p>
                                {{ 'Por favor espere mientras se genera el reporte...' }}
                            </p>
                        </div>

                        <div class="form-group" align="center">
                            <button type="submit" class="btn btn-primary"
                                    onclick="this.form.submit(); call_execute()">
                                <i class="fa fa-upload"></i> Generar reporte
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('footer')
@endsection

@section('javascript')
    <script>
        $("#waitMessage").hide();

        function call_execute(){
            $('#waitMessage').show();
            //.delay(2000).fadeOut('slow');
        }
    </script>
@endsection
