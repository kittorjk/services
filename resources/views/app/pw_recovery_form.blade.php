<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 06/04/2017
 * Time: 05:35 PM
 */
?>

@extends('layouts.master')

@section('header')
    @parent
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-sm-4 col-sm-offset-4 col-xs-12">
        <div class="panel panel-info">
            @if($return&&empty($user))
                <div class="panel-heading">
                    <div class="panel-title" align="center">Reestablecer contraseña</div>
                </div>
                <div class="panel-body">
                    <form novalidate="novalidate" action="{{ '/login/pw_recovery' }}" method="post">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="service" value="{{ $service }}">
                        <input type="hidden" name="return" value="{{ $return }}">

                        @include('app.session_flashed_messages', array('opt' => 1))

                        <p>Indique su nombre de usuario:</p>

                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-user fa-fw"></i></span>
                                <input required="required" type="text" class="form-control" name="login" placeholder="Usuario">
                            </div>
                        </div>

                        <p><em>Se enviará un email a la dirección de correo electrónico registrada para este usuario.</em></p>

                        <div class="form-group" align="center">
                            <button type="submit" class="btn btn-warning" onclick="history.back();">
                                <i class="fa fa-arrow-circle-left"></i> Atrás
                            </button>
                            <button type="submit" class="btn btn-primary" onclick="this.disabled=true; this.form.submit()">
                                <i class="fa fa-key"></i> Reestablecer
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>

@endsection

@section('footer')
@endsection
