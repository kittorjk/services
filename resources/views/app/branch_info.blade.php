<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 14/12/2017
 * Time: 03:19 PM
 */
?>

@extends('layouts.adm_structure')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    <li><a href="#">&ensp;<i class="fa fa-building-o"></i> SUCURSALES <span class="caret"></span>&ensp;</a>
        <ul class="sub-menu">
            <li><a href="{{ '/branch' }}"><i class="fa fa-bars fa-fw"></i> Ver todo </a></li>
            @if($user->action->adm_bch_mod/*$user->priv_level==4*/)
                <li><a href="{{ '/branch/create' }}"><i class="fa fa-user-plus fa-fw"></i> Nueva sucursal </a></li>
            @endif
        </ul>
    </li>
     <li>
        <a data-toggle="modal" href="#searchBox">&ensp;<i class="fa fa-search"></i> BUSCAR&ensp;</a>
    </li>
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

        <div class="panel panel-10gray">
            <div class="panel-heading" align="center">
                <div class="panel-title">Información de sucursal</div>
            </div>
            <div class="panel-body">
                <div class="col-sm-12 mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                    <a href="{{ '/branch' }}" class="btn btn-warning" title="Volver a la tabla de sucursales">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <div class="col-sm-12 mg10 mg-tp-px-10">

                    <table class="table table-striped table-hover table-bordered">
                        <thead>
                        <tr>
                            <th width="40%">Nombre:</th>
                            <td>{{ $branch->name }}</td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <th width="40%">Ciudad:</th>
                            <td>{{ $branch->city }}</td>
                        </tr>
                        <tr>
                            <th>Estado de registro</th>
                            <td>{{ $branch->active==1 ? 'Activo' : 'Deshabilitado' }}</td>
                        </tr>
                        @if($branch->address!='')
                            <tr>
                                <th>Dirección</th>
                                <td>{{ $branch->address }}</td>
                            </tr>
                        @endif
                        @if($branch->phone>0)
                            <tr>
                                <th>Teléfono</th>
                                <td>{{ $branch->phone }}</td>
                            </tr>
                        @endif
                        @if($branch->alt_phone>0)
                            <tr>
                                <th>Teléfono alternativo</th>
                                <td>{{ $branch->alt_phone }}</td>
                            </tr>
                        @endif
                        <tr>
                            <th>Encargado de sucursal</th>
                            <td>
                                {{ $branch->head_person ? $branch->head_person->first_name.' '.
                                    $branch->head_person->last_name : 'N/E' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Registro creado el</th>
                            <td>{{ date_format($branch->created_at, 'd-m-Y') }}</td>
                        </tr>
                        <tr>
                            <th>Última actualización</th>
                            <td>{{ date_format($branch->updated_at, 'd-m-Y') }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                @if($user->action->adm_bch_mod /*$user->priv_level==4*/)
                    <div class="col-sm-12 mg10" align="center">
                        <a href="/branch/{{ $branch->id }}/edit" class="btn btn-success">
                            <i class="fa fa-pencil-square-o"></i> Actualizar datos
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'branches','id'=>0))
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
