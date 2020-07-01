<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 24/07/2017
 * Time: 03:42 PM
 */
?>

@extends('layouts.projects_structure')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/custom_table.css") }}">
    <style>
        .dropdown-menu-prim > li > a {
            width: 170px;
        }
    </style>
@endsection

@section('menu_options')
    @include('app.project_navigation_button', array('user'=>$user))
    <div class="btn-group">
        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
            <i class="fa fa-money"></i> Solicitudes de viáticos <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            {{--<li><a href="{{ '/rbs_viatic' }}"><i class="fa fa-refresh fa-fw"></i> Recargar página </a></li>--}}
            <li><a href="" onclick="window.location.reload();"><i class="fa fa-refresh fa-fw"></i> Recargar página </a></li>
            <li><a href="{{ '/rbs_viatic/create' }}"><i class="fa fa-plus fa-fw"></i> Nueva solicitud </a></li>
            @if($user->priv_level>=2)
                <li><a href="{{ '/rbs_viatic/approve_list' }}"><i class="fa fa-check fa-fw"></i> Aprobar solicitud </a></li>
                <li><a href="{{ '/rbs_viatic/observed_list' }}"><i class="fa fa-eye fa-fw"></i> Solicitudes observadas </a></li>
                @if($user->priv_level==4)
                    <li class="divider"></li>
                    <li class="dropdown-submenu">
                        <a href="#" data-toggle="dropdown"><i class="fa fa-file-excel-o"></i> Exportar a Excel</a>
                        <ul class="dropdown-menu dropdown-menu-prim">
                            <li>
                                <a href="{{ '/excel/rbs_viatics' }}"><i class="fa fa-file-excel-o fa-fw"></i> Tabla de viáticos</a>
                            </li>
                            <li>
                                <a href="{{ '/excel/rbs_viatic_requests' }}">
                                    <i class="fa fa-file-excel-o fa-fw"></i> Tabla de viáticos asignados a técnicos
                                </a>
                            </li>
                            <li>
                                <a href="{{ '/excel/rbs_viatic_site' }}">
                                    <i class="fa fa-file-excel-o fa-fw"></i> Tabla Pivot Viático-Sitio
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif
            @endif
        </ul>
    </div>
    @if($user->priv_level>=2)
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchBox">
            <i class="fa fa-search"></i> Buscar
        </button>
    @endif
@endsection

@section('content')

    @if($waiting_approval>0)
        <div class="col-sm-12 mg10">
            <div class="alert alert-info" align="center">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <i class="fa fa-info-circle fa-2x pull-left"></i>
                <a href="{{ '/rbs_viatic/approve_list' }}" style="color: inherit;">
                    {{ $waiting_approval==1 ? 'Existe 1 solicitud de viáticos pendiente de aprobación' :
                         'Existen '.$waiting_approval.' solicitudes de viáticos pendientes de aprobación' }}
                </a>
            </div>
        </div>
    @endif

    @if($observed>0)
        <div class="col-sm-12 mg10">
            <div class="alert alert-warning" align="center">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <i class="fa fa-warning fa-2x pull-left"></i>
                <a href="{{ '/rbs_viatic/observed_list' }}" style="color: inherit;">
                    {{ $observed==1 ? '1 solicitud de viáticos ha sido observada!' :
                         $observed.' solicitudes de viáticos han sido observadas!' }}
                </a>
            </div>
        </div>
    @endif

    <div class="col-sm-12 mg10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10">

        <p>Registros encontrados: {{ $viatics->total() }}</p>

        <table class="formal_table table_blue tablesorter" id="fixable_table">
            <thead>
            <tr>
                <th># Solicitud</th>
                <th>Fecha</th>
                <th width="15%">Técnicos</th>
                <th width="25%">Trabajo</th>
                <th width="20%">Sitios</th>
                <th width="15%">Estado</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
            {{--
                    $statuses = array();
                    $statuses[0] = 'Nueva';
                    $statuses[1] = 'Observada';
                    $statuses[2] = 'Modificada';
                    $statuses[3] = 'Aprobada';
                    $statuses[4] = 'Rechazada';
                    $statuses[5] = 'Completada';
                    $statuses[6] = 'Cancelada';
            --}}
            @foreach ($viatics as $viatic)
                <tr style="{{ $viatic->status==4 ? 'background-color: lightgrey' : '' }}">
                    <td>
                        <a href="/rbs_viatic/{{ $viatic->id }}" title="Ver información de solicitud">
                            {{ $viatic->id }}
                        </a>
                        @if($user->priv_level==4)
                            <a href="/rbs_viatic/{{ $viatic->id }}/edit" title="Modificar solicitud">
                                <i class="fa fa-pencil-square-o pull-right"></i>
                            </a>
                        @endif
                    </td>
                    <td>{{ date_format($viatic->created_at,'d-m-Y') }}</td>
                    <td>
                        @foreach($viatic->technician_requests as $request)
                            {!! $request->technician ? $request->technician->name.'<br>' : '' !!}
                        @endforeach
                    </td>
                    <td>{{ $viatic->work_description }}</td>
                    <td>
                        @foreach($viatic->sites as $site)
                            <a href="/site/{{ $site->id }}/show" title="Ver información de sitio">{{ $site->name }}</a>
                            {!! '<br>' !!}
                        @endforeach
                    </td>
                    <td>{{ $viatic->statuses($viatic->status) }}</td>
                    <td>
                        @if($viatic->status!=4&&$viatic->status!=5&&$viatic->status!=6)
                            @if(($viatic->status==0||$viatic->status==2)&&$user->priv_level>=2)
                                <a href="/rbs_viatic/status/{{ $viatic->id }}?action=observe" title="Observar solicitud de viáticos">
                                    <i class="fa fa-eye"></i>
                                </a>
                                &ensp;
                                <a href="/rbs_viatic/approve/{{ $viatic->id }}" title="Aprobar solicitud de viáticos">
                                    <i class="fa fa-check"></i>
                                </a>
                                &ensp;
                            @elseif($viatic->status==1&&($user->id==$viatic->user_id||$user->priv_level==4))
                                <a href="/rbs_viatic/{{ $viatic->id }}/edit" title="Modificar solicitud observada">
                                    <i class="fa fa-pencil-square-o"></i>
                                </a>
                                &ensp;
                            @elseif($viatic->status==3&&$user->priv_level>=2)
                                <a href="/rbs_viatic/status/{{ $viatic->id }}?action=complete" title="Confirmar pago de solicitud">
                                    <i class="fa fa-usd"></i>
                                </a>
                                &ensp;
                            @endif
                            @if($user->priv_level>=2)
                                <a href="/rbs_viatic/status/{{ $viatic->id }}?action=reject" title="Rechazar solicitud de viáticos">
                                    <i class="fa fa-times"></i>
                                </a>
                                @if($user->id==$viatic->user_id)
                                &ensp;
                                    <a href="/rbs_viatic/status/{{ $viatic->id }}?action=cancel"
                                       title="Cancelar solicitud de viáticos">
                                        <i class="fa fa-ban"></i>
                                    </a>
                                @endif
                            @endif
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

    </div>

    <div class="col-sm-12 mg10" align="center">
        {!! $viatics->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="formal_table table_blue" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'rbs_viatics','id'=>0))
    </div>

@endsection

@section('footer')
    @parent
@endsection

@section('javascript')
    <script src="{{ asset('app/js/fix_table_header.js') }}"></script> {{-- For fixed header --}}
    <script>
        $('#alert').delay(2000).fadeOut('slow');

        $(function(){
            $('#fixable_table').tablesorter({
                cssAsc: 'headerSortUp',
                cssDesc: 'headerSortDown',
                cssNone: ''
            });
        });

        /*
        $('.confirm_close').on('click', function () {
            return confirm('Está seguro de que desea marcar este registro como: No asignado?');
        });

        $('.confirm_applied').on('click', function () {
            return confirm('Está seguro de que desea registrar el envío de documentación para aplicar a la ' +
                    'licitación indicada?');
        });

        $('.confirm_assignment').on('click', function () {
            return confirm('Está seguro de que desea crear una asignación de trabajo de este proyecto?');
        });
        */
    </script>
@endsection
