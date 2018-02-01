<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 20/02/2017
 * Time: 11:03 AM
 */
?>

@extends('layouts.actives_structure')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/custom_table.css") }}">
    <style>
        .dropdown-menu-prim > li > a {
            width: 190px;
        }
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    <a href="{{ '/device' }}" class="btn btn-primary"><i class="fa fa-laptop"></i> Equipos</a>
    <div class="btn-group">
        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
            <i class="fa fa-caret-square-o-right"></i> Características <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li>
                <a href="/characteristics/device/{{ $device_info ? $device_info->id : 0 }}">
                    <i class="fa fa-bars"></i> Ver todo
                </a>
            </li>
            @if($device_info&&$user->action->acv_dvc_edt /*($user->work_type=='Almacén'||$user->priv_level>=3)*/)
                <li>
                    <a href="/characteristics/device/{{ $device_info->id }}/create">
                        <i class="fa fa-plus"></i> Agregar característica </a>
                </li>
            @endif
            @if($user->priv_level==4)
                <li class="divider"></li>
                <li>
                    <a href="/excel/device_characteristics/{{ $device_info ? $device_info->id : 0 }}">
                        <i class="fa fa-file-excel-o"></i> Exportar a Excel </a>
                </li>
            @endif
        </ul>
    </div>
@endsection

@section('content')
    <div class="col-sm-12 mg10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10">
        @if($device_info)
            Equipo: <a href="/device/{{ $device_info->id }}">
                {{ $device_info->type.' '.$device_info->model.' - S/N '.$device_info->serial }}
                </a>
        @endif

        <p>
            {{ $characteristics->total()==1 ? 'Se encontró 1 registro' :
                'Se encontraron '.$characteristics->total().' registros' }}
        </p>

        <table class="formal_table table_blue tablesorter" id="fixable_table">
            <thead>
            <tr>
                <th>Fecha</th>
                @if(empty($device_info))
                    <th>Equipo</th>
                @endif
                <th>Tipo</th>
                <th>Valor</th>
                <th>Unidades</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($characteristics as $characteristic)
                <tr>
                    <td>
                        {{ date_format($characteristic->created_at,'d-m-Y') }}
                        @if($user->action->acv_dvc_edt /*$user->work_type=='Almacén'||$user->priv_level==4*/)
                            <a href="/characteristics/device/{{ $characteristic->id }}/edit" class="pull-right">
                                <i class="fa fa-pencil-square-o"></i>
                            </a>
                        @endif
                    </td>
                    @if(empty($device_info))
                        <td>
                            <a href="/device/{{ $characteristic->device_id }}">
                                {{ $characteristic->device->type.' '.$characteristic->device->model.' - S/N '.
                                    $characteristic->device->serial }}
                            </a>
                        </td>
                    @endif
                    <td>{{ $characteristic->type }}</td>
                    <td>{{ $characteristic->value }}</td>
                    <td>{{ $characteristic->units }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-sm-12 mg10" align="center">
        {!! $characteristics->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="formal_table table_blue" id="cloned"></table>
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
                cssNone: '',
                dateFormat: 'uk'
            });
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function(){
            $.post('/set_current_url', { url: window.location.href }, function(){});
        });
    </script>
@endsection
