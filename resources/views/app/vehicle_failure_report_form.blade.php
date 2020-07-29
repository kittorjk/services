<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 17/11/2017
 * Time: 11:48 AM
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
                <div class="panel-title">
                    {{ 'Editar registro de reporte de falla - '.($report->vehicle ? $report->vehicle->license_plate : 'N/E') }}
                </div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                    <a href="{{ '/vehicle_failure_report?vhc='.$report->vehicle_id }}" class="btn btn-warning"
                       title="Volver a la tabla de reportes de falla de éste vehiculo">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <form novalidate="novalidate" action="{{ '/vehicle_failure_report/'.$report->id }}" method="post">
                    <input type="hidden" name="_method" value="put">

                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="form-group">
                        <div class="input-group">

                            <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                <div class="input-group" style="width: 100%">
                                    <label for="vehicle_id" class="input-group-addon" style="width: 23%;text-align: left">
                                        Vehículo:
                                    </label>

                                    <select required="required" class="form-control" name="vehicle_id" id="vehicle_id">
                                        <option value="" hidden>Seleccione un vehículo</option>
                                        <option value="{{ $report->vehicle_id }}" selected="selected">{{ $report->vehicle->type.
                                            ' '.$report->vehicle->model }}</option>
                                    </select>
                                </div>

                                <div class="input-group" style="width: 100%">
                                    <label for="reason" class="input-group-addon" style="width: 23%;text-align: left">
                                        Problema: <span class="pull-right">(*)</span>
                                    </label>

                                    <textarea rows="5" required="required" class="form-control" name="reason"
                                              id="reason" placeholder="Especifique el problema encontrado en el vehículo">{{
                                              $report ? $report->reason : old('reason') }}</textarea>
                                </div>

                            </div>

                        </div>
                    </div>

                    @include('app.loader_gif')

                    <div class="form-group" align="center">
                        <button type="submit" class="btn btn-danger"
                                onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                            <i class="fa fa-save"></i> Guardar cambios
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
