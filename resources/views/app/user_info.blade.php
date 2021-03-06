<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 08/05/2017
 * Time: 04:14 PM
 */
?>

@extends('layouts.adm_structure')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    <li><a href="#">&ensp;<i class="fa fa-users"></i> USUARIOS <span class="caret"></span>&ensp;</a>
        <ul class="sub-menu">
            <li><a href="{{ '/user' }}"><i class="fa fa-bars fa-fw"></i> Ver todos </a></li>
            <li><a href="{{ '/user/create' }}"><i class="fa fa-user-plus fa-fw"></i> Agregar usuario </a></li>
        </ul>
    </li>
    <li>
        <!--<a href="/search/users/0"><i class="fa fa-search"></i> BUSCAR </a>-->
        <a data-toggle="modal" href="#searchBox">&ensp;<i class="fa fa-search"></i> BUSCAR&ensp;</a>
    </li>
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

        <div class="panel panel-10gray">
            <div class="panel-heading" align="center">
                <div class="panel-title">Información de usuario</div>
            </div>
            <div class="panel-body">
                <div class="col-sm-12 mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                    @if($user->priv_level==4)
                        <a href="{{ '/user' }}" class="btn btn-warning" title="Volver a lista de usuarios">
                            <i class="fa fa-arrow-up"></i>
                        </a>
                    @endif
                </div>

                <div class="col-sm-12 mg10">
                    @include('app.session_flashed_messages', array('opt' => 1))
                </div>

                <div class="col-sm-12 mg10 mg-tp-px-10">

                    <table class="table table-striped table-hover table-bordered">
                        <thead>
                        <tr>
                            <th width="40%">Nombre visible en el sistema:</th>
                            <td>{{ $view_user->name }}</td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <th width="40%">Nombre completo:</th>
                            <td>{{ $view_user->full_name }}</td>
                        </tr>
                        <tr>
                            <th>Oficina a la que pertenece</th>
                            <td>{{ $view_user->branch_record ? $view_user->branch_record->name : 'N/E' }}</td>
                        </tr>
                        <tr>
                            <th>Área</th>
                            <td>{{ $view_user->area.' - '.$view_user->work_type }}</td>
                        </tr>
                        @if($view_user->role)
                            <tr>
                                <th>Cargo:</th>
                                <td>{{ $view_user->role }}</td>
                            </tr>
                        @endif
                        @if($view_user->rank)
                            <tr>
                                <th>Rango:</th>
                                <td>{{ $view_user->rank }}</td>
                            </tr>
                        @endif
                        @if($view_user->cost && $view_user->cost > 0 && ($user->priv_level == 4 || $user->action->adm_add_usr == 1))
                            <tr>
                                <th>Salario:</th>
                                <td>{{ number_format($view_user->cost,2).' Bs' }}</td>
                            </tr>
                        @endif

                        @if($view_user->phone || $view_user->email)
                            <tr><td colspan="2"></td></tr>
                            <tr>
                                <th colspan="2">Datos de contacto</th>
                            </tr>
                            @if($view_user->phone && $view_user->phone != 0)
                                <tr>
                                    <td>Teléfono</td>
                                    <td>{{ $view_user->phone }}</td>
                                </tr>
                            @endif
                            @if($view_user->email)
                                <tr>
                                    <td>Correo electrónico</td>
                                    <td><a href="mailto:{{ $view_user->email }}">{{ $view_user->email }}</a></td>
                                </tr>
                            @endif
                        @endif

                        <tr><td colspan="2"></td></tr>
                        <tr>
                            <th colspan="2">Datos de cuenta</th>
                        </tr>
                        <tr>
                            <td>Nombre de usuario:</td>
                            <td>{{ $view_user->login }}</td>
                        </tr>
                        <tr>
                            <td>Tiene acceso a:</td>
                            <td>
                                {!! $view_user->acc_cite == 1 ? "CITES<br>" : '' !!}
                                {!! $view_user->acc_oc == 1 ? "OCs<br>" : '' !!}
                                {!! $view_user->acc_project == 1 ? "Seguimiento de Proyectos<br>" : '' !!}
                                {!! $view_user->acc_active == 1 ? "Activos<br>" : '' !!}
                                {!! $view_user->acc_warehouse == 1 ? "Almacén<br>" : '' !!}
                                {!! $view_user->acc_staff == 1 ? 'Registro de personal' : '' !!}
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                @if($user->id == $view_user->id || $user->priv_level == 4 || $user->action->adm_add_usr == 1)
                    <div class="col-sm-12 mg10" align="center">
                        <a href="/user/{{ $view_user->id }}/edit" class="btn btn-success">
                            <i class="fa fa-pencil-square-o"></i> Actualizar datos
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'users','id'=>0))
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
