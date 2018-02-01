<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 07/09/2017
 * Time: 04:39 PM
 */
?>

@extends('layouts.master')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">
        <div class="panel panel-brown">
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    {{ 'Rechazar requerimiento de equipo '.$requirement->code }}
                </div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-undo"></i>
                    </a>
                    <a href="{{ '/device_requirement' }}" class="btn btn-warning" title="Volver a resumen de requerimientos">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <form novalidate="novalidate" action="{{ '/device_requirement/reject/'.$requirement->id }}" method="post">
                    <input type="hidden" name="_method" value="put">

                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="form-group">
                        <div class="input-group">

                            <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                <div id="device_container" class="input-group" style="width: 100%">
                                    <label for="device" class="input-group-addon" style="width: 23%;text-align: left">
                                        Equipo:
                                    </label>

                                    <input type="text" name="device" id="device" class="form-control"
                                           value="{{ $requirement->device ? $requirement->device->type.' '.
                                            $requirement->device->model : '' }}" readonly="readonly">
                                </div>

                                <textarea rows="5" required="required" class="form-control" name="stat_obs" id="stat_obs"
                                          placeholder="Especifique el motivo de rechazo de este requerimiento">{{
                                          old('reason') }}</textarea>

                            </div>

                        </div>
                    </div>

                    @include('app.loader_gif')

                    <div class="form-group" align="center">
                        <button type="submit" class="btn btn-danger"
                                onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                            <i class="fa fa-ban"></i> Rechazar requerimiento
                        </button>
                    </div>
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
