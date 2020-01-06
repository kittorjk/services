<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 30/03/2017
 * Time: 02:59 PM
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
            <i class="fa fa-cogs"></i> Contratos <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li><a href="{{ '/project' }}"><i class="fa fa-refresh fa-fw"></i> Recargar página </a></li>
            @if($user->priv_level>=1)
                <li><a href="{{ '/project/create' }}"><i class="fa fa-plus fa-fw"></i> Agregar contrato </a></li>
            @endif
            @if($user->priv_level>=3)
                <li><a href="{{ '/project?mode=rb' }}"><i class="fa fa-list fa-fw"></i> Ver contratos de Radiobases </a></li>
                <li><a href="{{ '/project?mode=fo' }}"><i class="fa fa-list fa-fw"></i> Ver contratos de Fibra óptica </a></li>
                <li><a href="{{ '/project?mode=arch' }}"><i class="fa fa-list fa-fw"></i> Ver contratos finalizados </a></li>
            @endif
            @if($user->action->prj_vtc_rep)
                <li><a href="{{ '/project/expense_report/stipend' }}"><i class="fa fa-money fa-fw"></i> Reporte de gastos</a></li>
            @endif
            @if($user->action->prj_exp)
                <li class="divider"></li>
                <li><a href="{{ '/excel/projects' }}"><i class="fa fa-file-excel-o fa-fw"></i> Exportar a Excel </a></li>
            @endif
        </ul>
    </div>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchBox">
        <i class="fa fa-search"></i> Buscar
    </button>
@endsection

@section('content')

    <?php
        /*
        $ending = 0;
        $ended = 0;

        foreach($projects as $project){
            if($project->award=='Licitación'&&$project->applied==0&&$project->status=='Activo'&&
                ($user->area=='Gerencia General'||$user->priv_level==4)){
                if(Carbon\Carbon::now()->diffInDays($project->application_deadline,false)<=5&&
                    Carbon\Carbon::now()->diffInDays($project->application_deadline,false)>=0){
                    $ending++;
                }
                elseif((Carbon\Carbon::now()->diffInDays($project->application_deadline,false)<0)){
                    $ended++;
                }
            }
        }
        */
    ?>

    @foreach($projects as $project)
        @foreach($project->guarantees as $guarantee)
            @if($guarantee->closed==0&&($user->area=='Gerencia General'||$user->priv_level==4))
                @if(Carbon\Carbon::now()->diffInDays($guarantee->expiration_date,false)<=5)
                    <div class="col-sm-12 mg10">
                        <div class="alert alert-danger" align="center">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            <i class="fa fa-warning fa-2x pull-left"></i>
                            <a href="/project/{{ $project->id }}" style="color: darkred">
                                {{ 'La poliza de garantia del proyecto '.$project->code.
                                    (Carbon\Carbon::now()->diffInDays($guarantee->expiration_date,false)<0 ?
                                         ' ha expirado' : ' expira pronto' ) }}
                            </a>
                        </div>
                    </div>
                @endif
            @endif
        @endforeach

        {{--@if($project->application_deadline->toDateTimeString()>0&&$project->applied==0&&$project->status=='Activo'&&
            ($user->area=='Gerencia General'||$user->priv_level==4))--}}
        {{--
        @if($project->award=='Licitación'&&$project->applied==0&&$project->status=='Activo'&&
            ($user->area=='Gerencia General'||$user->priv_level==4))
            @if(Carbon\Carbon::now()->diffInDays($project->application_deadline,false)<=5)
                <div class="col-sm-12 mg10">
                    <div class="alert alert-danger" align="center">
                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                        <a href="/project/{{ $project->id }}" style="color: darkred">
                            {{ 'El plazo para presentación a la licitación '.$project->code.
                                (Carbon\Carbon::now()->diffInDays($project->application_deadline,false)<0 ?
                                     ' ha vencido' : ' vence pronto' ) }}
                        </a>
                    </div>
                </div>
            @endif
        @endif
        --}}
    @endforeach

    @if($projects->ending>0)
        <div class="col-sm-12 mg10">
            <div class="alert alert-warning" align="center">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <i class="fa fa-warning fa-2x pull-left"></i>
                {{ 'El plazo de vigencia de '.($projects->ending==1 ? '1 contrato' :
                    $projects->ending.' contratos').' vence pronto' }}
            </div>
        </div>
    @endif

    {{--
    @if($ending>0)
        <div class="col-sm-12 mg10">
            <div class="alert alert-warning" align="center">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <i class="fa fa-warning fa-2x pull-left"></i>
                {{ 'El plazo para presentación a '.$ending.($ending==1 ? ' licitación' : ' licitaciones').' vence pronto' }}
            </div>
        </div>
    @endif

    @if($ended>0)
        <div class="col-sm-12 mg10">
            <div class="alert alert-danger" align="center">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <i class="fa fa-warning fa-2x pull-left"></i>
                {{ 'El plazo para presentación a '.$ended.($ended==1 ? ' licitación' : ' licitaciones').' ha vencido' }}
            </div>
        </div>
    @endif
    --}}

    <div class="col-sm-12 mg10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10">

        <p>Registros encontrados: {{ $projects->total() }}</p>

        <table class="formal_table table_blue tablesorter" id="fixable_table">
            <thead>
            <tr>
                <th>Código</th>
                <th width="20%">Contrato</th>
                <th>Cliente</th>
                <th>Área de trabajo</th>
                <th>Tipo de adjudicación</th>
                <th>Estado</th>
                <th>Asignaciones</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($projects as $project)
                <tr>
                    <td>
                        <a href="/project/{{ $project->id }}" title="Ver información de este contrato">
                            {{ $project->code }}
                            {{-- 'PRJ-'.str_pad($project->id, 3, "0", STR_PAD_LEFT).date_format($project->created_at,'y') --}}
                        </a>
                    </td>
                    <td>
                        {{ $project->name }}

                        @if((($user->id==$project->user_id||$user->action->prj_edt /*$user->priv_level==3*/)&&
                            $project->status=='Activo')||$user->priv_level==4)
                            <a href="/project/{{ $project->id }}/edit" title="Modificar información de contrato">
                                <i class="fa fa-pencil-square-o"></i>
                            </a>
                        @endif
                    </td>
                    <td>{{ $project->client }}</td>
                    <td>{{ $project->type }}</td>
                    <td>{{ $project->award }}</td>
                    <td>
                        <span {{--style="{{ $project->award=='Licitación'&&$project->applied==0&&$project->status=='Activo'&&
                            ($user->area=='Gerencia General'||$user->priv_level==4) ?
                            (Carbon\Carbon::now()->diffInDays($project->application_deadline,false)<=5&&
                            Carbon\Carbon::now()->diffInDays($project->application_deadline,false)>=0 ?
                            'color: darkorange' : (Carbon\Carbon::now()->diffInDays($project->application_deadline,false)<0 ?
                            'color: darkred' : '')) : '' }}"--}}>

                            {{ $project->status }}

                            {{-- $project->status=='No asignado' ? $project->status :
                            (Carbon\Carbon::now()->hour(0)->minute(0)->second(0)->diffInDays($project->valid_to)>0 ?
                             'En vigencia' : 'Concluído') --}}
                        </span>

                        @if($project->status!='No asignado'&&$project->assignments->count()==0)
                            <a href="/project/close/{{ $project->id }}" title="Marcar contrato como: No asignado"
                               class="confirm_close">
                                <i class="fa fa-ban pull-right"></i>
                            </a>
                        @endif
                        {{--
                        @if($project->award=='Licitación'&&$project->applied==0&&$project->status!='No asignado')
                            <a href="/project/applied/{{ $project->id }}" title="Registrar envió de documentación"
                               class="confirm_applied">
                                <i class="fa fa-send pull-right"></i>
                            </a>
                        @endif
                        --}}
                        @if(/*$project->applied==1&&*/$project->status=='Activo'&&$project->assignments->count()==0)
                            <a href="/project/add_assignment/{{ $project->id }}" title="Crear asignación para este contrato"
                               class="confirm_assignment">
                                <i class="fa fa-plus pull-right"></i>
                            </a>
                        @endif

                        {{--@if($project->award=='Licitación'&&$project->applied==0&&$project->status=='Activo'&&
                            ($user->area=='Gerencia General'||$user->priv_level==4))--}}
                            @if(Carbon\Carbon::now()->diffInDays($project->valid_to,false)<=5&&
                                Carbon\Carbon::now()->diffInDays($project->valid_to,false)>=0
                                /*Carbon\Carbon::now()->diffInDays($project->application_deadline,false)<=5&&
                                Carbon\Carbon::now()->diffInDays($project->application_deadline,false)>=0*/)
                                <a href="/project/{{ $project->id }}" style="color: darkorange">
                                    <i class="fa fa-exclamation-circle pull-right"
                                       title="{{ 'Este contrato vence pronto' }}">
                                    </i>
                                </a>
                            {{--
                            @elseif((Carbon\Carbon::now()->diffInDays($project->application_deadline,false)<0))
                                <a href="/project/{{ $project->id }}" style="color: darkred">
                                    <i class="fa fa-exclamation-circle pull-right"
                                       title="{{ 'Plazo de presentación vencido' }}">
                                    </i>
                                </a>
                                --}}
                            @endif
                        {{--@endif--}}
                    </td>
                    <td>
                        @if($project->status!='No asignado')
                            <a href="{{ '/assignment?prj='.$project->id }}" title="Ver asignaciones de este proyecto">
                                {{ $project->assignments->count()==1 ? '1 asignación' :
                                 $project->assignments->count().' asignaciones' }}
                            </a>
                        @else
                            {{ '-' }}
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

    </div>

    <div class="col-sm-12 mg10" align="center">
        {!! $projects->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="formal_table table_blue" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'projects','id'=>0))
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

        /*
        $('.confirm_applied').on('click', function () {
            return confirm('Está seguro de que desea registrar el envío de documentación para aplicar a la ' +
                    'licitación indicada?');
        });
        */

        $('.confirm_assignment').on('click', function () {
            return confirm('Está seguro de que desea crear una asignación de trabajo dentro de este contrato?');
        });
    </script>
@endsection
