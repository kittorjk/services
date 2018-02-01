<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 28/07/2017
 * Time: 04:11 PM
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
                            @if($user->priv_level>=2)
                                <li><a href="#control" data-toggle="tab"> Control</a></li>
                            @endif
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
                            <a href="{{ '/rbs_viatic' }}" class="btn btn-warning">
                                <i class="fa fa-bars"></i> Solicitudes
                            </a>
                        </div>
                        @if($user->area=='Gerencia Tecnica'&&$user->priv_level>=1)
                            <div class="col-lg-7" align="right">
                                <a href="/excel/rbs_viatics/{{ $rbs_viatic->id }}" class="btn btn-success">
                                    <i class="fa fa-file-excel-o"></i> Generar Excel
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
                                    <th width="24%">Solicitud</th>
                                    <td width="12%" align="right">{{ $rbs_viatic->id }}</td>
                                    <td colspan="4"></td>
                                </tr>
                                <tr>
                                    <th>Tipo</th>
                                    <td colspan="2">{{ $rbs_viatic->type }}</td>
                                    <th>Estado</th>
                                    <td colspan="2">
                                        {{ $rbs_viatic->statuses($rbs_viatic->status) }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Trabajo</th>
                                    <td colspan="5">{{ $rbs_viatic->work_description }}</td>
                                </tr>
                                <tr>
                                    <th>Desde</th>
                                    <td colspan="2">{{ date_format(new \DateTime($rbs_viatic->date_from), 'd-m-Y') }}</td>
                                    <th>Hasta</th>
                                    <td colspan="2">{{ date_format(new \DateTime($rbs_viatic->date_to), 'd-m-Y') }}</td>
                                </tr>

                                <tr><td colspan="6"></td></tr>

                                <tr>
                                    <th colspan="6">Transporte</th>
                                </tr>
                                <tr>
                                    <th>Tipo de transp.</th>
                                    <td colspan="5">{{ $rbs_viatic->type_transport }}</td>
                                </tr>
                                @if($rbs_viatic->type_transport=='Vehículo alquilado')
                                    <tr>
                                        <th>Alquilado por</th>
                                        <td colspan="2" align="center">
                                            {{ $rbs_viatic->vehicle_rent_days==1 ? '1 día' : $rbs_viatic->vehicle_rent_days.' días' }}
                                        </td>
                                        <th>Costo por día</th>
                                        <td colspan="2" align="right">{{ $rbs_viatic->vehicle_rent_cost_day.' Bs' }}</td>
                                    </tr>
                                @endif

                                <tr><td colspan="6"></td></tr>

                                <tr>
                                    <th colspan="6">
                                        Técnicos
                                        <span class="pull-right">{{ $rbs_viatic->num_technicians }}</span>
                                    </th>
                                </tr>
                                <tr>
                                    <td colspan="2">Nombre</td>
                                    <td>Viático [Bs]</td>
                                    <td>Extras [Bs]</td>
                                    <td>Pasaje ida [Bs]</td>
                                    <td>Pasaje vuelta [Bs]</td>
                                </tr>
                                @foreach($rbs_viatic->technician_requests as $request)
                                    <tr>
                                        <td colspan="2">{{ $request->technician ? $request->technician->name : '' }}</td>
                                        <td align="right">{{ $request->viatic_amount }}</td>
                                        <td align="right">{{ $request->extra_expenses }}</td>
                                        <td align="right">{{ $request->departure_cost }}</td>
                                        <td align="right">{{ $request->return_cost }}</td>
                                    </tr>
                                @endforeach

                                <tr><td colspan="6"></td></tr>

                                <tr>
                                    <th colspan="6">
                                        Sitios
                                        <span class="pull-right">{{ $rbs_viatic->num_sites }}</span>
                                    </th>
                                </tr>
                                <tr>
                                    <td colspan="6">
                                        @foreach($rbs_viatic->sites as $site)
                                            <a href="/site/{{ $site->id }}/show" title="Ver información de sitio">
                                                {{ $site->name }}
                                            </a>
                                            {!! '<br>' !!}
                                        @endforeach
                                    </td>
                                </tr>

                                <tr><td colspan="6"></td></tr>

                                @if($rbs_viatic->extra_expenses>0)
                                    <tr>
                                        <th colspan="6">Detalle de extras</th>
                                    </tr>
                                    <tr>
                                        <td>Extras</td>
                                        <td colspan="5" align="right">{{ $rbs_viatic->extra_expenses.' Bs' }}</td>
                                    </tr>
                                    @if($rbs_viatic->extra_expenses_detail)
                                        <tr>
                                            <td>Motivo</td>
                                            <td colspan="5">{{ $rbs_viatic->extra_expenses_detail }}</td>
                                        </tr>
                                    @endif

                                    <tr><td colspan="6"></td></tr>
                                @endif

                                @if($rbs_viatic->materials_cost>0)
                                    <tr>
                                        <th colspan="6">Materiales adicionales</th>
                                    </tr>
                                    <tr>
                                        <td>Costo total</td>
                                        <td colspan="5" align="right">{{ $rbs_viatic->materials_cost.' Bs' }}</td>
                                    </tr>
                                    @if($rbs_viatic->materials_detail)
                                        <tr>
                                            <td>Motivo</td>
                                            <td colspan="5">{{ $rbs_viatic->materials_detail }}</td>
                                        </tr>
                                    @endif

                                    <tr><td colspan="6"></td></tr>
                                @endif

                                <tr>
                                    <th colspan="4">Solicitud creada por</th>
                                    <td colspan="2">{{ $rbs_viatic->user ? $rbs_viatic->user->name : 'n/e' }}</td>
                                </tr>

                                </tbody>
                            </table>
                        </div>

                        @if($user->id==$rbs_viatic->user_id||$user->priv_level==4)
                            <div class="col-sm-12 mg10" align="center">
                                <a href="/rbs_viatic/{{ $rbs_viatic->id }}/edit" class="btn btn-primary">
                                    <i class="fa fa-pencil-square-o"></i> Modificar solicitud
                                </a>
                            </div>
                        @endif

                    </div>

                    @if($user->priv_level>=2)
                        <div class="tab-pane fade" id="control">
                            @include('app.rbs_viatic_control', array('rbs_viatic' => $rbs_viatic, 'user' => $user,
                                'parameters' => $parameters, 'budgets' => $budgets))
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
    <script>
        $('#alert').delay(2000).fadeOut('slow');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
@endsection
