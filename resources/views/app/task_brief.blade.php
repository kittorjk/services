@extends('layouts.projects_structure')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/custom_table.css") }}">
    <link rel="stylesheet" href="{{ asset("app/css/progress_bar.css") }}">
    <style>
        .dropdown-menu-prim > li > a {
            width: 210px;
            /*white-space: normal; /* Set code to a second line */
        }
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    @if($site_info)
        <a href="/site/{{ $site_info->assignment_id }}" class="btn btn-primary">
            <i class="fa fa-arrow-circle-up"></i> Sitios
        </a>
    @endif

    @include('app.project_navigation_button', array('user'=>$user))

    <div class="btn-group">
        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
            <i class="fa fa-cogs"></i> Items <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li>
                <a href="/task/{{ $site_info ? $site_info->id : '' }}"><i class="fa fa-bars fa-fw"></i> Resumen</a>
            </li>
            @if(($site_info&&$site_info->status!=$site_info->last_stat()/*'Concluído'*/&&$site_info->status!=0/*'No asignado'*/&&
                $user->priv_level>=1)||$user->priv_level==4)
                <li>
                    <a href="/task/{{ $site_info->id }}/add" title="Agregar items desde la lista proporcionada por el cliente">
                        <i class="fa fa-plus fa-fw"></i> Agregar item(s)
                    </a>
                </li>
                <li>
                    <a href="/task/{{ $site_info->id }}/create"><i class="fa fa-plus fa-fw"></i> Crear item adicional</a>
                </li>
                <li class="dropdown-submenu">
                    <a href="#" data-toggle="dropdown"><i class="fa fa-upload fa-fw"></i> Importar</a>
                    <ul class="dropdown-menu dropdown-menu-prim">
                        <li>
                            <a href="/import/tasks/{{ $site_info->id }}" title="Importar items desde excel a este sitio">
                                <i class="fa fa-upload fa-fw"></i> Importar items
                            </a>
                        </li>
                        {{-- Obsolete
                        <li>
                            <a href="/import/tasks-from-oc/{{ $site_info->id }}" title="Importar items desde una OC a este sitio">
                                <i class="fa fa-upload"></i> Importar de OC
                            </a>
                        </li>
                        --}}
                        <li>
                            <a href="/import/items/{{ $site_info->id }}" title="Cargar una nueva categoría de items de proyecto">
                                <i class="fa fa-upload fa-fw"></i> Cargar items
                            </a>
                        </li>
                    </ul>
                </li>
                @if($user->action->prj_acc_cat)
                    <li>
                        <a href="{{ '/item_category' }}"><i class="fa fa-list fa-fw"></i> Categorías</a>
                    </li>
                @endif
                <li class="dropdown-submenu">
                    <a href="#" data-toggle="dropdown">
                        <i class="fa fa-file-excel-o fa-fw"></i> Planilla de cantidades
                    </a>
                    <ul class="dropdown-menu dropdown-menu-prim">
                        {{-- No longer in use
                        <li>
                            <a href="/excel/tasks_qty/{{'rural-'.$site_info->id }}">
                                <i class="fa fa-download"></i> SP Rural 2017
                            </a>
                        </li>
                        <li>
                            <a href="/excel/tasks_qty/{{'urban-'.$site_info->id }}">
                                <i class="fa fa-download"></i> SP Urbano 2017
                            </a>
                        </li>
                        --}}
                        <li>
                            <a href="/excel/tasks_qty/{{'raw-'.$site_info->id }}"
                               title="Exportar a excel cantidades ejecutadas de  los items de éste sitio">
                                <i class="fa fa-download fa-fw"></i> Lista actual
                            </a>
                        </li>
                    </ul>
                </li>
                @if($user->action->prj_st_clr /*$user->priv_level==4*/)
                    <li>
                        <a href="/site/clear_all/{{ $site_info->id }}">
                            <i class="fa fa-trash fa-fw"></i> Eliminar items de este sitio
                        </a>
                    </li>
                @endif
            @endif
            {{--
            @if($site_info&&$tasks->total()==0)
                <!-- Import and load options (only if it is previously empty) -->
            @endif
            @if($user->priv_level==4)
                <li><a href="/delete/task"><i class="fa fa-trash-o"></i> Borrar archivo</a></li>
            @endif
            --}}

            @if($user->action->prj_tk_exp)
                <li class="divider"></li>
                <li class="dropdown-submenu">
                    <a href="#" data-toggle="dropdown"><i class="fa fa-file-excel-o fa-fw"></i> Exportar a Excel</a>
                    <ul class="dropdown-menu dropdown-menu-prim">
                        @if($site_info)
                            <li>
                                <a href="/excel/per-site-progress/{{ $site_info->id }}">
                                    <i class="fa fa-file-excel-o fa-fw"></i> Reporte de avance de sitio
                                </a>
                            </li>
                        @endif
                        @if($user->priv_level>=3)
                            <li>
                                <a href="/excel/tasks/{{ $site_info->id }}">
                                    <i class="fa fa-file-excel-o fa-fw"></i> Items de este sitio
                                </a>
                            </li>
                        @endif
                        @if($user->priv_level==4)
                            <li>
                                <a href="{{ '/excel/items' }}">
                                    <i class="fa fa-file-excel-o fa-fw"></i> Lista de items (DB)
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
        </ul>
    </div>
    @if($user->priv_level>=2)
        {{--
            <a href="/search/tasks/{{ $site_info ? $site_info->id : '0' }}" class="btn btn-primary">
                <i class="fa fa-search"></i> Buscar
            </a>
        --}}
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchBox">
            <i class="fa fa-search"></i> Buscar
        </button>
    @endif
    <a href="/task/refresh_data/{{ $site_info ? $site_info->id : 0 }}" class="btn btn-primary">
        <i class="fa fa-refresh"></i> Actualizar
    </a>
@endsection

@section('content')
    <div class="col-sm-12 mg10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10">
        <p>
            <a href="{{ '/assignment' }}" title="Ir a resumen de asignaciones">Asignaciones</a>
            {{ ' > ' }}
            <a href="/site/{{ $site_info ? $site_info->assignment_id : '' }}" title="Ir a listado de sitios">Sitios</a>
            {{ ' > Items' }}
        </p>

        @if($site_info)
            Asignación: <a href="/assignment/{{ $site_info->assignment->id }}">
                {{ str_limit($site_info->assignment->name,50) }}</a>
            &emsp;&emsp;
            {{ 'Código de cliente: '.($site_info->assignment->client_code ? $site_info->assignment->client_code : 'N/E') }}
            @if($site_info->assignment->project)
                &emsp;&emsp;
                {{ 'Categoría: ' }}
                {{ $site_info->assignment->project->item_categories()->count()==0 ?
                    'Sin categorías asignadas' : '' }}
                {!! $site_info->assignment->project->item_categories()->count()==1 ?
                    '<em title="'.$site_info->assignment->project->item_categories()->first()->name.'">'.
                    str_limit($site_info->assignment->project->item_categories()->first()->name,50).'</em>' : '' !!}
                @if($site_info->assignment->project->item_categories()->count()>1)
                    <em title="
                            @foreach($site_info->assignment->project->item_categories as $category)
                                {!! $category->name.'<br>' !!}
                            @endforeach
                            ">{{ 'Varias categorías de items' }}</em>
                @endif
            @endif
            <br>
            Sitio: <a href="/site/{{ $site_info->id }}/show">{{ str_limit($site_info->name,50) }}</a>

            <a href="/site/calendar/{{ $site_info->id }}" class="pull-right">
                <i class="fa fa-calendar"></i> Cambiar a vista calendario
            </a>
        @endif
        <p>{{ $tasks->total()==1 ? 'Se encontró 1 item' : 'Se encontraron '.$tasks->total().' items' }}</p>

        <table class="formal_table table_blue tablesorter" id="fixable_table">
            <thead>
            <tr>
                <th>Código</th>
                <th width="25%">Item</th>
                <th>Unidades</th>
                <th width="8%">Proyectado</th>
                <th width="8%">Ejecutado</th>
                {{--@if(($user->area=='Gerencia Tecnica'&&$user->priv_level==2)||$user->priv_level>=3)--}}
                    <th>Estado</th>
                    <th width="8%" class="{sorter: 'digit'}">Avance</th>
                    <th width="10%" class="{sorter: 'digit'}">Tiempo restante</th>
                {{--@endif--}}
                <th>Desarrollo</th>
            </tr>
            </thead>
            <tbody>

            <?php
                /*
                $options_upgrade = array();
                $options_upgrade['En espera'] = 'Ejecución';
                $options_upgrade['Ejecución'] = 'Revisión';
                $options_upgrade['Revisión'] = 'Concluído';

                $options_downgrade = array();
                $options_downgrade['Concluído'] = 'Revisión';
                $options_downgrade['Revisión'] = 'Ejecución';
                $options_downgrade['Ejecución'] = 'En espera';
                */
            ?>

            @foreach ($tasks as $task)
                <tr {!! $task->additional==1 ? 'style="background-color: lightgray" title="Item adicional"' : '' !!}>
                    <td>
                        @if($user->priv_level>=1||$user->id==$task->responsible)
                            <a href="/task/{{ $task->id }}/show" title="Ver información de item">
                                {{ $task->item&&$task->item->client_code!='' ? $task->item->client_code :
                                    ($task->item&&$task->item->number!='' ? $task->item->number : $task->code) }}
                            </a>
                        @else
                            {{ $task->item&&$task->item->client_code!='' ? $task->item->client_code :
                                ($task->item&&$task->item->number!='' ? $task->item->number : $task->code) }}
                            {{-- 'TK-'.str_pad($task->id, 4, "0", STR_PAD_LEFT).'0'.$task->number.
                                date_format($task->created_at,'-y') --}}
                        @endif
                    </td>
                    <td>
                        {{ $task->name }}

                        @if(/*(($user->area=='Gerencia Tecnica'&&$user->priv_level>=1)||$user->priv_level>=3)*/
                            ($user->action->prj_tk_edt&&
                            $task->status!=$task->last_stat()/*'Concluído'*/&&$task->status!=0/*'No asignado'*/)||
                            $user->priv_level==4)

                            <a href="/task/{{ $task->id }}/edit" title="Modificar información de item">
                                <i class="fa fa-pencil-square-o"></i>
                            </a>

                            @if($task->summary_category)
                                <a href="{{ '/task_category/'.$task->summary_category->id.'/edit' }}"
                                    title="Modificar la categoría de este item en el resumen de avance">
                                    <i class="fa fa-sticky-note"></i>
                                </a>
                            @else
                                <a href="{{ '/task_category/create?id='.$task->id }}" title="Agregar este item al resumen de avance">
                                    <i class="fa fa-sticky-note-o"></i>
                                </a>
                            @endif
                        @endif
                    </td>
                    <td>{{ $task->units }}</td>
                    <td align="center">{{ $task->total_expected }}</td>
                    <td align="center">{{ $task->progress }}</td>

                    {{--@if(($user->area=='Gerencia Tecnica'&&$user->priv_level==2)||$user->priv_level>=3)--}}
                        <td>
                            {{ $task->statuses($task->status) }}
                            @if($task->statuses($task->status)=='Relevamiento')
                                <a href="/task/stat/{{ $task->id.'?action=close' }}" class="confirm_close"
                                   title="Marcar registro como: No asignado">
                                    <i class="fa fa-ban pull-right"></i>
                                </a>
                            @endif

                            @if($task->status!=0/*'No asignado'*/)
                                @if($task->status!=$task->last_stat()/*'Concluído'*/)
                                    <a href="/task/stat/{{ $task->id.'?action=upgrade' }}" class="confirm_status_change"
                                       title="{{ 'Cambiar estado a: '.$task->statuses($task->status+1)
                                       /*$options_upgrade[$task->status]*/ }}"
                                       data-option="{{ $task->statuses($task->status+1)/*$options_upgrade[$task->status]*/ }}">
                                        <i class="fa fa-level-up pull-right"></i> <!-- Formerly arrow-up -->
                                    </a>
                                @endif

                                @if($task->statuses($task->status)!='Relevamiento')
                                    <a href="/task/stat/{{ $task->id.'?action=downgrade' }}" class="confirm_status_change"
                                       title="{{ 'Cambiar estado a: '.$task->statuses($task->status-1)
                                       /*$options_downgrade[$task->status]*/ }}"
                                       data-option="{{ $task->statuses($task->status-1)/*$options_downgrade[$task->status]*/ }}">
                                        <i class="fa fa-level-down pull-right"></i> <!-- Formerly arrow-down -->
                                    </a>
                                @endif
                            @endif

                            {{--
                            @if($task->status!='Concluído'&&$task->status!='No asignado')
                                <a href="/task/stat/{{ $task->id.'?action=upgrade' }}" class="pull-right confirm_upgrade"
                                   title="
                                @if($task->status=='En espera')
                                   {{ 'Cambiar estado a: Ejecución' }}
                                @elseif($task->status=='Ejecución')
                                   {{ 'Cambiar estado a: Revisión' }}
                                @elseif($task->status=='Revisión')
                                   {{ 'Cambiar estado a: Concluído' }}
                                @endif
                                           ">
                                    <i class="fa fa-arrow-up"></i>
                                </a>
                            @endif
                            --}}
                        </td>
                        <td align="center">
                            <div class="progress">
                                <div class="progress-bar progress-bar-success"
                                     style="{{ 'width: '.number_format(($task->progress/$task->total_expected)*100,2,'.','').'%' }}">
                                    <span>{{ number_format(($task->progress/$task->total_expected)*100,2).' %' }}</span>
                                </div>
                            </div>
                        </td>
                        <td align="center">
                            {{--@if($task->status!='Concluído'&&$task->status!='No asignado')--}}
                            @if($task->start_date->year<1||$task->end_date->year<1
                            /*$task->start_date=='0000-00-00 00:00:00'||$task->end_date=='0000-00-00 00:00:00'*/)
                                <span class="label label-gray uniform_width" style="font-size: 12px">
                                    {{ 'No especificado' }}
                                </span>
                            @elseif($task->statuses($task->status)=='Ejecución')
                                @if($current_date->diffInDays($task->start_date,false)<=0)
                                    @if($current_date->diffInDays($task->end_date,false)<=1)
                                        <span class="label label-danger uniform_width" style="font-size: 12px">
                                        @if($current_date->diffInDays($task->end_date,false)==1)
                                            {{ $current_date->diffInDays($task->end_date,false).' dia' }}
                                        @elseif($current_date->diffInDays($task->end_date,false)==0)
                                            {{ 'Vence hoy' }}
                                        @elseif($current_date->diffInDays($task->end_date,false)<0)
                                            {{ abs($current_date->diffInDays($task->end_date,false)).' dia(s) vencido' }}
                                        @endif
                                        </span>
                                    @else
                                        @if($current_date->diffInDays($task->end_date,false)<=3)
                                            <span class="label label-danger uniform_width" style="font-size: 12px">
                                        @elseif($current_date->diffInDays($task->end_date,false)<=5)
                                            <span class="label label-warning uniform_width" style="font-size: 12px">
                                        @elseif($current_date->diffInDays($task->end_date,false)<=10)
                                            <span class="label label-yellow uniform_width" style="font-size: 12px">
                                        @else
                                            <span class="label label-apple uniform_width" style="font-size: 12px">
                                        @endif
                                            {{ $current_date->diffInDays($task->end_date,false).' dias' }}
                                        </span>
                                    @endif
                                @else
                                    <span class="label label-blue uniform_width" style="font-size: 12px">
                                        {{ $current_date->diffInDays($task->end_date,false).' dias' }}
                                    </span>
                                @endif
                            @else
                                <span class="label label-gray uniform_width" style="font-size: 12px"
                                      title="Tiempo transcurridodesde el inicio del proyecto">
                                    {{ $task->start_date->diffInDays($task->updated_at).' dias' }}
                                </span>
                            @endif
                        </td>
                    {{--@endif--}}
                    <td align="center"
                        @if($task->statuses($task->status)=='Relevamiento')
                            title="Cambie el estado del item para poder agregar avances"
                        @endif
                    >
                        @if((($user->priv_level>=1||$user->id==$task->responsible)&&
                            $task->statuses($task->status)!='En espera')||$user->priv_level==4)
                            <a href="/activity/{{ $task->id }}">
                                {{ $task->activities->count()==1 ? '1 actividad' : $task->activities->count().' actividades' }}
                            </a>
                        @else
                            {{ $task->activities->count()==1 ? '1 actividad' : $task->activities->count().' actividades' }}
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

    </div>
    <div class="col-sm-12 mg10" align="center">
        {!! $tasks->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="formal_table table_blue" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @if($site_info)
            @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'tasks','id'=>$site_info->id))
        @else
            @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'tasks','id'=>0))
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
            return confirm('Está seguro de que desea marcar este registro como: No asignado?');
        });

        $('.confirm_status_change').on('click', function () {
            return confirm('Está seguro de que desea cambiar el estado de este registro a ' + $(this).data('option') + '?');
        });
    </script>
@endsection
