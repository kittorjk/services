<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 01/09/2017
 * Time: 12:21 PM
 */
?>

@extends('layouts.projects_structure')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/custom_table.css") }}">
    <link rel="stylesheet" href="{{ asset("app/css/progress_bar.css") }}">
    <style>
        .container {
            width: 60em;
            overflow-x: auto;
            white-space: nowrap;
            padding: 0;
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
            font-size: 12px;
            padding: .5em .5em;
            width: 80px;
            overflow: hidden;
            white-space: nowrap;
        }

        .container td {
            font-size: 12px;
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
            width: 75px;
            word-wrap: break-word;
        }
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    <a href="{{ '/assignment' }}" class="btn btn-primary" title="Ir a resumen de asignaciones">
        <i class="fa fa-arrow-up"></i> Asig.
    </a>

    @include('app.project_navigation_button', array('user'=>$user))

    <div class="btn-group">
        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
            <i class="fa fa-map-marker"></i> Sitios <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li>
                <a href="/site{{ '/'.$assignment->id }}">
                    <i class="fa fa-bars"></i> Resumen
                </a>
            </li>
            @if(($assignment&&$assignment->status!=$assignment->last_stat()/*'Concluído'*/&&
                $assignment->status!=0/*'No asignado'*/&&$user->priv_level>=1)||$user->priv_level==4)
                <li>
                    <a href="/site/{{ $assignment->id }}/create">
                        <i class="fa fa-plus"></i> Nuevo Sitio
                    </a>
                </li>
            @endif
            @if($assignment&&$assignment->sites->count()==0)
                <li>
                    <a href="/import/sites/{{ $assignment->id }}">
                        <i class="fa fa-upload"></i> Importar sitios
                    </a>
                </li>
            @endif

            <li class="dropdown-submenu">
                <a href="#" data-toggle="dropdown"><i class="fa fa-list"></i> Lista de materiales de cliente</a>
                <ul class="dropdown-menu dropdown-menu-prim">
                    <li>
                        <a href="{{ '/client_listed_material?client='.$assignment->client }}">
                            <i class="fa fa-list"></i> Ver materiales
                        </a>
                    </li>
                    <li>
                        <a href="/import/client_listed_materials/{{ $assignment->id }}">
                            <i class="fa fa-upload"></i> Importar lista de materiales
                        </a>
                    </li>
                </ul>
            </li>

            @if($assignment)
                <li>
                    <a href="/excel/per-assignment-progress/{{ $assignment->id }}">
                        <i class="fa fa-file-excel-o"></i> Reporte de avance general
                    </a>
                </li>
            @endif
            @if($user->priv_level>=3)
                <li class="divider"></li>
                <li>
                    <a href="/excel/sites{{ '/'.$assignment->id }}">
                        <i class="fa fa-file-excel-o"></i> Exportar a Excel
                    </a>
                </li>
            @endif
            @if($user->priv_level==4)
                <li><a href="{{ '/excel/order_site' }}"><i class="fa fa-file-excel-o"></i> Exportar Pivot</a></li>
            @endif
        </ul>
    </div>
    @if($user->priv_level>=2)
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchBox">
            <i class="fa fa-search"></i> Buscar
        </button>
    @endif
    <a href="{{ '/contact' }}" class="btn btn-primary"><i class="fa fa-phone"></i> Contactos </a>

    @if($assignment&&$assignment->type=='Radiobases')
        @if(($user->work_type=='Radiobases'&&$user->priv_level>=1)||$user->priv_level==4)
            <a href="{{ '/rbs_viatic' }}" class="btn btn-primary"><i class="fa fa-arrow-circle-right"></i> Viáticos</a>
        @endif
    @endif

    @if($assignment)
        <a href="/site/refresh_data/{{ $assignment->id }}" class="btn btn-primary">
            <i class="fa fa-refresh"></i> Actualizar
        </a>
    @endif
@endsection

@section('content')
    <div class="col-sm-12 mg10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10">
        <p>
            <a href="{{ '/assignment' }}" title="Ir a resumen de asignaciones">Asignaciones</a>
            {{ ' > Sitios' }}
        </p>
        @if($assignment)
            @if($user->priv_level>=1)
                Asignación: <a href="/assignment/{{ $assignment->id }}">{{ str_limit($assignment->name,50) }}</a>
            @else
                {{ 'Asignación: '.str_limit($assignment->name,50) }}
            @endif
            &emsp;&emsp;
            {{ 'Código de cliente: '.($assignment->client_code ? $assignment->client_code : 'N/E') }}

            <div class="pull-right" align="right">
                <a href="/site{{ '/'.$assignment->id }}" title="Cambiar vista a resumen" style="color:black; text-decoration: none">
                    <i class="fa fa-list"></i> Resumen
                </a>
                &ensp;
                <a href="/site{{ '/'.$assignment->id }}" title="Cambiar vista a resumen" style="text-decoration: none">
                    <i class="fa fa-toggle-on"></i>
                </a>
                &ensp;
                <a href="/site/schedule{{ '?asg_id='.$assignment->id.'&opt=all' }}" style="color: black; text-decoration: none"
                   title="Recargar vista cronograma">
                    <i class="fa fa-calendar"></i> Cronograma
                </a>

                <br>
                &ensp;
                <a href="/site/schedule{{ '?asg_id='.$assignment->id.'&opt=projected' }}">Proyectado</a>
                &ensp;
                <a href="/site/schedule{{ '?asg_id='.$assignment->id.'&opt=exec' }}">Ejecutado</a>
                &ensp;
                <a href="/site/schedule{{ '?asg_id='.$assignment->id.'&opt=all' }}">Ambos</a>
            </div>
        @endif

        <p>Sitios encontrados: {{ $assignment->sites->count() }}</p>

        <table class="formal_table table_blue" id="fixable_table">
            <thead>
            <tr>
                <th class="fix" width="15%">Sitio</th>
                <th width="8%">Tipo</th>
                <th width="8%">Localidad</th>
                <th style="padding: 5px 0 0 0;">
                    <div class="container styled-scroll">
                        <table>
                            <thead>
                            <tr>
                                @for($i=0;$i<=$interval;$i++)
                                    <th style="text-align:center;">
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
            @foreach ($assignment->sites as $site)
                <tr>
                    <td>
                        <a href="/site/{{ $site->id }}/show">{{ $site->name }}</a>
                        @if(($user->action->prj_st_edt /*(($user->area=='Gerencia Tecnica'&&$user->priv_level>=1)||$user->priv_level>=3)*/&&
                            ($site->status!=$site->last_stat()/*'Concluído'*/&&$site->status!=0/*'No asignado'*/))||
                            $user->priv_level==4)
                            <a href="/site/{{ $site->id }}/edit" title="Editar"><i class="fa fa-pencil-square-o"></i></a>
                        @endif
                    </td>
                    <td>
                        @if($assignment&&$assignment->type=='Radiobases')
                            {{ $site->rbs_char ? $site->rbs_char->type_rbs.' - '.$site->rbs_char->type_station : 'N/E' }}
                        @endif
                    </td>
                    <td>
                        {{ $site->municipality }}
                    </td>
                    <td>

                        <div class="container hide-scroll">
                            <table>
                                <tbody>
                                <tr>
                                    <?php $date->subDays($interval); ?>
                                    @for($i=0;$i<=$interval;$i++)

                                        <?php $i==0 ? $date : $date->addDays(1) ?>

                                        <td style="
                                            @if($date->between($site->start_line,$site->deadline)&&
                                                $date->between($site->start_date,$site->end_date)&&$opt=='all')
                                                background-color: greenyellow;
                                            @elseif($date->between($site->start_line,$site->deadline)&&
                                                ($opt=='projected'||$opt=='all'))
                                                background-color: yellow;
                                            @elseif($date->between($site->start_date,$site->end_date)&&
                                                ($opt=='exec'||$opt=='all'))
                                                background-color: lawngreen;
                                            @else
                                                background-color: white;
                                            @endif
                                                 " align="center">
                                            <div class="same-width">

                                                @if($site->rbs_char)
                                                    @if($site->rbs_char->tech_group)
                                                        @if(($date->between($site->start_line,$site->deadline)&&$opt=='projected')||
                                                            ($date->between($site->start_date,$site->end_date)&&$opt=='exec')||
                                                            (($date->between($site->start_line,$site->deadline)||
                                                            $date->between($site->start_date,$site->end_date))&&$opt=='all'))
                                                            {!! 'G'.$site->rbs_char->tech_group->group_number.'<br>' !!}
                                                        @endif
                                                    @endif
                                                @endif
                                                @foreach($site->events as $event)
                                                    @if($event->date==$date)
                                                        <a href="#" onclick="show_event(id='{{ $event->id }}')">
                                                            <strong title="{{ $event->description }}">
                                                                {{ 'Evento' }}
                                                            </strong>
                                                        </a>
                                                        @if($user->action->prj_evt_edt /*$user->priv_level==4*/)
                                                            <a href="/event/{{ 'site' }}/{{ $event->id }}/edit" title="Editar">
                                                                <i class="fa fa-pencil-square-o"></i>
                                                            </a>
                                                        @endif
                                                    @endif
                                                @endforeach
                                                @if($assignment->start_line==$date)
                                                    <strong style="color:dodgerblue;">{{ 'Inicio' }}</strong>
                                                @endif
                                                @if($assignment->deadline==$date)
                                                    <strong style="color:red;">{{ 'Deadline' }}</strong>
                                                @endif
                                                @if($date==$current_date)
                                                    <?php $empty=true; ?>
                                                    @foreach($site->events as $event)
                                                        @if($event->date==$date)
                                                            <?php $empty=false; ?>
                                                        @endif
                                                    @endforeach
                                                    @if($empty)
                                                        @if($site->status!=$site->last_stat()&&$site->status!=0)
                                                            <a href="/event/{{ 'site' }}/{{ $site->id }}/create"
                                                                title="Agregar un evento a este sitio">
                                                                <i class="fa fa-plus"></i> Agregar
                                                            </a>
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
        @if($assignment)
            @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'sites','id'=>$assignment->id))
        @else
            @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'sites','id'=>0))
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

        function show_event(e){
            $.post('/load_event_info', { event_id: e }, function(data){
                $("#load_info").html(data).show();
            });
        }

        var Container = $(".container");

        Container.scroll(function() {
            Container.scrollLeft($(this).scrollLeft());
        });
    </script>
@endsection
