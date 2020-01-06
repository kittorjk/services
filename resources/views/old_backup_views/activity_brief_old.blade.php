@extends('layouts.master')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/custom_table.css") }}">
@endsection

@section('content')
    <div class="col-sm-12 mg20 mg-tp-px-10">
        <div class="row">
            <div class="col-sm-8">
                @if($user->priv_level==4)
                    <a href="/" class="btn btn-primary"><i class="fa fa-home"></i> Inicio </a>
                @endif
                <a href="#" onclick="history.back();" class="btn btn-primary"><i class="fa fa-arrow-left"></i> Atrás</a>
                    @if($site_info)
                        <a href="/site/{{ $site_info->assignment_id }}" class="btn btn-primary">
                            <i class="fa fa-arrow-circle-left"></i> Sitio
                        </a>
                    @elseif($task_info)
                        <a href="/task/{{ $task_info->site_id }}" class="btn btn-primary"><i class="fa fa-arrow-circle-left"></i> Item</a>
                    @endif
                <div class="btn-group">
                    <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
                        <i class="fa fa-arrow-circle-right"></i> Ir a <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-prim">
                        <li><a href="/assignment/"><i class="fa fa-arrow-right"></i> Proyectos </a></li>
                        <li><a href="/site/"><i class="fa fa-arrow-right"></i> Sitios </a></li>
                        @if($user->area=='Gerencia Administrativa'||$user->area=='Gerencia General'||$user->priv_level>=3)
                            <li><a href="/order/"><i class="fa fa-arrow-right"></i> Ordenes </a></li>
                            <li><a href="/contract/"><i class="fa fa-arrow-right"></i> Contratos </a></li>
                            <li><a href="/guarantee/"><i class="fa fa-arrow-right"></i> Polizas </a></li>
                            <li><a href="/bill/"><i class="fa fa-arrow-right"></i> Facturas </a></li>
                        @endif
                    </ul>
                </div>
                <div class="btn-group">
                    <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
                        <i class="fa fa-cogs"></i>{{ $site_info ? ' Eventos ' : ' Actividades ' }}<span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-prim">
                        @if($site_info)
                            <li><a href="/activity/{{ $site_info->id }}"><i class="fa fa-bars"></i> Resumen </a></li>
                        @elseif($task_info)
                            <li><a href="/activity/task/{{ $task_info->id }}"><i class="fa fa-bars"></i> Resumen </a></li>
                        @else
                            <li><a href="/activity"><i class="fa fa-bars"></i> Resumen </a></li>
                        @endif
                        @if($site_info)
                            @if(($site_info->status!='Concluído'&&$site_info->status!='No asignado')||$user->priv_level==4)
                                <li><a href="/activity/{{ 'st-'.$site_info->id }}/create"><i class="fa fa-plus"></i> Agregar evento </a></li>
                            @endif
                        @elseif($task_info)
                            @if(($task_info->status!='Concluído'&&$task_info->status!='No asignado')||$user->priv_level==4)
                                <li><a href="/activity/{{ 'tk-'.$task_info->id }}/create"><i class="fa fa-plus"></i> Agregar actividad </a></li>
                            @endif
                        @endif
                        @if($user->priv_level==4)
                            <li><a href="/delete/activity"><i class="fa fa-trash-o"></i> Borrar archivo</a></li>
                        @endif
                        @if($user->priv_level>=3)
                            <li class="divider"></li>
                            @if($site_info)
                                <li>
                                    <a href="/excel/activities-per-site/{{ $site_info->id }}">
                                        <i class="fa fa-file-excel-o"></i> Exportar a Excel
                                    </a>
                                </li>
                            @elseif($task_info)
                                <li>
                                    <a href="/excel/activities-per-task/{{ $task_info->id }}">
                                        <i class="fa fa-file-excel-o"></i> Exportar a Excel
                                    </a>
                                </li>
                            @endif
                        @endif
                    </ul>
                </div>
                @if($user->priv_level>=2)
                    <!--
                    <a href="/search/activities-per-site/{ //$site_info->id }}" class="btn btn-primary"><i class="fa fa-search"></i> Buscar </a>
                    <a href="/search/activities-per-task/{ //$task_info->id }}" class="btn btn-primary"><i class="fa fa-search"></i> Buscar </a>
                    -->
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchBox">
                        <i class="fa fa-search"></i> Buscar
                    </button>
                @endif
            </div>
            <div class="col-sm-4" align="right">
                @include('app.menu_upper_right')
            </div>
        </div>
    </div>
    <div class="col-sm-12 mg10">
        @if (Session::has('message'))
            <div class="alert alert-info" align="center" id="alert">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>{{ Session::get('message') }}
            </div>
        @endif
    </div>
    <div class="col-sm-12 mg10">
        @if($site_info)
            Proyecto: <a href="/site/{{ $site_info->assignment->id }}">{{ str_limit($site_info->assignment->name,50) }}</a><br>
            Sitio: <a href="/task/{{ $site_info->id }}">{{ str_limit($site_info->name,50) }}</a>
        @elseif($task_info)
            Proyecto: <a href="/site/{{ $task_info->site->assignment->id }}">{{ str_limit($task_info->site->assignment->name,50) }}</a><br>
            Sitio: <a href="/task/{{ $task_info->site->id }}">{{ str_limit($task_info->site->name,50) }}</a>
            &emsp;
            Tarea: <a href="/activity/task/{{ $task_info->id }}">{{ $task_info->name }}</a>
        @endif
        @if($activities->total()==1)
            <p>Se encontró 1 actividad</p>
        @else
            <p>Se encontraron {{ $activities->total() }} actividades</p>
        @endif
        <table class="formal_table table_blue tablesorter" id="ordenar_tabla">
            <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Actividad / Evento</th>
                @if(empty($task_info)&&empty($site_info))
                    <th>Item</th>
                    <th>Sitio</th>
                    <th>Proyecto</th>
                @endif
                <th width="20%"></th>
            </tr>
            </thead>
            <tbody>
            @foreach ($activities as $activity)
                <tr class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" data-target="{{ '#collapse'.$activity->id }}">
                    <td>
                        @if($activity->task_id!=0)
                            {{ $activity->task->number.'-'.$activity->number }}
                        @else
                            {{ '0-'.$activity->number }}
                        @endif
                    </td>
                    <td>{{ date_format(new \DateTime($activity->start_date), 'd-m-Y') }}</td>
                    <td>{{ $activity->type }}
                        @if($user->priv_level==4)
                            &emsp;<a href="/activity/{{ 'tk-'.$activity->id }}/edit"><i class="fa fa-pencil-square-o"></i> Modificar</a>
                        @endif
                    </td>
                    @if(empty($task_info)&&empty($site_info))
                        <td>
                            @if($activity->task_id!=0)
                                <a href="/task/{{ $activity->task->id }}/show">{{ $activity->task->name }}</a>
                            @else
                                {{ 'N/A' }}
                            @endif
                        </td>
                        <td><a href="/site/{{ $activity->site->id }}/show">{{ $activity->site->name }}</a></td>
                        <td><a href="/assignment/{{ $activity->site->assignment->id }}">{{ $activity->site->assignment->name }}</a></td>
                    @endif
                    <td>
                        <a href="/activity/{{ $activity->id }}/show">{{ 'Ver en una ventana aparte' }}</a>
                        <a data-toggle="collapse" data-parent="#accordion" href="{{ '#collapse'.$activity->id }}">
                            <i class="indicator glyphicon glyphicon-chevron-right pull-right"></i>
                        </a>
                    </td>
                </tr>
                <tr style="background-color: transparent" class="tablesorter-childRow expand-child">
                    <td colspan="
                        @if(empty($site_info)&&empty($task_info)){{ '7' }}
                        @else {{ '4' }}
                        @endif" style="padding: 0">
                        <div id="{{ 'collapse'.$activity->id }}" class="panel-collapse collapse mg-tp-px-10 col-sm-10 col-sm-offset-1">

                            <table class="table table_sky">
                                <tr>
                                    <th>Detalle de la actividad / evento:</th>
                                    <th colspan="2" width="35%">Archivos de respaldo:</th>
                                </tr>
                                <tr>
                                    <td rowspan="3" style="background-color: white">
                                        <p>{{ $activity->description }}</p>
                                        @if($activity->progress!=0&&$activity->task_id!=0)
                                            <p>{{ 'Avance: '.$activity->progress.' '.$activity->task->units }}</p>
                                        @endif
                                    </td>
                                    <td style="text-align: center;background-color: white" colspan="2">
                                        <?php $restantes='0' ?>
                                        @foreach($files as $file)
                                            @if($file->imageable_id == $activity->id)
                                                <a href="/download/{{ $file->id }}" style="text-decoration:none">
                                                    @if($file->type=="pdf")
                                                        <img src="/imagenes/pdf-icon.png" alt="PDF" />
                                                        <?php $restantes++ ?>
                                                    @elseif($file->type=="docx"||$file->type=="doc")
                                                        <img src="/imagenes/word-icon.png" alt="WORD" />
                                                        <?php $restantes++ ?>
                                                    @elseif($file->type=="xlsx"||$file->type=="xls")
                                                        <img src="/imagenes/excel-icon.png" alt="EXCEL" />
                                                        <?php $restantes++ ?>
                                                    @elseif($file->type=="jpg"||$file->type=="jpeg"||$file->type=="png")
                                                        <img src="/imagenes/image-icon.png" alt="IMAGE" />
                                                        <?php $restantes++ ?>
                                                    @endif
                                                </a>
                                            @endif
                                        @endforeach
                                        @if($restantes<5)
                                            @if($site_info)
                                                @if(($site_info->status!='Concluído'&&$site_info->status!='No asignado')||$user->priv_level==4)
                                                    <a href="/files/activity/{{ $activity->id }}">Subir archivo</a>
                                                @endif
                                            @elseif($task_info)
                                                @if(($task_info->status!='Concluído'&&$task_info->status!='No asignado')||$user->priv_level==4)
                                                    <a href="/files/activity/{{ $activity->id }}">Subir archivo</a>
                                                @endif
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th width="17%">Responsable de tarea:</th>
                                    <td style="background-color: white">
                                        @if($activity->task_id!=0)
                                            @foreach($user_names as $user_name)
                                                @if($user_name->id==$activity->task->responsible)
                                                    {{ $user_name->name }}
                                                @endif
                                            @endforeach
                                        @else
                                            {{ 'N/A' }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Evento agregado por:</th>
                                    <td style="background-color: white">
                                        @foreach($user_names as $user_name)
                                            @if($user_name->id==$activity->user_id)
                                                {{ $user_name->name }}
                                            @endif
                                        @endforeach
                                    </td>
                                </tr>
                            </table>

                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

            <table class="formal_table table_blue" id="fixed"></table>

    </div>
    <div class="col-sm-12 mg10" align="center">
        {!! $activities->render() !!}
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @if($site_info)
            @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'activities-per-site','id'=>$site_info->id))
        @elseif($task_info)
            @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'activities-per-task','id'=>$task_info->id))
        @endif
    </div>

@endsection

@section('footer')
    @parent
@endsection

@section('javascript')

    <script>
        $('#alert').delay(2000).fadeOut('slow');
        $(function(){
            $('#ordenar_tabla').tablesorter({
                cssAsc: 'headerSortUp',
                cssDesc: 'headerSortDown',
                cssNone: ''
            });
        });

        $('.collapse').on('show.bs.collapse', function () {
            $('.collapse.in').collapse('hide');
            $(this).closest('tr').prev(".accordion-toggle").find('.indicator').toggleClass("glyphicon-chevron-down glyphicon-chevron-right");

        }).on('hide.bs.collapse', function () {
            $(this).closest('tr').prev(".accordion-toggle").find('.indicator').toggleClass("glyphicon-chevron-right glyphicon-chevron-down");

        });
/*
        $('.indicator').on('click', function() {
            $(this).toggleClass('glyphicon-chevron-down glyphicon-chevron-up');
        });
*/
        var tableOffset = $("#ordenar_tabla").offset().top;
        var $header = $("#ordenar_tabla > thead");
        var $fixedHeader = $('#fixed').append($header.clone()).css({ "position":"fixed", "top":"0", "width":"98%",
            "display":"none", "border-collapse":"collapse" });

        $(window).bind("scroll", function() {
            var offset = $(this).scrollTop();

            if (offset >= tableOffset && $fixedHeader.is(":hidden")) {
                $fixedHeader.show();

                $.each($header.find('tr > th'), function(ind,val){
                    var original_width = $(val).width();
                    var original_padding = $(val).css("padding");
                    $($fixedHeader.find('tr > th')[ind])
                            .width(original_width)
                            .css("padding",original_padding);
                });
            }
            else if (offset < tableOffset) {
                $fixedHeader.hide();
            }
        });

    </script>
@endsection
