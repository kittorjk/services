<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 22/02/2017
 * Time: 05:23 PM
 */
?>

@extends('layouts.projects_structure')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/custom_table.css") }}">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    @include('app.project_navigation_button', array('user'=>$user))

    @if($type_info)
        @if($type=='site')
            <a href="{{ '/site/'.$type_info->assignment_id }}" class="btn btn-primary">
                <i class="fa fa-arrow-circle-left"></i> Sitios
            </a>
        @elseif($type=='assignment')
            <a href="{{ '/assignment' }}" class="btn btn-primary">
                <i class="fa fa-arrow-circle-left"></i> Asignaciones
            </a>
        @elseif($type=='task')
            <a href="{{ '/task/'.$type_info->site_id }}" class="btn btn-primary">
                <i class="fa fa-arrow-circle-left"></i> Items
            </a>
        @elseif($type=='oc')
            <a href="{{ '/oc' }}" class="btn btn-primary"><i class="fa fa-arrow-circle-left"></i> OCs</a>
        @elseif($type=='invoice')
            <a href="{{ '/invoice' }}" class="btn btn-primary">
                <i class="fa fa-arrow-circle-left"></i> Facturas de proveedor
            </a>
        @endif
    @endif
    <div class="btn-group">
        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
            <i class="fa fa-cogs"></i> Eventos <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            @if($type_info)
                <li><a href="/event/{{ $type }}/{{ $id }}"><i class="fa fa-bars"></i> Ver todo </a></li>
                @if($open/*||$user->priv_level==4*/)
                    <li>
                        <a href="/event/{{ $type }}/{{ $id }}/create"><i class="fa fa-plus"></i> Agregar evento </a>
                    </li>
                @endif
            @endif
            @if(/*$user->priv_level>=3&&*/$type_info)
                <li class="divider"></li>
                <li>
                    <a href="/excel/events_per_type/{{ $type.'-'.$id }}">
                        <i class="fa fa-file-excel-o"></i> Exportar a Excel
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
        @if($type_info&&$type=='site')
            Asignación: <a href="/site/{{ $type_info->assignment->id }}">{{ str_limit($type_info->assignment->name,50) }}</a><br>
            Sitio: <a href="/task/{{ $type_info->id }}">{{ str_limit($type_info->name,50) }}</a>
        @elseif($type_info&&$type=='task')
            Asignación: <a href="/site/{{ $type_info->site->assignment->id }}">
                {{ str_limit($type_info->site->assignment->name,50) }}
            </a><br>
            Sitio: <a href="/task/{{ $type_info->site->id }}">{{ str_limit($type_info->site->name,50) }}</a><br>
            Item: <a href="/task/{{ $type_info->id }}/show">{{ str_limit($type_info->name,50) }}</a>
        @endif
        <p>{{ $events->total()==1 ? 'Se encontró 1 evento' : 'Se encontraron '.$events->total().' eventos' }}</p>

        <table class="formal_table table_blue tablesorter" id="fixable_table">
            <thead>
            <tr>
                <th>#</th>
                <th>Fecha desde</th>
                <th>Fecha hasta</th>
                <th>Evento</th>
                <th width="20%"></th>
            </tr>
            </thead>
            <tbody>
            @foreach ($events as $event)
                <tr class="accordion-toggle" data-toggle="collapse" data-parent="#accordion"
                    data-target="{{ '#collapse'.$event->id }}">

                    <td>{{ $id.'-'.$event->number }}</td>
                    <td>{{ date_format(new \DateTime($event->date), 'd-m-Y') }}</td>
                    <td>
                        {{ $event->date_to!='0000-00-00 00:00:00' ? date_format(new \DateTime($event->date_to), 'd-m-Y') : '' }}
                    </td>
                    <td>
                        {{ $event->description }}

                        @if($user->action->prj_evt_edt&&($event->user_generated==1||$user->priv_level==4))
                            {{-- Only the events created by users can be edited --}}
                            &ensp;
                            <a href="/event/{{ $type }}/{{ $event->id }}/edit" title="Modificar">
                                <i class="fa fa-pencil-square-o"></i>
                            </a>
                        @endif
                    </td>
                    {{--
                    @if(empty($type_info))
                        <td><a href="/site/{{ $event->eventable->id }}/show">{{ $event->eventable->name }}</a></td>
                        <td>
                            <a href="/assignment/{{ $event->eventable->assignment->id }}">
                                {{ $event->eventable->assignment->name }}
                            </a>
                        </td>
                    @endif
                    --}}
                    <td>
                        <a data-toggle="collapse" data-parent="#accordion" href="{{ '#collapse'.$event->id }}">
                            <i class="indicator glyphicon glyphicon-chevron-right pull-right"></i>
                        </a>
                    </td>
                </tr>
                <tr style="background-color: transparent" class="tablesorter-childRow expand-child">
                    <td colspan="5" style="padding: 0">
                        <div id="{{ 'collapse'.$event->id }}" class="panel-collapse collapse mg-tp-px-10 col-sm-10 col-sm-offset-1">

                            <table class="table table_sky">
                                <tr>
                                    <th>Detalle del evento:</th>
                                    <th colspan="2" width="35%">Archivos de respaldo (max. 5):</th>
                                </tr>
                                <tr>
                                    <td rowspan="3" style="background-color: white">
                                        <p>{{ $event->detail }}</p>
                                    </td>
                                    <td style="text-align: center;background-color: white" colspan="2">
                                        @foreach($event->files as $file)
                                            <a href="/download/{{ $file->id }}" style="text-decoration:none">
                                                @if($file->type=="pdf")
                                                    <img src="{{ '/imagenes/pdf-icon.png' }}" alt="PDF" />
                                                @elseif($file->type=="docx"||$file->type=="doc")
                                                    <img src="{{ '/imagenes/word-icon.png' }}" alt="WORD" />
                                                @elseif($file->type=="xlsx"||$file->type=="xls")
                                                    <img src="{{ '/imagenes/excel-icon.png' }}" alt="EXCEL" />
                                                @elseif($file->type=="jpg"||$file->type=="jpeg"||$file->type=="png")
                                                    <img src="{{ '/imagenes/image-icon.png' }}" alt="IMAGE" />
                                                @endif
                                            </a>
                                        @endforeach
                                        @if($event->files->count()<5)
                                            @if($open||$user->priv_level==4)
                                                <a href="/files/event/{{ $event->id }}">
                                                    <i class="fa fa-upload"></i> Subir archivo
                                                </a>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th width="17%">Responsable:</th>
                                    <td style="background-color: white">
                                        {{ $event->responsible ? $event->responsible->name : 'N/A' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Evento agregado por:</th>
                                    <td style="background-color: white">
                                        {{ $event->user->name }}
                                    </td>
                                </tr>
                            </table>

                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-sm-12 mg10" align="center">
        {!! $events->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="formal_table table_blue" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    @if($type_info)
        <div id="searchBox" class="modal fade" role="dialog">
            @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'events','id'=>$type.'-'.$id))
        </div>
    @endif

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

        $(function(){
            $('#fixable_table').tablesorter({
                cssAsc: 'headerSortUp',
                cssDesc: 'headerSortDown',
                cssNone: ''
            });
        });

        $('.collapse').on('show.bs.collapse', function () {
            $('.collapse.in').collapse('hide');
            $(this).closest('tr').prev(".accordion-toggle").find('.indicator')
                    .toggleClass("glyphicon-chevron-down glyphicon-chevron-right");

        }).on('hide.bs.collapse', function () {
            $(this).closest('tr').prev(".accordion-toggle").find('.indicator')
                    .toggleClass("glyphicon-chevron-right glyphicon-chevron-down");

        });
    </script>
@endsection
