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
            <i class="fa fa-file"></i> Cuentas de empleados <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li><a href="{{ '/employee_account' }}"><i class="fa fa-refresh fa-fw"></i> Recargar página </a></li>
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
            @if($user->action->prj_vtc_exp)
                <li class="divider"></li>
                <li class="dropdown-submenu">
                    <a href="#" data-toggle="dropdown"><i class="fa fa-file-excel-o"></i> Exportar a Excel</a>
                    <ul class="dropdown-menu dropdown-menu-prim">
                        <li>
                            <a href="{{ '/excel/stipend_requests' }}">
                                <i class="fa fa-file-excel-o fa-fw"></i> Tabla de solicitudes
                            </a>
                        </li>
                        @if ($asg)
                            <li>
                                <a href="{{ '/excel/stipend_requests/'.$asg }}">
                                    <i class="fa fa-file-excel-o fa-fw"></i> Solicitudes por asignación
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
            --}}
        </ul>
    </div>
    <a href="{{ '/rendicion_viatico' }}" class="btn btn-primary" title="Ver rendiciones de viáticos">
      <i class="fa fa-file"></i> Rendiciones
    </a>
    <a href="{{ '/stipend_request' }}" class="btn btn-primary" title="Ver solicitudes de viaticos">
      <i class="fa fa-file"></i> Solicitudes
    </a>
    {{--
    @if ($user->priv_level >= 2)
      <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchBox">
        <i class="fa fa-search"></i> Buscar
      </button>
    @endif
    --}}
@endsection

@section('content')
    {{--
    @if($waiting_payment>0)
        <div class="col-sm-12 mg10">
            <div class="alert alert-info" align="center">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <i class="fa fa-info-circle fa-2x pull-left"></i>
                <a href="{{ '/stipend_request/payment_list' }}" style="color: inherit;">
                    {{ $waiting_payment==1 ? 'Existe 1 solicitud de viáticos pendiente de pago' :
                         'Existen '.$waiting_payment.' solicitudes de viáticos pendientes de pago' }}
                </a>
            </div>
        </div>
    @endif
    --}}

    <div class="col-sm-12 mg10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10">
        <p>Registros encontrados: {{ $employees->total() }}</p>

        <table class="formal_table table_blue tablesorter" id="fixable_table">
            <thead>
            <tr>
                <th>Núm</th>
                <th width="25%">Apellidos</th>
                <th width="25%">Nombres</th>
                <th># Solicitudes</th>
                <th># Rendiciones</th>
                <th width="10%">Estado</th>
                <th width="8%">Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php $i = 0; ?>
            @foreach ($employees as $employee)
                <tr>
                    <td>{{ ++$i }}</td>
                    <td>
                        <a href="/employee_account/{{ $employee->id }}" title="Ver detalle de empleado">
                            {{ $employee->last_name }}
                        </a>
                        {{--<a href="/stipend_request/{{ $stipend->id }}" title="Ver información de solicitud">
                            {{ $stipend->code }}
                        </a>--}}
                    </td>
                    <td>
                        <a href="/employee_account/{{ $employee->id }}" title="Ver detalle de empleado">
                            {{ $employee->first_name }}
                        </a>
                        {{--<a href="/stipend_request/{{ $stipend->id }}" title="Ver información de solicitud">
                            {{ $stipend->code }}
                        </a>--}}
                    </td>
                    <td>{{ $employee->stipend_requests->count() }}</td>
                    <td>
                        <?php $j = 0 ?>
                        @foreach ($employee->stipend_requests as $request)
                            @if (!$request->rendicion_viatico)
                                <?php $j++ ?>
                            @endif
                        @endforeach
                        {{ $j }}
                    </td>
                    <td>{{ '' }}</td>
                    <td>{{ '' }}</td>
                    {{--
                    <td align="center">
                        @if($stipend->status != 'Rejected' && $stipend->status != 'Documented')
                            @if(($stipend->status=='Sent')&&$user->action->prj_vtc_pmt)
                                <a href="{{ '/stipend_request/close?mode=complete&id='.$stipend->id }}"
                                   title="Confirmar pago y dar por concluída esta solicitud" class="confirm_close">
                                    <i class="fa fa-usd"></i>
                                </a>
                                &ensp;
                            @elseif(($stipend->status=='Approved_tech')&&$user->action->prj_vtc_mod)
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
        {!! $employees->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="formal_table table_blue" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    {{--
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'stipend_requests','id'=>($asg ?: 0)))
    </div>
    --}}

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

        $(function() {
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
