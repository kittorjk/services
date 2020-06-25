<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 30/11/2017
 * Time: 11:49 AM
 */
?>

@extends('layouts.projects_structure')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/custom_table.css") }}">
    <style>
        .dropdown-menu-prim > li > a {
            width: 190px;
        }
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    @if ($user->priv_level > 1)
      @include('app.project_navigation_button', array('user'=>$user))
    @endif
    <div class="btn-group">
        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
            <i class="fa fa-user"></i> Cuentas de empleado <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li>
                <a href="{{ '/employee_account'.($employee_record && $employee_record->id ? '/'.$employee_record->id : '') }}">
                    <i class="fa fa-refresh fa-fw"></i> Recargar página
                </a>
            </li>
            {{--
            @if ($user->priv_level > 0)
              <li>
                  <a href="{{ $asg ? '/stipend_request/create?asg='.$asg : '/stipend_request/seleccionar_proyecto' }}">
                      <i class="fa fa-plus fa-fw"></i> Nueva solicitud 
                  </a>
              </li>
            @endif
            @if($user->action->prj_vtc_mod)
                <li>
                    <a href="{{ '/stipend_request/approve_list' }}">
                        <i class="fa fa-check fa-fw"></i> Pendientes de aprobación
                    </a>
                </li>
                <li>
                    <a href="{{ '/stipend_request/observed_list' }}">
                        <i class="fa fa-eye fa-fw"></i> Observadas
                    </a>
                </li>
            @endif
            @if($user->action->prj_vtc_pmt)
                <li>
                    <a href="{{ '/stipend_request/payment_list' }}">
                        <i class="fa fa-check fa-fw"></i> Pendientes de pago
                    </a>
                </li>
            @endif
            @if ($user->priv_level > 0 && $asg)
              <li>
                  <a href="/import/stipend_requests/{{ $asg }}">
                      <i class="fa fa-upload"></i> Importar solicitudes
                  </a>
              </li>
            @endif
            --}}
            @if ($user->action->prj_vtc_exp)
                @if ($employee_record)
                    <li class="divider"></li>
                    <li>
                        <a href="{{ '/excel/employee_account_info/'.$employee_record->id }}">
                            <i class="fa fa-file-excel-o fa-fw"></i> Descargar tabla
                        </a>
                    </li>
                @endif
            @endif
        </ul>
    </div>
    <a href="{{ '/rendicion_viatico' }}" class="btn btn-primary" title="Ver rendiciones de viáticos">
      <i class="fa fa-file"></i> Rendiciones
    </a>
    <a href="{{ '/employee_account' }}" class="btn btn-primary" title="Ver cuentas por empleado">
      <i class="fa fa-users"></i> Cuentas de empleados
    </a>
    <a href="{{ '/stipend_request' }}" class="btn btn-primary" title="Ver solicitudes de viaticos">
      <i class="fa fa-file"></i> Solicitudes
    </a>
    @if ($user->priv_level >= 2)
      <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchBox">
        <i class="fa fa-search"></i> Buscar
      </button>
    @endif
@endsection

@section('content')
    <div class="col-sm-12 mg10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-8 col-sm-offset-4 mg10">
        <table class="table table-striped table-hover table-bordered">
            <thead>
                <tr>
                    <th width="20%">Nombre</th>
                    <td>{{ $employee_record->first_name.' '.$employee_record->last_name }}</td>
                    <th width="20%">Código</th>
                    <td>{{ $employee_record->code }}</td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="3" align="right">Total de solicitudes</td>
                    <td align="right">{{ number_format($total_solicitudes, 2).' Bs' }}</td>
                </tr>
                <tr>
                    <td colspan="3" align="right">Total rendido</td>
                    <td align="right">{{ number_format($total_rendiciones, 2).' Bs' }}</td>
                </tr>
                @if ($saldo_global_abros > 0)
                    <tr>
                        <td colspan="3" align="right">Saldo a favor de ABROS</td>
                        <td align="right">{{ number_format($saldo_global_abros, 2).' Bs' }}</td>
                    </tr>
                @elseif ($saldo_global_empleado > 0)
                    <tr>
                        <td colspan="3" align="right">Saldo a favor del empleado</td>
                        <td align="right">{{ number_format($saldo_global_empleado, 2).' Bs' }}</td>
                    </tr>
                @else
                    <tr>
                        <td colspan="4" align="right">No quedan saldos pendientes</td>
                    </tr>
                @endif
                {{--
                @if($employee_record->bnk != '')
                    <tr>
                        <th>Banco</th>
                        <td>{{ $employee_record->bnk }}</td>
                    </tr>
                @endif
                @if($employee_record->bnk_account != '')
                    <tr>
                        <th>Cuenta</th>
                        <td>{{ $employee_record->bnk_account }}</td>
                    </tr>
                @endif
                --}}
            </tbody>
        </table>
    </div>

    <div class="col-sm-12 mg10">

        <p>Registros encontrados: {{ $stipend_requests->total() }}</p>

        <table class="formal_table table_blue tablesorter" id="fixable_table">
            <thead>
            <tr>
                <th>Solicitud</th>
                <th title="Centro de costos">C.C.</th>
                <th width="7%">Fecha</th>
                <th>Viático</th>
                <th>Adicionales</th>
                <th width="16%">Trabajo</th>
                <th width="7%">Desde</th>
                <th width="7%">Hasta</th>
                <th width="10%">Estado</th>
                <th>Monto solicitado</th>
                <th>Monto rendido</th>
                <th title="Saldo a favor de ABROS">Saldo ABROS</th>
                <th title="Saldo a favor del empleado">Saldo Empleado</th>
                {{--<th width="8%">Acciones</th>--}}
            </tr>
            </thead>
            <tbody>
                @foreach($stipend_requests as $stipend)
                    <tr style="{{ $stipend->status=='Rechazada' ? 'background-color: lightgrey' : '' }}">
                        <td>
                            <a href="/stipend_request/{{ $stipend->id }}" title="Ver información de solicitud">
                                {{ $stipend->code }}
                            </a>
                        </td>
                        <td>{{ $stipend->assignment && $stipend->assignment->cost_center > 0 ? $stipend->assignment->cost_center : '' }}</td>
                        <td>{{ date_format($stipend->created_at,'d-m-Y') }}</td>
                        <td align="right">{{ number_format($stipend->total_amount,2).' Bs' }}</td>
                        <td align="right">{{ number_format($stipend->additional,2).' Bs' }}</td>
                        <td>{{ $stipend->reason }}</td>
                        <td>{{ $stipend->date_from->format('d-m-Y') }}</td>
                        <td>{{ $stipend->date_to->format('d-m-Y') }}</td>
                        <td>
                            {{ \App\StipendRequest::$stats[$stipend->status] }}
                            @if($stipend->status == 'Sent' && $stipend->xls_gen != '')
                                &ensp;
                                <span title="Se registrará el pago de todas las solicitudes enviadas en un mismo archivo">
                                    {{ $stipend->xls_gen }}
                                </span>
                            @endif
                        </td>
                        <td align="right">{{ number_format($stipend->total_amount + $stipend->additional,2).' Bs' }}</td>
                        @if ($stipend->rendicion_viatico)
                            <td align="right">{{ number_format($stipend->rendicion_viatico->total_rendicion, 2).' Bs' }}</td>
                            <td align="right">
                                {{ $stipend->rendicion_viatico->saldo_favor_empresa > 0 ? number_format($stipend->rendicion_viatico->saldo_favor_empresa, 2).' Bs' : '-' }}
                            </td>
                            <td align="right">
                                {{ $stipend->rendicion_viatico->saldo_favor_persona > 0 ? number_format($stipend->rendicion_viatico->saldo_favor_persona, 2).' Bs' : '-' }}
                            </td>
                        @else
                            <td align="right">{{ '-' }}</td>
                            <td align="right">{{ number_format($stipend->total_amount + $stipend->additional,2).' Bs' }}</td>
                            <td align="right">{{ '-' }}</td>
                        @endif
                        {{--
                        <td align="center">
                            @if($stipend->status != 'Rejected' && $stipend->status != 'Documented')
                                @if(($stipend->status=='Sent')&&$user->action->prj_vtc_pmt)
                                    <a href="{{ '/stipend_request/close?mode=complete&id='.$stipend->id }}"
                                    title="Confirmar pago y dar por concluída esta solicitud" class="confirm_close">
                                        <i class="fa fa-usd"></i>
                                    </a>
                                    &ensp;
                                @elseif(($stipend->status=='Approved_tech')&&$user->action->prj_vtc_mod )
                                    <a href="/stipend_request/request_adm/{{ '?type=excel&id='.$stipend->id }}"
                                    title="Enviar email de solicitud de viáticos al área administrativa">
                                        <i class="fa fa-send"></i>
                                    </a>
                                    &ensp;
                                @elseif($stipend->status=='Observed'&&($user->id==$stipend->user_id||
                                    $user->action->prj_vtc_mod))
                                    <a href="/stipend_request/{{ $stipend->id }}/edit" title="Modificar solicitud observada">
                                        <i class="fa fa-pencil-square-o"></i>
                                    </a>
                                    &ensp;
                                @elseif($stipend->status=='Pending'&&($user->id==$stipend->user_id||$user->action->prj_vtc_mod))
                                    <a href="/stipend_request/stat/{{ '?mode=observe&id='.$stipend->id }}"
                                    title="Observar solicitud de viáticos">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    &ensp;
                                    @if($user->action->prj_vtc_mod)
                                        <a href="/stipend_request/stat/{{ '?mode=approve&id='.$stipend->id }}"
                                        title="Aprobar solicitud de viáticos">
                                            <i class="fa fa-check"></i>
                                        </a>
                                        &ensp;
                                    @endif
                                @endif

                                @if(($stipend->status=='Observed'||$stipend->status=='Pending')&&
                                    ($user->id==$stipend->user_id||$user->action->prj_vtc_mod))
                                    <a href="/stipend_request/stat/{{ '?mode=reject&id='.$stipend->id }}"
                                    title="Rechazar solicitud de viáticos">
                                        <i class="fa fa-times"></i>
                                    </a>
                                @endif

                                @if ($stipend->status == 'Completed' &&
                                (($employee_record && $employee_record->access_id == $user->id) || $user->priv_level == 4))
                                    @if ($stipend->rendicion_viatico)
                                    <a href="/rendicion_viatico/{{ $stipend->rendicion_viatico->id }}"
                                        title="Complete la rendición de esta solicitud">
                                        <i class="fa fa-file-o"></i>
                                    </a>
                                    @else
                                    <a href="/rendicion_viatico/rendir/{{ $stipend->id }}"
                                        title="Iniciar rendición de viáticos">
                                        <i class="fa fa-file"></i>
                                    </a>
                                    @endif
                                @endif
                            @endif
                        </td>
                        --}}
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="col-sm-12 mg10" align="center">
        {!! $stipend_requests->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="formal_table table_blue" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user' => $user, 'service' => $service, 'table' => 'employee_account_info', 'id' => ($employee_record->id ?: 0)))
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

         /*
         $('.confirm_applied').on('click', function () {
         return confirm('Está seguro de que desea registrar el envío de documentación para aplicar a la ' +
         'licitación indicada?');
         });

         $('.confirm_assignment').on('click', function () {
         return confirm('Está seguro de que desea crear una asignación de trabajo de este proyecto?');
         });
         */
    </script>
@endsection
