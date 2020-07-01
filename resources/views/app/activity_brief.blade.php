<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 22/02/2017
 * Time: 05:25 PM
 */
?>

@extends('layouts.projects_structure')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/custom_table.css") }}">
    <style>
        .container {
            width: 65em;
            overflow-x: auto;
            white-space: nowrap;
        }

        .container table, .container th, .container td {
            border: 1px solid black;
        }

        .container th, .container td {
            font-size: 14px;
            padding: .5em .5em;
            width: 80px;
            overflow: hidden;
            white-space: nowrap;
        }

        .table_green th {
            color: white;
        }
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    @if($task_info)
        <a href="/task/{{ $task_info->site_id }}" class="btn btn-primary">
            <i class="fa fa-arrow-circle-left"></i> Items
        </a>
    @endif

    @include('app.project_navigation_button', array('user'=>$user))

    <div class="btn-group">
        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
            <i class="fa fa-cogs"></i> Actividades <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            @if($task_info)
                {{--<li><a href="/activity/{{ $task_info->id }}"><i class="fa fa-refresh"></i> Recargar página</a></li>--}}
                <li><a href="" onclick="window.location.reload();"><i class="fa fa-refresh"></i>Recargar página</a></li>
                @if(($task_info->status!=$task_info->last_stat()/*'Concluído'*/&&
                    $task_info->status!=0/*'No asignado'*/)||$user->priv_level==4)
                    <li>
                        <a href="/activity/{{ 'tk-'.$task_info->id }}/create">
                            <i class="fa fa-plus"></i> Agregar actividad
                        </a>
                    </li>
                @endif
                @if($user->action->prj_tk_clr /*$user->priv_level==4*/)
                    <li><a href="/task/clear_all/{{ $task_info->id }}"><i class="fa fa-trash"></i> Eliminar actividades</a></li>
                @endif
            @endif
            {{--
            @if($user->priv_level==4)
                <li><a href="/delete/activity"><i class="fa fa-trash-o"></i> Borrar archivo</a></li>
            @endif
            --}}
            @if($user->action->prj_act_exp /*$user->priv_level>=3*/)
                <li class="divider"></li>
                <li>
                    <a href="/excel/activities-per-task/{{ $task_info ? $task_info->id : 0 }}">
                        <i class="fa fa-file-excel-o"></i> Exportar a Excel
                    </a>
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
        <p>
            <a href="{{ '/assignment' }}" title="Ir a resumen de asignaciones">Asignaciones</a>
            {{ ' > ' }}
            <a href="/site/{{ $task_info ? $task_info->site->assignment_id : '' }}" title="Ir a listado de sitios">Sitios</a>
            {{ ' > ' }}
            <a href="/task/{{ $task_info ? $task_info->site_id : '' }}" title="Ir a listado de items">Items</a>
            {{ ' > Actividades' }}
        </p>

        @if($task_info)
            Asignación: <a href="/assignment/{{ $task_info->site->assignment->id }}">
                {{ str_limit($task_info->site->assignment->name,50) }}</a>
            <br>
            Sitio: <a href="/site/{{ $task_info->site->id }}/show">{{ str_limit($task_info->site->name,50) }}</a>
            {{--Tarea: <a href="/activity/{{ $task_info->id }}">{{ str_limit($task_info->name,80) }}</a>--}}
        @endif

        <p>{{ $activities->count()==1 ? 'Se encontró 1 actividad' : 'Se encontraron '.$activities->count().' actividades' }}</p>

        <table class="formal_table table_blue tablesorter" id="fixable_table">
            <thead>
            <tr>
                <th width="30%">Item</th>
                <th>Fechas y avance</th>
            </tr>
            </thead>
            <tbody>

            <tr>
                <td style="background-color: white">
                    @if($task_info)
                        <a href="/task/{{ $task_info->id }}/show">{{ $task_info->name }}</a>
                    @endif
                </td>
                <td style="background-color: white">
                    @if($task_info)
                        <div class="container">
                            <table>
                                <thead>
                                <tr>
                                    @for($i=0;$i<=$interval;$i++)
                                        <th>
                                            {{ $i==0 ? $date->format('d-m-Y') : $date->addDays(1)->format('d-m-Y') }}
                                        </th>
                                    @endfor
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <?php $date->subDays($interval) ?>
                                    @for($i=0;$i<=$interval;$i++)
                                        <td style="background-color: white" align="center">
                                            <?php $i==0 ? $date : $date->addDays(1) ?>
                                            @foreach($activities as $activity)
                                                @if($activity->date==$date)
                                                    @if($user->action->prj_act_edt /*$user->priv_level==4*/)
                                                        <a href="/activity/{{ 'tk-'.$activity->id }}/edit"
                                                            style="color:green;">
                                                            <i class="fa fa-pencil-square-o"></i>
                                                        </a>
                                                    @endif
                                                    <a href="#" onclick="show_info(id='{{ $activity->id }}')">
                                                        <p style="color:green;">
                                                            <strong>
                                                                {{ $activity->progress.' '.$task_info->units }}
                                                            </strong>
                                                        </p>
                                                    </a>
                                                @endif
                                            @endforeach
                                            @if($task_info->start_date==$date)
                                                <p style="color:dodgerblue;"><strong>{{ 'Inicio' }}</strong></p>
                                            @endif
                                            @if($task_info->end_date==$date)
                                                <p style="color:red;"><strong>{{ 'Deadline' }}</strong></p>
                                            @endif
                                            @if($date==Carbon\Carbon::now()->hour(0)->minute(0)->second(0)&&
                                                ($task_info->status!=$task_info->last_stat()/*'Concluído'*/&&
                                                $task_info->status!=0/*'No asignado'*/))
                                                <a href="/activity/{{ 'tk-'.$task_info->id }}/create">
                                                    <i class="fa fa-plus"></i> Agregar
                                                </a>
                                            @endif
                                        </td>
                                    @endfor
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    @endif
                </td>
            </tr>

            </tbody>
        </table>

        {{-- testing code for the addition of cells based on iterations
            <div class="container">
                <table>
                    <thead>
                    <tr>
                        @for($i=0;$i<50;$i++)
                            <th>{{ 'Column '.$i }}</th>
                        @endfor
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        @for($i=0;$i<50;$i++)
                            <td>{{ 'Row 1 Cell '.$i }}</td>
                        @endfor
                    </tr>
                    </tbody>
                </table>
            </div>
        --}}

    </div>
    {{--
    <div class="col-sm-12 mg10" align="center">
        {!! $activities->appends(request()->except('page'))->render() !!}
    </div>
    --}}

    <div class="col-sm-12 mg10" id="fixed">
        <table class="formal_table table_blue" id="cloned"></table>
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

        /* not required in this view
        $(function(){
            $('#fixable_table').tablesorter({
                cssAsc: 'headerSortUp',
                cssDesc: 'headerSortDown',
                cssNone: ''
            });
        });
        */

        $('.collapse').on('show.bs.collapse', function () {
            $('.collapse.in').collapse('hide');
            $(this).closest('tr').prev(".accordion-toggle").find('.indicator')
                    .toggleClass("glyphicon-chevron-down glyphicon-chevron-right");

        }).on('hide.bs.collapse', function () {
            $(this).closest('tr').prev(".accordion-toggle").find('.indicator')
                    .toggleClass("glyphicon-chevron-right glyphicon-chevron-down");
        });

        $("#load_info").hide();

        function show_info(e){
            $.post('/load_activity_info', { activity_id: e }, function(data){
                $("#load_info").html(data).show();
            });
        }
    </script>
@endsection
