<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 02/03/2017
 * Time: 11:28 AM
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
                <div class="panel-title">{{ 'Registrar devolución de vehículo' }}</div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                    <a href="{{ '/vehicle' }}" class="btn btn-warning" title="Volver al resumen de vehículos">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                    <form novalidate="novalidate" action="{{ '/driver/devolution' }}" method="post">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                    <div class="input-group">
                                        <label for="vehicle_id"></label>

                                        <select required="required" class="form-control" name="vehicle_id" id="vehicle_id">
                                            <option value="" hidden>Seleccione un vehículo</option>
                                            <option value="{{ $vehicle->id }}" selected="selected">
                                                {{ $vehicle->type.' '.$vehicle->license_plate }}
                                            </option>
                                        </select>
                                    </div>

                                    <div class="input-group">
                                        <span class="input-group-addon" style="width:120px;text-align: left">Kilometraje:</span>
                                        <input required="required" type="number" class="form-control" name="mileage_before"
                                               step="any" min="0" value="{{ $vehicle->mileage }}" placeholder="0.00">
                                        <span class="input-group-addon">Km</span>
                                    </div>

                                    <textarea rows="3" class="form-control" name="observations"
                                              placeholder="Observaciones del vehículo"></textarea>

                                </div>
                            </div>
                        </div>

                        @include('app.loader_gif')

                        <div class="form-group" align="center">
                            <button type="submit" class="btn btn-success"
                                    onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                                <i class="fa fa-floppy-o"></i> Guardar
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
