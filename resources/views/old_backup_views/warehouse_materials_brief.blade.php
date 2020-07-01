<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 04/05/2017
 * Time: 10:51 AM
 */
?>

@extends('layouts.wh_structure')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/image_modal.css") }}">
@endsection

@section('menu_options')
    {{--<li><a href="/warehouse/materials/{{ $wh_info->id }}">&ensp;<i class="fa fa-refresh"></i> RECARGAR&ensp;</a></li>--}}
    <li><a href="" onclick="window.location.reload();">&ensp;<i class="fa fa-refresh"></i> RECARGAR&ensp;</a></li>
    @if($user->work_type=='Almacén'||$user->priv_level==4)
        <li><a href="#">&ensp;<i class="fa fa-exchange"></i> MOVIMIENTOS <span class="caret"></span>&ensp;</a>
            <ul class="sub-menu">
                <li><a href="{{ '/wh_entry' }}"><i class="fa fa-sign-in fa-fw"></i> Ver entradas </a></li>
                <li><a href="{{ '/wh_entry/create' }}"><i class="fa fa-plus fa-fw"></i> Registrar entrada </a></li>
                <li><a href="{{ '/wh_outlet' }}"><i class="fa fa-sign-out fa-fw"></i> Ver salidas </a></li>
                <li><a href="{{ '/wh_outlet/create' }}"><i class="fa fa-plus fa-fw"></i> Registrar salida </a></li>
                <li><a href="{{ '/warehouse/transfer' }}"><i class="fa fa-exchange fa-fw"></i> Registrar traspaso </a></li>
            </ul>
        </li>
    @endif
    <li><a href="/excel/materials/{{ $wh_info->id }}">&ensp;<i class="fa fa-file-excel-o"></i> EXPORTAR&ensp;</a></li>
    <li><a data-toggle="modal" href="#searchBox">&ensp;<i class="fa fa-search"></i> BUSCAR&ensp;</a></li>
@endsection

@section('content')
    <div class="col-sm-12 mg10 mg-tp-px-10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10 mg-tp-px-10">

        <p>Almacén: <a href="/warehouse/{{ $wh_info->id }}">{{ $wh_info->name }}</a></p>
        <p>Materiales encontrados: {{ $wh_materials->total() }}</p>

        <table class="fancy_table table_gray" id="fixable_table">
            <thead>
            <tr>
                <th>Foto</th>
                <th>Código</th>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Cantidad</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($wh_materials as $material)
                <tr>
                    <td align="center">
                        @if($material->main_pic_id!=0)
                            <img class="myImg" src="/files/thumbnails/{{ 'thumb_'.$material->main_pic->name }}" height="50"
                                 border="0" alt="{{ $material->main_pic->description }}" onclick="show_modal(this)">
                        @else
                            <i class="fa fa-file-image-o" title="Éste material no tiene una imagen disponible"></i>
                        @endif
                    </td>
                    <td><a href="/material/{{ $material->id }}">{{ $material->code }}</a></td>
                    <td>{{ $material->name }}</td>
                    <td>{{ $material->type }}</td>
                    <td align="right">{{ $material->pivot->qty.' ['.$material->units.']' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

    </div>
    <div class="col-sm-12 mg10" align="center">
        {!! $wh_materials->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="fancy_table table_gray" id="cloned"></table>
    </div>

    <!-- Modal for previewing images -->
    <div id="picModal" class="pic_modal">
        <span class="pic_close" id="pic_close">&times;</span>
        <img class="pic_modal-content" id="pic_modal_content" src="">
        <div id="pic_caption"></div>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'materials','id'=>$wh_info->id))
    </div>

@endsection

@section('footer')
    @parent
@endsection

@section('javascript')
    <script src="{{ asset('app/js/fix_table_header.js') }}"></script> {{-- For fixed header --}}
    <script>
        $('#alert').delay(2000).fadeOut('slow');

        $(function(){
            $('#fixable_table').tablesorter({
                cssAsc: 'headerSortUp',
                cssDesc: 'headerSortDown',
                cssNone: '',
                dateFormat: 'uk'
            });
        });

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
