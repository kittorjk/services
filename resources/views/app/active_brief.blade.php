@extends('layouts.actives_structure')

@section('header')
    @parent
    <style>
        .dropdown-menu-prim > li > a {
            width: 200px;
        }
    </style>
@endsection

@section('menu_options')
    {{--
    <a href="/active" class="btn btn-primary"><i class="fa fa-refresh"></i> Recargar página </a>
    --}}
    <div class="btn-group">
        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
            <i class="fa fa-car"></i> Vehículos <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li><a href="{{ '/vehicle' }}"><i class="fa fa-arrow-right fa-fw"></i> Ver vehículos </a></li>
            <li><a href="{{ '/driver' }}"><i class="fa fa-arrow-right fa-fw"></i> Ver asignaciones </a></li>
            <li><a href="{{ '/vehicle_requirement' }}"><i class="fa fa-arrow-right fa-fw"></i> Ver requerimientos </a></li>
            @if($user->action->acv_vhc_add /*$user->work_type=='Transporte'||$user->priv_level==4*/)
                <li><a href="{{ '/vehicle/create' }}"><i class="fa fa-plus fa-fw"></i> Registrar vehículo </a></li>
            @endif
            @if($user->action->acv_vhc_req)
                {{--<li><a href="/driver/create"><i class="fa fa-exchange fa-fw"></i> Asignar vehículo </a></li>--}}
                <li>
                    <a href="{{ '/vehicle_requirement/create' }}"><i class="fa fa-exchange fa-fw"></i> Nuevo requerimiento</a>
                </li>
            @endif
        </ul>
    </div>
    <div class="btn-group">
        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
            <i class="fa fa-laptop"></i> Equipos <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li><a href="{{ '/device' }}"><i class="fa fa-arrow-right fa-fw"></i> Ver equipos </a></li>
            <li><a href="{{ '/operator' }}"><i class="fa fa-arrow-right fa-fw"></i> Ver asignaciones </a></li>
            <li><a href="{{ '/device_requirement' }}"><i class="fa fa-arrow-right fa-fw"></i> Ver requerimientos </a></li>
            @if($user->action->acv_dvc_add /*$user->work_type=='Almacén'||$user->priv_level==4*/)
                <li><a href="{{ '/device/create' }}"><i class="fa fa-plus fa-fw"></i> Registrar equipo </a></li>
            @endif
            @if($user->action->acv_dvc_req)
                {{--<li><a href="/operator/create"><i class="fa fa-exchange fa-fw"></i> Asignar equipo </a></li>--}}
                <li>
                    <a href="{{ '/device_requirement/create' }}"><i class="fa fa-exchange fa-fw"></i> Nuevo requerimiento </a>
                </li>
            @endif
        </ul>
    </div>
    <div class="btn-group">
        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
            <i class="fa fa-phone"></i> Líneas corporativas <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li><a href="{{ '/corporate_line' }}"><i class="fa fa-arrow-right fa-fw"></i> Ver líneas </a></li>
            <li><a href="{{ '/line_assignation' }}"><i class="fa fa-arrow-right fa-fw"></i> Ver asignaciones </a></li>
            <li><a href="{{ '/line_requirement' }}"><i class="fa fa-arrow-right fa-fw"></i> Ver requerimientos </a></li>
            @if($user->action->acv_ln_add /*($user->priv_level>=2&&$user->area=='Gerencia General')||$user->priv_level==4*/)
                <li><a href="{{ '/corporate_line/create' }}"><i class="fa fa-plus fa-fw"></i> Agregar nueva línea </a></li>
            @endif
            @if($user->action->acv_ln_asg)
                <li><a href="{{ '/line_assignation/create' }}"><i class="fa fa-exchange fa-fw"></i> Asignar línea </a></li>
            @endif
            @if($user->action->acv_ln_req /*$user->priv_level>=1*/)
                <li>
                    <a href="{{ '/line_requirement/create' }}"><i class="fa fa-exchange fa-fw"></i> Nuevo requerimiento </a>
                </li>
            @endif
        </ul>
    </div>
    <a href="{{ '/maintenance' }}" class="btn btn-primary"><i class="fa fa-wrench"></i> Activos en mantenimiento</a>
@endsection

@section('content')
    @if($vehicle_maintenance_counter!=0&&($user->priv_level>=2||$user->work_type=='Transporte'))
        <div class="col-sm-12 mg10">
            <div class="alert alert-warning" align="center">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <i class="fa fa-warning fa-2x pull-left"></i>
                <a href="{{ '/maintenance/request/vehicle' }}" style="color: inherit">
                    {{ $vehicle_maintenance_counter==1 ? '1 vehículo requiere mantenimiento' :
                        $vehicle_maintenance_counter.' vehículos requieren mantenimiento' }}
                </a>
            </div>
        </div>
    @endif

    @if($device_maintenance_counter!=0&&($user->priv_level>=2||$user->work_type=='Almacén'))
        <div class="col-sm-12 mg10">
            <div class="alert alert-warning" align="center">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <i class="fa fa-warning fa-2x pull-left"></i>
                <a href="{{ '/maintenance/request/device' }}" style="color: inherit">
                    {{ $device_maintenance_counter==1 ? '1 equipo requiere mantenimiento' :
                        $device_maintenance_counter.' equipos requieren mantenimiento' }}
                </a>
            </div>
        </div>
    @endif

    <div class="col-sm-12 mg10 mg-tp-px-10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-6 mg10">
        {{--
        <p class="col-sm-6">Asignaciones de vehículo recientes:</p>
        <p class="col-sm-6" align="right"><a href="/driver">Vertodo</a></p>
        --}}

        <div class="mg-btm-px-10 col-md-12 col-sm-12 col-xs-12">
            <div class="panel panel-purple">
                <div class="panel-heading">
                    <div class="panel-title" align="center">Vehículos</div>
                </div>
                <div class="panel-body" align="center">

                    <div class="col-md-6">
                        <a href="{{ '/vehicle' }}" style="color: inherit">
                            <span class="fa-stack fa-lg fa-5x">
                                <i class="fa fa-square-o fa-stack-2x"></i>
                                <i class="fa fa-car fa-stack-1x"></i>
                            </span>

                            {{--<i class="fa fa-car fa-5x" style="vertical-align: middle"></i>--}}
                        </a>
                    </div>

                    <div class="col-md-6" align="left">
                        <p>
                            <a href="{{ '/vehicle' }}">
                                <i class="fa fa-arrow-right fa-fw"></i> Ver vehículos
                            </a>
                        </p>
                        <p><a href="{{ '/driver' }}"><i class="fa fa-arrow-right fa-fw"></i> Ver asignaciones </a></p>
                        <p>
                            <a href="{{ '/vehicle_requirement' }}"><i class="fa fa-arrow-right fa-fw"></i> Ver requerimientos </a>
                        </p>
                        @if($user->action->acv_vhc_add /*$user->work_type=='Transporte'||$user->priv_level==4*/)
                            <p><a href="{{ '/vehicle/create' }}"><i class="fa fa-plus fa-fw"></i> Registrar vehículo </a></p>
                        @endif
                        @if($user->action->acv_vhc_req)
                            <p>
                                <a href="{{ '/vehicle_requirement/create' }}">
                                    <i class="fa fa-exchange fa-fw"></i> Nuevo requerimiento
                                </a>
                            </p>
                        @endif
                    </div>

                    <br>

                    <table class="fancy_table table_10gray">
                        <thead>
                        <tr>
                            <th colspan="2">
                                Últimas asignaciones de vehículo
                                <a href="{{ '/driver' }}" class="pull-right" style="color: inherit">
                                    Ver todo <i class="fa fa-arrow-right"></i>
                                </a>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($drivers as $driver)
                            <tr>
                                <td width="18%">
                                    <a href="/driver/{{ $driver->id }}" title="Ver información de asignación">
                                        {{ date_format(new \DateTime($driver->date), 'd-m-Y') }}
                                    </a>
                                </td>
                                <td>
                                    {{ 'Se entrega '.$driver->vehicle->type.' '.$driver->vehicle->model.
                                        ' con placa '.$driver->vehicle->license_plate.' a ' }}
                                    <span style="{{ $driver->confirmation_flags[3]==0 ? 'color:darkred;' : '' }}"
                                          title="{{ $driver->confirmation_flags[3]==0 ? 'Pendiente de confirmación' :
                                           'Confirmado' }}">
                                        <strong>
                                            {{ $driver->receiver->name }}
                                        </strong>
                                    </span>
                                    @if($driver->confirmation_flags[3]==0)
                                        @if($driver->who_receives==$user->id||$user->priv_level==4)
                                            <a href="{{ '/driver/confirm/'.$driver->id }}" style="text-decoration: none;"
                                               title="Confirmar recepción de vehículo" class="pull-right">
                                                <i class="fa fa-check-circle"></i>
                                            </a>
                                        @else
                                            <i class="fa fa-warning pull-right" title="Pendiente de confirmación" style="color: darkred"></i>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        @if($drivers->count()==0)
                            <tr>
                                <td colspan="2" align="center">
                                    No existen registros que mostrar
                                </td>
                            </tr>
                        @endif
                        </tbody>
                    </table>

                    <br>
                    {{--<p class="col-sm-6">Registros de estado de vehículo recientes:</p>--}}

                    {{--
                    <table class="fancy_table table_blue">
                        <thead>
                        <tr>
                            <th colspan="2">Últimos registros de estado de vehículo</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($condition_records as $condition_record)
                            <tr>
                                <td width="16%">
                                    <a href="/vehicle_condition/{{ $condition_record->vehicle_id }}"
                                        title="Ver información de registro">
                                        {{ date_format(new \DateTime($condition_record->created_at), 'd-m-Y') }}
                                    </a>
                                </td>
                                <td>
                                    {{ $condition_record->user->name.' agregó un registro de estado del vehículo '.
                                        $condition_record->vehicle->type.' '.$condition_record->vehicle->model.
                                        ' con placa '.$condition_record->vehicle->license_plate }}
                                </td>
                            </tr>
                        @endforeach
                        @if($condition_records->count()==0)
                            <tr>
                                <td colspan="2" align="center">
                                    No existen registros que mostrar
                                </td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                    --}}

                    <table class="fancy_table table_10gray">
                        <thead>
                        <tr>
                            <th colspan="2">
                                Últimos requerimientos de vehículo
                                <a href="{{ '/vehicle_requirement' }}" class="pull-right" style="color: inherit">
                                    Ver todo <i class="fa fa-arrow-right"></i>
                                </a>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($vehicle_requirements as $requirement)
                            <tr>
                                <td width="18%">
                                    <a href="{{ '/vehicle_requirement/'.$requirement->id }}" title="Ver detalle de requerimiento">
                                        {{ date_format($requirement->created_at, 'd-m-Y') }}
                                    </a>
                                </td>
                                <td>
                                    {{ ($requirement->user ? $requirement->user->name : 'Se').' solicita '.
                                        $requirement->vehicle->type.' '.$requirement->vehicle->model.' con placa '.
                                        $requirement->vehicle->license_plate.' para '.$requirement->person_for->name }}

                                    @if($requirement->status==1)
                                        @if($requirement->from_id==$user->id||$user->priv_level==4)
                                            <div class="pull-right">
                                                <a href="{{ '/driver/create?req='.$requirement->id }}" style="text-decoration: none;"
                                                   title="Completar este requerimiento / Registrar asignación de vehículo">
                                                    <i class="fa fa-file"></i>
                                                </a>
                                                <a href="{{ '/vehicle_requirement/reject/'.$requirement->id }}" style="text-decoration: none;"
                                                   title="Rechazar requerimiento de vehículo">
                                                    <i class="fa fa-ban"></i>
                                                </a>
                                            </div>
                                        @else
                                            <i class="fa fa-hourglass-2 pull-right" title="Requerimiento en proceso"></i>
                                        @endif
                                    @elseif($requirement->status==0)
                                        <i class="fa fa-times-circle pull-right" title="Requerimiento rechazado"></i>
                                    @elseif($requirement->status==2)
                                        <i class="fa fa-check-square pull-right" title="Completado"></i>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        @if($vehicle_requirements->count()==0)
                            <tr>
                                <td colspan="2" align="center">
                                    No existen registros que mostrar
                                </td>
                            </tr>
                        @endif
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 mg10">
        {{--
        <p class="col-sm-6">Asignaciones de equipo recientes:</p>
        <p class="col-sm-6" align="right"><a href="/operator">Vertodo</a></p>
        --}}

        <div class="mg-btm-px-10 col-md-12 col-sm-12 col-xs-12">
            <div class="panel panel-strongBrown">
                <div class="panel-heading">
                    <div class="panel-title" align="center">Equipos</div>
                </div>
                <div class="panel-body" align="center">

                    <div class="col-md-6">
                        <a href="{{ '/device' }}" style="color: inherit">
                            <span class="fa-stack fa-lg fa-5x">
                                <i class="fa fa-laptop fa-stack-2x"></i>
                            </span>
                        </a>
                    </div>

                    <div class="col-md-6" align="left">
                        <p><a href="{{ '/device' }}"><i class="fa fa-arrow-right fa-fw"></i> Ver equipos </a></p>
                        <p><a href="{{ '/operator' }}"><i class="fa fa-arrow-right fa-fw"></i> Ver asignaciones </a></p>
                        <p>
                            <a href="{{ '/device_requirement' }}">
                                <i class="fa fa-arrow-right fa-fw"></i> Ver requerimientos
                            </a>
                        </p>
                        @if($user->action->acv_dvc_add /*$user->work_type=='Almacén'||$user->priv_level==4*/)
                            <p><a href="{{ '/device/create' }}"><i class="fa fa-plus fa-fw"></i> Registrar equipo </a></p>
                        @endif
                        @if($user->action->acv_dvc_req)
                            <p>
                                <a href="{{ '/device_requirement/create' }}">
                                    <i class="fa fa-exchange fa-fw"></i> Nuevo requerimiento
                                </a>
                            </p>
                        @endif
                    </div>

                    <table class="fancy_table table_10gray">
                        <thead>
                        <tr>
                            <th colspan="2">
                                Últimas asignaciones de equipo
                                <a href="{{ '/operator' }}" class="pull-right" style="color: inherit">
                                    Ver todo <i class="fa fa-arrow-right"></i>
                                </a>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($operators as $operator)
                            <tr>
                                <td width="18%">
                                    <a href="/operator/{{ $operator->id }}" title="Ver información de asignación">
                                        {{ date_format(new \DateTime($operator->date), 'd-m-Y') }}
                                    </a>
                                </td>
                                <td>
                                    {{ 'Se entrega '.$operator->device->type.' '.$operator->device->model.
                                        ' con S/N '.$operator->device->serial.' a ' }}
                                    <span style="{{ $operator->confirmation_flags[3]==0 ? 'color:darkred;' : '' }}"
                                          title="{{ $operator->confirmation_flags[3]==0 ? 'Pendiente de confirmación' :
                                           'Confirmado' }}">
                                        <strong>
                                            {{ $operator->receiver->name }}
                                        </strong>
                                    </span>
                                    @if($operator->confirmation_flags[3]==0)
                                        @if($operator->who_receives==$user->id||$user->priv_level==4)
                                            <a href="{{ '/operator/confirm/'.$operator->id }}" style="text-decoration: none;"
                                               title="Confirmar recepción de equipo" class="pull-right">
                                                <i class="fa fa-check-circle"></i>
                                            </a>
                                        @else
                                            <i class="fa fa-warning pull-right" title="Pendiente de confirmación" style="color: darkred"></i>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        @if($operators->count()==0)
                            <tr>
                                <td colspan="2" align="center">
                                    No existen registros que mostrar
                                </td>
                            </tr>
                        @endif
                        </tbody>
                    </table>

                    <br>

                    <table class="fancy_table table_10gray">
                        <thead>
                        <tr>
                            <th colspan="2">
                                Últimos requerimientos de equipo
                                <a href="{{ '/device_requirement' }}" class="pull-right" style="color: inherit">
                                    Ver todo <i class="fa fa-arrow-right"></i>
                                </a>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($device_requirements as $requirement)
                            <tr>
                                <td width="18%">
                                    <a href="{{ '/device_requirement/'.$requirement->id }}" title="Ver detalle de requerimiento">
                                        {{ date_format($requirement->created_at, 'd-m-Y') }}
                                    </a>
                                </td>
                                <td>
                                    {{ ($requirement->user ? $requirement->user->name : 'Se').' solicita '.
                                        $requirement->device->type.' '.$requirement->device->model.' con S/N '.
                                        $requirement->device->serial.' para '.$requirement->person_for->name }}

                                    @if($requirement->status==1)
                                        @if($requirement->from_id==$user->id||$user->priv_level==4)
                                            <div class="pull-right">
                                                <a href="{{ '/operator/create?req='.$requirement->id }}" style="text-decoration: none;"
                                                   title="Completar este requerimiento / Registrar asignación de equipo">
                                                    <i class="fa fa-file"></i>
                                                </a>
                                                <a href="{{ '/device_requirement/reject/'.$requirement->id }}" style="text-decoration: none;"
                                                   title="Rechazar requerimiento de equipo">
                                                    <i class="fa fa-ban"></i>
                                                </a>
                                            </div>
                                        @else
                                            <i class="fa fa-hourglass-2 pull-right" title="Requerimiento en proceso"></i>
                                        @endif
                                    @elseif($requirement->status==0)
                                        <i class="fa fa-times-circle pull-right" title="Requerimiento rechazado"></i>
                                    @elseif($requirement->status==2)
                                        <i class="fa fa-check-square pull-right" title="Completado"></i>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        @if($device_requirements->count()==0)
                            <tr>
                                <td colspan="2" align="center">
                                    No existen registros que mostrar
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
    @parent
@endsection

@section('javascript')
    <script>
        $('#alert').delay(2000).fadeOut('slow');
    </script>
@endsection
