<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 01/09/2017
 * Time: 05:54 PM
 */
?>

@extends('layouts.staff_structure')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    <li><a href="#">&ensp;<i class="fa fa-users"></i> ROLES & CARGOS <span class="caret"></span>&ensp;</a>
        <ul class="sub-menu">
            <li><a href="{{ '/staff_role' }}"><i class="fa fa-bars fa-fw"></i> Ver todo </a></li>
            <li><a href="{{ '/staff_role/create' }}"><i class="fa fa-plus fa-fw"></i> Agregar un rol o cargo </a></li>
            @if($user->priv_level==4)
                <li><a href="{{ '/excel/staff_roles' }}"><i class="fa fa-file-excel-o"></i> Exportar a Excel </a></li>
            @endif
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
        <p>Registros encontrados: {{ $roles->total() }}</p>

        <table class="fancy_table table_gray" id="fixable_table">
            <thead>
            <tr>
                <th>Código</th>
                <th width="20%">Cargo / Rol</th>
                <th width="30%">Descripción</th>
                <th>Estado</th>
                <th>Asignado a</th>
                <th>Última actualización</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($roles as $role)
                <tr>
                    <td>
                        <a href="/staff_role/{{ $role->id }}" title="Ver detalle de cargo / rol">
                            {{ $role->code }}
                        </a>
                        <a href="/staff_role/{{ $role->id }}/edit" title="Modificar contenido de registro">
                            <i class="fa fa-pencil-square-o"></i>
                        </a>
                    </td>
                    <td>{{ $role->name }}</td>
                    <td>{{ $role->description }}</td>
                    <td>
                        {{ $role->in_use==1 ? 'Habilitado' : 'Deshabilitado' }}
                        @if($role->in_use==0)
                            <a href="/staff_role/{{ $role->id }}/enable" title="Habilitar cargo para asignar a personal">
                                <i class="fa fa-pencil-square-o"></i>
                            </a>
                        @endif
                    </td>
                    <td>
                        {{ $role->users ? ($role->users->count()==1 ? '1 persona' : $role->users->count().' personas') : '' }}
                    </td>
                    <td>
                        {{ $role->updated_at }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="col-sm-12 mg10" align="center">
        {!! $roles->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="fancy_table table_gray" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'staff_roles','id'=>0))
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
