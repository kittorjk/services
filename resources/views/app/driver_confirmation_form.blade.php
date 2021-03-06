<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 12/09/2017
 * Time: 05:15 PM
 */
?>

@extends('layouts.master')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">
        <div class="panel panel-violet">
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    {{ 'Confirmar recepción de vehículo'.($driver->vehicle ? ': Placa '.$driver->vehicle->license_plate : '') }}
                </div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                    <a href="{{ '/operator' }}" class="btn btn-warning" title="Volver a lista de asignaciones">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <form novalidate="novalidate" action="{{ '/driver/confirm/'.$driver->id }}" method="post">
                    <input type="hidden" name="_method" value="put">

                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="form-group">
                        <div class="input-group">

                            <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                <div id="device_container" class="input-group" style="width: 100%">
                                    <label for="vehicle" class="input-group-addon" style="width: 23%;text-align: left">
                                        Vehículo:
                                    </label>

                                    <input type="text" name="vehicle" id="vehicle" class="form-control"
                                           value="{{ $driver->vehicle ? $driver->vehicle->type.' '.
                                            $driver->vehicle->model : '' }}" readonly="readonly">
                                </div>

                                <textarea rows="5" required="required" class="form-control" name="confirmation_obs"
                                          id="confirmation_obs"
                                          placeholder="Observaciones del estado del vehículo y de sus componentes (opcional)">{{
                                          old('confirmation_obs') }}</textarea>

                            </div>

                        </div>
                    </div>

                    @include('app.loader_gif')

                    <div class="form-group" align="center">
                        <button type="submit" class="btn btn-success"
                                onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                            <i class="fa fa-check"></i> Confirmar recepción
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
