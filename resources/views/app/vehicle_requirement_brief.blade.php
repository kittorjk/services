<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 11/09/2017
 * Time: 06:12 PM
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
            <i class="fa fa-exchange"></i> Requerimientos <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li>
                <a href="{{ '/vehicle_requirement'.($vhc ? '?vhc='.$vhc : '') }}"><i class="fa fa-refresh"></i> Recargar página</a>
            </li>
            <li><a href="{{ '/vehicle' }}"><i class="fa fa-arrow-right"></i> Ver vehículos</a></li>
            <li><a href="{{ '/driver' }}"><i class="fa fa-arrow-right"></i> Ver asignaciones </a></li>
            @if($user->action->acv_vhc_req /*$user->work_type=='Transporte'||$user->priv_level>=2*/)
                <li><a href="{{ '/vehicle_requirement/create' }}"><i class="fa fa-plus"></i> Nuevo requerimiento </a></li>
            @endif
            @if($user->acv_vhc_exp /*$user->priv_level==4*/)
                <li class="divider"></li>
                <li><a href="{{ '/excel/vehicle_requirements' }}"><i class="fa fa-file-excel-o"></i> Exportar a Excel</a></li>
            @endif
        </ul>
    </div>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchBox">
        <i class="fa fa-search"></i> Buscar
    </button>
@endsection

@section('content')

    @if($requirements->where('status', 1)->count()!=0)
        <div class="col-sm-12 mg10">
            <div class="alert alert-info" align="center">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <i class="fa fa-info-circle fa-2x pull-left"></i>
                {{ $requirements->where('status', 1)->count()==1 ? 'Existe 1 requerimiento en proceso' :
                        'Existen '.$requirements->where('status', 1)->count().' requerimientos en proceso' }}
            </div>
        </div>
    @endif

    <div class="col-sm-12 mg10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10 mg-tp-px-10">
        <p>Requerimientos encontrados: {{ $requirements->total() }}</p>

        <table class="fancy_table table_purple tablesorter" id="fixable_table">
            <thead>
            <tr>
                <th>Fecha</th>
                <th>Requerimiento</th>
                <th>Vehículo requerido</th>
                <th>Placa</th>
                <th>Resp. previo</th>
                <th>Entregar a</th>
                <th width="18%">Motivo</th>
                <th width="10%">Tipo de req.</th>
                <th>Estado</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($requirements as $requirement)
                <tr>
                    <td>{{ date_format($requirement->created_at,'Y-m-d') }}</td>
                    <td>
                        <a href="/vehicle_requirement/{{ $requirement->id }}">
                            {{ $requirement->code }}
                        </a>
                    </td>
                    <td>
                        <a href="/vehicle/{{ $requirement->vehicle->id }}">
                            {{ $requirement->vehicle->type.' '.$requirement->vehicle->model }}
                        </a>
                    </td>
                    <td>{{ $requirement->vehicle->license_plate }}</td>
                    <td>
                        {{ $requirement->person_from ? $requirement->person_from->name : ($requirement->vehicle->last_driver ?
                            $requirement->vehicle->last_driver->name : '') }}
                        @if($requirement->status==1&&((($requirement->from_id==$user->id&&($requirement->type=='transfer_tech'||
                            $requirement->type=='devolution'))||(($requirement->type=='borrow'||$requirement->type=='transfer_branch')&&
                            $user->work_type=='Transporte'&&$requirement->branch_origin==$user->branch))||$user->priv_level==4))
                            <div class="pull-right">
                                <a href="/driver/create{{ '?req='.$requirement->id }}" style="text-decoration: none;"
                                   title="Registrar entrega de vehículo">
                                    <i class="fa fa-file"></i>
                                </a>
                                <a href="{{ '/vehicle_requirement/reject/'.$requirement->id }}" style="text-decoration: none;"
                                   title="Rechazar requerimiento de vehículo">
                                    <i class="fa fa-ban"></i>
                                </a>
                            </div>
                        @endif
                    </td>
                    <td>
                        {{ $requirement->person_for ? $requirement->person_for->name : '' }}
                        {{--
                        @if($requirement->status==2&&($requirement->for_id==$user->id||$user->priv_level==4))
                            <a href="/operator/confirm/{{ $requirement->id }}" style="text-decoration: none;"
                               title="Confirmar recepción de equipo" class="pull-right">
                                <i class="fa fa-check-circle"></i>
                            </a>
                        @endif
                        --}}
                    </td>
                    <td>{{ $requirement->reason }}</td>
                    <td>{{ App\VehicleRequirement::$types[$requirement->type] }}</td>
                    <td>{{ App\VehicleRequirement::$stat_names[$requirement->status] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-sm-12 mg10" align="center">
        {!! $requirements->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="fancy_table table_purple" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'vehicle_requirements','id'=>0))
    </div>

@endsection

@section('footer')
    @parent
@endsection

@section('javascript')
    <script src="{{ asset('app/js/fix_table_header.js') }}"></script> {{-- For fixed header --}}
    <script>
        $('#alert').delay(2000).fadeOut('slow');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function(){
            $.post('/set_current_url', { url: window.location.href }, function(){});
        });

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
