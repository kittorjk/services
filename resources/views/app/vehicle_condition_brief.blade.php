<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 08/02/2017
 * Time: 05:05 PM
 */
?>

@extends('layouts.actives_structure')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/custom_table.css") }}">
    <style>
        .dropdown-menu-prim > li > a {
            width: 190px;
            /*white-space: normal; /* Set content to a second line */
        }
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    <a href="{{ '/vehicle' }}" class="btn btn-primary" title="Ir a lista de vehículos"><i class="fa fa-car"></i> Vehículos</a>
    <div class="btn-group">
        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
            <i class="fa fa-book"></i> Libro de vehículo <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li>
                <a href="/vehicle_condition/{{ $vehicle_info ? $vehicle_info->id : '0' }}">
                    <i class="fa fa-refresh"></i> Recargar página
                </a>
            </li>
            @if($vehicle_info && (($vehicle_info->flags[2]==1 && ($user->id==$vehicle_info->responsible || $user->priv_level==4)) ||
                $vehicle_info->flags[3]==1 && ($user->id==$vehicle_info->responsible||$user->priv_level==4) && ($user->work_type=='Transporte' || $user->work_type=='Director Regional')))
                <li>
                    <a href="{{ '/vehicle_condition/'.$vehicle_info->id.'/create?mode=travel' }}">
                        <i class="fa fa-plus"></i> Registrar recorrido
                    </a>
                </li>
                <li>
                    <a href="{{ '/vehicle_condition/'.$vehicle_info->id.'/create?mode=refill' }}">
                        <i class="fa fa-plus"></i> Registrar carga de combustible
                    </a>
                </li>
            @endif
            @if($user->action->acv_vhc_exp /*$user->priv_level==4*/)
                <li class="divider"></li>
                <li>
                    <a href="/excel/vehicle_conditions/{{ $vehicle_info ? $vehicle_info->id : '0' }}">
                        <i class="fa fa-file-excel-o"></i> Exportar a Excel
                    </a>
                </li>
            @endif
        </ul>
    </div>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchBox">
        <i class="fa fa-search"></i> Buscar
    </button>
@endsection

@section('content')
    <div class="col-sm-12 mg10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10">
        @if($vehicle_info)
            Vehículo: <a href="/vehicle/{{ $vehicle_info->id }}">
                {{ $vehicle_info->type.' '.$vehicle_info->model.' - '.$vehicle_info->license_plate }}
            </a>
        @endif

        <p>{{ $condition_records->total()==1 ? 'Se encontró 1 registro' :
            'Se encontraron '.$condition_records->total().' registros' }}</p>

        <table class="formal_table table_blue tablesorter" id="fixable_table">
            <thead>
            <tr>
                <th>Fecha</th>
                @if(empty($vehicle_info))
                    <th>Vehículo</th>
                @endif
                <th class="{sorter: 'digit'}">Km inicio</th>
                <th class="{sorter: 'digit'}">Km final</th>
                <th class="{sorter: 'digit'}">Nivel de combustible</th>
                <th class="{sorter: 'digit'}">Carga de combustible</th>
                <th width="30%">Observaciones</th>
                <th>Último mantenimiento</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($condition_records as $condition_record)
                <tr>
                    <td>
                        {{ date_format($condition_record->created_at,'d-m-Y') }}
                        @if($user->id==$condition_record->user_id||$user->priv_level==4)
                            <a href="/vehicle_condition/{{ $condition_record->id }}/edit" class="pull-right"
                                title="Modificar registro">
                                <i class="fa fa-pencil-square-o"></i>
                            </a>
                        @endif
                    </td>
                    @if(empty($vehicle_info))
                        <td>
                            <a href="/vehicle/{{ $condition_record->vehicle_id }}">
                                {{ $vehicle_info->type.' '.$vehicle_info->model.' - '.$vehicle_info->license_plate }}
                            </a>
                        </td>
                    @endif
                    <td align="right">{{ $condition_record->mileage_start.' Km' }}</td>
                    <td align="right">{{ $condition_record->mileage_end.' Km' }}</td>
                    <td align="right">{{ $condition_record->gas_level.' lts (aprox.)' }}</td>
                    <td>
                        {{ $condition_record->gas_filled!=0 ? $condition_record->gas_filled.' lts'.
                            ($condition_record->gas_filled==$vehicle_info->gas_capacity ? ' (F)' : '') : '-' }}
                        @if($condition_record->gas_filled!=0)
                            <a href="/vehicle_condition/{{ $condition_record->id }}/show" class="pull-right">Detalles</a>
                        @endif
                    </td>
                    <td>{{ $condition_record->observations ? $condition_record->observations : '-' }}</td>
                    <td>
                        {{ $condition_record->last_maintenance->year<1 ? 'No existen mantenimientos previos para este vehículo' :
                            date_format($condition_record->last_maintenance,'d-m-Y') }}

                        @if($condition_record->maintenance_id!=0)
                            <a href="/maintenance/{{ $condition_record->maintenance_id }}" class="pull-right">Ver</a>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-sm-12 mg10" align="center">
        {!! $condition_records->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="formal_table table_blue" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @if($vehicle_info)
            @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'vehicle_conditions',
                'id'=>$vehicle_info->id))
        @else
            @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'vehicle_conditions','id'=>0))
        @endif
    </div>

@endsection

@section('footer')
    @parent
@endsection

@section('javascript')
    <script src="{{ asset('app/js/fix_table_header.js') }}"></script> {{-- For fixed header --}}
    <script src="{{ asset('app/js/set_current_url.js') }}"></script> {{-- For recording current url --}}
    <script>
        $('#alert').delay(2000).fadeOut('slow');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
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
