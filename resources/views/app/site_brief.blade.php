@extends('layouts.projects_structure')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/custom_table.css") }}">
    <link rel="stylesheet" href="{{ asset("app/css/progress_bar.css") }}">
    <style>
        .dropdown-menu-prim > li > a {
            width: 210px;
            /*white-space: normal; /* Set content to a second line */
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
                <a href="/site{{ $assignment_info ? '/'.$assignment_info->id : '' }}">
                    <i class="fa fa-bars fa-fw"></i> Resumen
                </a>
            </li>
            @if(($assignment_info&&$assignment_info->status!=$assignment_info->last_stat()/*'Concluído'*/&&
                $assignment_info->status!=0/*'No asignado'*/&&$user->priv_level>=1)||$user->priv_level==4)
                <li>
                    <a href="/site/{{ $assignment_info ? $assignment_info->id : 0 }}/create">
                        <i class="fa fa-plus fa-fw"></i> Nuevo Sitio
                    </a>
                </li>
            @endif
            {{--
            @if($user->priv_level==4)
                <li><a href="/delete/site"><i class="fa fa-trash-o"></i> Borrar archivo</a></li>
            @endif
            --}}
            {{--@if($assignment_info&&$sites->total()==0)--}}
                <li>
                    <a href="/import/sites/{{ $assignment_info->id }}">
                        <i class="fa fa-upload fa-fw"></i> Importar sitios
                    </a>
                </li>
            {{--@endif--}}

            <li class="dropdown-submenu">
                <a href="#" data-toggle="dropdown"><i class="fa fa-list fa-fw"></i> Lista de materiales de cliente</a>
                <ul class="dropdown-menu dropdown-menu-prim">
                    <li>
                        <a href="{{ '/client_listed_material?client='.($assignment_info ? $assignment_info->client : 'all') }}">
                            <i class="fa fa-list fa-fw"></i> Ver materiales
                        </a>
                    </li>
                    <li>
                        <a href="/import/client_listed_materials/{{ $assignment_info ? $assignment_info->id : 0 }}">
                            <i class="fa fa-upload fa-fw"></i> Importar lista de materiales
                        </a>
                    </li>
                </ul>
            </li>

            @if($assignment_info)
                @if($user->action->prj_asg_edt)
                    <li class="dropdown-submenu">
                        <a href="#" data-toggle="dropdown"><i class="fa fa-calendar fa-fw"></i> Establecer fechas globalmente</a>
                        <ul class="dropdown-menu dropdown-menu-prim">
                            <li>
                                <a href="{{ '/site/set_global_dates/'.$assignment_info->id.'?mode=set&interval=exec' }}">
                                    <i class="fa fa-arrow-right fa-fw"></i> Intervalo de fechas de ejecución
                                </a>
                            </li>
                            <li>
                                <a href="{{ '/site/set_global_dates/'.$assignment_info->id.'?mode=set&interval=asg' }}">
                                    <i class="fa fa-arrow-right fa-fw"></i> Intervalo de fechas asignado por el cliente
                                </a>
                            </li>
                            <li>
                                <a href="{{ '/site/set_global_dates/'.$assignment_info->id.'?mode=add&interval=exec' }}">
                                    <i class="fa fa-arrow-right fa-fw"></i> Agregar días (ejecución)
                                </a>
                            </li>
                            <li>
                                <a href="{{ '/site/set_global_dates/'.$assignment_info->id.'?mode=add&interval=asg' }}">
                                    <i class="fa fa-arrow-right fa-fw"></i> Agregar días (asignado)
                                </a>
                            </li>
                            <li>
                                <a href="{{ '/site/set_global_dates/'.$assignment_info->id.'?mode=sub&interval=exec' }}">
                                    <i class="fa fa-arrow-right fa-fw"></i> Restar días (ejecución)
                                </a>
                            </li>
                            <li>
                                <a href="{{ '/site/set_global_dates/'.$assignment_info->id.'?mode=sub&interval=asg' }}">
                                    <i class="fa fa-arrow-right fa-fw"></i> Restar días (asignado)
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                {{-- Obsolete due to max exec error  
                <li>
                    <a href="/excel/per-assignment-progress/{{ $assignment_info->id }}">
                        <i class="fa fa-file-excel-o fa-fw"></i> Reporte de avance general
                    </a>
                </li>
                --}}
                <li>
                    <a href="/excel/report/per-assignment-progress/{{ $assignment_info->id }}">
                        <i class="fa fa-file-excel-o fa-fw"></i> Reporte de avance general
                    </a>
                </li>
                <li>
                    <a href="/excel/load_format/tracking-report/{{ $assignment_info->id }}">
                        <i class=" fa fa-file-excel-o fa-fw"></i> Tracking report
                    </a>
                </li>
                @if($user->action->prj_vtc_rep /*$user->priv_level>=3*/)
                    <li>
                        <a href="{{ '/site/expense_report/stipend/'.$assignment_info->id }}">
                            <i class="fa fa-money fa-fw"></i> Reporte de gastos
                        </a>
                    </li>
                @endif
            @endif

            @if($user->action->prj_st_exp)
                <li class="divider"></li>
                <li>
                    <a href="/excel/sites{{ $assignment_info ? '/'.$assignment_info->id : '' }}">
                        <i class="fa fa-file-excel-o fa-fw"></i> Exportar a Excel
                    </a>
                </li>
            @endif

            @if($user->priv_level==4)
                <li><a href="{{ '/excel/order_site' }}"><i class="fa fa-file-excel-o fa-fw"></i> Exportar Pivot</a></li>
            @endif
        </ul>
    </div>
    @if($user->priv_level>=2)
        {{--
        <a href="/search/sites/{{ $assignment_info ? $assignment_info->id : '0' }}" class="btn btn-primary">
            <i class="fa fa-search"></i> Buscar
        </a>
        --}}
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchBox">
            <i class="fa fa-search"></i> Buscar
        </button>
    @endif

    <a href="{{ '/stipend_request?asg='.($assignment_info ? $assignment_info->id : 0) }}" class="btn btn-primary">
        <i class="fa fa-money"></i> Viáticos
    </a>
    {{--
    @if($assignment_info&&$assignment_info->type=='Radiobases')
        @if(($user->work_type=='Radiobases'&&$user->priv_level>=1)||
            ($user->area=='Gerencia Tecnica'&&$user->priv_level==3)||$user->priv_level==4)
            <a href="{{ '/rbs_viatic' }}" class="btn btn-primary"><i class="fa fa-arrow-circle-right"></i> Viáticos</a>
        @endif
    @endif
    --}}

    @if($assignment_info)
        <a href="/site/refresh_data/{{ $assignment_info->id }}" class="btn btn-primary">
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
        @if($assignment_info)
            @if($user->priv_level>=1)
                Asignación: <a href="/assignment/{{ $assignment_info->id }}">{{ str_limit($assignment_info->name,50) }}</a>
            @else
                {{ 'Asignación: '.str_limit($assignment_info->name,50) }}
            @endif
            &emsp;&emsp;
                {{ 'Código de cliente: '.($assignment_info->client_code ? $assignment_info->client_code : 'N/E') }}

            <div class="pull-right" align="right">
                <a href="/site/{{ $assignment_info->id }}" title="Recargar vista resumen"
                   style="color: black; text-decoration: none">
                    <i class="fa fa-list"></i> Resumen
                </a>
                &ensp;
                <a href="/site/schedule{{ '?asg_id='.$assignment_info->id.'&opt=all' }}" title="Cambiar vista a cronograma"
                    style="text-decoration: none">
                    <i class="fa fa-toggle-off"></i>
                </a>
                &ensp;
                <a href="/site/schedule{{ '?asg_id='.$assignment_info->id.'&opt=all' }}" style="color: black; text-decoration: none"
                   title="Cambiar vista a cronograma">
                    <i class="fa fa-calendar"></i> Cronograma
                </a>
            </div>
        @endif

        <p>Sitios encontrados: {{ $sites->total() }}</p>

        <table class="formal_table table_blue tablesorter" id="fixable_table">
            <thead>
            <tr>
                @if($user->priv_level==4)
                    <th>Código</th>
                @endif
                <th width="20%">Sitio</th>
                @if(empty($assignment_info))
                    <th width="18%">Asignación</th>
                    <th>Cliente</th>
                @endif
                @if($assignment_info&&$assignment_info->type=='Radiobases')
                    <th>Tipo</th>
                @endif
                <th>Estado</th>
                {{--@if(($user->area=='Gerencia Tecnica'&&$user->priv_level==2)||$user->priv_level>=3)--}}
                <th width="8%">Avance</th>
                <th width="11%" class="{sorter: 'digit'}">Tiempo restante</th>
                {{--@endif--}}
                @if($user->priv_level>=1)
                    <th>Ordenes</th>
                    <th>Acciones</th>
                @endif
                <th class="{sorter: 'digit'}">Desarrollo de sitio</th>
            </tr>
            </thead>
            <tbody>
            <?php
                /*
                $options_upgrade = array();
                $options_upgrade['Relevamiento'] = 'Cotizado';
                $options_upgrade['Cotizado'] = 'Ejecución';
                $options_upgrade['Ejecución'] = 'Revisión';
                $options_upgrade['Revisión'] = 'Cobro';
                $options_upgrade['Cobro'] = 'Concluído';

                $options_downgrade = array();
                $options_downgrade['Cobro'] = 'Revisión';
                $options_downgrade['Revisión'] = 'Ejecución';
                $options_downgrade['Ejecución'] = 'Cotizado';
                $options_downgrade['Cotizado'] = 'Relevamiento';
                */
            ?>
            @foreach ($sites as $site)
                <tr>
                    @if($user->priv_level==4)
                        <td>
                            <a href="/site/{{ $site->id }}/show">
                                {{ $site->code }}
                                {{-- 'ST-'.str_pad($site->id, 4, "0", STR_PAD_LEFT).date_format($site->created_at,'-y') --}}
                            </a>
                        </td>
                    @endif
                    <td>
                        <a href="/site/{{ $site->id }}/show">{{ $site->name }}</a>

                        @if(((($user->area=='Gerencia Tecnica'&&$user->priv_level>=1)||$user->priv_level>=3)&&
                            ($site->status!=$site->last_stat()/*'Concluído'*/&&$site->status!=0/*'No asignado'*/))||
                            $user->priv_level==4)
                            <a href="/site/{{ $site->id }}/edit" title="Editar"><i class="fa fa-pencil-square-o"></i></a>
                        @endif
                    </td>
                    @if(empty($assignment_info))
                        <td>
                            <a href="/assignment/{{ $site->assignment->id }}" title="{{ $site->assignment->name }}">
                                {{ str_limit($site->assignment->name,100) }}
                            </a>
                        </td>
                        <td>{{ $site->assignment->client }}</td>
                    @endif
                    @if($assignment_info&&$assignment_info->type=='Radiobases')
                        <td>
                            {{ $site->rbs_char ? $site->rbs_char->type_rbs.' - '.$site->rbs_char->type_station : 'N/E' }}
                        </td>
                    @endif
                    <td>
                        {{ $site->statuses($site->status) }}
                        @if($site->statuses($site->status)=='Cotización')
                            <a href="/site/stat/{{ $site->id.'?action=close' }}" class="confirm_close"
                               title="Marcar registro como: No asignado">
                                <i class="fa fa-ban pull-right"></i>
                            </a>
                        @endif

                        @if($site->status!=$site->last_stat()/*'Concluído'*/&&$site->status!=0/*'No asignado'*/)
                            <a href="/site/stat/{{ $site->id.'?action=upgrade' }}" class="confirm_status_change"
                               title="{{ 'Cambiar estado a: '.$site->statuses($site->status+1)
                               /*$options_upgrade[$site->status]*/ }}"
                               data-option="{{ $site->statuses($site->status+1)/*$options_upgrade[$site->status]*/ }}">
                                <i class="fa fa-level-up pull-right"></i> <!-- Formerly arrow-up -->
                            </a>

                            @if($site->statuses($site->status)!='Relevamiento')
                                <a href="/site/stat/{{ $site->id.'?action=downgrade' }}" class="confirm_status_change"
                                   title="{{ 'Cambiar estado a: '.$site->statuses($site->status-1)
                                   /*$options_downgrade[$site->status]*/ }}"
                                   data-option="{{ $site->statuses($site->status-1)/*$options_downgrade[$site->status]*/ }}">
                                    <i class="fa fa-level-down pull-right"></i> <!-- Formerly arrow-down -->
                                </a>
                            @endif
                        @endif

                        {{--
                        @if($site->status!='Concluído'&&$site->status!='No asignado')
                            <a href="/site/stat/{{ $site->id.'?action=upgrade' }}" class="confirm_upgrade"
                               title="
                               @if($site->status=='Relevamiento')
                                    {{ 'Cambiar estado a: Cotizado' }}
                               @elseif($site->status=='Cotizado')
                                    {{ 'Cambiar estado a: Ejecución' }}
                               @elseif($site->status=='Ejecución')
                                    {{ 'Cambiar estado a: Revisión' }}
                               @elseif($site->status=='Revisión')
                                    {{ 'Cambiar estado a: Cobro' }}
                               @elseif($site->status=='Cobro')
                                    {{ 'Cambiar estado a: Concluído' }}
                               @endif
                                       "
                            >
                                <i class="fa fa-arrow-up pull-right"></i>
                            </a>
                        @endif
                        --}}
                    </td>

                    {{--@if(($user->area=='Gerencia Tecnica'&&$user->priv_level==2)||$user->priv_level>=3)--}}
                        <td align="center">
                            <div class="progress" data-popover="true" data-html=true data-content="
                                @if($site->assignment->type=='Fibra óptica')
                                {{
                                    'Cable tendido:
                                        <i class="fa fa-question-circle"
                                        title="Debe agregar items a esta categoría para reflejar su avance"></i>
                                        <span class="pull-right">
                                        <strong>'.$site->cable_executed.'</strong> de <strong>'.
                                        $site->cable_projected.'</strong></span><br>
                                    Empalmes ejecutados:
                                        <i class="fa fa-question-circle"
                                        title="Debe agregar items a esta categoría para reflejar su avance"></i>
                                        &ensp;
                                        <span class="pull-right">
                                        <strong>'.$site->splice_executed.'</strong> de <strong>'.
                                        $site->splice_projected.'</strong></span><br>'
                                }}
                                @endif
                                {{
                                   'Avance s/cantidades:
                                    <span class="pull-right"><strong>'.number_format($site->percentage_completed,2).
                                    ' %</strong></span><br>
                                   Avance s/costos:
                                    <span class="pull-right"><strong>'.($site->assigned_price==0 ? 'n/e' :
                                       number_format(($site->executed_price/$site->assigned_price)*100,2).' %').
                                   '</strong></span><br>
                                   Última actualización:
                                    <span class="pull-right"><strong>'.date_format($site->updated_at,'d-m-Y').'</strong>
                                    </span>'
                                }}
                            ">
                                <div class="progress-bar progress-bar-success"
                                     style="{{ 'width: '.number_format($site->percentage_completed,2).'%' }}">
                                    <span>
                                        {{ number_format($site->percentage_completed,2).' %' }}
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td align="center">
                            {{--@if($site->status!='Concluído'&&$site->status!='No asignado')--}}
                            @if($site->start_line->year<1||$site->deadline->year<1
                            /*$site->start_line=='0000-00-00 00:00:00'||$site->deadline=='0000-00-00 00:00:00'*/)
                                <span class="label label-gray uniform_width" style="font-size: 12px">
                                    {{ 'No especificado' }}
                                </span>
                            @elseif($site->statuses($site->status)=='Ejecución')
                                @if($current_date->diffInDays($site->start_line,false)<=0)
                                    @if($current_date->diffInDays($site->deadline,false)<=1)
                                        <span class="label label-danger uniform_width" style="font-size: 12px">
                                            @if($current_date->diffInDays($site->deadline,false)==1)
                                                {{ '1 día' }}
                                            @elseif($current_date->diffInDays($site->deadline,false)==0)
                                                {{ 'Vence hoy' }}
                                            @elseif($current_date->diffInDays($site->deadline,false)<0)
                                                {{ abs($current_date->diffInDays($site->deadline,false)).' dia(s) vencido' }}
                                            @endif
                                        </span>
                                    @else
                                        @if($current_date->diffInDays($site->deadline,false)<=3)
                                            <span class="label label-danger uniform_width" style="font-size: 12px">
                                        @elseif($current_date->diffInDays($site->deadline,false)<=5)
                                            <span class="label label-warning uniform_width" style="font-size: 12px">
                                        @elseif($current_date->diffInDays($site->deadline,false)<=10)
                                            <span class="label label-yellow uniform_width" style="font-size: 12px">
                                        @else
                                            <span class="label label-apple uniform_width" style="font-size: 12px">
                                        @endif
                                            {{ $current_date->diffInDays($site->deadline,false).' dias' }}
                                        </span>
                                    @endif
                                @else
                                    <span class="label label-blue uniform_width" style="font-size: 12px">
                                        {{ $current_date->diffInDays($site->deadline,false).' dias' }}
                                    </span>
                                @endif
                            @else
                                <span class="label label-gray uniform_width" style="font-size: 12px">
                                    {{ $site->status==$site->last_stat()/*'Concluído'*/ ?
                                         $site->start_line->diffInDays($site->updated_at).' dias' : 'n/a' }}
                                </span>
                            @endif
                        </td>
                    {{--@endif--}}

                    @if($user->priv_level>=1)
                        <td>
                            @foreach($site->orders as $order)
                                <a href="/order/{{ $order->id }}">{{ $order->type.' - '.$order->code }}</a><br>
                            @endforeach
                            {{ $site->orders->count()==0 ? 'Sin ordenes asociadas' : '' }}
                        </td>
                        <td align="center">
                            @if($site->status!=$site->last_stat()/*'Concluído'*/&&$site->status!=0/*'No asignado'*/)
                                <a href="/files/site/{{ $site->id }}" title="Subir archivo"><i class="fa fa-upload"></i></a>
                                &ensp;
                                <a href="/join/site-to-order/{{ $site->id }}" title="Asociar orden">
                                    <i class="fa fa-link"></i>
                                </a>
                                @if($user->action->prj_st_edt /*($user->priv_level==2&&$user->work_type=='Radiobases')*/||
                                    $user->priv_level>=3)
                                    &ensp;
                                    <a href="/site/{{ $site->id }}/control" title="Cambiar parámetros de control de sitio">
                                        <i class="fa fa-cog"></i>
                                    </a>
                                @endif
                                @if($site->assignment->type=='Radiobases')
                                    &ensp;
                                    @if($site->rbs_char)
                                        <a href="/rbs_site_characteristics/{{ $site->rbs_char->id }}/edit"
                                           title="Modificar características de sitio"><i class="fa fa-sticky-note"></i></a>
                                    @else
                                        <a href="{{ '/rbs_site_characteristics/create?site_id='.$site->id }}"
                                            title="Agregar características de sitio"><i class="fa fa-sticky-note-o"></i></a>
                                    @endif
                                @endif
                            @endif
                        </td>
                    @endif
                    <td align="right" style="padding-right: 1em">
                        <a href="/task/{{ $site->id }}">
                            {{ $site->tasks->count()==1 ? '1 item' : $site->tasks->count().' items' }}
                        </a>
                        &emsp;&ensp;
                        <a href="/event/site/{{ $site->id }}" title="Eventos relacionados a este sitio">
                            {{ $site->events->count() }} <i class="fa fa-flag"></i>
                        </a>
                        &emsp;&ensp;
                        <a href="{{ '/dead_interval?st_id='.$site->id }}"
                           title="Intervalos de tiempo muerto (inactividad) de este sitio">
                            {{ $site->dead_intervals->count() }} <i class="fa fa-hourglass-half"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

    </div>
    <div class="col-sm-12 mg10" align="center">
        {!! $sites->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="formal_table table_blue" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @if($assignment_info)
            @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'sites','id'=>$assignment_info->id))
        @else
            @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'sites','id'=>0))
        @endif
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
