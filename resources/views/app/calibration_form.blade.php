<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 24/03/2017
 * Time: 05:56 PM
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
                <div class="panel-title">
                    {{ ($calibration ? 'Modificar ' : 'Agregar ').'registro de calibración de equipo' }}
                </div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="javascript:history.back()" {{-- onclick="history.back();" --}} class="btn btn-warning" title="Atrás">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                    <a href="{{ '/calibration' }}" class="btn btn-warning" title="Volver a resumen de calibraciones">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                @if($calibration)
                    <form id="delete" action="/calibration/{{ $calibration->id }}" method="post">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                    <form novalidate="novalidate" action="{{ '/calibration/'.$calibration->id }}" method="post">
                        <input type="hidden" name="_method" value="put">
                        @else
                            <form novalidate="novalidate" action="{{ '/calibration' }}" method="post">
                                @endif
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                                <div class="form-group">
                                    <div class="input-group">

                                        <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                            <div class="input-group" style="width: 100%">
                                                <label for="device_id" class="input-group-addon" style="width: 23%;text-align: left"
                                                    title="Campo obligatorio">
                                                    Equipo: <span class="pull-right">*</span>
                                                </label>

                                                <select required="required" class="form-control" name="device_id" id="device_id">
                                                    <option value="" hidden>Seleccione un equipo</option>
                                                    @foreach($devices as $device)
                                                        <option value="{{ $device->id }}"
                                                                {{ ($calibration&&$calibration->device_id==$device->id)||
                                                                    ($preselected_id==$device->id)||
                                                                    old('device_id')==$device->id ?
                                                                    'selected="selected"' : '' }}>
                                                            {{ $device->type.' '.$device->model.' - S/N: '.$device->serial  }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="input-group" style="width: 100%">
                                                <label for="detail" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Info. adicional
                                                </label>

                                                <textarea rows="4" required="required" class="form-control" name="detail"
                                                          id="detail" placeholder="Detalle de trabajo realizado / por realizar">{{
                                                           $calibration ? $calibration->detail : old('detail') }}</textarea>
                                            </div>

                                            @if($user->priv_level==4)
                                                <div class="input-group" style="width: 100%;">
                                                    <label for="date_in" class="input-group-addon"
                                                           style="font-weight: normal; width: 23%; text-align: right">
                                                        Fecha de ingreso:
                                                    </label>

                                                    <div class="input-group-addon">
                                                        <input type="date" name="date_in" id="date_in" step="1" min="2014-01-01"
                                                               value="{{ $calibration ? $calibration->date_in :
                                                                (old('date_in') ?: date('Y-m-d')) }}">
                                                    </div>
                                                </div>

                                                <div class="input-group" style="width: 100%">
                                                    <label for="date_out" class="input-group-addon"
                                                           style="font-weight: normal; width: 23%; text-align: right">
                                                        Fecha de salida:
                                                    </label>

                                                    <div class="input-group-addon">
                                                        <input type="date" name="date_out" id="date_out" step="1" min="2014-01-01"
                                                               value="{{ $calibration ? $calibration->date_out :
                                                                    old('date_out') }}">
                                                    </div>
                                                </div>
                                            @endif

                                        </span>

                                    </div>
                                </div>

                                @include('app.loader_gif')

                                <div class="form-group" align="center">
                                    <button type="submit" class="btn btn-success"
                                            onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                                        <i class="fa fa-floppy-o"></i> Guardar
                                    </button>

                                    @if($calibration&&$user->priv_level==4)
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
        $(document).ready(function(){
            $("#wait").hide();
        });
    </script>
@endsection
