<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 21/11/2017
 * Time: 10:33 AM
 */
?>

@extends('layouts.actives_structure')

@section('header')
    @parent
    <style>
        .dropdown-menu-prim > li > a {
            width: 200px;
        }
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    <div class="btn-group">
        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
            <i class="fa fa-warning"></i> Reportes de falla <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li>
                <a href="{{ '/device_failure_report'.($device ? '?dvc='.$device->id : '') }}">
                    <i class="fa fa-refresh"></i> Recargar página
                </a>
            </li>
            <li><a href="{{ '/device' }}"><i class="fa fa-arrow-right"></i> Ver equipos</a></li>
            <li><a href="{{ '/operator' }}"><i class="fa fa-arrow-right"></i> Ver asignaciones</a></li>
            <li><a href="{{ '/device_requirement' }}"><i class="fa fa-arrow-right"></i> Ver requerimientos</a></li>
            @if($user->action->acv_dfr_mod /*$user->priv_level==4*/)
                <li class="divider"></li>
                <li><a href="{{ '/excel/dvc_failure_reports' }}"><i class="fa fa-file-excel-o"></i> Exportar tabla a Excel</a></li>
                @if($device)
                    <li>
                        <a href="{{ '/excel/dvc_failure_reports/'.$device->id }}">
                            <i class="fa fa-file-excel-o"></i> Exportar reportes de este equipo
                        </a>
                    </li>
                @endif
            @endif
        </ul>
    </div>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchBox">
        <i class="fa fa-search"></i> Buscar
    </button>
@endsection

@section('content')

    @if($reports->where('status', 0)->count()!=0)
        <div class="col-sm-12 mg10">
            <div class="alert alert-warning" align="center">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <i class="fa fa-warning fa-2x pull-left"></i>
                {{ $reports->where('status', 0)->count()==1 ? 'Existe 1 reporte de falla pendiente' :
                        'Existen '.$reports->where('status', 0)->count().' reportes de falla pendientes' }}
            </div>
        </div>
    @endif

    <div class="col-sm-12 mg10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10 mg-tp-px-10">
        <p>Reportes encontrados: {{ $reports->total() }}</p>

        <table class="fancy_table table_brown tablesorter" id="fixable_table">
            <thead>
            <tr>
                <th>Fecha</th>
                <th>Código</th>
                <th>Equipo</th>
                <th>Serial</th>
                <th width="25%">Falla reportada</th>
                <th>Reportado por</th>
                <th>Estado</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($reports as $report)
                <tr>
                    <td>{{ date_format($report->created_at,'d-m-Y') }}</td>
                    <td>
                        <a href="/device_failure_report/{{ $report->id }}">
                            {{ $report->code }}
                        </a>
                        @if($user->priv_level==4)
                            <a href="{{ '/device_failure_report/'.$report->id.'/edit' }}">
                                <i class="fa fa-pencil-square"></i>
                            </a>
                        @endif
                    </td>
                    <td>
                        {{ $report->device->type.' '.$report->device->model }}
                    </td>
                    <td>
                        <a href="/device/{{ $report->device->id }}">
                            {{ $report->device->serial }}
                        </a>
                    </td>
                    <td>{{ $report->reason }}</td>
                    <td>{{ $report->user->name }}</td>
                    <td>
                        {{ \App\DvcFailureReport::$stat_names[$report->status] }}

                        <div class="pull-right">
                            @if(/*$user->work_type=='Almacén'||$user->priv_level>=3*/$user->action->acv_dfr_mod||
                                $report->user_id==$user->id)
                                @if($report->status==0)
                                    &ensp;
                                    <a href="{{ '/device_failure_report/move_stat?stat=in_process&rep='.$report->id }}" style="text-decoration: none;"
                                       title="Cambiar el estado de este reporte a 'En proceso de solución'">
                                        <i class="fa fa-wrench"></i>
                                    </a>
                                @endif
                                @if($report->status<2)
                                    &ensp;
                                    <a href="{{ '/device_failure_report/move_stat?stat=solved&rep='.$report->id }}" style="text-decoration: none;"
                                       title="Cambiar el estado de este reporte a 'Resuelto'">
                                        <i class="fa fa-check"></i>
                                    </a>
                                @endif
                            @endif
                            @if(($report->user_id==$user->id&&$report->status<2)||$user->priv_level==4)
                                &ensp;
                                <a href="/files/dvc_failure_report/{{ $report->id }}" style="text-decoration: none"
                                   title="Subir archivo de respaldo">
                                    <i class="fa fa-upload"></i>
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-sm-12 mg10" align="center">
        {!! $reports->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="fancy_table table_brown" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'dvc_failure_reports','id'=>$device->id))
    </div>

@endsection

@section('footer')
    @parent
@endsection

@section('javascript')
    <script src="{{ asset('app/js/fix_table_header.js') }}"></script> {{-- For fixed header --}}
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function(){
            $.post('/set_current_url', { url: window.location.href }, function(){});
        });

        $('#alert').delay(2000).fadeOut('slow');

        $(function(){
            $('#fixable_table').tablesorter({
                cssAsc: 'headerSortUp',
                cssDesc: 'headerSortDown',
                cssNone: '',
                dateFormat: 'uk'
            });
        });
    </script>
@endsection
