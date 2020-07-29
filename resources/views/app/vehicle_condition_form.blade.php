<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 09/02/2017
 * Time: 10:16 AM
 */
?>

@extends('layouts.master')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

<div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">
    <div class="panel panel-10gray">
        <div class="panel-heading" align="center">
            <div class="panel-title">
                {{ $vehicle_condition ? 'Modificar registro' : 'Agregar registro de condiciones de vehículo' }}
            </div>
        </div>
        <div class="panel-body">
            <div class="mg20">
                <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                    <i class="fa fa-arrow-left"></i>
                </a>
                <a href="/vehicle_condition/{{ $vehicle->id }}" class="btn btn-warning" title="Volver a resumen de condiciones">
                    <i class="fa fa-arrow-up"></i>
                </a>
            </div>

            @include('app.session_flashed_messages', array('opt' => 1))

            <p><em>Nota.- Los campos con * son obligatorios</em></p>

            @if($vehicle_condition)
            <form id="delete" action="/vehicle_condition/{{ $vehicle_condition->id }}" method="post">
                <input type="hidden" name="_method" value="delete">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
            </form>
            <form novalidate="novalidate" action="{{ '/vehicle_condition/'.$vehicle_condition->id }}" method="post">
                <input type="hidden" name="_method" value="put">
                @else
                <form novalidate="novalidate" action="{{ '/vehicle_condition' }}" method="post">
                    @endif
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                <input type="hidden" name="mode" value="{{ $mode }}">

                                <div class="input-group" style="width: 100%">
                                    <label for="vehicle_id" class="input-group-addon" style="width: 23%;text-align: left">
                                        Vehículo: <span class="pull-right">*</span>
                                    </label>

                                    <select required="required" class="form-control" name="vehicle_id" id="vehicle_id">
                                        <option value="" hidden>Seleccione un vehículo</option>
                                        <option value="{{ $vehicle->id }}" selected="selected">
                                            {{ $vehicle->type.' '.$vehicle->license_plate }}
                                        </option>
                                    </select>
                                </div>

                                {{--
                                <div class="input-group">
                                    <span class="input-group-addon" style="width:170px;text-align: left">Km inicio:</span>

                                    <input required="required" type="number" class="form-control" name="mileage_start" step="any"
                                           min="0" placeholder="0.00" value="{{ old('mileage_start') ? old('mileage_start') :
                                            ($vehicle_condition ? $vehicle_condition->mileage_start : $vehicle->mileage) }}">

                                    <span class="input-group-addon">Km</span>
                                </div>
                                --}}

                                <div class="input-group" style="width: 75%">
                                    <span class="input-group-addon" style="width: 31%;text-align: left">
                                        Km actual: <span class="pull-right">*</span>
                                    </span>

                                    <input required="required" type="number" class="form-control" name="mileage_end" step="any"
                                           min="0" placeholder="{{ 'Último Km registrado '.$vehicle->mileage }}"
                                           value="{{ $vehicle_condition ? $vehicle_condition->mileage_end : old('mileage_end') }}">

                                    <span class="input-group-addon">Km</span>
                                </div>

                                {{--
                                    <div class="input-group">
                                        <span class="input-group-addon" style="width:170px;text-align: left">
                                            Nivel de combustible:
                                        </span>
                                        <input required="required" type="number" class="form-control" name="gas_level"
                                            step="any" min="0"
                                            value="{{ old('gas_level') ? old('gas_level') :
                                            ($vehicle_condition ? $vehicle_condition->gas_level : '') }}" placeholder="0.00">
                                        <span class="input-group-addon">Lts</span>
                                    </div>
                                --}}

                                <div class="input-group" style="width:75%">
                                    <label for="gas_level" class="input-group-addon" style="width:31%;text-align: left"
                                        title="Nivel de combustible actual (al momento del llenado del formulario)">
                                        Nivel combustible:
                                    </label>

                                    <input type="range" class="form-control" list="indicators" name="gas_level"
                                           id="gas_level" min="0" max="{{ $vehicle->gas_capacity }}" step="0.01"
                                           value="{{ $vehicle_condition ? $vehicle_condition->gas_level : old('gas_level') }}">

                                    <datalist id="indicators">
                                        <option value="0">
                                        <option value="{{ $vehicle->gas_capacity/4 }}">
                                        <option value="{{ $vehicle->gas_capacity/2 }}">
                                        <option value="{{ ($vehicle->gas_capacity*3)/4 }}">
                                        <option value="{{ $vehicle->gas_capacity }}">
                                    </datalist>
                                    <!--<span class="input-group-addon">Lts</span>-->
                                </div>

                                @if($mode=='refill')
                                    {{--
                                    <div class="input-group col-sm-offset-1">
                                        <div style="text-align: left">
                                            <br>
                                            <input class="checkbox-inline" type="checkbox" name="gas_added" id="gas_added" value="1"
                                                    {{ ($vehicle_condition&&$vehicle_condition->gas_filled!=0)||
                                                        old('gas_added')==1 ? 'checked="checked"' : 'checked=""' }}>

                                            <label for="gas_added" class="control-label"> Se cargó combustible </label>
                                        </div>
                                    </div>

                                    <div class="input-group col-sm-offset-1">
                                        <br>
                                        <div style="text-align: left">
                                            <input class="radio-inline" type="radio" name="gas_full" id="gas_full" value="1">
                                            <label for="gas_full" class="control-label">Tanque lleno</label>
                                            &ensp;
                                            <input class="radio-inline" type="radio" name="gas_full" id="gas_not_full" value="0">
                                            <label for="gas_not_full" class="control-label">Otro</label>
                                        </div>
                                        <br>
                                    </div>
                                    --}}

                                    <input type="hidden" name="gas_added" value="1">

                                    <div class="input-group" style="width: 75%">
                                        <span class="input-group-addon" style="width: 31%;text-align: left"
                                            title="Litros de combustible cargado">Combustible:</span>

                                        <input required="required" type="number" class="form-control" name="gas_filled"
                                               id="gas_filled" step="any" min="0" placeholder="0.00"
                                               value="{{ $vehicle_condition ? $vehicle_condition->gas_filled : old('gas_filled') }}">

                                        <span class="input-group-addon">{{ $vehicle->gas_type=='gnv' ? 'm3' : 'Lts' }}</span>
                                    </div>

                                    <div class="input-group" style="width: 75%">
                                        <label for="gas_bill" class="input-group-addon" style="width: 31%;text-align: left">
                                            # Factura:
                                        </label>

                                        <input required="required" type="text" class="form-control" name="gas_bill" id="gas_bill"
                                               value="{{ $vehicle_condition&&$vehicle_condition->gas_bill!=0 ?
                                                        $vehicle_condition->gas_bill : old('gas_bill') }}"
                                               placeholder="Número de factura">
                                    </div>
                                @endif

                                <div class="input-group" style="width: 100%">
                                    <label for="observations" class="input-group-addon" style="width: 23%;text-align: left">
                                        Observaciones:
                                    </label>

                                    <textarea rows="3" required="required" class="form-control" name="observations"
                                              id="observations" placeholder="Observaciones">{{ $vehicle_condition ?
                                            $vehicle_condition->observations : old('observations') }}</textarea>
                                </div>

                            </span>
                        </div>
                    </div>

                    @include('app.loader_gif')

                    <div class="form-group" align="center">
                        <button type="submit" class="btn btn-success"
                                onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                            <i class="fa fa-floppy-o"></i> Guardar
                        </button>

                        @if($vehicle_condition&&$user->priv_level==4)
                            <button type="submit" form="delete" class="btn btn-danger">
                                <i class="fa fa-trash-o"></i> Eliminar
                            </button>
                        @endif
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

            /*
            var $gas_added = $('#gas_added'), $gas_bill = $('#gas_bill'), $container = $('#container');
            $gas_added.click(function () {
                if ($gas_added.prop('checked')) {
                    $container.show();
                    $gas_bill.removeAttr('disabled').show();
                } else {
                    $container.hide();
                    $gas_bill.attr('disabled', 'disabled').hide();
                }
            }).trigger('click');

            var $gas_not_full = $('#gas_not_full'), $gas_filled = $('#gas_filled'), $fill_container = $("#fill_container");

            $('input[type=radio][name=gas_full]').click(function(){
                if ($gas_not_full.prop('checked')) {
                    $fill_container.show();
                    $gas_filled.removeAttr('disabled').show();
                } else {
                    $fill_container.hide();
                    $gas_filled.attr('disabled', 'disabled').hide();
                }
            }).trigger('click');
            */
        });
    </script>
@endsection
