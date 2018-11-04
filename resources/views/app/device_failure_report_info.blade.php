<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 21/11/2017
 * Time: 03:02 PM
 */
?>

@extends('layouts.actives_structure')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/image_modal.css") }}">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    <div class="btn-group">
        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
            <i class="fa fa-warning"></i> Reportes de falla <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li>
                <a href="{{ '/device_failure_report'.($report->device ? '?dvc='.$report->device->id : '') }}">
                    <i class="fa fa-refresh"></i> Ver lista de fallas reportadas
                </a>
            </li>
            <li><a href="{{ '/device' }}"><i class="fa fa-arrow-right"></i> Ver equipos</a></li>
            <li><a href="{{ '/operator' }}"><i class="fa fa-arrow-right"></i> Ver asignaciones</a></li>
            <li><a href="{{ '/device_requirement' }}"><i class="fa fa-arrow-right"></i> Ver requerimientos</a></li>
            @if($user->action->acv_dfr_mod /*$user->priv_level==4*/)
                <li class="divider"></li>
                @if($report->device)
                    <li>
                        <a href="{{ '/excel/dvc_failure_reports/'.$report->device->id }}">
                            <i class="fa fa-file-excel-o"></i> Exportar reportes de este equipo
                        </a>
                    </li>
                @endif
            @endif
        </ul>
    </div>
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
    <script src="{{ asset('app/js/set_current_url.js') }}"></script> {{-- For recording current url --}}
    <script>
        $('#alert').delay(2000).fadeOut('slow');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });        
    </script>
@endsection
