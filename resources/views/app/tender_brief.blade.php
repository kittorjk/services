<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 18/01/2018
 * Time: 03:40 PM
 */
?>

@extends('layouts.projects_structure')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/custom_table.css") }}">
    <style>
        .dropdown-menu-prim > li > a {
            width: 190px;
            /*white-space: normal; /* Set code to a second line */
        }
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    @include('app.project_navigation_button', array('user'=>$user))
    <div class="btn-group">
        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
            <i class="fa fa-cogs"></i> Licitaciones <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li><a href="{{ '/tender' }}"><i class="fa fa-refresh fa-fw"></i> Recargar página </a></li>
            <li><a href="{{ '/tender/create' }}"><i class="fa fa-plus fa-fw"></i> Nueva licitación </a></li>
            @if($user->priv_level>=3)
                <li><a href="{{ '/tender?mode=asg' }}"><i class="fa fa-list fa-fw"></i> Ver licitaciones ganadas </a></li>
                <li><a href="{{ '/tender?mode=nsg' }}"><i class="fa fa-list fa-fw"></i> Ver licitaciones perdidas </a></li>
                <li><a href="{{ '/tender?mode=np' }}"><i class="fa fa-list fa-fw"></i> Ver licitaciones no presentadas </a></li>
            @endif
            @if($user->priv_level==4)
                <li class="divider"></li>
                <li><a href="{{ '/excel/tenders' }}"><i class="fa fa-file-excel-o fa-fw"></i> Exportar a Excel </a></li>
            @endif
        </ul>
    </div>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchBox">
        <i class="fa fa-search"></i> Buscar
    </button>
@endsection

@section('content')

    @if($tenders->ending>0)
        <div class="col-sm-12 mg10">
            <div class="alert alert-warning" align="center">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <i class="fa fa-warning fa-2x pull-left"></i>
                {{ 'El plazo para presentación a '.($tenders->ending==1 ? '1 licitación' : $tenders->ending.
                    ' licitaciones').' vence pronto' }}
            </div>
        </div>
    @endif

    @if($tenders->ended>0)
        <div class="col-sm-12 mg10">
            <div class="alert alert-danger" align="center">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <i class="fa fa-warning fa-2x pull-left"></i>
                {{ 'El plazo para presentación a '.
                    ($tenders->ended==1 ? '1 licitación' : $tenders->ended.' licitaciones').' ha vencido' }}
            </div>
        </div>
    @endif

    <div class="col-sm-12 mg10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10">

        <p>Registros encontrados: {{ $tenders->total() }}</p>

        <table class="formal_table table_blue tablesorter" id="fixable_table">
            <thead>
            <tr>
                <th>Código</th>
                <th width="25%">Licitación</th>
                <th>Cliente</th>
                <th>Área de trabajo</th>
                <th width="15%">Plazo para presentación</th>
                <th>Estado</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($tenders as $tender)
                <tr>
                    <td>
                        <a href="/tender/{{ $tender->id }}" title="Ver información de esta licitación">
                            {{ $tender->code }}
                        </a>
                    </td>
                    <td>
                        {{ $tender->name }}

                        @if((($user->id==$tender->user_id)&&$tender->status=='Activo')||$user->priv_level==4)
                            <a href="/tender/{{ $tender->id }}/edit" title="Editar información de esta licitación">
                                <i class="fa fa-pencil-square-o"></i>
                            </a>
                        @endif
                    </td>
                    <td>{{ $tender->client }}</td>
                    <td>{{ $tender->area }}</td>
                    <td>
                        {{ $tender->application_deadline!='0000-00-00 00:00:00' ?
                            $tender->application_deadline->format('d-m-Y') : '' }}
                    </td>
                    <td>
                        <span style="{{ $tender->applied==0&&$tender->status=='Activo' ?
                            (Carbon\Carbon::now()->diffInDays($tender->application_deadline,false)<=5&&
                            Carbon\Carbon::now()->diffInDays($tender->application_deadline,false)>=0 ?
                            'color: darkorange' : (Carbon\Carbon::now()->diffInDays($tender->application_deadline,false)<0 ?
                            'color: darkred' : '')) : '' }}">

                            {{ $tender->status }}
                        </span>

                        @if($tender->status=='Asignado')
                            @if($tender->project)
                                <a href="/project/{{ $tender->project->id }}" title="Ver registro de contrato de esta licitación">
                                    <i class="fa fa-eye pull-right"></i>
                                </a>
                            @else
                                <a href="/tender/add_contract/{{ $tender->id }}" title="Registrar contrato de licitación ganada">
                                    <i class="fa fa-plus pull-right"></i>
                                </a>
                            @endif
                        @endif
                        @if($tender->status=='Documentación enviada')
                            <a href="/tender/won/{{ $tender->id }}" title="Marcar licitación como: Asignado/Ganado"
                               class="confirm_won">
                                <i class="fa fa-check pull-right"></i>
                            </a>
                            &emsp;
                            <a href="/tender/close/{{ $tender->id }}" title="Marcar licitación como: No asignada"
                               class="confirm_close">
                                <i class="fa fa-ban pull-right"></i>
                            </a>
                        @endif
                        @if($tender->status=='Activo')
                            <a href="/tender/applied/{{ $tender->id }}" title="Registrar envío de documentación"
                               class="confirm_applied">
                                <i class="fa fa-send pull-right"></i>
                            </a>
                        @endif

                        @if($tender->applied==0&&$tender->status=='Activo')
                            @if(Carbon\Carbon::now()->diffInDays($tender->application_deadline,false)<=5&&
                                Carbon\Carbon::now()->diffInDays($tender->application_deadline,false)>=0)
                                <a href="/tender/{{ $tender->id }}" style="color: darkorange">
                                    <i class="fa fa-exclamation-circle pull-right"
                                       title="{{ 'El plazo de presentación vence pronto' }}">
                                    </i>
                                </a>
                            @elseif((Carbon\Carbon::now()->diffInDays($tender->application_deadline,false)<0))
                                <a href="/tender/{{ $tender->id }}" style="color: darkred">
                                    <i class="fa fa-exclamation-circle pull-right"
                                       title="{{ 'El plazo de presentación ha vencido' }}">
                                    </i>
                                </a>
                            @endif
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

    </div>

    <div class="col-sm-12 mg10" align="center">
        {!! $tenders->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="formal_table table_blue" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'tenders','id'=>0))
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
                cssNone: ''
            });
        });

        $('.confirm_close').on('click', function () {
            return confirm('Está seguro de que desea marcar esta licitación como: No asignada?');
        });

        $('.confirm_applied').on('click', function () {
            return confirm('Está seguro de que desea registrar el envío de documentación para aplicar a la ' +
                'licitación indicada?');
        });

        $('.confirm_won').on('click', function () {
            return confirm('Está seguro de que desea marcar esta licitación como: Asignada?');
        });
    </script>
@endsection
