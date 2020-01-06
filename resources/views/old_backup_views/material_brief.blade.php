<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 02/05/2017
 * Time: 06:31 PM
 */
?>

@extends('layouts.wh_structure')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/image_modal.css") }}">
@endsection

@section('menu_options')
    <li><a href="#">&ensp;<i class="fa fa-wrench"></i> MATERIALES <span class="caret"></span>&ensp;</a>
        <ul class="sub-menu">
            <li><a href="{{ '/material' }}"><i class="fa fa-refresh fa-fw"></i> Recargar página </a></li>
            <li><a href="{{ '/material/create' }}"><i class="fa fa-plus fa-fw"></i> Agregar material </a></li>
            @if($user->priv_level==4)
                <li><a href="{{ '/excel/materials' }}"><i class="fa fa-file-excel-o fa-fw"></i> Exportar a Excel </a></li>
            @endif
        </ul>
    </li>
    <li><a data-toggle="modal" href="#searchBox">&ensp;<i class="fa fa-search"></i> BUSCAR&ensp;</a></li>
@endsection

@section('content')
    <div class="col-sm-12 mg10 mg-tp-px-10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10 mg-tp-px-10">
        <p>Materiales encontrados: {{ $materials->total() }}</p>

        <table class="fancy_table table_gray" id="fixable_table">
            <thead>
            <tr>
                <th>Foto</th>
                <th>Código</th>
                <th width="30%">Nombre</th>
                <th>Tipo</th>
                <th>Marca</th>
                <th>Categoría</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($materials as $material)
                <tr>
                    <td align="center">
                        @if($material->main_pic_id!=0)
                            <img class="myImg" src="/files/thumbnails/{{ 'thumb_'.$material->main_pic->name }}"
                                 height="50" border="0" alt="{{ $material->main_pic->description }}" onclick="show_modal(this)">
                        @endif

                        @if($material->main_pic_id==0&&($user->work_type=='Almacén'||$user->priv_level==4))
                            <a href="/files/material_img/{{ $material->id }}"><i class="fa fa-upload"></i> Subir foto</a>
                        @endif
                    </td>
                    <td><a href="/material/{{ $material->id }}">{{ $material->code }}</a></td>
                    <td>{{ $material->name }}</td>
                    <td>{{ $material->type }}</td>
                    <td>{{ $material->brand }}</td>
                    <td>{{ $material->category }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-sm-12 mg10" align="center">
        {!! $materials->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="fancy_table table_gray" id="cloned"></table>
    </div>

    <!-- Modal for previewing images -->
    <div id="picModal" class="pic_modal">
        <span class="pic_close" id="pic_close">&times;</span>
        <img class="pic_modal-content" id="pic_modal_content">
        <div id="pic_caption"></div>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'materials','id'=>0))
    </div>

@endsection

@section('footer')
    @parent
@endsection

@section('javascript')
    <script src="{{ asset('app/js/fix_table_header.js') }}"></script> {{-- For fixed header --}}
    <script>
        $('#alert').delay(2000).fadeOut('slow');

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
        //var span = document.getElementsByClassName("close")[0];
        var span = document.getElementById("pic_close");
        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = "none";
        }

    </script>
@endsection
