<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 15/03/2017
 * Time: 03:23 PM
 */
?>

@extends('layouts.projects_structure')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/custom_table.css") }}">
    <link rel="stylesheet" href="{{ asset("app/css/progress_bar.css") }}">
    <style>
        .container {
            width: 55em;
            overflow-x: auto;
            white-space: nowrap;
        }
        /*
        .container table, .container th, .container td {
            border: 1px solid black;
        }
        */
        .container table {
            table-layout: fixed;
        }

        .container th {
            font-size: 14px;
            padding: .5em .5em;
            width: 80px;
            overflow: hidden;
            white-space: nowrap;
        }

        .container td {
            font-size: 14px;
            padding: .5em .5em;
            overflow: hidden;
        }

        .table_green th {
            color: white;
        }

        .hide-scroll::-webkit-scrollbar {
            width: 0;  /* remove scrollbar space */
            height: 0;
            background: transparent;  /* optional: just make scrollbar invisible */
        }

        .styled-scroll::-webkit-scrollbar {
            height: 10px;
        }

        .styled-scroll::-webkit-scrollbar-track {
            -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
            border-radius: 10px;
        }

        .styled-scroll::-webkit-scrollbar-thumb {
            border-radius: 10px;
            color: #3c763d;
            -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.5);
        }

        .same-width {
            display: block;
            width: 85px;
            word-wrap: break-word;
        }
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    @if($site)
        <a href="/site/{{ $site->assignment_id }}" class="btn btn-primary">
            <i class="fa fa-arrow-circle-left"></i> Sitios
        </a>
    @endif

    @include('app.project_navigation_button', array('user'=>$user))

    <div class="btn-group">
        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
            <i class="fa fa-cogs"></i> Items <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li><a href="/site/calendar/{{ $site->id }}"><i class="fa fa-refresh"></i> Recargar </a></li>
            @if(($site&&$site->status!=$site->last_stat()/*'Concluído'*/&&$site->status!=0/*'No asignado'*/&&
                $user->priv_level>=1)||$user->priv_level==4)
                <li><a href="/task/{{ $site->id }}/add"><i class="fa fa-plus"></i> Agregar item</a></li>
                <li><a href="/task/{{ $site->id }}/create"><i class="fa fa-plus"></i> Crear nuevo item</a></li>
                <li><a href="/import/tasks/{{ $site->id }}"><i class="fa fa-upload"></i> Importar items</a></li>
                <li>
                    <a href="/import/tasks-from-oc/{{ $site->id }}"><i class="fa fa-upload"></i> Importar de OC</a>
                </li>
                <li><a href="/import/items/{{ $site->id }}"><i class="fa fa-upload"></i> Cargar items</a></li>
            @endif
            {{--
            @if($user->priv_level==4)
                <li><a href="/delete/task"><i class="fa fa-trash-o"></i> Borrar archivo</a></li>
            @endif
            --}}
            @if($user->action->prj_st_exp /*$user->priv_level>=3*/)
                <li class="divider"></li>
                <li><a href="/excel/tasks/{{ $site ? $site->id : '' }}">
                        <i class="fa fa-file-excel-o"></i> Exportar a Excel </a>
                </li>
            @endif
            @if($user->action->prj_tk_exp /*$user->priv_level==4*/)
                <li>
                    <a href="{{ '/excel/items' }}">
                        <i class="fa fa-file-excel-o"></i> Exportar Items
                    </a>
                </li>
            @endif
        </ul>
    </div>
    @if($user->priv_level>=2)
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchBox">
            <i class="fa fa-search"></i> Buscar
        </button>
    @endif
@endsection

@section('content')
    <div class="col-sm-12 mg10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10">
        <p>
            <a href="{{ '/assignment' }}" title="Ir a resumen de asignaciones">Asignaciones</a>
            {{ ' > ' }}
            <a href="/site/{{ $site ? $site->assignment_id : '' }}" title="Ir a listado de sitios">Sitios</a>
            {{ ' > Items' }}
        </p>

        Asignación: <a href="/assignment/{{ $site->assignment->id }}">{{ str_limit($site->assignment->name,50) }}</a>
        &emsp;&emsp;
        {{ 'Código de cliente: '.($site->assignment->client_code ? $site->assignment->client_code : 'N/E') }}
        <br>
        Sitio: <a href="/site/{{ $site->id }}/show">{{ str_limit($site->name,50) }}</a>
        <a href="/task/{{ $site->id }}" class="pull-right"><i class="fa fa-bars"></i> Cambiar a vista resumen</a>

        <p>{{ $site->tasks->count()==1 ? 'Se encontró 1 item' : 'Se encontraron '.$site->tasks->count().' items' }}</p>

        <table class="formal_table table_blue" id="fixable_table">
            <thead>
            <tr>
                <th class="fix" width="25%">Item</th>
                <th width="8%">Unidades</th>
                <th width="8%">Proyectado</th>
                <th width="8%" class="{sorter: 'digit'}">Avance</th>
                <th>
                    <div class="container styled-scroll">
                        <table>
                            <thead>
                            <tr>
                                @for($i=0;$i<=$interval;$i++)
                                    <th style="text-align:center">
                                        <div class="same-width">
                                            {{ $i==0 ? $date->format('d M, Y') : $date->addDays(1)->format('d M, Y') }}
                                        </div>
                                    </th>
                                @endfor
                            </tr>
                            </thead>
                        </table>
                    </div>
                </th>
            </tr>
            </thead>
            <tbody>
            @foreach ($site->tasks as $task)
                <tr>
                    <td>
                        <a href="/task/{{ $task->id }}/show">{{ $task->name }}</a>
                        @if($user->action->prj_tk_edt /*(($user->area=='Gerencia Tecnica'&&$user->priv_level==2)||
                            $user->priv_level>=3)*/&&($task->status!=$task->last_stat()/*'Concluído'*/&&
                            $task->status!=0/*'No asignado'*/)||$user->priv_level==4)
                            <a href="/task/{{ $task->id }}/edit" title="Modificar"><i class="fa fa-pencil-square-o"></i></a>
                        @endif
                    </td>
                    <td>{{ $task->units }}</td>
                    <td align="center">{{ $task->total_expected }}</td>
                    <td align="center">
                        <div class="progress">
                            <div class="progress-bar progress-bar-success" style="{{ 'width: '.number_format(($task->progress/$task->total_expected)*100,2).'%' }}">
                                <span>{{ number_format(($task->progress/$task->total_expected)*100,2).' %' }}</span>
                            </div>
                        </div>
                    </td>
                    <td {{--align="center"--}}>

                        <div class="container hide-scroll">
                            <table>
                                {{--
                                <thead>
                                <tr style="visibility: collapse;">
                                    @for($i=0;$i<=$interval;$i++)
                                        <th style="text-align:center; background-color: white;height:1px;overflow-y: hidden">
                                            {{ $i==0 ? $date->format('d-m-Y') : $date->addDays(1)->format('d-m-Y') }}
                                        </th>
                                    @endfor
                                </tr>
                                </thead>
                                --}}
                                <tbody>
                                <tr>
                                    <?php $date->subDays($interval) ?>
                                    @for($i=0;$i<=$interval;$i++)
                                        <td style="background-color: white" align="center">
                                            <div class="same-width">

                                            <?php $i==0 ? $date : $date->addDays(1) ?>

                                                {{--<p style="color: transparent">{{ $date->format('d-m-Y') }}</p>--}}

                                            @foreach($task->activities as $activity)
                                                @if($activity->date==$date)
                                                    <a href="#" onclick="show_info(id='{{ $activity->id }}')" style="color:green;">
                                                        <strong>
                                                            {{ $activity->progress }}
                                                        </strong>
                                                    </a>
                                                    @if($user->action->prj_act_edt /*$user->priv_level==4*/)
                                                        <a href="/activity/{{ 'tk-'.$activity->id }}/edit"
                                                           style="color:green;">
                                                            <i class="fa fa-pencil-square-o"></i>
                                                        </a>
                                                    @endif
                                                @endif
                                            @endforeach
                                            @if($task->start_date==$date)
                                                <p style="color:dodgerblue;"><strong>{{ 'Inicio' }}</strong></p>
                                            @endif
                                            @if($task->end_date==$date)
                                                <p style="color:red;"><strong>{{ 'Deadline' }}</strong></p>
                                            @endif
                                            @if($date==Carbon\Carbon::now()->hour(0)->minute(0)->second(0))
                                                <?php $empty=true; ?>
                                                @foreach($task->activities as $activity)
                                                    @if($activity->date==$date)
                                                        <?php $empty=false; ?>
                                                    @endif
                                                @endforeach
                                                @if($empty)
                                                    @if($task->statuses($task->status)=='Ejecución')
                                                        <a href="/activity/{{ 'tk-'.$task->id }}/create">
                                                            <i class="fa fa-plus"></i> Agregar
                                                        </a>
                                                    @else
                                                        <p title="Cambie el estado del item para poder agregar avances">
                                                            Bloqueado
                                                        </p>
                                                    @endif
                                                @endif
                                            @endif
                                            </div>
                                        </td>
                                    @endfor
                                </tr>
                                </tbody>
                            </table>
                        </div>

                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="formal_table table_blue" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @if($site)
            @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'tasks','id'=>$site->id))
        @else
            @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'tasks','id'=>0))
        @endif
    </div>

    <div class="col-sm-10 col-sm-offset-1 mg10 mg-tp-px-20" align="center" id="load_info">
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

        $("#load_info").hide();

        function show_info(e){
            $.post('/load_activity_info', { activity_id: e }, function(data){
                $("#load_info").html(data).show();
            });
        }

        var Container = $(".container");

        Container.scroll(function() {
            Container.scrollLeft($(this).scrollLeft());
        });
    </script>
@endsection
