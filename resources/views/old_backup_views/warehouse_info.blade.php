<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 02/05/2017
 * Time: 05:50 PM
 */
?>

@extends('layouts.info_master')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/image_modal.css") }}">
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

        <div class="panel panel-10gray">
            <div class="panel-heading" align="center">
                <div class="panel-title">Información de almacén</div>
            </div>
            <div class="panel-body">
                <div class="col-lg-4 mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning">
                        <i class="fa fa-arrow-circle-left"></i> Volver
                    </a>
                </div>

                <div class="col-sm-12 mg10">
                    @include('app.session_flashed_messages', array('opt' => 0))
                </div>

                <div class="col-sm-12 mg10 mg-tp-px-10">
                    <table class="table table-striped table-hover table-bordered">
                        <thead>
                        <tr>
                            <th width="40%">#</th>
                            <td>{{ $warehouse->id }}</td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <th>Nombre</th>
                            <td>{{ $warehouse->name }}</td>
                        </tr>
                        <tr><td colspan="2"> </td></tr>

                        <tr>
                            <th colspan="2">Dirección</th>
                        </tr>
                        <tr>
                            <td colspan="2">{{ $warehouse->location }}</td>
                        </tr>
                        <tr><td colspan="2"> </td></tr>

                        <tr>
                            <th colspan="2">
                                Fotos
                                <a href="/files/warehouse_img/{{ $warehouse->id }}" class="pull-right">
                                    <i class="fa fa-upload"></i> Subir
                                </a>
                            </th>
                        </tr>
                        <tr>
                            <td colspan="2" align="center">
                                <?php $pic_count=0; ?>
                                @foreach($warehouse->files as $file)
                                    @if($file->type=='jpg'||$file->type=='jpeg'||$file->type=='png')
                                        <img class="myImg" src="/files/thumbnails/{{ 'thumb_'.$file->name }}" height="50"
                                             border="0" alt="{{ $file->description }}" onclick="show_modal(this)">
                                    @endif
                                    <?php $pic_count++; ?>
                                @endforeach

                                {{ $pic_count==0 ? 'No se subieron imágenes del almacén' : '' }}

                                <div id="picModal" class="pic_modal">
                                    <span class="pic_close" id="pic_close">&times;</span>
                                    <img class="pic_modal-content" id="pic_modal_content">
                                    <div id="pic_caption"></div>
                                </div>
                            </td>
                        </tr>

                        @if($user->work_type=='Almacén'||$user->priv_level==4)
                            <tr><td colspan="2"></td></tr>
                            <tr>
                                <th colspan="2">Documentos</th>
                            </tr>
                            @foreach($warehouse->files as $file)
                                @if($file->type=='pdf')
                                    <tr>
                                        <td>{{ $file->description }}</td>
                                        <td>
                                            <a href="/download/{{ $file->id }}" style="text-decoration: none">
                                                <img src="{{ '/imagenes/pdf-icon.png' }}" alt="PDF" />
                                            </a>
                                            &emsp;
                                            <a href="/display_file/{{ $file->id }}" title="Abrir archivo">Ver</a>
                                            &emsp;
                                            <a href="/file/{{ $file->id }}" title="Información de archivo">Detalles</a>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                            <tr>
                                <td colspan="2" align="center">
                                    <a href="/files/warehouse_file/{{ $warehouse->id }}">
                                        <i class="fa fa-upload"></i> Subir documento
                                    </a>
                                </td>
                            </tr>
                        @endif

                        </tbody>
                    </table>
                </div>
                @if($user->work_type=='Almacén'||$user->priv_level==4)
                    <div class="col-sm-12 mg10" align="center">
                        <a href="/warehouse/{{ $warehouse->id }}/edit" class="btn btn-success">
                            <i class="fa fa-pencil-square-o"></i> Modificar / Actualizar datos
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection

@section('footer')
@endsection

@section('javascript')
    <script>
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
