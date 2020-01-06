<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 09/05/2017
 * Time: 06:11 PM
 */
?>

@extends('layouts.master')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/image_modal.css") }}">
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">
        <div class="panel panel-violet">
            <div class="panel-heading" align="center">
                <div class="panel-title">{{ 'Seleccione una nueva imagen principal' }}</div>
            </div>
            <div class="panel-body">
                <div class="col-sm-12 mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning">
                        <i class="fa fa-arrow-circle-left"></i> Volver
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <form novalidate="novalidate" action="{{ '/'.$type.'/change/main_pic_id/'.$model->id }}" method="post">

                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    <div class="col-sm-12">
                        <table class="table table-striped table-hover table-bordered">
                            <tbody>
                            <tr>
                                <th>Seleccionar</th>
                                <th>Imagen</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                            </tr>
                            @foreach($model->files as $file)
                                @if($file->type=='jpg'||$file->type=='jpeg'||$file->type=='png')
                                    <tr>
                                        <td align="center">
                                            <input type="radio" name="new_id" id="{{ 'new_id_'.$file->id }}" value="{{ $file->id }}"
                                            {{ $file->id==$model->main_pic_id ? 'checked="checked"' : '' }}>
                                        </td>
                                        <td>
                                            <img class="myImg" src="/files/thumbnails/{{ 'thumb_'.$file->name }}" height="50"
                                                 border="0" alt="{{ $file->description }}" onclick="show_modal(this)">
                                        </td>
                                        <td>
                                            <label for="{{ 'new_id_'.$file->id }}" style="font-weight: normal">
                                                {{ $file->name }}
                                            </label>
                                        </td>
                                        <td>{{ $file->description ? $file->description : 'n/e' }}</td>
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    @include('app.loader_gif')

                    <div class="form-group" align="center">
                         <button type="submit" class="btn btn-success"
                                 onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                             <i class="fa fa-floppy-o"></i> Guardar
                         </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Image preview Modal -->
    <div id="picModal" class="pic_modal">
        <span class="pic_close" id="pic_close">&times;</span>
        <img class="pic_modal-content" id="pic_modal_content" src="">
        <div id="pic_caption"></div>
    </div>

@endsection

@section('footer')
@endsection

@section('javascript')
    <script>
        $("#wait").hide();

        var modal = document.getElementById('picModal');
        // Get the image and insert it inside the modal - use its "alt" text as a caption
        var modalImg = document.getElementById("pic_modal_content");
        var captionText = document.getElementById("pic_caption");
        function show_modal(element){
            var fullSizedSource = element.src.replace('thumbnails/thumb_', '');

            modal.style.display = "block";
            modalImg.src = fullSizedSource;
            captionText.innerHTML = element.alt;
        }
        // Get the <span> element that closes the modal
        var span = document.getElementById("pic_close");
        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = "none";
        }
    </script>
@endsection
