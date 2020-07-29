<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 14/09/2017
 * Time: 05:46 PM
 */
?>

@extends('layouts.master')

@section('header')
    @parent
    <style>
        .progress
        {
            display:none;
            position:relative;
            background-color: lightgray;
            width:400px;
            border: 1px solid #ddd;
            padding: 1px;
            border-radius: 3px;
        }
        .bar
        {
            width:0;
            height:20px;
            border-radius: 3px;
        }
        .percent
        {
            position:absolute;
            display:inline-block;
            vertical-align: middle;
            left:48%;
        }
    </style>

    <script type="text/javascript" src="{{ asset('/app/js/jQuery-File-Upload-9.18.0/js/vendor/jquery.ui.widget.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/app/js/jQuery-File-Upload-9.18.0/js/jquery.iframe-transport.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/app/js/jQuery-File-Upload-9.18.0/js/jquery.fileupload.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/app/js/jQuery-File-Upload-9.18.0/js/jquery.fileupload-process.js') }}"></script>
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
        <div class="panel panel-info" >
            <div class="panel-heading" align="center">
                <div class="panel-title">{{ 'Subir archivo modelo - '.$format }}</div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                    <a href="{{ '/site/'.$id }}" class="btn btn-warning" title="Ir a la tabla de sitios">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <form method="post" action="/excel/fill/{{ $format }}/{{ $id }}" accept-charset="UTF-8"
                      enctype="multipart/form-data" id="myForm">

                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-cloud-upload"></i></span>
                            <input type="file" class="form-control" name="file" id="fileupload">

                        </div>
                    </div>

                    <div id="wait" align="center" style="margin-top: 10px;margin-bottom: 10px">
                        <div id="progress" class="progress w3-light-grey w3-round" align="left">
                            <div class="bar w3-green w3-round w3-container w3-center" style="width: 0;">
                                <div class="percent w3-round">0%</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" align="center">
                        <button type="submit" class="btn btn-success" id="start"
                                onclick="$('#wait').show(); this.form.submit();">
                            <i class="fa fa-upload"></i> Cargar y llenar archivo
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
    <script>
        /* For progress bar */
        $('#myForm').fileupload({
            dataType: 'json',
            replaceFileInput: false,

            add: function (e, data) {

                $('#start').click(function () {
                    data.submit();
                });

            },
            progress: function (e, data) {

                var progress = parseInt(data.loaded / data.total * 100, 10);
                $('.progress').css('display','block');
                $('#progress').find('.bar').css('width', progress + '%')
                        .find('.percent').html(progress + '%');

            }
        });
    </script>
@endsection
