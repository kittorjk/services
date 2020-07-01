<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 02/05/2017
 * Time: 03:37 PM
 */
?>

@extends('layouts.wh_structure')

@section('header')
    @parent
@endsection

@section('menu_options')
    <li><a href="#">&ensp;<i class="fa fa-barcode"></i> ALMACENES <span class="caret"></span>&ensp;</a>
        <ul class="sub-menu">
            {{--<li><a href="{{ '/warehouse' }}"><i class="fa fa-refresh fa-fw"></i> Recargar página </a></li>--}}
            <li><a href="" onclick="window.location.reload();"><i class="fa fa-refresh fa-fw"></i> Recargar página </a></li>
            @if($user->work_type=='Almacén'||$user->priv_level==4)
                <li><a href="{{ '/warehouse/create' }}"><i class="fa fa-plus fa-fw"></i> Agregar almacén </a></li>
            @endif
            @if($user->priv_level==4)
                <li>
                    <a href="{{ '/excel/warehouses' }}"><i class="fa fa-file-excel-o fa-fw"></i> Exportar a Excel</a>
                </li>
                <li>
                    <a href="{{ '/excel/material_warehouse' }}"><i class="fa fa-file-excel-o fa-fw"></i> Exportar linked</a>
                </li>
            @endif
        </ul>
    </li>
    @if($user->work_type=='Almacén'||$user->priv_level==4)
        <li><a href="{{ '/material' }}">&ensp;<i class="fa fa-wrench"></i> MATERIALES&ensp;</a></li>
        <li><a href="#">&ensp;<i class="fa fa-exchange"></i> MOVIMIENTOS <span class="caret"></span>&ensp;</a>
            <ul class="sub-menu">
                <li><a href="{{ '/wh_entry' }}"><i class="fa fa-sign-in fa-fw"></i> Ver entradas </a></li>
                <li><a href="{{ '/wh_entry/create' }}"><i class="fa fa-plus fa-fw"></i> Registrar entrada </a></li>
                <li><a href="{{ '/wh_outlet' }}"><i class="fa fa-sign-out fa-fw"></i> Ver salidas </a></li>
                <li><a href="{{ '/wh_outlet/create' }}"><i class="fa fa-plus fa-fw"></i> Registrar salida </a></li>
                <li><a href="{{ '/warehouse/transfer' }}"><i class="fa fa-exchange fa-fw"></i> Registrar traspaso </a></li>
            </ul>
        </li>
        <li><a href="{{ '/warehouse/events/0' }}">&ensp;<i class="fa fa-bars"></i> EVENTOS&ensp;</a></li>
    @endif
    <li><a data-toggle="modal" href="#searchBox">&ensp;<i class="fa fa-search"></i> BUSCAR&ensp;</a></li>
@endsection

@section('content')
    <div class="col-sm-12 mg10 mg-tp-px-10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10 mg-tp-px-10">
        <p>Almacenes encontrados: {{ $warehouses->total() }}</p>

        <table class="fancy_table table_gray" id="fixable_table">
            <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Dirección</th>
                <th>Contenido</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($warehouses as $warehouse)
                <tr>
                    <td>{{ $warehouse->id }}</td>
                    <td>
                        <a href="/warehouse/{{ $warehouse->id }}" title="Ver información de almacén">
                            {{ $warehouse->name }}
                        </a>
                    </td>
                    <td>{{ $warehouse->location }}</td>
                    <td>
                        <a href="/warehouse/materials/{{ $warehouse->id }}" title="Ver contenido de almacén">
                            {{ 'Ver materiales' }}
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="col-sm-12 mg10" align="center">
        {!! $warehouses->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="fancy_table table_gray" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'warehouses','id'=>0))
    </div>

@endsection

@section('footer')
    @parent
@endsection

@section('javascript')
    <script src="{{ asset('app/js/fix_table_header.js') }}"></script> {{-- For fixed header --}}
    <script>
        $('#alert').delay(2000).fadeOut('slow');
    </script>
@endsection
