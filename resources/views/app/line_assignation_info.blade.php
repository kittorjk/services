<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 20/09/2017
 * Time: 05:06 PM
 */
?>

@extends('layouts.actives_structure')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/image_modal.css") }}">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    <div class="btn-group">
        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
            <i class="fa fa-exchange"></i> Asignación de líneas <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li><a href="{{ '/line_assignation' }}"><i class="fa fa-refresh fa-fw"></i> Ver asignaciones </a></li>
            <li><a href="{{ '/corporate_line' }}"><i class="fa fa-arrow-right fa-fw"></i> Ver lista de líneas </a></li>
            <li><a href="{{ '/line_requirement' }}"><i class="fa fa-arrow-right fa-fw"></i> Ver requerimientos </a></li>
            @if($user->action->acv_ln_asg /*($user->area=='Gerencia General'&&$user->priv_level>=2)||$user->priv_level==4*/)
                <li><a href="{{ '/line_assignation/create' }}"><i class="fa fa-exchange fa-fw"></i> Asignar línea </a></li>
            @endif
            @if($user->action->acv_ln_req /*$user->priv_level>=1*/)
                <li><a href="{{ '/line_requirement/create' }}"><i class="fa fa-plus fa-fw"></i> Nuevo requerimiento </a></li>
            @endif
        </ul>
    </div>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchBox">
        <i class="fa fa-search"></i> Buscar
    </button>
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

        <div class="panel panel-brown">
            <div class="panel-heading" align="center">
                <div class="panel-title">Información de asignación de línea</div>
            </div>
            <div class="panel-body">
                <div class="col-sm-12 mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning">
                        <i class="fa fa-arrow-circle-left"></i> Volver
                    </a>
                    <a href="{{ '/line_assignation' }}" class="btn btn-warning"
                       title="Ir a la tabla de asignaciones de línea corporativa">
                        <i class="fa fa-arrow-circle-up"></i> Asignaciones
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <div class="col-sm-12 mg10 mg-tp-px-10">

                    <table class="table table-striped table-hover table-bordered">
                        <thead>
                        <tr>
                            <th width="40%">Línea:</th>
                            <td>
                                <a href="{{ '/corporate_line/'.$assignation->line->id }}">
                                    {{ $assignation->line->number }}
                                </a>
                            </td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr><td colspan="2"> </td></tr>
                        <tr>
                            <th>Tipo de asignación</th>
                            <td>{{ $assignation->type }}</td>
                        </tr>
                        <tr>
                            <th>Requerimiento</th>
                            <td>
                                @if($assignation->requirement)
                                    <a href="/line_requirement/{{ $assignation->requirement->id }}">
                                        {{ $assignation->requirement->code }}
                                    </a>
                                @else
                                    {{ 'Sin requerimiento' }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Fecha de asignación</th>
                            <td>{{ date_format($assignation->created_at,'d-m-Y') }}</td>
                        </tr>
                        <tr>
                            <th>Área de servicio</th>
                            <td>{{ $assignation->service_area }}</td>
                        </tr>
                        <tr>
                            <th>Responsable actual:</th>
                            <td>{{ $assignation->resp_after ? $assignation->resp_after->name : 'N/E' }}</td>
                        </tr>
                        <tr>
                            <th>Responsable anterior:</th>
                            <td>{{ $assignation->resp_before ? $assignation->resp_before->name : 'N/E' }}</td>
                        </tr>

                        @if($assignation->observations)
                            <tr><td colspan="2"></td></tr>
                            <tr>
                                <th colspan="2">Observaciones</th>
                            </tr>
                            <tr>
                                <td colspan="2">{{ $assignation->observations }}</td>
                            </tr>
                        @endif

                        <tr><td colspan="2"></td></tr>
                        <tr>
                            <th>Conformidad firmada</th>
                            <td>
                                @foreach($assignation->files as $file)
                                    @include('app.info_document_options', array('file'=>$file))
                                @endforeach
                                @if($assignation->files()->count()==0)
                                    <a href="/files/line_assignation/{{ $assignation->id }}">
                                        <i class="fa fa-upload"></i> Documento firmado
                                    </a>
                                @endif
                            </td>
                        </tr>

                        <tr><td colspan="2"></td></tr>
                        <tr>
                            <th>Registro creado por</th>
                            <td>{{ $assignation->user ? $assignation->user->name : 'N/E' }}</td>
                        </tr>

                        </tbody>
                    </table>
                </div>

                @if($user->action->acv_ln_edt&&$user->action->acv_ln_asg /*$user->priv_level==4*/)
                    <div class="col-sm-12 mg10" align="center">
                        <a href="/line_assignation/{{ $assignation->id }}/edit" class="btn btn-success">
                            <i class="fa fa-pencil-square-o"></i> Modificar datos
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'corp_line_assignations','id'=>0))
    </div>

@endsection

@section('footer')
@endsection

@section('javascript')
    <script src="{{ asset('app/js/set_current_url.js') }}"></script> {{-- For recording current url --}}
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#alert').delay(2000).fadeOut('slow');
    </script>
@endsection
