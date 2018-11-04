<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 07/09/2017
 * Time: 03:22 PM
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
            <i class="fa fa-exchange"></i> Requerimientos <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li>
                <a href="{{ '/device_requirement' }}"><i class="fa fa-refresh"></i> Ver requerimientos</a>
            </li>
            <li><a href="{{ '/device' }}"><i class="fa fa-arrow-right"></i> Ver equipos</a></li>
            <li><a href="{{ '/operator' }}"><i class="fa fa-arrow-right"></i> Ver asignaciones </a></li>
            @if($user->action->acv_dvc_req /*$user->work_type=='Almacén'||$user->priv_level>=2*/)
                <li><a href="{{ '/device_requirement/create' }}"><i class="fa fa-plus"></i> Nuevo requerimiento </a></li>
            @endif
        </ul>
    </div>
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

        <div class="panel panel-brown">
            <div class="panel-heading" align="center">
                <div class="panel-title">Información de requerimiento de equipo</div>
            </div>
            <div class="panel-body">
                <div class="col-sm-12 mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning">
                        <i class="fa fa-arrow-circle-left"></i> Volver
                    </a>
                    <a href="{{ '/device_requirement' }}" class="btn btn-warning" title="Ir a la tabla de requerimientos de equipo">
                        <i class="fa fa-arrow-circle-up"></i> Requerimientos
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <div class="col-sm-12 mg10 mg-tp-px-10">

                    <table class="table table-striped table-hover table-bordered">
                        <thead>
                        <tr>
                            <th width="30%">Requerimiento</th>
                            <td colspan="3">{{ $requirement->code }}</td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <th>Tipo</th>
                            <td colspan="3">{{ App\DeviceRequirement::$types[$requirement->type] }}</td>
                        </tr>
                        <tr>
                            <th>Fecha</th>
                            <td>{{ date_format($requirement->created_at,'Y-m-d') }}</td>
                            <th width="22%">Estado</th>
                            <td>{{ App\DeviceRequirement::$stat_names[$requirement->status] }}</td>
                        </tr>
                        <tr>
                            <th>Elaborado por</th>
                            <td colspan="3">{{ $requirement->user ? $requirement->user->name : '' }}</td>
                        </tr>
                        <tr><td colspan="4"></td></tr>

                        @if($requirement->device)
                            <tr>
                                <th>Equipo requerido</th>
                                <td colspan="3">{{ $requirement->device->type.' '.$requirement->device->model }}</td>
                            </tr>
                            <tr>
                                <th>Serial</th>
                                <td colspan="3">{{ $requirement->device->serial }}</td>
                            </tr>
                            <tr>
                                <th>Resp. actual</th>
                                <td colspan="3">{{ $requirement->person_from ? $requirement->person_from->name : 'N/E' }}</td>
                            </tr>
                            <tr>
                                <th>Almacén a cargo</th>
                                <td colspan="3">{{ $requirement->branch_origin }}</td>
                            </tr>

                            @if($requirement->type=='borrow'||$requirement->type=='transfer_tech')
                                <tr>
                                    <th>Entregar a</th>
                                    <td colspan="3">{{ $requirement->person_for ? $requirement->person_for->name : 'N/E' }}</td>
                                </tr>
                            @else
                                <tr>
                                    <th>Enviar a</th>
                                    <td colspan="3">{{ 'Almacén '.$requirement->branch_destination }}</td>
                                </tr>
                            @endif
                            <tr><td colspan="4"></td></tr>
                        @endif

                        <tr>
                            <th colspan="4">Motivo de requerimiento</th>
                        </tr>
                        <tr>
                            <td colspan="4">{{ $requirement->reason }}</td>
                        </tr>

                        @if($requirement->stat_obs)
                            <tr><td colspan="4"></td></tr>
                            <tr>
                                <th>Fecha cambio de estado</th>
                                <td colspan="3">{{ $requirement->stat_change }}</td>
                            </tr>
                            <tr>
                                <th>Obervaciones de cambio de estado</th>
                                <td colspan="3">{{ $requirement->stat_obs }}</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>

                @if($user->priv_level==4)
                    <div class="col-sm-12 mg10" align="center">
                        <a href="/device_requirement/{{ $requirement->id }}/edit" class="btn btn-success">
                            <i class="fa fa-pencil-square-o"></i> Modificar requerimiento
                        </a>
                    </div>
                @endif
            </div>
        </div>
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
