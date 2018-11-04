<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 30/03/2017
 * Time: 04:04 PM
 */
?>

@extends('layouts.projects_structure')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/info_tabs.css") }}">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    @include('app.project_navigation_button', array('user'=>$user))
    <div class="btn-group">
        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
            <i class="fa fa-cogs"></i> Contratos <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li><a href="{{ '/project' }}"><i class="fa fa-refresh fa-fw"></i> Cargar tabla de contratos </a></li>
            @if($user->priv_level>=1)
                <li><a href="{{ '/project/create' }}"><i class="fa fa-plus fa-fw"></i> Agregar contrato </a></li>
            @endif
            @if($user->action->prj_vtc_rep)
                <li><a href="{{ '/project/expense_report/stipend' }}"><i class="fa fa-money fa-fw"></i> Reporte de gastos</a></li>
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
                            <li class="active"><a href="#details" data-toggle="tab"> Información general</a></li>
                            <li><a href="#documents" data-toggle="tab"> Documentos</a></li>
                        </ul>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>

            <div class="panel-body">

                <div class="tab-content">

                    <div class="tab-pane fade in active" id="details">

                        <div class="col-lg-5 mg20">
                            <a href="#" onclick="history.back();" class="btn btn-warning">
                                <i class="fa fa-arrow-circle-left"></i> Atrás
                            </a>
                            <a href="{{ '/project' }}" class="btn btn-warning" title="Ir a la tabla de proyectos">
                                <i class="fa fa-arrow-circle-up"></i> Contratos
                            </a>
                        </div>

                        <div class="col-lg-7" align="right"></div>

                        <div class="col-sm-12 mg10">
                            @include('app.session_flashed_messages', array('opt' => 0))
                        </div>

                        <div class="col-sm-12 mg10 mg-tp-px-10">
                            <table class="table table-striped table-hover table-bordered">
                                <tbody>
                                <tr>
                                    <th width="25%">Código:</th>
                                    <td width="25%">{{ $project->code }}</td>
                                </tr>
                                <tr>
                                    <th>Contrato:</th>
                                    <td colspan="3">{{ $project->name }}</td>
                                </tr>
                                <tr><td colspan="4"></td></tr>

                                @if($project->description!='')
                                    <tr>
                                        <th colspan="4">Descripción:</th>
                                    </tr>
                                    <tr>
                                        <td colspan="4">{{ $project->description }}</td>
                                    </tr>
                                    <tr><td colspan="4"></td></tr>
                                @endif

                                <tr>
                                    <th width="25%">Area de trabajo:</th>
                                    <td>{{ $project->type }}</td>
                                    <th>Cliente:</th>
                                    <td>{{ $project->client }}</td>
                                </tr>
                                <tr>
                                    <th width="25%">Tipo adjudicación:</th>
                                    <td colspan="3" style="vertical-align: middle;">{{ $project->award }}</td>
                                </tr>

                                @if($project->award=='Licitación'&&$project->tender)
                                    <tr>
                                        <th>Licitación</th>
                                        <td colspan="3">
                                            <a href="{{ '/tender/'.$project->tender_id }}">{{ $project->tender->name }}</a>
                                        </td>
                                    </tr>
                                    {{--
                                    <tr>
                                        <th width="25%">Plazo de presentación:</th>
                                        <td colspan="3" style="vertical-align: middle;">
                                            {{ $project->status=='No asignado' ? 'No aplica' :
                                                $project->application_deadline.
                                                ($project->applied==1 ? ' (Documentación presentada)' : ' (Pendiente)') }}
                                        </td>
                                    </tr>
                                    @if($project->application_details)
                                        <tr>
                                            <th width="25%">Detalle de licitación:</th>
                                            <td colspan="3" style="vertical-align: middle;">
                                                {!! str_replace('\n','<br/>',$project->application_details) !!}
                                            </td>
                                        </tr>
                                    @endif
                                    --}}
                                @endif
                                <tr><td colspan="4"></td></tr>

                                <tr>
                                    <th>Asignaciones:</th>
                                    <td colspan="3" style="text-align:center">
                                        <a href="{{ '/assignment?prj='.$project->id }}" title="Ver asignaciones de este proyecto">
                                            {{ $project->assignments->count()==1 ? '1 asignación' :
                                                $project->assignments->count().' asignaciones' }}
                                        </a>
                                    </td>
                                </tr>
                                <tr><td colspan="4"></td></tr>

                                <tr>
                                    <th>Categorías de items</th>
                                    <td colspan="3">
                                        @if($project->item_categories->count()>0)
                                            <ul style="padding-left: 1em">
                                                @foreach($project->item_categories as $item_category)
                                                    <li>
                                                        <a href="/item/{{ '?cat='.$item_category->id }}">
                                                            {{ $item_category->name }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            {{ 'No se han cargado listas de items para este contrato' }}
                                        @endif
                                    </td>
                                </tr>
                                <tr><td colspan="4"></td></tr>

                                <tr>
                                    <th colspan="4" style="text-align: center">Vigencia</th>
                                </tr>
                                @if($project->status!='No asignado')
                                    <tr>
                                        <th>Desde:</th>
                                        <td>{{ $project->valid_from }}</td>
                                        <th>Hasta:</th>
                                        <td>{{ $project->valid_to }}</td>
                                    </tr>
                                @else
                                    <tr>
                                        <td colspan="4">{{ 'No asignado' }}</td>
                                    </tr>
                                @endif
                                <tr><td colspan="4"></td></tr>

                                <tr>
                                    <th>Agregado por:</th>
                                    <td colspan="3">
                                        {{ $project->user ? $project->user->name : 'N/A' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Agregado en:</th>
                                    <td colspan="3">
                                        {{ date_format($project->created_at,'d/m/Y') }}
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        @if($project->user_id==$user->id||$user->action->prj_edt /*$user->priv_level>=3*/)
                            <div class="col-sm-12 mg10" align="center">
                                <a href="/project/{{ $project->id }}/edit" class="btn btn-success">
                                    <i class="fa fa-pencil-square-o"></i> Modificar
                                </a>
                            </div>
                        @endif

                    </div>

                    <div class="tab-pane fade" id="documents">

                        <div class="col-lg-5 mg20">
                            <a href="#" onclick="history.back();" class="btn btn-warning">
                                <i class="fa fa-arrow-circle-left"></i> Volver
                            </a>
                        </div>

                        <div class="col-sm-12 mg10 mg-tp-px-10">
                            <table class="table table-striped table-hover table-bordered">
                                <tbody>
                                <tr>
                                    <th width="25%">Código:</th>
                                    <td width="25%">{{ $project->code }}</td>
                                    <td colspan="2"></td>
                                </tr>
                                <tr>
                                    <th>Contrato:</th>
                                    <td colspan="3">{{ $project->name }}</td>
                                </tr>
                                <tr><td colspan="4"></td></tr>

                                <tr>
                                    <th colspan="4">Archivos:</th>
                                </tr>
                                @foreach($project->files as $file)
                                    <tr>
                                        <td>{{ date_format(new \DateTime($file->updated_at), 'd-m-Y') }}</td>
                                        <td colspan="3">
                                            {{ $file->description }}

                                            <div class="pull-right">
                                                @include('app.info_document_options', array('file'=>$file))
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach

                                @foreach($project->guarantees as $guarantee)
                                    <tr>
                                        <td>{{ date_format(new \DateTime($guarantee->start_date), 'd-m-Y') }}</td>
                                        <td colspan="2">Poliza de garantía</td>
                                        <td>
                                            <a href="/guarantee/{{ $guarantee->id }}" title="Ver información de poliza">
                                                {{ $guarantee->code }}
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"></td>
                                        <td>Vence</td>
                                        <td>
                                            {{ date_format(new \DateTime($guarantee->expiration_date), 'd-m-Y') }}
                                            @if($user->action->prj_acc_wty
                                                /*($user->area=='Gerencia General'||$user->priv_level>=3)*/&&
                                                $guarantee->closed==0)
                                                &ensp;
                                                <a href="/guarantee/{{ $guarantee->id }}/edit" title="Modificar poliza">
                                                    <i class="fa fa-pencil-square-o"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach

                                @if($project->files->count()==0&&$project->guarantees->count()==0)
                                    <tr>
                                        <td colspan="4" align="center">No se cargó ningún documento.</td>
                                    </tr>
                                @endif

                                @if($project->status!='No asignado')
                                    <tr>
                                        <th colspan="4" style="text-align: center">
                                            <a href="/files/project/{{ $project->id }}">
                                                <i class="fa fa-upload"></i> Subir archivo
                                            </a>
                                        </th>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>

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
