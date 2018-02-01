<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 21/07/2017
 * Time: 11:56 AM
 */
?>

@extends('layouts.master')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">

        <div class="panel panel-sky">
            <div class="panel-heading" align="center">
                <div class="panel-title">{{ 'Cambiar parámetros de control de sitio' }}</div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-undo"></i>
                    </a>
                    <a href="/site/{{ $site->assignment_id }}" class="btn btn-warning" title="Volver a resumen de sitios">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <form novalidate="novalidate" action="{{ '/site/'.$site->id.'/control' }}" method="post">
                    <input type="hidden" name="_method" value="put">

                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    <div class="form-group">
                        <div class="input-group" style="width:100%">
                            <span class="input-group-addon" style="width: 25%;text-align: left">Asignación:</span>
                            <input type="text" class="form-control" name="site_name"
                                   value="{{ $site->assignment->name }}" placeholder="Nombre de asignación"
                                   readonly="readonly">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-group" style="width:100%">
                            <span class="input-group-addon" style="width: 25%;text-align: left">Sitio:</span>
                            <input type="text" class="form-control" name="site_name"
                                   value="{{ $site->name }}" placeholder="Nombre de sitio" readonly="readonly">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-group" style="width: 100%">
                            <span class="input-group-addon" style="width: 25%;text-align: left">Presupuesto:</span>
                            <input required="required" type="number" class="form-control" name="budget"
                                   step="any" min="0" value="{{ $site->budget==0 ? '' : $site->budget }}"
                                   placeholder="Limite presupuestado de ejecución">
                            <span class="input-group-addon">Bs.</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-group" style="width: 100%">
                            <span class="input-group-addon" style="width: 25%;text-align: left">Depreciación vehic.:</span>
                            <input required="required" type="number" class="form-control" name="vehicle_dev_cost"
                                   step="any" min="0" value="{{ $site->vehicle_dev_cost==0 ? '' : $site->vehicle_dev_cost }}"
                                   placeholder="Costo por depreciación de vehículo de la empresa">
                            <span class="input-group-addon">Bs.</span>
                        </div>
                    </div>

                    @include('app.loader_gif')

                    <div class="form-group" align="center">
                        <button type="submit" class="btn btn-success"
                                onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                            <i class="fa fa-floppy-o"></i> Guardar
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
