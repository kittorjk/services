<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 06/09/2017
 * Time: 11:21 AM
 */
?>

@extends('layouts.actives_structure')

@section('header')
    @parent
    <style>
        .dropdown-menu-prim > li > a {
            width: 200px;
        }
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    <div class="btn-group">
        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
            <i class="fa fa-exchange"></i> Requerimientos <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li>
                <a href="{{ '/device_requirement'.($dvc ? '?dvc='.$dvc : '') }}"><i class="fa fa-refresh"></i> Recargar página</a>
            </li>
            <li><a href="{{ '/device' }}"><i class="fa fa-arrow-right"></i> Ver equipos</a></li>
            <li><a href="{{ '/operator' }}"><i class="fa fa-arrow-right"></i> Ver asignaciones </a></li>
            @if($user->action->acv_dvc_req /*$user->work_type=='Almacén'||$user->priv_level>=2*/)
                <li><a href="{{ '/device_requirement/create' }}"><i class="fa fa-plus"></i> Nuevo requerimiento </a></li>
            @endif
            @if($user->action->acv_dvc_exp /*$user->priv_level==4*/)
                <li class="divider"></li>
                <li><a href="{{ '/excel/device_requirements' }}"><i class="fa fa-file-excel-o"></i> Exportar a Excel</a></li>
            @endif
        </ul>
    </div>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchBox">
        <i class="fa fa-search"></i> Buscar
    </button>
@endsection

@section('content')

    @if($requirements->where('status', 1)->count()!=0)
        <div class="col-sm-12 mg10">
            <div class="alert alert-info" align="center">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <i class="fa fa-info-circle fa-2x pull-left"></i>
                {{ $requirements->where('status', 1)->count()==1 ? 'Existe 1 requerimiento en proceso' :
                        'Existen '.$requirements->where('status', 1)->count().' requerimientos en proceso' }}
            </div>
        </div>
    @endif

    <div class="col-sm-12 mg10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10 mg-tp-px-10">
        <p>Requerimientos encontrados: {{ $requirements->total() }}</p>

        <table class="fancy_table table_brown tablesorter" id="fixable_table">
            <thead>
            <tr>
                <th>Fecha</th>
                <th>Requerimiento</th>
                <th width="20%">Equipo requerido</th>
                <th># Serie</th>
                <th>Resp. previo</th>
                <th>Entregar a</th>
                <th width="18%">Motivo</th>
                <th width="10%">Tipo de req.</th>
                <th>Estado</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($requirements as $requirement)
                <tr>
                    <td>{{ date_format($requirement->created_at,'Y-m-d') }}</td>
                    <td>
                        <a href="/device_requirement/{{ $requirement->id }}">
                            {{ $requirement->code }}
                        </a>
                    </td>
                    <td>
                        <a href="/device/{{ $requirement->device->id }}">
                            {{ $requirement->device->type.' '.$requirement->device->model }}
                        </a>
                    </td>
                    <td>{{ $requirement->device->serial }}</td>
                    <td>
                        {{ $requirement->person_from ? $requirement->person_from->name : ($requirement->device->last_operator ?
                            $requirement->device->last_operator->name : '') }}

                        @if(($requirement->status==1&&($requirement->from_id==$user->id||
                            $requirement->for_id==$user->id||$user->priv_level==4))
                            /*&&($requirement->type=='transfer_tech'||$requirement->type=='devolution')||
                            (($requirement->type=='borrow'||$requirement->type=='transfer_wh')&&
                            $user->work_type=='Almacén'&&$requirement->branch_origin==$user->branch)*/)
                            <div class="pull-right">
                                <a href="/operator/create{{ '?req='.$requirement->id }}" style="text-decoration: none;"
                                   title="Registrar entrega de equipo">
                                    <i class="fa fa-file"></i>
                                </a>
                                <a href="{{ '/device_requirement/reject/'.$requirement->id }}" style="text-decoration: none;"
                                   title="Rechazar requerimiento de equipo">
                                    <i class="fa fa-ban"></i>
                                </a>
                            </div>
                        @endif
                    </td>
                    <td>
                        {{ $requirement->person_for ? $requirement->person_for->name : '' }}
                        {{--
                        @if($requirement->status==2&&($requirement->for_id==$user->id||$user->priv_level==4))
                            <a href="/operator/confirm/{{ $requirement->id }}" style="text-decoration: none;"
                               title="Confirmar recepción de equipo" class="pull-right">
                                <i class="fa fa-check-circle"></i>
                            </a>
                        @endif
                        --}}
                    </td>
                    <td>{{ $requirement->reason }}</td>
                    <td>{{ App\DeviceRequirement::$types[$requirement->type] }}</td>
                    <td>{{ App\DeviceRequirement::$stat_names[$requirement->status] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-sm-12 mg10" align="center">
        {!! $requirements->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="fancy_table table_brown" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'device_requirements','id'=>0))
    </div>

@endsection

@section('footer')
    @parent
@endsection

@section('javascript')
    <script src="{{ asset('app/js/fix_table_header.js') }}"></script> {{-- For fixed header --}}
    <script src="{{ asset('app/js/set_current_url.js') }}"></script> {{-- For recording current url --}}
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#alert').delay(2000).fadeOut('slow');

        $(function(){
            $('#fixable_table').tablesorter({
                cssAsc: 'headerSortUp',
                cssDesc: 'headerSortDown',
                cssNone: '',
                dateFormat: 'uk'
            });
        });
    </script>
@endsection
