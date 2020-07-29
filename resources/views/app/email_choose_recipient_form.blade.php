<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 18/05/2017
 * Time: 03:37 PM
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
        <div class="panel panel-10gray">
            <div class="panel-heading" align="center">
                <div class="panel-title">{{ 'Enviar correo a un destinatario diferente' }}</div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                    <a href="{{ '/email' }}" class="btn btn-warning" title="Volver a lista de correos">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <p><em>Nota.- Los campos con * son obligatorios</em></p>

                <form novalidate="novalidate" action="{{ '/mail/send/'.$email->id }}" method="post">

                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                <div id="name_container" class="form-group has-feedback">
                                    <div class="input-group" style="width: 100%">
                                        <label for="name" class="input-group-addon" style="width: 23%;text-align: left">
                                            Destinatario
                                        </label>

                                        <input required="required" type="text" class="form-control" name="name" id="name"
                                               value="{{ old('name') }}"
                                               placeholder="Destinatario (Opcional / Sólo si está registrado en éste sistema)">
                                    </div>

                                    <div class="input-group" style="width: 100%;text-align: center" id="result" align="center"></div>
                                </div>

                                <div class="input-group" style="width: 100%">
                                    <label for="email" class="input-group-addon" style="width: 23%;text-align: left">
                                        Email <span class="pull-right">*</span>
                                    </label>

                                    <input required="required" type="text" class="form-control" name="email" id="email"
                                           placeholder="Correo electronico" value="{{ old('email') }}">
                                </div>

                            </div>
                        </div>
                    </div>

                    @include('app.loader_gif')

                    <div class="form-group" align="center">
                        <button type="submit" class="btn btn-success"
                                onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                            <i class="fa fa-send"></i> Enviar
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

        function check_email_address(){

            var name=$('#name').val();
            if(name.length >0){
                $.post('/check_email_address', { name: name }, function(data){
                    //alert(data.email);
                    $("#email").val(data.email);
                    if(data.status==="warning"){
                        $("#result").html(data.message).show();
                        $('#name_container').addClass("has-warning").removeClass("has-success");
                    }
                    else {
                        $("#result").hide();
                    }
                });
            }
            else{
                $("#result").hide();
                $('#name_container').removeClass("has-warning").removeClass("has-success");
            }
        }

        $(document).ready(function(){
            $("#wait").hide();
            $("#result").hide();
            $('#name').focusout(check_email_address);
        });

        $('#name').autocomplete({
            type: 'post',
            serviceUrl:'/autocomplete/users',
            dataType: 'JSON',
            onSelect: check_email_address
        });
    </script>
@endsection
