<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 13/02/2017
 * Time: 12:28 PM
 */
?>

@extends('layouts.info_master')

@section('header')
    @parent
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

        <div class="panel panel-10gray">
            <div class="panel-heading" align="center">
                <div class="panel-title">Detalle de {{ $service_parameter->name }}</div>
            </div>
            <div class="panel-body">
                <div class="col-sm-12 mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning">
                        <i class="fa fa-arrow-circle-left"></i> Volver
                    </a>
                    <a href="{{ '/service_parameter' }}" class="btn btn-warning" title="Ir a la tabla de parámetros de sistema">
                        <i class="fa fa-arrow-circle-up"></i> Parámetros
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <div class="col-sm-12 mg10 mg-tp-px-10">

                    <table class="table table-striped table-hover table-bordered">
                        <thead>
                        <tr>
                            <th width="30%">Descripción</th>
                            <td>
                                {{ $service_parameter->description }}
                            </td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr><td colspan="2"></td></tr>
                        <tr>
                            <th colspan="2">Contenido</th>
                        </tr>
                        <tr>
                            <td colspan="2" style="padding-left: 20px">
                                @if($service_parameter->literal_content)
                                    {!! nl2br($service_parameter->literal_content) !!}
                                @elseif($service_parameter->numeric_content)
                                    {{ $service_parameter->numeric_content.' '.$service_parameter->units }}
                                @endif
                            </td>
                        </tr>

                        <tr><td colspan="2"></td></tr>
                        <tr>
                            <th>Creado por</th>
                            <td>{{ $service_parameter->user ? $service_parameter->user->name : 'N/E' }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('footer')
@endsection
