<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 02/10/2017
 * Time: 01:17 PM
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
                <a href="{{ '/line_requirement' }}"><i class="fa fa-refresh"></i> Recargar página</a>
            </li>
            <li><a href="{{ '/corporate_line' }}"><i class="fa fa-arrow-right"></i> Ver lista de líneas</a></li>
            <li><a href="{{ '/line_assignation' }}"><i class="fa fa-arrow-right"></i> Ver asignaciones </a></li>
            @if($user->action->acv_ln_req /*$user->priv_level>=1*/)
                <li><a href="{{ '/line_requirement/create' }}"><i class="fa fa-plus"></i> Nuevo requerimiento</a></li>
            @endif
            @if($user->priv_level>=3)
                <li class="divider"></li>
                <li><a href="{{ '/excel/corp_line_requirements' }}"><i class="fa fa-file-excel-o"></i> Exportar a Excel</a></li>
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
                {{ $requirements->where('status', 1)->count()==1 ? 'Existe 1 requerimiento pendiente' :
                        'Existen '.$requirements->where('status', 1)->count().' requerimientos pendientes' }}
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
                <th>Solicitado por</th>
                <th>Entregar a</th>
                <th width="18%">Motivo</th>
                <th>Estado</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($requirements as $requirement)
                <tr>
                    <td>{{ date_format($requirement->created_at,'Y-m-d') }}</td>
                    <td>
                        <a href="/line_requirement/{{ $requirement->id }}">
                            {{ $requirement->code }}
                        </a>
                        @if($user->action->edt /*$user->priv_level==4*/)
                            <a href="/line_requirement/{{ $requirement->id }}/edit">
                                <i class="fa fa-pencil-square"></i>
                            </a>
                        @endif
                    </td>
                    <td>
                        {{ $requirement->user ? $requirement->user->name : 'N/E' }}

                        @if($requirement->status==1&&($user->action->acv_ln_asg
                            /*($user->priv_level==2&&$user->area=='Gerencia General')*/||$user->priv_level==4))
                            <div class="pull-right">
                                <a href="/line_assignation/create{{ '?req='.$requirement->id }}" style="text-decoration: none;"
                                   title="Registrar entrega de una línea">
                                    <i class="fa fa-file"></i>
                                </a>
                                <a href="{{ '/line_requirement/reject/'.$requirement->id }}" style="text-decoration: none;"
                                   title="Rechazar requerimiento de línea">
                                    <i class="fa fa-ban"></i>
                                </a>
                            </div>
                        @endif
                    </td>
                    <td>
                        {{ $requirement->person_for ? $requirement->person_for->name : '' }}
                    </td>
                    <td>{{ $requirement->reason }}</td>
                    <td>{{ App\VehicleRequirement::$stat_names[$requirement->status] }}</td>
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
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'corp_line_requirements','id'=>0))
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
