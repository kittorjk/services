<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 20/02/2017
 * Time: 11:52 AM
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
                <div class="panel-title">{{ $characteristic ? 'Modificar característica' : 'Agregar característica' }}</div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-undo"></i>
                    </a>
                    <a href="{{ '/device' }}" class="btn btn-warning" title="Volver al resumen de equipos">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                @if($characteristic)
                    <form id="delete" action="/characteristics/device/{{ $characteristic->id }}" method="post">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                    <form novalidate="novalidate" action="{{ '/characteristics/device/'.$characteristic->id }}" method="post">
                        <input type="hidden" name="_method" value="put">
                        @else
                            <form novalidate="novalidate" action="{{ '/characteristics/device' }}" method="post">
                                @endif
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="form-group">
                                    <div class="input-group">

                                        <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                            <div class="input-group" style="width: 100%">
                                                <label for="device_id" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Equipo: <span class="pull-right">(*)</span>
                                                </label>

                                                <select required="required" class="form-control" name="device_id" id="device_id">
                                                    <option value="" hidden>Seleccione un equipo</option>
                                                    <option value="{{ $device->id }}" selected="selected">
                                                        {{ $device->type.' '.$device->model.' - '.$device->serial }}
                                                    </option>
                                                </select>
                                            </div>

                                            <div class="input-group" style="width: 100%">
                                                <label for="type" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Tipo: <span class="pull-right">(*)</span>
                                                </label>

                                                <select required="required" class="form-control" name="type" id="type">
                                                    <option value="" hidden>Seleccione el tipo de característica</option>
                                                    @foreach($types as $type)
                                                        <option value="{{ $type->type }}"
                                                                {{ ($characteristic&&$characteristic->type==$type->type)||
                                                                    old('type')==$type->type ? 'selected="selected"' :
                                                                     '' }}>{{ $type->type }}</option>
                                                    @endforeach
                                                    <option value="Otro">Otro</option>
                                                </select>
                                            </div>
                                            <input required="required" type="text" class="form-control" name="other_type"
                                                   id="other_type" placeholder="Tipo de característica" disabled="disabled">

                                            <div class="input-group" style="width: 100%">
                                                <label for="value" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Valor: <span class="pull-right">(*)</span>
                                                </label>

                                                <input required="required" type="text" class="form-control" name="value" id="value"
                                                       value="{{ $characteristic ? $characteristic->value : old('value') }}"
                                                       placeholder="Valor">
                                            </div>

                                            <div class="input-group" style="width: 100%">
                                                <label for="units" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Unidades:
                                                </label>

                                                <select required="required" class="form-control" name="units" id="units">
                                                    <option value="" hidden>Seleccione las unidades</option>
                                                    @foreach($units as $unit)
                                                        <option value="{{ $unit->units }}"
                                                                {{ ($characteristic&&$characteristic->units==$unit->units)||
                                                                    old('units')==$unit->units ? 'selected="selected"' :
                                                                     '' }}>{{ $unit->units }}</option>
                                                    @endforeach
                                                    <option value="Otro">Otro</option>
                                                </select>
                                            </div>
                                            <input required="required" type="text" class="form-control" name="other_units"
                                                   id="other_units" placeholder="Unidades" disabled="disabled">

                                        </div>

                                    </div>
                                </div>

                                @include('app.loader_gif')

                                <div class="form-group" align="center">
                                    <button type="submit" class="btn btn-success"
                                            onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                                        <i class="fa fa-floppy-o"></i> Guardar
                                    </button>

                                    @if($characteristic&&$user->action->acv_dvc_edt /*$user->priv_level==4*/)
                                        <button type="submit" form="delete" class="btn btn-danger">
                                            <i class="fa fa-trash-o"></i> Quitar
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

        var $type = $('#type'), $other_type = $('#other_type');
        $type.change(function () {
            if ($type.val()==='Otro') {
                $other_type.removeAttr('disabled').show();
            } else {
                $other_type.attr('disabled', 'disabled').val('').hide();
            }
        }).trigger('change');

        var $units = $('#units'), $other_units = $('#other_units');
        $units.change(function () {
            if ($units.val()==='Otro') {
                $other_units.removeAttr('disabled').show();
            } else {
                $other_units.attr('disabled', 'disabled').val('').hide();
            }
        }).trigger('change');

        $(document).ready(function(){
            $("#wait").hide();
        });
    </script>
@endsection
