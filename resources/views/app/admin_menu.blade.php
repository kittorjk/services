<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 04/07/2017
 * Time: 06:13 PM
 */
?>

@extends('layouts.master')

@section('header')
    @parent
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-sm-4 col-sm-offset-4 col-xs-12">
        <div class="panel panel-info">
            <div class="panel-heading">
                <div class="panel-title" align="center">Menú de administrador</div>
            </div>
            <div class="panel-body">

                @if($user->priv_level==4)
                    <div class="row mg-tp-px-50">
                        <div class="col-md-12">
                            <div class="col-md-6 col-sm-12" align="center">
                                <a href="{{ '/user' }}" class="btn btn-success uniform_width">
                                    <i class="fa fa-users"></i> Usuarios
                                </a>
                            </div>

                            <div class="col-md-6 col-sm-12" align="center">
                                <a href="{{ '/service_parameter' }}" class="btn btn-success uniform_width">
                                    <i class="fa fa-key"></i> Parametros
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="row mg-tp-px-50">
                        <div class="col-md-12">
                            <div class="col-md-6 col-sm-12" align="center">
                                <a href="{{ route('exported_files') }}" class="btn btn-success uniform_width">
                                    <i class="fa fa-file"></i> Exportados
                                </a>
                            </div>

                            <div class="col-md-6 col-sm-12" align="center">
                                <a href="{{ '/client_session' }}" class="btn btn-success uniform_width">
                                    <i class="fa fa-list"></i> Sesiones
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="row mg-tp-px-50">
                    <div class="col-md-12">
                        @if($user->action->adm_acc_file)
                            <div class="col-md-6 col-sm-12" align="center">
                                <a href="{{ '/file' }}" class="btn btn-success uniform_width">
                                    <i class="fa fa-archive"></i> Archivos
                                </a>
                            </div>
                        @endif

                        @if($user->action->adm_acc_mail)
                            <div class="col-md-6 col-sm-12" align="center">
                                <a href="{{ '/email' }}" class="btn btn-success uniform_width">
                                    <i class="fa fa-envelope"></i> Correos
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="row mg-tp-px-50">
                    <div class="col-md-12">
                        @if($user->action->adm_acc_stf)
                            <div class="col-md-6 col-sm-12" align="center">
                                <a href="{{ '/employee?stat=active' }}" class="btn btn-success uniform_width">
                                    <i class="fa fa-users"></i> Personal
                                </a>
                            </div>
                        @endif

                        @if($user->action->adm_acc_bch)
                            <div class="col-md-6 col-sm-12" align="center">
                                <a href="{{ '/branch' }}" class="btn btn-success uniform_width">
                                    <i class="fa fa-building"></i> Sucursales
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <br><br>

                <div class="mg-tp-px-50">
                    <a href="#" onclick="history.back();" class="btn btn-primary">
                        <i class="fa fa-arrow-circle-left"></i> Atrás
                    </a>

                    @if($user->priv_level==4)
                        <a href="/" class="btn btn-primary">/ Raíz</a>
                    @endif
                </div>
            </div>

            {{--
                <div class="panel-heading">
                    <div class="panel-title" align="center">Error</div>
                </div>
                <div class="panel-body" align="center">
                    <p>Oops! Contenido no disponible</p>
                </div>
            --}}
        </div>
    </div>

@endsection

@section('footer')
@endsection
