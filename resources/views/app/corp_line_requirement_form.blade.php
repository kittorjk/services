<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 02/10/2017
 * Time: 02:03 PM
 */
?>

@extends('layouts.master')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/custom_autocomplete.css") }}">
    <script src="{{ asset('https://cdnjs.cloudflare.com/ajax/libs/jquery.devbridge-autocomplete/1.2.27/jquery.autocomplete.js') }}">
    </script>

    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">
        <div class="panel panel-brown">
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    {{ $requirement ? 'Modificar requerimiento' : 'Registrar requerimiento de línea' }}
                </div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                    <a href="{{ '/line_requirement' }}" class="btn btn-warning" title="Volver a requerimientos">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                @if($requirement)
                    <form id="delete" action="/line_requirement/{{ $requirement->id }}" method="post">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                    <form novalidate="novalidate" action="{{ '/line_requirement/'.$requirement->id }}" method="post">
                        <input type="hidden" name="_method" value="put">
                        @else
                            <form novalidate="novalidate" action="{{ '/line_requirement' }}" method="post">
                                @endif
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="form-group">
                                    <div class="input-group">

                                        <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                            <div id="for_container" class="form-group has-feedback">
                                                <div class="input-group" style="width: 100%">
                                                    <label for="for_name" class="input-group-addon" style="width: 23%;text-align: left">
                                                        Entregar a: <span class="pull-right">*</span>
                                                    </label>

                                                    <input required="required" type="text" class="form-control" name="for_name"
                                                           id="for_name"
                                                           value="{{ $requirement&&$requirement->person_for ?
                                                            $requirement->person_for->name : old('for_name') }}"
                                                           placeholder="Persona para la que se solicita la línea">
                                                </div>

                                                <div class="input-group" style="width: 100%;text-align: center" id="for_check" align="center"></div>
                                            </div>

                                            <textarea rows="3" required="required" class="form-control" name="reason" id="reason"
                                                      placeholder="Motivo de requerimiento *">{{ $requirement ? $requirement->reason :
                                                       old('reason') }}</textarea>

                                        </div>

                                    </div>
                                </div>

                                @include('app.loader_gif')

                                <div class="form-group" align="center">
                                    <button type="submit" class="btn btn-success"
                                            onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                                        <i class="fa fa-send"></i> Enviar requerimiento
                                    </button>

                                    @if($requirement&&$user->priv_level==4)
                                        <button type="submit" form="delete" class="btn btn-danger">
                                            <i class="fa fa-trash-o"></i> Eliminar
                                        </button>
                                    @endif
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

        function check_existence() {
            var for_name = $('#for_name').val();

            if (for_name.length > 0) {
                $.post('/check_existence', { value: for_name }, function(data) {
                    $("#for_check").html(data.message).show();
                    if (data.status === "warning") {
                        $('#for_container').addClass("has-warning").removeClass("has-success");
                    } else if (data.status === "success") {
                        $('#for_container').addClass("has-success").removeClass("has-warning");
                    }
                });
            } else {
                $("#for_check").hide();
                $('#for_container').removeClass("has-warning").removeClass("has-success");
            }
        }

        $(document).ready(function() {
            $("#wait").hide();
            $("#for_check").hide();
            $('#for_name').focusout(check_existence);
        });

        $('#for_name').autocomplete({
            type: 'post',
            serviceUrl:'/autocomplete/users',
            dataType: 'JSON',
            onSelect: check_existence
        });
    </script>
@endsection
