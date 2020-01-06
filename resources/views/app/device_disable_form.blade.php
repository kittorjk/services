<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 02/10/2017
 * Time: 10:26 AM
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
                <div class="panel-title">{{ 'Dar de baja este equipo' }}</div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-undo"></i>
                    </a>
                    <a href="{{ '/device' }}" class="btn btn-warning" title="Volver a resumen de equipos">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                @if($device)
                    <form novalidate="novalidate" action="{{ '/device/disable' }}" method="post">
                        <input type="hidden" name="_method" value="put">
                        @endif
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        <input type="hidden" name="device_id" value="{{ $device->id }}">

                        <div class="form-group">
                            <div class="input-group">

                                <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                    <div class="input-group" style="width: 100%">
                                        <label for="serial" class="input-group-addon" style="width: 23%;text-align: left">
                                            Serial: <span class="pull-right">(*)</span>
                                        </label>

                                        <input required="required" type="text" class="form-control" name="serial"
                                               id="serial" value="{{ $device ? $device->serial : old('serial') }}"
                                               readonly="readonly">
                                    </div>

                                    <div class="input-group" style="width: 100%">
                                        <label for="type" class="input-group-addon" style="width: 23%;text-align: left">
                                            Tipo: <span class="pull-right">(*)</span>
                                        </label>

                                        <input required="required" type="text" class="form-control" name="type" id="type"
                                               value="{{ $device ? $device->type : old('type') }}" readonly="readonly">
                                    </div>

                                    <div class="input-group" style="width: 100%">
                                        <label for="model" class="input-group-addon" style="width: 23%;text-align: left">
                                            Modelo: <span class="pull-right">(*)</span>
                                        </label>

                                        <input required="required" type="text" class="form-control" name="model" id="model"
                                               value="{{ $device ? $device->model : old('model') }}" readonly="readonly">
                                    </div>

                                    <div class="input-group" style="width: 100%">
                                        <label for="condition" class="input-group-addon" style="width: 23%;text-align: left">
                                            Motivo de baja: <span class="pull-right">(*)</span>
                                        </label>

                                        <textarea rows="5" required="required" class="form-control" name="condition"
                                                  id="condition"
                                                  placeholder="Especifique el motivo para dar de baja este equipo">{{
                                                  $device ? $device->condition : old('condition') }}</textarea>
                                    </div>

                                </div>
                            </div>
                        </div>

                        @include('app.loader_gif')

                        <div class="form-group" align="center">
                            <button type="submit" class="btn btn-danger"
                                    onclick="this.disabled=true; $('#wait').show(); this.form.submit()"
                                    title="Una vez que se dé de baja este equipo ya no podrá modificar su información">
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
