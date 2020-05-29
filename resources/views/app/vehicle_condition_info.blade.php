<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 09/02/2017
 * Time: 12:18 PM
 */
?>

@extends('layouts.actives_structure')

@section('header')
    @parent
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
                <a href="/vehicle_condition/{{ $condition_record->vehicle ? $condition_record->vehicle->id : '0' }}">
                    <i class="fa fa-refresh"></i> Ver registros
                </a>
            </li>
            @if($condition_record->vehicle && (($condition_record->vehicle->flags[2] == 1 &&
                ($user->id == $condition_record->vehicle->responsible || $user->priv_level == 4)) ||
                $condition_record->vehicle->flags[3] == 1 && ($user->id == $condition_record->vehicle->responsible ||
                $user->priv_level == 4) && ($user->work_type == 'Transporte' || $user->work_type == 'Director Regional')))
                <li>
                    <a href="{{ '/vehicle_condition/'.$condition_record->vehicle->id.'/create?mode=travel' }}">
                        <i class="fa fa-plus"></i> Registrar recorrido
                    </a>
                </li>
                <li>
                    <a href="{{ '/vehicle_condition/'.$condition_record->vehicle->id.'/create?mode=refill' }}">
                        <i class="fa fa-plus"></i> Registrar carga de combustible
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

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

        <div class="panel panel-sky">
            <div class="panel-heading" align="center">
                <div class="panel-title">Información de registro</div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning">
                        <i class="fa fa-arrow-circle-left"></i> Volver
                    </a>
                    <a href="{{ '/vehicle_condition/'.$condition_record->vehicle->id }}" class="btn btn-warning"
                       title="Ir a la tabla de registros de estado de vehículo">
                        <i class="fa fa-arrow-circle-up"></i> Libro de vehículo
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <div class="col-sm-12 mg10 mg-tp-px-10">

                    <table class="table table-striped table-hover table-bordered">
                        <thead>
                        <tr>
                            <th width="40%">Vehículo</th>
                            <td>
                                {{ $condition_record->vehicle->type.' '.$condition_record->vehicle->model }}
                            </td>
                        </tr>
                        <tr>
                            <th>Placa</th>
                            <td>{{ $condition_record->vehicle->license_plate }}</td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr><td colspan="2"> </td></tr>

                        <tr>
                            <th>Fecha de registro</th>
                            <td>{{ $condition_record->created_at }}</td>
                        </tr>
                        @if($condition_record->maintenance_id!=0)
                            <tr>
                                <th>Último mantenimiento</th>
                                <td>{{ $condition_record->last_maintenance }}</td>
                            </tr>
                            <tr><td colspan="2"> </td></tr>
                        @endif
                        <tr>
                            <th>Kilometraje anterior</th>
                            <td>{{ $condition_record->mileage_start.' Km' }}</td>
                        </tr>
                        <tr>
                            <th title="Kilometraje actual según el presente registro">
                                Kilometraje actual
                            </th>
                            <td>{{ $condition_record->mileage_end.' Km' }}</td>
                        </tr>
                        <tr>
                            <th>Nivel de combustible</th>
                            <td>{{ $condition_record->gas_level.' lts' }}</td>
                        </tr>

                        @if($condition_record->gas_filled!=0)
                            <tr><td colspan="2"> </td></tr>
                            <tr>
                                <th>Combustible cargado</th>
                                <td>{{ $condition_record->gas_filled.' lts' }}</td>
                            </tr>
                            <tr>
                                <th>Costo</th>
                                <td>{{ $condition_record->gas_cost.' Bs' }}</td>
                            </tr>
                            <tr>
                                <th>Número de factura</th>
                                <td>{{ $condition_record->gas_bill }}</td>
                            </tr>
                        @endif

                        @if($condition_record->observations)
                            <tr><td colspan="2"> </td></tr>
                            <tr>
                                <th colspan="2">Observaciones</th>
                            </tr>
                            <tr>
                                <td colspan="2">{{ $condition_record->observations }}</td>
                            </tr>
                        @endif

                        <tr><td colspan="2"></td></tr>
                        <tr>
                            <th>Registro creado por</th>
                            <td>{{ $condition_record->user ? $condition_record->user->name : 'N/E' }}</td>
                        </tr>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @if($condition_record->vehicle)
            @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'vehicle_conditions',
                'id'=>$condition_record->vehicle->id))
        @else
            @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'vehicle_conditions','id'=>0))
        @endif
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
