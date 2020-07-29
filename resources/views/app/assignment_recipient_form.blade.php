<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 04/10/2017
 * Time: 11:28 AM
 */
?>

@extends('layouts.master')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">

        <div class="panel panel-sky">
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    {{ $type=='asg_summary' ? 'Enviar resumen de avance por correo' : '' }}
                </div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="javascript:history.back()" {{-- onclick="history.back();" --}} class="btn btn-warning" title="Atrás">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                    <a href="{{ '/assignment' }}" class="btn btn-warning" title="Volver a asignaciones">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <form novalidate="novalidate" action="{{ '/assignment/mail/'.$type }}" method="post">

                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="form-group">
                        <div class="input-group">

                            <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                <div class="input-group" style="width: 100%">
                                    <label for="asg_id" class="input-group-addon" style="width: 23%;text-align: left">
                                        Asignación:
                                    </label>

                                    <select required="required" class="form-control" name="asg_id" id="asg_id">
                                        <option value="" hidden>Seleccione una asignación</option>
                                        <option value="{{ $assignment->id }}" selected="selected"
                                                title="{{ $assignment->name }}">{{ str_limit($assignment->name,100) }}</option>
                                    </select>
                                </div>

                                <div class="input-group" style="width: 100%">
                                    <label for="subject" class="input-group-addon" style="width: 23%;text-align: left">
                                        Asunto:
                                    </label>

                                    <input required="required" type="text" class="form-control" name="subject"
                                           id="subject"
                                           value="{{ old('subject') ?: ($type=='asg_summary' ?
                                                'Reporte de avance general '.$assignment->name : '') }}"
                                           placeholder="Asunto del correo que se enviará">
                                </div>

                                <div class="input-group" style="width: 100%">
                                    <label for="recipient" class="input-group-addon" style="width: 23%;text-align: left">
                                        Destinatario:
                                    </label>

                                    <input required="required" type="text" class="form-control" name="recipient"
                                           id="recipient"
                                           value="{{ old('recipient') }}"
                                           placeholder="Dirección de correo del cliente">
                                </div>

                                <textarea rows="3" class="form-control" name="comments"
                                          placeholder="Comentarios adicionales">{{ old('comments') }}</textarea>

                            </div>

                        </div>
                    </div>

                    @include('app.loader_gif')

                    <div class="form-group" align="center">
                        <button type="submit" class="btn btn-success"
                                onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                            <i class="fa fa-send"></i> Enviar correo
                        </button>
                    </div>

                    {{ csrf_field() }}
                </form>
            </div>
        </div>
    </div>

@endsection

@section('footer')
@endsection

@section('javascript')
    <script src="{{ asset('app/js/prevent_enter_form_submit.js') }}"></script> {{-- Avoid submitting form on enter press --}}
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function(){
            $("#wait").hide();
        });
    </script>
@endsection
