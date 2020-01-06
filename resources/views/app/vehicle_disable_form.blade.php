<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 29/09/2017
 * Time: 04:16 PM
 */
?>

@extends('layouts.master')

@section('header')
    @parent
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">
        <div class="panel panel-violet">
            <div class="panel-heading" align="center">
                <div class="panel-title">{{ 'Dar de baja este vehículo' }}</div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-undo"></i>
                    </a>
                    <a href="{{ '/vehicle' }}" class="btn btn-warning" title="Volver a resumen de vehiculos">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                @if($vehicle)
                    <form novalidate="novalidate" action="{{ '/vehicle/disable' }}" method="post">
                        <input type="hidden" name="_method" value="put">
                        @endif
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">

                        <div class="form-group">
                            <div class="input-group">

                                <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                    <div class="input-group" style="width: 100%">
                                        <label for="license_plate" class="input-group-addon" style="width: 23%;text-align: left">
                                            Placa: <span class="pull-right">(*)</span>
                                        </label>

                                        <input required="required" type="text" class="form-control" name="license_plate"
                                               id="license_plate"
                                               value="{{ $vehicle ? $vehicle->license_plate : old('license_plate') }}"
                                               readonly="readonly">
                                    </div>

                                    <div class="input-group" style="width: 100%">
                                        <label for="type" class="input-group-addon" style="width: 23%;text-align: left">
                                            Tipo: <span class="pull-right">(*)</span>
                                        </label>

                                        <input required="required" type="text" class="form-control" name="type" id="type"
                                               value="{{ $vehicle ? $vehicle->type : old('type') }}" readonly="readonly">
                                    </div>

                                    <div class="input-group" style="width: 100%">
                                        <label for="model" class="input-group-addon" style="width: 23%;text-align: left">
                                            Modelo: <span class="pull-right">(*)</span>
                                        </label>

                                        <input required="required" type="text" class="form-control" name="model" id="model"
                                               value="{{ $vehicle ? $vehicle->model : old('model') }}" readonly="readonly">
                                    </div>

                                    <div class="input-group" style="width: 100%">
                                        <label for="condition" class="input-group-addon" style="width: 23%;text-align: left">
                                            Motivo de baja: <span class="pull-right">(*)</span>
                                        </label>

                                        <textarea rows="5" required="required" class="form-control" name="condition"
                                                  id="condition"
                                                  placeholder="Especifique el motivo para dar de baja este vehículo">{{
                                                  $vehicle ? $vehicle->condition : old('condition') }}</textarea>
                                    </div>

                                </div>
                            </div>
                        </div>

                        @include('app.loader_gif')

                        <div class="form-group" align="center">
                            <button type="submit" class="btn btn-danger"
                                    onclick="this.disabled=true; $('#wait').show(); this.form.submit()"
                                    title="Una vez que se dé de baja este vehículo ya no podrá modificar su información">
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
        $(document).ready(function(){
            $("#wait").hide();
        });
    </script>
@endsection
