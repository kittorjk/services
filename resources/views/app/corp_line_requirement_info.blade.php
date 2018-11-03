<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 02/10/2017
 * Time: 02:18 PM
 */
?>

@extends('layouts.actives_structure')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/image_modal.css") }}">
@endsection

@section('menu_options')
    <div class="btn-group">
        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
            <i class="fa fa-exchange"></i> Requerimientos <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li>
                <a href="{{ '/line_requirement' }}"><i class="fa fa-refresh"></i> Ver requerimientos</a>
            </li>
            <li><a href="{{ '/corporate_line' }}"><i class="fa fa-arrow-right"></i> Ver lista de líneas</a></li>
            <li><a href="{{ '/line_assignation' }}"><i class="fa fa-arrow-right"></i> Ver asignaciones </a></li>
            @if($user->action->acv_ln_req /*$user->priv_level>=1*/)
                <li><a href="{{ '/line_requirement/create' }}"><i class="fa fa-plus"></i> Nuevo requerimiento</a></li>
            @endif
        </ul>
    </div>
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

        <div class="panel panel-brown">
            <div class="panel-heading" align="center">
                <div class="panel-title">Información de requerimiento de línea corporativa</div>
            </div>
            <div class="panel-body">
                <div class="col-sm-12 mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning">
                        <i class="fa fa-arrow-circle-left"></i> Volver
                    </a>
                    <a href="{{ '/line_requirement' }}" class="btn btn-warning" title="Ir a la tabla de requerimientos de línea">
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
                            <th>Fecha</th>
                            <td>{{ date_format($requirement->created_at,'Y-m-d') }}</td>
                            <th width="22%">Estado</th>
                            <td>{{ App\CorpLineRequirement::$stat_names[$requirement->status] }}</td>
                        </tr>
                        <tr>
                            <th>Elaborado por</th>
                            <td colspan="3">{{ $requirement->user ? $requirement->user->name : '' }}</td>
                        </tr>
                        <tr><td colspan="4"></td></tr>

                        <tr>
                            <th>Entregar línea a</th>
                            <td colspan="3">{{ $requirement->person_for ? $requirement->person_for->name : 'N/E' }}</td>
                        </tr>
                        <tr><td colspan="4"></td></tr>

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
                @if($user->action->acv_ln_req /*$user->priv_level==4*/)
                    <div class="col-sm-12 mg10" align="center">
                        <a href="/line_requirement/{{ $requirement->id }}/edit" class="btn btn-success">
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
    <script></script>
@endsection
