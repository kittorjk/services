<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 24/03/2017
 * Time: 03:00 PM
 */
?>

@extends('layouts.master')

@section('header')
    @parent
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">
        <div class="panel panel-10gray">
            <div class="panel-heading" align="center">
                <div class="panel-title">{{ 'Enlazar poliza' }}</div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                    <a href="/vehicle/{{ $vehicle->id }}" class="btn btn-warning" title="Volver a información de vehículo">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <form novalidate="novalidate" action="{{ '/vehicle/link/'.$type.'/'.$vehicle->id }}" method="post">
                    <input type="hidden" name="_method" value="put">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                <div class="input-group" style="width: 100%">
                                    <label for="vehicle" class="input-group-addon" style="width: 23%;text-align: left">
                                        Vehículo: <span class="pull-right">*</span>
                                    </label>

                                    <input required="required" type="text" class="form-control" name="vehicle" id="vehicle"
                                           value="{{ $vehicle->type.' '.$vehicle->model.
                                                    ' - Placa: '.$vehicle->license_plate }}" readonly="readonly">
                                </div>

                                <div class="input-group" style="width: 100%">
                                    <label for="option_id" class="input-group-addon" style="width: 23%;text-align: left">
                                        Poliza: <span class="pull-right">*</span>
                                    </label>

                                    <select required="required" class="form-control" name="option_id" id="option_id">
                                        <option value="" hidden>Seleccione el número de poliza</option>
                                        @foreach($options as $option)
                                            <option value="{{ $option->id }}"
                                                    {{ $option->id==$vehicle->policy_id ? 'selected="selected"' :
                                                     '' }}>{{ $option->code }}</option>
                                        @endforeach
                                        <option value="0">Este vehículo no tiene poliza</option>
                                    </select>
                                </div>

                            </div>
                        </div>
                    </div>

                    @include('app.loader_gif')

                    <div class="form-group" align="center">
                        <button type="submit" class="btn btn-success"
                                onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                            <i class="fa fa-link"></i> Enlazar
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
