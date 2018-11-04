<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 27/03/2017
 * Time: 04:28 PM
 */
?>

@extends('layouts.actives_structure')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/image_modal.css") }}">
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
            <i class="fa fa-bars"></i> Historial de equipo <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li><a href="/history/device/{{ $device->id }}"><i class="fa fa-refresh"></i> Recargar página </a></li>
            <li><a href="{{ '/device' }}"><i class="fa fa-bars"></i> Ir a equipos </a></li>
            <li><a href="{{ '/operator' }}"><i class="fa fa-bars"></i> Ir a asignaciones </a></li>
            <li><a href="{{ '/device_requirement' }}"><i class="fa fa-bars"></i> Ir a requerimientos</a></li>
            @if($user->action->acv_dvc_exp /*$user->priv_level==4*/)
                <li class="divider"></li>
                <li><a href="{{ '/excel/device_histories' }}"><i class="fa fa-file-excel-o"></i> Exportar tabla </a></li>
                <li>
                    <a href="/excel/device_history/{{ $device->id }}">
                        <i class="fa fa-file-excel-o"></i> Exportar historial
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

    <div class="col-sm-12 mg10 mg-tp-px-10">
        @if($device)
            Equipo: <a href="/device/{{ $device->id }}">
                {{ $device->type.' '.$device->model.' - '.$device->serial }}
            </a>
        @endif
        <p>Entradas en el historial: {{ $device_histories->total() }}</p>

        <table class="fancy_table table_gray" id="fixable_table">
            <thead>
            <tr>
                <th>Fecha</th>
                <th>Tipo de registro</th>
                <th width="40%">Descripción</th>
                <th>Estado del equipo</th>
                <th>Información adicional</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($device_histories as $history)
                <tr>
                    <td>{{ date_format($history->created_at,'d/m/Y') }}</td>
                    <td>{{ $history->type }}</td>
                    <td>{{ $history->contents }}</td>
                    <td>{{ $history->status }}</td>
                    <td>
                        @if($history->historyable_type=='App\Device')
                            <a href="/device/{{ $history->historyable_id }}">Ver información de equipo</a>
                        @elseif($history->historyable_type=='App\DeviceRequirement')
                            <a href="/device_requirement/{{ $history->historyable_id }}">Ver requerimiento</a>
                        @elseif($history->historyable_type=='App\Operator')
                            <a href="/operator/{{ $history->historyable_id }}">Ver asignación</a>
                        @elseif($history->historyable_type=='App\Maintenance')
                            <a href="/maintenance/{{ $history->historyable_id }}">Ver inf. de mantenimiento</a>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="col-sm-12 mg10" align="center">
        {!! $device_histories->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="fancy_table table_gray" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'device_histories','id'=>$device->id))
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
    </script>
@endsection
