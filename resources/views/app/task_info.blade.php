@extends('layouts.projects_structure')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/info_tabs.css") }}">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    @if($task->site)
        <a href="/site/{{ $task->site->assignment_id }}" class="btn btn-primary">
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
                <a href="/task/{{ $task->site ? $task->site->id : '' }}"><i class="fa fa-bars fa-fw"></i> Resumen</a>
            </li>
            @if(($task->site && $task->site->status != $task->site->last_stat() /*'Concluído'*/ && $task->site->status != 0 /*'No asignado'*/ &&
                $user->priv_level >= 1) || $user->priv_level == 4)
                <li>
                    <a href="/task/{{ $task->site->id }}/add" title="Agregar items desde la lista proporcionada por el cliente">
                        <i class="fa fa-plus fa-fw"></i> Agregar item(s)
                    </a>
                </li>
                <li>
                    <a href="/task/{{ $task->site->id }}/create"><i class="fa fa-plus fa-fw"></i> Crear item adicional</a>
                </li>
                <li class="dropdown-submenu">
                    <a href="#" data-toggle="dropdown"><i class="fa fa-upload fa-fw"></i> Importar</a>
                    <ul class="dropdown-menu dropdown-menu-prim">
                        <li>
                            <a href="/import/items/{{ $task->site->id }}" title="Cargar una nueva categoría de items de proyecto">
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
                        <li>
                            <a href="/excel/tasks_qty/{{'raw-'.$task->site->id }}"
                               title="Exportar a excel cantidades ejecutadas de  los items de éste sitio">
                                <i class="fa fa-download fa-fw"></i> Lista actual
                            </a>
                        </li>
                    </ul>
                </li>
            @endif
        </ul>
    </div>
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

        <div class="panel panel-sky">
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    <div class="pull-left">
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#details" data-toggle="tab"> Información de item</a></li>
                            @if($user->action->prj_vw_eco
                                /*(($user->area=='Gerencia General'||$user->area=='Gerencia Administrativa')&&
                                $user->priv_level==2)||$user->priv_level>=3*/)
                                <li><a href="#payments" data-toggle="tab"> Datos económicos</a></li>
                            @endif
                        </ul>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <!--<div class="panel-title">Detalle de tarea</div>-->
            </div>
            <div class="panel-body">

                <div class="tab-content">

                    <div class="tab-pane fade in active" id="details">

                        <div class="col-lg-5 mg20">
                            <a href="#" onclick="history.back();" class="btn btn-warning">
                                <i class="fa fa-arrow-circle-left"></i> Atrás
                            </a>
                            <a href="/task/{{ $task->site_id }}" class="btn btn-warning">
                                <i class="fa fa-arrow-circle-up"></i> Items
                            </a>
                        </div>

                        {{--
                        <div class="col-lg-7" align="right">
                            @if($user->area=='Gerencia Tecnica'&&$user->priv_level==2||$user->priv_level>=3)
                                <a href="/excel/task/{{ $task->id }}" class="btn btn-success">
                                    <i class="fa fa-file-excel-o"></i> Exportar a Excel
                                </a>
                            @endif
                            {{--
                            @if((($user->area=='Gerencia General'||$user->area=='Gerencia Administrativa')&&$user->priv_level==2)
                                ||$user->priv_level>=3)
                                    <a href="/task_fnc/{{ $task->id }}" class="btn btn-success">
                                        <i class="fa fa-money"></i> Resumen económico
                                    </a>
                            @endif

                        </div>
                        --}}

                        @include('app.session_flashed_messages', array('opt' => 1))

                        <div class="col-sm-12 mg10 mg-tp-px-10">
                            <table class="table table-striped table-hover table-bordered">
                                <tbody>
                                <tr>
                                    <th width="28%">Código:</th>
                                    <td colspan="3">{{ $task->code }}</td>
                                </tr>
                                @if($task->item&&$task->item->client_code!='')
                                    <tr>
                                        <th>Código de cliente</th>
                                        <td colspan="3">{{ $task->item->client_code }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <th>Item:</th>
                                    <td colspan="3">{{ $task->name }}</td>
                                </tr>
                                <tr>
                                    <th>Sitio:</th>
                                    <td colspan="3">
                                        @if($user->priv_level>=1)
                                            <a href="/site/{{ $task->site->id }}/show">{{ $task->site->name }}</a>
                                        @else
                                            {{ $task->site->name }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Asignación:</th>
                                    <td colspan="3">
                                        @if($user->priv_level>=1)
                                            <a href="/assignment/{{ $task->site->assignment->id }}">
                                                {{ $task->site->assignment->name }}
                                            </a>
                                        @else
                                            {{ $task->site->assignment->name }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Cliente:</th>
                                    <td>{{ $task->site->assignment->client }}</td>
                                    <th>Estado:</th>
                                    <td>{{ $task->statuses($task->status) }}</td>
                                </tr>
                                <tr><td colspan="4"></td></tr>

                                @if($task->description<>''||($task->item&&$task->item->detail!=''))
                                    <tr>
                                        <th colspan="4">Descripción de la tarea:</th>
                                    </tr>
                                    <tr>
                                        <td colspan="4">{{ $task->description ?: ($task->item ? $task->item->detail : '') }}</td>
                                    </tr>
                                    <tr><td colspan="4"></td></tr>
                                @endif

                                <tr>
                                    <th>Fecha de Inicio:</th>
                                    <td>
                                        {{ $task->start_date!='0000-00-00 00:00:00' ?
                                            date_format(new \DateTime($task->start_date), 'd-m-Y') : 'N/E' }}
                                    </td>
                                    <th>Fecha de Fin:</th>
                                    <td>
                                        {{ $task->end_date!='0000-00-00 00:00:00' ?
                                            date_format(new \DateTime($task->end_date), 'd-m-Y') : 'N/E' }}
                                    </td>
                                </tr>
                                <tr><td colspan="4"></td></tr>

                                <tr>
                                    <th>Responsable</th>
                                    <td colspan="3">
                                        {{ $task->responsible ? $task->person_responsible->name : 'Sin asignar' }}
                                    </td>
                                </tr>
                                <tr><td colspan="4"></td></tr>

                                <tr>
                                    <th colspan="2">Cantidad proyectada</th>
                                    <td colspan="2">{{ $task->total_expected.' ['.$task->units.']' }}</td>
                                </tr>
                                <tr>
                                    <th colspan="2">Cantidad completada</th>
                                    <td colspan="2">{{ $task->progress.' ['.$task->units.']' }}</td>
                                </tr>
                                <tr>
                                    <th colspan="2">% de avance</th>
                                    <td colspan="2">
                                        {{ number_format(($task->progress/$task->total_expected)*100,2).' %' }}
                                    </td>
                                </tr>
                                <tr><td colspan="4"></td></tr>

                                {{--@if($user->area=='Gerencia Tecnica'||$user->priv_level>=3)--}}
                                    <tr>
                                        <th colspan="4">Desarrollo del item:</th>
                                    </tr>
                                    <tr>
                                        <td colspan="4" style="text-align:center">
                                            <a href="/activity/{{ $task->id }}">{{ 'Ver actividades' }}</a>
                                            @if(($user->area=='Gerencia Tecnica'&&$user->priv_level>=1)||$user->priv_level>=3)
                                            &emsp;{{ ' | ' }}&emsp;
                                            <a href="/event/task/{{ $task->id }}">{{ 'Ver eventos' }}</a>
                                            @endif
                                        </td>
                                    </tr>
                                {{--@endif--}}

                                <tr><td colspan="4"></td></tr>
                                <tr>
                                    <th colspan="2">Registro creado por</th>
                                    <td colspan="2">{{ $task->user ? $task->user->name : 'N/E' }}</td>
                                </tr>

                                </tbody>
                            </table>
                        </div>

                        @if((/*$user->priv_level>=2*/$user->action->prj_tk_edt&&$task->status<>$task->last_stat()/*'Concluído'*/&&
                            $task->status<>0/*'No asignado'*/)||$user->priv_level==4)
                            <div class="col-sm-12 mg10" align="center">
                                <a href="/task/{{ $task->id }}/edit" class="btn btn-success">
                                    <i class="fa fa-pencil-square-o"></i> Modificar item
                                </a>
                            </div>
                        @endif

                    </div>

                    @if($user->action->prj_vw_eco
                        /*(($user->area=='Gerencia General'||$user->area=='Gerencia Administrativa')&&
                        $user->priv_level==2)||$user->priv_level>=3*/)
                        <div class="tab-pane fade" id="payments">
                            @include('app.task_financial_details')
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

@endsection

@section('footer')
@endsection

@section('javascript')
    <script src="{{ asset('app/js/set_current_url.js') }}"></script> {{-- For recording current url --}}
    <script>
        $('#alert').delay(2000).fadeOut('slow');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
@endsection
