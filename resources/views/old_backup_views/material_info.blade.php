<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 03/05/2017
 * Time: 10:29 AM
 */
?>

@extends('layouts.info_master')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/image_modal.css") }}">
    <style>
        .modal-dialog{
            width: 60%;
            max-height: 80%;
        }
    </style>
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

        <div class="panel panel-10gray">
            <div class="panel-heading" align="center">
                <div class="panel-title">Información de material</div>
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
                            <th width="40%">Código:</th>
                            <td>{{ $material->code }}</td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <th>Nombre</th>
                            <td>{{ $material->name }}</td>
                        </tr>
                        <tr>
                            <th>Tipo</th>
                            <td>{{ $material->type }}</td>
                        </tr>

                        @if($material->description)
                            <tr><td colspan="2"> </td></tr>
                            <tr>
                                <th colspan="2">Descripción</th>
                            </tr>
                            <tr>
                                <td colspan="2">{{ $material->description }}</td>
                            </tr>
                        @endif

                        <tr><td colspan="2"> </td></tr>
                        <tr>
                            <th>Unidades</th>
                            <td>{{ $material->units }}</td>
                        </tr>
                        @if($material->cost_unit!=0)
                            <tr>
                                <th>Costo por unidad</th>
                                <td>{{ $material->cost_unit.' Bs' }}</td>
                            </tr>
                        @endif
                        @if($material->brand)
                            <tr>
                                <th>Marca</th>
                                <td>{{ $material->brand }}</td>
                            </tr>
                        @endif
                        @if($material->supplier)
                            <tr>
                                <th>Proveedor</th>
                                <td>{{ $material->supplier }}</td>
                            </tr>
                        @endif
                        <tr>
                            <th>Categoría</th>
                            <td>{{ $material->category }}</td>
                        </tr>

                        <tr><td colspan="2"></td></tr>
                        <tr>
                            <th colspan="2">
                                Imagenes
                                <div class="pull-right">
                                    @if($user->work_type=='Almacén'||$user->priv_level==4)
                                        <a href="/files/material_img/{{ $material->id }}"
                                           title="Subir una imagen del material">
                                            <i class="fa fa-upload"></i> Subir
                                        </a>
                                        &ensp;
                                        @if($material->main_pic_id!=0||$material->files->count()!=0)
                                            <a href="/material/change/main_pic_id/{{ $material->id }}"
                                               title="{{ $material->main_pic_id!=0 ?
                                               'Cambiar imagen principal (visible en página resumen)' :
                                               'Seleccionar imagen principal (visible en página resumen)' }}">
                                                <i class="fa fa-refresh"></i> Cambiar
                                            </a>
                                        @endif
                                    @endif
                                </div>
                            </th>
                        </tr>
                        <tr>
                            <td colspan="2" align="center">
                                @foreach($material->files as $file)
                                    @if($file->type=='jpg'||$file->type=='jpeg'||$file->type=='png')
                                        {{--<a href="#" class="pop"></a>--}}
                                        <img src="/files/thumbnails/{{ 'thumb_'.$file->name }}" style="height: 60px;" class="pop"
                                             alt="{{ $file->description }}">
                                    @endif
                                @endforeach

                                {{ $material->files->count()==0 ? 'No se subieron imágenes de este material' : '' }}

                                <div class="modal fade" id="imagemodal" tabindex="-1" role="dialog"
                                     aria-labelledby="myModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-body">
                                                <button type="button" class="close" data-dismiss="modal">
                                                    <span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
                                                </button>
                                                <img src="" class="imagepreview" style="height: 90%; max-width: 100%">
                                            </div>
                                            <div class="modal-footer captioned">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                @if($user->work_type=='Almacén'||$user->priv_level==4)
                    <div class="col-sm-12 mg10" align="center">
                        <a href="/material/{{ $material->id }}/edit" class="btn btn-success">
                            <i class="fa fa-pencil-square-o"></i> Modificar registro
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
        $(function() {
            $('.pop').on('click', function() {
                var fullSizedSource = $(this).attr('src').replace('thumbnails/thumb_', '');

                $('.imagepreview').attr('src', fullSizedSource /*$(this).find('img').attr('src')*/);
                $('.captioned').html($(this).find('img').attr('alt'));
                $('#imagemodal').modal('show');
            });
        });
    </script>
@endsection
