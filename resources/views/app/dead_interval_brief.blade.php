<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 03/04/2017
 * Time: 10:15 AM
 */
?>

@extends('layouts.projects_structure')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/custom_table.css") }}">
    <style>
        .dropdown-menu-prim > li > a {
            width: 170px;
            /*white-space: normal; /* Set content to a second line */
        }
    </style>
    <script type="text/javascript" src="{{ asset('http://viralpatel.net/blogs/demo/jquery/jquery.shorten.1.0.js') }}"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    @include('app.project_navigation_button', array('user'=>$user))
    <div class="btn-group">
        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
            <i class="fa fa-cogs"></i> Tiempos muertos <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li>
                @if($assignment)
                    <a href="{{ '/dead_interval?assig_id='.$assignment->id }}"><i class="fa fa-bars"></i> Resumen </a>
                @elseif($site)
                    <a href="{{ '/dead_interval?st_id='.$site->id }}"><i class="fa fa-bars"></i> Resumen </a>
                @endif
            </li>
            <li>
                @if($assignment)
                    <a href="{{ '/dead_interval/create?assig_id='.$assignment->id }}">
                        <i class="fa fa-plus"></i> Insertar tiempo muerto
                    </a>
                @elseif($site)
                    <a href="{{ '/dead_interval/create?st_id='.$site->id }}">
                        <i class="fa fa-plus"></i> Insertar tiempo muerto
                    </a>
                @endif
            </li>
            @if($user->priv_level>=3)
                <li class="divider"></li>
                <li>
                    @if($assignment)
                        <a href="/excel/dead_intervals_assig/{{ $assignment->id }}">
                            <i class="fa fa-file-excel-o"></i> Exportar a Excel
                        </a>
                    @elseif($site)
                        <a href="/excel/dead_intervals_st/{{ $site->id }}">
                            <i class="fa fa-file-excel-o"></i> Exportar a Excel
                        </a>
                    @endif
                </li>
            @endif
            @if($user->priv_level==4)
                <li>
                    <a href="{{ '/excel/dead_intervals' }}">
                        <i class="fa fa-file-excel-o"></i> Exportar tabla completa
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

    <?php $open_intervals=0; ?>
    @foreach($dead_intervals as $dead_interval)
        @if($dead_interval->closed==0)
            <?php $open_intervals++; ?>
        @endif
    @endforeach

    @if($open_intervals>0)
        <div class="col-sm-12 mg10">
            <div class="alert alert-info" align="center">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                {{ 'Un intervalo de tiempo muerto está en marcha actualmente' }}
            </div>
        </div>
    @endif

    <div class="col-sm-12 mg10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10">
        @if($assignment)
            <p>Asignación: {{ str_limit($assignment->name,100) }}</p>
        @elseif($site)
            <p>Sitio: {{ str_limit($site->name,100) }}</p>
        @endif
        <p>Registros encontrados: {{ $dead_intervals->total() }}</p>

        <table class="formal_table table_blue tablesorter" id="fixable_table">
            <thead>
            <tr>
                <th>#</th>
                <th>Desde</th>
                <th>Hasta</th>
                <th>Total en días</th>
                <th width="45%">Motivo</th>
                <th>Agregado por</th>
            </tr>
            </thead>
            <tbody>
            <?php $i=1; ?>
            @foreach ($dead_intervals as $dead_interval)
                <tr>
                    <td>{{ $i }}</td>
                    <td>
                        {{ date_format($dead_interval->date_from,'d/m/Y') }}
                        @if($user->action->prj_di_edt /*($dead_interval->user_id==$user->id)*/||$user->priv_level==4)
                            {{-- &&$dead_interval->closed==0 --}}
                            <a href="/dead_interval/{{ $dead_interval->id }}/edit" title="Modificar registro">
                                <i class="fa fa-pencil-square-o"></i>
                            </a>
                        @endif
                    </td>
                    <td>
                        {{ $dead_interval->closed==1 ? date_format($dead_interval->date_to,'d/m/Y') : 'En marcha' }}
                        @if($dead_interval->closed==0)
                            <a href="/dead_interval/close/{{ $dead_interval->id }}" class="pull-right confirm_close"
                               title="Cerrar intervalo de tiempo muerto">
                                <i class="fa fa-window-close"></i>
                            </a>
                        @endif
                    </td>
                    <td align="center">
                        {{ $dead_interval->closed==1 ? $dead_interval->total_days :
                             \Carbon\Carbon::now()->diffInDays($dead_interval->date_from) }}
                    </td>
                    <td>
                        <a href="#" class="pull-right" title="Archivos" data-toggle="modal"
                           data-target="{{ '#filesBox'.$dead_interval->id }}">
                            <i class="fa fa-files-o"></i>
                        </a>
                        <div class="comment">
                            {{ $dead_interval->reason }}
                        </div>

                        <div id="{{ 'filesBox'.$dead_interval->id }}" class="modal fade" role="dialog">
                            @include('app.dead_interval_files',
                                array('user'=>$user,'service'=>$service,'dead_interval'=>$dead_interval))
                        </div>
                    </td>
                    <td>{{ $dead_interval->user ? $dead_interval->user->name : 'N/E' }}</td>
                </tr>
                <?php $i++; ?>
            @endforeach
            </tbody>
        </table>

    </div>
    <div class="col-sm-12 mg10" align="center">
        {!! $dead_intervals->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="formal_table table_blue" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @if($assignment)
            @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'dead_intervals_assig',
                'id'=>$assignment->id))
        @elseif($site)
            @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'dead_intervals_st',
                'id'=>$site->id))
        @endif
    </div>

@endsection

@section('footer')
    @parent
@endsection

@section('javascript')
    <script src="{{ asset('app/js/fix_table_header.js') }}"></script> {{-- For fixed header --}}
    <script>
        $('#alert').delay(2000).fadeOut('slow');

        $('.confirm_close').on('click', function () {
            return confirm('Está seguro de que desea cerrar este intervalo de tiempo muerto?' +
                    ' Una vez cerrado no podrá ser modificado');
        });

        $(function(){
            $('#fixable_table').tablesorter({
                cssAsc: 'headerSortUp',
                cssDesc: 'headerSortDown',
                cssNone: '',
                dateFormat: 'uk'
            });
        });

        $(".comment").shorten({
            "showChars" : 150,
            "moreText"	: "ver más",
            "lessText"	: "ocultar"
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
