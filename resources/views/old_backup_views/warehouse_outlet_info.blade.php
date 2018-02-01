<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 04/05/2017
 * Time: 10:03 AM
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
                <div class="panel-title">Información de salida de material</div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning">
                        <i class="fa fa-arrow-circle-left"></i> Volver
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <div class="col-sm-12 mg10 mg-tp-px-10">

                    <table class="table table-striped table-hover table-bordered">
                        <thead>
                        <tr>
                            <th width="40%">Fecha</th>
                            <td>{{ date_format($outlet->date,'d-m-Y') }}</td>
                        </tr>
                        <tr>
                            <th>Almacén</th>
                            <td>{{ $outlet->warehouse->name }}</td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <th>Material</th>
                            <td>{{ $outlet->material->name }}</td>
                        </tr>
                        <tr>
                            <th>Cantidad entregada</th>
                            <td>{{ $outlet->qty.' '.$outlet->material->units }}</td>
                        </tr>
                        <tr>
                            <th>Motivo de salida</th>
                            <td>{{ $outlet->reason }}</td>
                        </tr>

                        <tr><td colspan="2"></td></tr>
                        <tr>
                            <th>Entregado por:</th>
                            <td>{{ $outlet->delivered_by }}</td>
                        </tr>
                        <tr>
                            <th>Entregado a:</th>
                            <td>{{ $outlet->received_by }}</td>
                        </tr>
                        <tr>
                            <th>Tipo de salida</th>
                            <td>{{ $outlet->outlet_type }}</td>
                        </tr>

                        <tr><td colspan="2"></td></tr>
                        <tr>
                            <th>Recibo firmado</th>
                            <td>
                                <?php $remaining=0; ?>
                                @foreach($outlet->files as $file)
                                    @if($file->type=="pdf")
                                        <a href="/download/{{ $file->id }}" style="text-decoration: none">
                                            <img src="{{ '/imagenes/pdf-icon.png' }}" alt="PDF" />
                                        </a>
                                        &emsp;
                                        <a href="/display_file/{{ $file->id }}" title="Abrir archivo">Ver</a>
                                        &emsp;
                                        <a href="/file/{{ $file->id }}" title="Información de archivo">Detalles</a>
                                        <?php $remaining++ ?>
                                    @endif
                                @endforeach
                                @if($remaining<1)
                                    <a href="/files/wh_outlet_receipt/{{ $outlet->id }}">
                                        <i class="fa fa-upload"></i> Subir recibo
                                    </a>
                                @endif
                            </td>
                        </tr>

                        <tr><td colspan="2"></td></tr>
                        <tr>
                            <th colspan="2">
                                Imagenes de respaldo
                                @if((\Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($outlet->date))<5)||
                                    $user->priv_level==4)
                                    <a href="/files/wh_outlet_img/{{ $outlet->id }}" class="pull-right">
                                        <i class="fa fa-upload"></i> Subir
                                    </a>
                                @endif
                            </th>
                        </tr>
                        <tr>
                            <td colspan="2" align="center">
                                <?php $exist_pictures = false; ?>
                                @foreach($outlet->files as $file)
                                    @if($file->type!='pdf')
                                        <img class="myImg" src="/files/thumbnails/{{ 'thumb_'.$file->name }}" height="50"
                                             border="0" alt="{{ $file->description }}" onclick="show_modal(this)">
                                        <?php $exist_pictures = true; ?>
                                    @endif
                                @endforeach

                                {{ !$exist_pictures ? 'No se subieron imágenes' : '' }}

                                <div id="picModal" class="pic_modal">
                                    <span class="pic_close" id="pic_close">&times;</span>
                                    <img class="pic_modal-content" id="pic_modal_content">
                                    <div id="pic_caption"></div>
                                </div>
                            </td>
                        </tr>

                        </tbody>
                    </table>
                </div>
                @if($user->priv_level==4)
                    <div class="col-sm-12 mg10" align="center">
                        <a href="/wh_outlet/{{ $outlet->id }}/edit" class="btn btn-success">
                            <i class="fa fa-pencil-square-o"></i> Modificar datos
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
