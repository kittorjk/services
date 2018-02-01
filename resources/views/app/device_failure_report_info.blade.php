<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 21/11/2017
 * Time: 03:02 PM
 */
?>

@extends('layouts.info_master')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/image_modal.css") }}">
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

        <div class="panel panel-brown">
            <div class="panel-heading" align="center">
                <div class="panel-title">{{ 'Reporte de falla - '.$report->device->serial }}</div>
            </div>
            <div class="panel-body">
                <div class="col-sm-12 mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning">
                        <i class="fa fa-arrow-circle-left"></i> Volver
                    </a>
                    <a href="{{ '/device_failure_report?dvc='.$report->device_id }}" class="btn btn-warning"
                       title="Ir a la tabla de reportes de falla de equipo">
                        <i class="fa fa-arrow-circle-up"></i> Reportes
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <div class="col-sm-12 mg10 mg-tp-px-10">

                    <table class="table table-striped table-hover table-bordered">
                        <thead>
                        <tr>
                            <th width="25%">Reporte</th>
                            <td colspan="3">{{ $report->code }}</td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <th>Equipo</th>
                            <td>{{ $report->device->type.' '.$report->device->model }}</td>
                            <th>Serial</th>
                            <td>{{ $report->device->serial }}</td>
                        </tr>
                        <tr>
                            <th>Estado</th>
                            <td colspan="3">{{ App\DvcFailureReport::$stat_names[$report->status] }}</td>
                        </tr>
                        <tr>
                            <th>Fecha registro</th>
                            <td>{{ date_format($report->created_at,'Y-m-d') }}</td>
                            <th>Última actualización</th>
                            <td>{{ $report->date_stat->year<1 ? '-' : date_format($report->date_stat, 'Y-m-d') }}</td>
                        </tr>
                        <tr><td colspan="4"></td></tr>

                        <tr>
                            <th>Detalle</th>
                            <td colspan="3">{{ $report->reason }}</td>
                        </tr>
                        <tr>
                            <th>Elaborado por</th>
                            <td colspan="3">{{ $report->user ? $report->user->name : 'N/E' }}</td>
                        </tr>
                        <tr><td colspan="4"></td></tr>

                        <tr>
                            <th colspan="4">Archivos:</th>
                        </tr>
                        @foreach($report->files as $file)
                            <tr>
                                <td>{{ date_format(new \DateTime($file->updated_at), 'd-m-Y') }}</td>
                                <td colspan="3">
                                    {{ $file->description }}

                                    <div class="pull-right">
                                        @include('app.info_document_options', array('file'=>$file))
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        @if($report->files->count()==0)
                            <tr>
                                <td colspan="4" align="center">
                                    {{ 'No se cargó ningún archivo para este reporte de falla' }}
                                </td>
                            </tr>
                        @endif
                        @if($report->status<2 /* "Open" */)
                            <tr>
                                <th colspan="4" style="text-align: center">
                                    <a href="/files/dvc_failure_report/{{ $report->id }}"
                                       title="Subir archivo de respaldo">
                                        <i class="fa fa-upload"></i> Subir archivo de respaldo
                                    </a>
                                </th>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
                @if($user->priv_level==4)
                    <div class="col-sm-12 mg10" align="center">
                        <a href="/device_failure_report/{{ $report->id }}/edit" class="btn btn-success">
                            <i class="fa fa-pencil-square-o"></i> Modificar reporte
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection

@section('footer')
@endsection

@section('javascript')
    <script></script>
@endsection
