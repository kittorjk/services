<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 01/12/2017
 * Time: 03:39 PM
 */
?>

@extends('layouts.projects_structure')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    @include('app.project_navigation_button', array('user'=>$user))
    <div class="btn-group">
        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
            <i class="fa fa-money"></i> Solicitudes de viáticos <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li><a href="{{ '/stipend_request' }}"><i class="fa fa-refresh fa-fw"></i> Ver solicitudes </a></li>
            <li><a href="{{ '/stipend_request/create' }}"><i class="fa fa-plus fa-fw"></i> Nueva solicitud </a></li>
            @if($user->action->prj_vtc_mod /*$user->priv_level>=2*/)
                <li>
                    <a href="{{ '/stipend_request/approve_list' }}">
                        <i class="fa fa-check fa-fw"></i> Ver pendientes de aprobación
                    </a>
                </li>
                <li>
                    <a href="{{ '/stipend_request/observed_list' }}">
                        <i class="fa fa-eye fa-fw"></i> Ver observadas
                    </a>
                </li>
            @endif
            @if($user->action->prj_vtc_pmt)
                <li>
                    <a href="{{ '/stipend_request/payment_list' }}">
                        <i class="fa fa-check fa-fw"></i> Ver pendientes de pago
                    </a>
                </li>
            @endif
        </ul>
    </div>
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1 mg-btm-px-40">
        <div class="panel panel-sky">
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    {{ $stipend_requests->count()==1 ? '1 solicitud pendiente de aprobación' :
                        $stipend_requests->count().' solicitudes pendientes de aprobación' }}
                </div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                    {{--
                    <a href="{{ '/rbs_viatic' }}" class="btn btn-warning" title="Volver a resumen de solicitudes">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                    --}}
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <div class="col-sm-12">
                    <table class="table table-striped table-hover table-bordered">
                        <tbody>
                        <tr>
                            <th># Solicitud</th>
                            <th>Fecha</th>
                            <th>Solicitado para</th>
                            <th>Total</th>
                            <th width="20%">Motivo</th>
                            <th>Acciones</th>
                        </tr>
                        @foreach($stipend_requests as $stipend)
                            <tr>
                                <td align="center">
                                    <a href="/stipend_request/{{ $stipend->id }}" title="Ver información de solicitud">
                                        {{ $stipend->code }}
                                    </a>
                                </td>
                                <td>{{ date_format($stipend->created_at,'d-m-Y') }}</td>
                                <td>{{ $stipend->employee->first_name.' '.$stipend->employee->last_name }}</td>
                                <td>{{ number_format($stipend->total_amount+$stipend->additional).' Bs' }}</td>
                                <td>{{ $stipend->reason }}</td>
                                <td>
                                    @if($stipend->status=='Pending')
                                        <a href="/stipend_request/stat/{{ '?mode=observe&id='.$stipend->id }}"
                                           title="Observar solicitud de viáticos">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        &ensp;
                                        <a href="/stipend_request/stat/{{ '?mode=approve&id='.$stipend->id }}"
                                           title="Aprobar solicitud de viáticos">
                                            <i class="fa fa-check"></i>
                                        </a>
                                        &ensp;
                                        <a href="/stipend_request/stat/{{ '?mode=reject&id='.$stipend->id }}"
                                           title="Rechazar solicitud de viáticos">
                                            <i class="fa fa-times"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        @if($stipend_requests->count()==0)
                            <tr>
                                <td colspan="6" align="center">
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
