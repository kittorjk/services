<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 30/11/2017
 * Time: 05:55 PM
 */
?>

@extends('layouts.projects_structure')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/info_tabs.css") }}">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    @if ($user->priv_level > 0)
      @include('app.project_navigation_button', array('user'=>$user))
    @endif
    <div class="btn-group">
        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
            <i class="fa fa-money"></i> Solicitudes de viáticos <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li><a href="{{ '/stipend_request?asg='.$stipend->assignment_id }}"><i class="fa fa-refresh fa-fw"></i> Ver solicitudes </a></li>
            @if ($user->priv_level > 0)
              <li><a href="{{ '/stipend_request/create?asg='.$stipend->assignment_id }}"><i class="fa fa-plus fa-fw"></i> Nueva solicitud </a></li>
            @endif
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
    @if ($user->priv_level >= 2)
      <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchBox">
        <i class="fa fa-search"></i> Buscar
      </button>
    @endif
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
                                @if($stipend->assignment && $stipend->assignment->cost_center > 0)
                                    <tr>
                                        <th><span title="Centro de costos">C.C.</span></th>
                                        <td colspan="3">{{ $stipend->assignment->cost_center }}</td>
                                    </tr>
                                @endif
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
                                    <th>Solicitado para</th>
                                    <td colspan="3">
                                        {{ $stipend->employee ?
                                            $stipend->employee->first_name.' '.$stipend->employee->last_name : '' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2"></td>
                                    <td>Monto por día [Bs]</td>
                                    <td>{{ 'Monto * '.$stipend->in_days.' día(s) [Bs]' }}</td>
                                </tr>
                                <tr>
                                    <td colspan="2">{{ 'Viático' }}</td>
                                    <td align="right">{{ $stipend->per_day_amount }}</td>
                                    <td align="right">{{ $stipend->per_day_amount * $stipend->in_days }}</td>
                                    <!--<td align="right">{{ $stipend->additional }}</td>-->
                                </tr>
                                <tr>
                                    <td colspan="2">{{ 'Alojamiento' }}</td>
                                    <td align="right">{{ $stipend->hotel_amount }}</td>
                                    <td align="right">{{ $stipend->hotel_amount * $stipend->in_days }}</td>
                                </tr>
                                <tr>
                                    <th colspan="3" style="text-align: right">Subtotal [Bs]</th>
                                    <td align="right">{{ number_format($stipend->total_amount, 2) }}</td>
                                </tr>
                                
                                <tr><td colspan="4"></td></tr>

                                @if($stipend->additional > 0)
                                    <tr>
                                        <th colspan="4">Detalle de adicionales</th>
                                    </tr>
                                    @if($stipend->transport_amount>0)
                                        <tr>
                                            <td colspan="3">Transporte (pasajes) [Bs]</td>
                                            <td align="right">{{ $stipend->transport_amount }}</td>
                                        </tr>
                                    @endif
                                    @if($stipend->gas_amount>0)
                                        <tr>
                                            <td colspan="3">Combustible [Bs]</td>
                                            <td align="right">{{ $stipend->gas_amount }}</td>
                                        </tr>
                                    @endif
                                    @if($stipend->taxi_amount>0)
                                        <tr>
                                            <td colspan="3">Taxi (pasajes) [Bs]</td>
                                            <td align="right">{{ $stipend->taxi_amount }}</td>
                                        </tr>
                                    @endif
                                    @if($stipend->comm_amount>0)
                                        <tr>
                                            <td colspan="3">Comunicaciones [Bs]</td>
                                            <td align="right">{{ $stipend->comm_amount }}</td>
                                        </tr>
                                    @endif
                                    {{--
                                    @if($stipend->hotel_amount>0)
                                        <tr>
                                            <td colspan="3">Alojamiento [Bs]</td>
                                            <td align="right">{{ $stipend->hotel_amount }}</td>
                                        </tr>
                                    @endif
                                    --}}
                                    @if($stipend->materials_amount>0)
                                        <tr>
                                            <td colspan="3">Compra de materiales [Bs]</td>
                                            <td align="right">{{ $stipend->materials_amount }}</td>
                                        </tr>
                                    @endif
                                    @if($stipend->extras_amount>0)
                                        <tr>
                                            <td colspan="3">Extras [Bs]</td>
                                            <td align="right">{{ $stipend->extras_amount }}</td>
                                        </tr>
                                    @endif
                                    <tr><td colspan="4"></td></tr>
                                @endif

                                <tr>
                                    <th colspan="3" style="text-align: right">Total a depositar [Bs]</th>
                                    <th style="text-align: right">{{ number_format($stipend->total_amount+$stipend->additional,2) }}</th>
                                </tr>

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

                        @if((($user->id == $stipend->user_id || $user->action->prj_vtc_edt) && ($stipend->status === 'Pending' || $stipend->status === 'Observed')) || $user->priv_level == 4)
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

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'stipend_requests','id'=>0))
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
