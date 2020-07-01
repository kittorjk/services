<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 24/08/2017
 * Time: 11:59 AM
 */
?>

@extends('layouts.projects_structure')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    @include('app.project_navigation_button', array('user'=>$user))
    <div class="btn-group">
        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
            <i class="fa fa-user"></i> Materiales <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            {{--<li><a href="{{ '/client_listed_material?client='.$client }}"><i class="fa fa-refresh"></i> Recargar página </a></li>--}}
            <li><a href="" onclick="window.location.reload();"><i class="fa fa-refresh"></i> Recargar página </a></li>
            <li><a href="{{ '/client_listed_material' }}"><i class="fa fa-bars"></i> Ver todo </a></li>
            <li>
                <a href="{{ '/client_listed_material/create?client='.$client }}"><i class="fa fa-plus"></i> Agregar material </a>
            </li>
            @if($user->priv_level==4)
                <li class="divider"></li>
                <li><a href="{{ '/excel/client_listed_materials' }}"><i class="fa fa-file-excel-o"></i> Exportar a Excel </a></li>
            @endif
        </ul>
    </div>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchBox">
        <i class="fa fa-search"></i> Buscar
    </button>
@endsection

@section('content')

    <div class="col-sm-12 mg10 mg-tp-px-10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10 mg-tp-px-10">
        <p>Registros encontrados: {{ $listed_materials->total() }}</p>

        <table class="fancy_table table_sky" id="fixable_table">
            <thead>
            <tr>
                <th>Cliente</th>
                <th>Código</th>
                <th>Nombre</th>
                <th>Modelo</th>
                <th>Aplica a (soluciones)</th>
                <th width="20%">Última modificación</th>
            </tr>
            </thead>
            <tbody>
            @foreach($listed_materials as $listed_material)
                <tr>
                    <td>{{ $listed_material->client }}</td>
                    <td>{{ $listed_material->code }}</td>
                    <td>
                        {{ $listed_material->name }}

                        <a href="/client_listed_material/{{ $listed_material->id }}/edit" title="Modificar datos de material">
                            <i class="fa fa-pencil-square-o"></i>
                        </a>
                    </td>
                    <td>{{ $listed_material->model }}</td>
                    <td>{{ $listed_material->applies_to }}</td>
                    <td>{{ $listed_material->updated_at }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-sm-12 mg10" align="center">
        {!! $listed_materials->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="fancy_table table_sky" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'client_listed_materials','id'=>0))
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
