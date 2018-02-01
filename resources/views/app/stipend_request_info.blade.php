<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 30/11/2017
 * Time: 05:55 PM
 */
?>

@extends('layouts.info_master')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="stylesheet" href="{{ asset("app/css/info_tabs.css") }}">
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

        <div class="panel panel-sky">
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    <div class="pull-left">
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#main" data-toggle="tab"> Información general</a></li>
                        </ul>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
            <div class="panel-body">

                <div class="tab-content">

                    <div class="tab-pane fade in active" id="main">

                        <div class="col-lg-5 mg20">
                            <a href="#" onclick="history.back();" class="btn btn-warning">
                                <i class="fa fa-arrow-circle-left"></i> Atrás
                            </a>
                            <a href="{{ '/stipend_request' }}" class="btn btn-warning">
                                <i class="fa fa-bars"></i> Solicitudes
                            </a>
                        </div>

                        @if($user->action->prj_vtc_exp /*$user->area=='Gerencia Tecnica'&&$user->priv_level>=1*/)
                            <div class="col-lg-7" align="right">
                                <a href="/excel/stipend_requests/{{ $stipend->assignment_id }}" class="btn btn-success">
                                    <i class="fa fa-file-excel-o"></i> Exportar
                                </a>
                            </div>
                        @endif

                        <div class="col-sm-12 mg10">
                            @include('app.session_flashed_messages', array('opt' => 0))
                        </div>

                        <div class="col-sm-12 mg10 mg-tp-px-10">
                            <table class="table table-striped table-hover table-bordered">
                                <tbody>

                                <tr>
                                    <th width="25%">Solicitud #</th>
                                    <td width="25%" align="right">{{ $stipend->code }}</td>
                                </tr>
                                <tr>
                                    <th>Estado</th>
                                    <td colspan="3">
                                        {{ \App\StipendRequest::$stats[$stipend->status] }}
                                    </td>
                                </tr>
                                @if($stipend->observations!='')
                                    <tr>
                                        <th>Observaciones</th>
                                        <td colspan="3">{{ $stipend->observations }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <th>Trabajo</th>
                                    <td colspan="3">{{ $stipend->reason }}</td>
                                </tr>
                                <tr>
                                    <th>Desde</th>
                                    <td width="25%">{{ date_format(new \DateTime($stipend->date_from), 'd-m-Y') }}</td>
                                    <th>Hasta</th>
                                    <td width="25%">{{ date_format(new \DateTime($stipend->date_to), 'd-m-Y') }}</td>
                                </tr>
                                <tr>
                                    <td colspan="2"></td>
                                    <th>Cant. días</th>
                                    <td>{{ $stipend->in_days }}</td>
                                </tr>

                                <tr><td colspan="4"></td></tr>

                                <tr>
                                    <th colspan="4">Solicitado para</th>
                                </tr>
                                <tr>
                                    <td colspan="2">Nombre</td>
                                    <td>Viático [Bs]</td>
                                    <td>Adicionales [Bs]</td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        {{ $stipend->employee ?
                                            $stipend->employee->first_name.' '.$stipend->employee->last_name : '' }}
                                    </td>
                                    <td align="right">{{ $stipend->per_day_amount }}</td>
                                    <td align="right">{{ $stipend->additional }}</td>
                                </tr>
                                <tr>
                                    <th colspan="3" style="text-align: right">Total a depositar [Bs]</th>
                                    <td align="right">{{ number_format($stipend->total_amount+$stipend->additional,2) }}</td>
                                </tr>
                                <tr><td colspan="4"></td></tr>

                                @if($stipend->additional>0)
                                    <tr>
                                        <th colspan="4">Detalle de adicionales</th>
                                    </tr>
                                    @if($stipend->transport_amount>0)
                                        <tr>
                                            <td colspan="3">Transporte (pasajes)</td>
                                            <td align="right">{{ $stipend->transport_amount.' Bs' }}</td>
                                        </tr>
                                    @endif
                                    @if($stipend->gas_amount>0)
                                        <tr>
                                            <td colspan="3">Combustible</td>
                                            <td align="right">{{ $stipend->gas_amount.' Bs' }}</td>
                                        </tr>
                                    @endif
                                    @if($stipend->taxi_amount>0)
                                        <tr>
                                            <td colspan="3">Taxi (pasajes)</td>
                                            <td align="right">{{ $stipend->taxi_amount.' Bs' }}</td>
                                        </tr>
                                    @endif
                                    @if($stipend->comm_amount>0)
                                        <tr>
                                            <td colspan="3">Comunicaciones</td>
                                            <td align="right">{{ $stipend->comm_amount.' Bs' }}</td>
                                        </tr>
                                    @endif
                                    @if($stipend->hotel_amount>0)
                                        <tr>
                                            <td colspan="3">Alojamiento</td>
                                            <td align="right">{{ $stipend->hotel_amount.' Bs' }}</td>
                                        </tr>
                                    @endif
                                    @if($stipend->materials_amount>0)
                                        <tr>
                                            <td colspan="3">Compra de materiales</td>
                                            <td align="right">{{ $stipend->materials_amount.' Bs' }}</td>
                                        </tr>
                                    @endif
                                    @if($stipend->extras_amount>0)
                                        <tr>
                                            <td colspan="3">Extras</td>
                                            <td align="right">{{ $stipend->extras_amount.' Bs' }}</td>
                                        </tr>
                                    @endif
                                    <tr><td colspan="4"></td></tr>
                                @endif

                                <tr>
                                    <th colspan="4">Asignación</th>
                                </tr>
                                <tr>
                                    <td colspan="4">
                                        <a href="/assignment/{{ $stipend->assignment_id }}" title="Ver información de asignación">
                                            {{ $stipend->assignment ? $stipend->assignment->name : '' }}
                                        </a>
                                    </td>
                                </tr>
                                @if($stipend->sites()->count()>0)
                                    <tr>
                                        <th colspan="4">Sitios</th>
                                    </tr>
                                    <tr>
                                        <td colspan="4">
                                            <ul>
                                                @foreach($stipend->sites as $site)
                                                    <li>
                                                        <a href="/site/{{ $site->id }}/show" title="Ver información de sitio">
                                                            {{ $site->name }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </td>
                                    </tr>
                                @endif
                                <tr><td colspan="4"></td></tr>

                                <tr>
                                    <th>Elaborado por</th>
                                    <td colspan="3">{{ $stipend->user ? $stipend->user->name : 'n/e' }}</td>
                                </tr>

                                </tbody>
                            </table>
                        </div>

                        @if($user->id==$stipend->user_id||$user->action->prj_vtc_edt /*$user->priv_level==4*/)
                            <div class="col-sm-12 mg10" align="center">
                                <a href="/stipend_request/{{ $stipend->id }}/edit" class="btn btn-primary">
                                    <i class="fa fa-pencil-square-o"></i> Modificar solicitud
                                </a>
                            </div>
                        @endif

                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection

@section('footer')
@endsection

@section('javascript')
    <script>
        $('#alert').delay(2000).fadeOut('slow');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
@endsection
