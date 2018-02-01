<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 20/09/2017
 * Time: 02:55 PM
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
            <i class="fa fa-exchange"></i> Asignación de líneas <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li><a href="{{ '/line_assignation' }}"><i class="fa fa-refresh fa-fw"></i> Recargar página </a></li>
            <li><a href="{{ '/corporate_line' }}"><i class="fa fa-arrow-right fa-fw"></i> Ver lista de líneas </a></li>
            <li><a href="{{ '/line_requirement' }}"><i class="fa fa-arrow-right fa-fw"></i> Ver requerimientos </a></li>
            @if($user->action->acv_ln_asg /*($user->area=='Gerencia General'&&$user->priv_level>=2)||$user->priv_level==4*/)
                <li><a href="{{ '/line_assignation/create' }}"><i class="fa fa-exchange fa-fw"></i> Asignar línea </a></li>
            @endif
            @if($user->action->acv_ln_req /*$user->priv_level>=1*/)
                <li><a href="{{ '/line_requirement/create' }}"><i class="fa fa-plus fa-fw"></i> Nuevo requerimiento </a></li>
            @endif
            @if($user->priv_level==4)
                <li class="divider"></li>
                <li>
                    <a href="{{ '/excel/corp_line_assignations' }}"><i class="fa fa-file-excel-o fa-fw"></i> Exportar a Excel</a>
                </li>
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
        <p>Registros encontrados: {{ $assignations->total() }}</p>

        <table class="fancy_table table_brown tablesorter" id="fixable_table">
            <thead>
            <tr>
                <th>Fecha</th>
                <th>Línea</th>
                <th>Responsable previo</th>
                <th>Responsable actual</th>
                <th>Tipo de asignación</th>
                <th>Respaldo</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($assignations as $assignation)
                <tr>
                    <td>
                        <a href="{{ '/line_assignation/'.$assignation->id }}" title="Ver información detallada de asignación">
                            {{ date_format(new \DateTime($assignation->created_at), 'd-m-Y') }}
                        </a>

                        @if($user->action->acv_ln_edt /*$user->priv_level==4*/)
                            <a href="{{ '/line_assignation/'.$assignation->id.'/edit' }}"
                               title="Modificar información de asignación">
                                <i class="fa fa-pencil-square"></i>
                            </a>
                        @endif
                    </td>
                    <td>
                        <a href="{{ '/corporate_line/'.$assignation->line->id }}" title="Ver información detallada de línea">
                            {{ $assignation->line->number }}
                        </a>
                    </td>
                    <td>
                        {{ $assignation->resp_before ? $assignation->resp_before->name : 'N/E' }}
                    </td>
                    <td>
                        {{ $assignation->resp_after ? $assignation->resp_after->name : 'N/E' }}
                    </td>
                    <td>{{ $assignation->type }}</td>
                    <td>
                        @foreach($assignation->files as $file)
                            Recibo:
                            <a href="/download/{{ $file->id }}">
                                <img src="{{ '/imagenes/pdf-icon.png' }}" alt="PDF" />
                            </a>
                        @endforeach
                        @if($assignation->files()->count()==0)
                            <a href="/files/line_assignation/{{ $assignation->id }}">
                                <i class="fa fa-upload"></i> Subir archivo
                            </a>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-sm-12 mg10" align="center">
        {!! $assignations->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="fancy_table table_brown" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'corp_line_assignations','id'=>0))
    </div>

@endsection

@section('footer')
    @parent
@endsection

@section('javascript')
    <script src="{{ asset('app/js/fix_table_header.js') }}"></script> {{-- For fixed header --}}
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function(){
            $.post('/set_current_url', { url: window.location.href }, function(){});
        });

        $('#alert').delay(2000).fadeOut('slow');

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
