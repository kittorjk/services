<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 01/08/2017
 * Time: 04:55 PM
 */
?>

@extends('layouts.master')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">
        <div class="panel panel-sky">
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    {{ $viatics->count()==1 ? '1 solicitud pendiente de aprobación' :
                        $viatics->count().' solicitudes pendientes de aprobación' }}
                </div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-undo"></i>
                    </a>
                    <a href="{{ '/rbs_viatic' }}" class="btn btn-warning" title="Volver a resumen de solicitudes">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <div class="col-sm-12">
                    <table class="table table-striped table-hover table-bordered">
                        <tbody>
                        <tr>
                            <th># Solicitud</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                        @foreach($viatics as $viatic)
                            <tr>
                                <td align="center">
                                    <a href="/rbs_viatic/{{ $viatic->id }}" title="Ver información de solicitud">
                                        {{ $viatic->id }}
                                    </a>
                                </td>
                                <td>{{ date_format($viatic->created_at,'d-m-Y') }}</td>
                                <td>{{ $viatic->statuses($viatic->status) }}</td>
                                <td>
                                    @if(($viatic->status==0||$viatic->status==2)&&$user->priv_level>=2)
                                        <a href="/rbs_viatic/status/{{ $viatic->id }}?action=observe"
                                           title="Observar solicitud de viáticos">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        &ensp;
                                        <a href="/rbs_viatic/approve/{{ $viatic->id }}" title="Aprobar solicitud de viáticos">
                                            <i class="fa fa-check"></i>
                                        </a>
                                        &ensp;
                                        <a href="/rbs_viatic/status/{{ $viatic->id }}?action=reject"
                                           title="Rechazar solicitud de viáticos">
                                            <i class="fa fa-times"></i>
                                        </a>
                                        @if($user->id==$viatic->user_id)
                                            &ensp;
                                            <a href="/rbs_viatic/status/{{ $viatic->id }}?action=cancel"
                                               title="Cancelar solicitud de viáticos">
                                                <i class="fa fa-ban"></i>
                                            </a>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        @if($viatics->count()==0)
                            <tr>
                                <td colspan="4" align="center">
                                    No existen solicitudes pendientes de aprobación
                                </td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

@endsection

@section('footer')
@endsection

@section('javascript')
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
@endsection
