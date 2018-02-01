<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 29/11/2017
 * Time: 06:16 PM
 */
?>

@extends('layouts.adm_structure')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    <li><a href="#">&ensp;<i class="fa fa-users"></i> EMPLEADOS <span class="caret"></span>&ensp;</a>
        <ul class="sub-menu">
            <li><a href="{{ '/employee' }}"><i class="fa fa-bars fa-fw"></i> Ver todo </a></li>
            <li><a href="{{ '/employee/create' }}"><i class="fa fa-user-plus fa-fw"></i> Agregar empleado </a></li>
            {{--@if($user->priv_level==4)--}}
                <li><a href="{{ '/excel/employees' }}"><i class="fa fa-file-excel-o"></i> Exportar a Excel </a></li>
            {{--@endif--}}
        </ul>
    </li>
    <li>
        <a data-toggle="modal" href="#searchBox">&ensp;<i class="fa fa-search"></i> BUSCAR&ensp;</a>
    </li>
@endsection

@section('content')
    <div class="col-sm-12 mg10 mg-tp-px-10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10 mg-tp-px-10">
        <p>Registros encontrados: {{ $employees->total() }}</p>

        <table class="fancy_table table_gray" id="fixable_table">
            <thead>
            <tr>
                <th>Código</th>
                <th>Apellidos</th>
                <th>Nombres</th>
                <th>C.I.</th>
                <th>Cargo</th>
                <th>Área</th>
                <th>Email corporativo</th>
                <th>Teléfono</th>
            </tr>
            </thead>
            <tbody>
            <?php
                $areas = array();
                $areas['Gerencia Tecnica'] = 'Tecnica';
                $areas['Gerencia General'] = 'G. General';
                $areas['Gerencia Administrativa'] = 'Administrativa';
                $areas['Cliente'] = 'Cliente';
                $areas['Subcontratista'] = 'Subcontratista';
            ?>

            @foreach ($employees as $employee)
                <tr @if($employee->active==0)style="background-color: #ba5e5e" title="Empleado retirado"@endif>
                    <td>
                        {{--@if($user->priv_level==4)--}}
                            <a href="/employee/{{ $employee->id }}" title="Ver información de empleado"
                               @if($employee->active==0)style="color: inherit"@endif>
                                {{ $employee->code }}
                            </a>
                            <a href="/employee/{{ $employee->id }}/edit" title="Modificar registro de empleado"
                               @if($employee->active==0)style="color: inherit"@endif>
                                <i class="fa fa-pencil-square"></i>
                            </a>
                        {{--@endif--}}
                    </td>
                    <td>{{ $employee->last_name }}</td>
                    <td>{{ $employee->first_name }}</td>
                    <td>{{ $employee->id_card.' '.$employee->id_extension }}</td>
                    <td>{{ $employee->role }}</td>
                    <td>{{ $employee->area }}</td>
                    <td>{{ $employee->corp_email }}</td>
                    <td>{{ $employee->phone!=0 ? $employee->phone : '' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="col-sm-12 mg10" align="center">
        {!! $employees->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="fancy_table table_gray" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'employees','id'=>0))
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
    </script>
@endsection
