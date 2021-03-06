<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 17/11/2017
 * Time: 11:26 AM
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
                <a href="{{ '/vehicle_failure_report'.($report->vehicle ? '?vhc='.$report->vehicle->id : '') }}">
                    <i class="fa fa-refresh"></i> Ver reportes de falla
                </a>
            </li>
            <li><a href="{{ '/vehicle' }}"><i class="fa fa-arrow-right"></i> Ver vehículos</a></li>
            <li><a href="{{ '/driver' }}"><i class="fa fa-arrow-right"></i> Ver asignaciones</a></li>
            <li><a href="{{ '/vehicle_requirement' }}"><i class="fa fa-arrow-right"></i> Ver requerimientos</a></li>
            @if($user->action->acv_vfr_mod /*$user->priv_level==4*/)
                <li class="divider"></li>
                @if($report->vehicle)
                    <li>
                        <a href="{{ '/excel/vhc_failure_reports/'.$report->vehicle->id }}">
                            <i class="fa fa-file-excel-o"></i> Exportar reportes de este vehículo
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

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

        <div class="panel panel-violet">
            <div class="panel-heading" align="center">
                <div class="panel-title">{{ 'Reporte de falla - '.$report->vehicle->license_plate }}</div>
            </div>
            <div class="panel-body">
                <div class="col-sm-12 mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning">
                        <i class="fa fa-arrow-circle-left"></i> Volver
                    </a>
                    <a href="{{ '/vehicle_failure_report?vhc='.$report->vehicle_id }}" class="btn btn-warning"
                       title="Ir a la tabla de reportes de falla de vehículo">
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
                            <th>Vehículo</th>
                            <td>{{ $report->vehicle->type.' '.$report->vehicle->model }}</td>
                            <th>Placa</th>
                            <td>{{ $report->vehicle->license_plate }}</td>
                        </tr>
                        <tr>
                            <th>Estado</th>
                            <td colspan="3">{{ App\VhcFailureReport::$stat_names[$report->status] }}</td>
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
                                    <a href="/files/vhc_failure_report/{{ $report->id }}"
                                       title="Subir archivo de respaldo">
                                        <i class="fa fa-upload"></i> Subir archivo de respaldo
                                    </a>
                                </th>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
                @if($user->id==$report->user_id||$user->priv_level==4)
                    <div class="col-sm-12 mg10" align="center">
                        <a href="/vehicle_failure_report/{{ $report->id }}/edit" class="btn btn-success">
                            <i class="fa fa-pencil-square-o"></i> Modificar reporte
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'vhc_failure_reports','id'=>$report->vehicle->id))
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
