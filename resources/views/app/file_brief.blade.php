<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 09/05/2017
 * Time: 11:00 AM
 */
?>

@extends('layouts.adm_structure')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    <li><a href="#">&ensp;<i class="fa fa-file"></i> ARCHIVOS <span class="caret"></span>&ensp;</a>
        <ul class="sub-menu">
            <li><a href="{{ '/file' }}"><i class="fa fa-refresh"></i> Recargar página </a></li>
            @if($user->action->adm_file_del /*$user->priv_level==4*/)
                <li><a href="{{ '/delete/type' }}"><i class="fa fa-trash-o"></i> Borrar un archivo</a></li>
            @endif
            @if($user->action->adm_file_exp /*$user->priv_level==4*/)
                <li><a href="{{ '/excel/files' }}"><i class="fa fa-file-excel-o"></i> Exportar lista</a></li>
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
        <p>Registros encontrados: {{ $files->total() }}</p>

        <table class="fancy_table table_gray" id="fixable_table">
            <thead>
            <tr>
                <th width="10%">Fecha</th>
                <th>Nombre</th>
                <th width="30%">Descripción</th>
                <th>Tipo</th>
                <th>Subido por</th>
                <th>Pertenece a</th>
                <th>Estado</th>
                <th width="13%">Acciones</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($files as $file)
                <tr>
                    <td>{{ date_format($file->created_at,'d/m/Y') }}</td>
                    <td>
                        {{--@if($user->priv_level==4)--}}
                            <a href="/file/{{ $file->id }}" title="Ver información de archivo">{{ $file->name }}</a>
                        {{--@else
                            {{ $file->name }}
                        @endif--}}
                    </td>
                    <td>{{ $file->description }}</td>
                    <td>{{ $file->type }}</td>
                    <td>{{ $file->user->name }}</td>
                    <td>{{ $file->imageable_type }}</td>
                    <td>{{ $file->status==0 ? 'Activo' : 'Archivado' }}</td>
                    <td>
                        @if($file->type=='pdf')
                            <img src="{{ '/imagenes/pdf-icon.png' }}" alt="PDF" />
                        @elseif($file->type=='doc'||$file->type=='docx')
                            <img src="{{ '/imagenes/word-icon.png' }}" alt="WORD" />
                        @elseif($file->type=='xls'||$file->type=='xlsx')
                            <img src="{{ '/imagenes/excel-icon.png' }}" alt="EXCEL" />
                        @elseif($file->type=='jpg'||$file->type=='jpeg'||$file->type=='png')
                            <img src="{{ '/imagenes/image-icon.png' }}" alt="IMAGE" />
                        @endif
                        &emsp;
                        <a href="/download/{{ $file->id }}" title="Descargar"><i class="fa fa-download"></i></a>
                        @if($file->type=='pdf')
                            &emsp;
                            <a href="/display_file/{{ $file->id }}" title="Abrir archivo"><i class="fa fa-eye"></i></a>
                        @endif
                        @if($file->status==0||$user->priv_level==4)
                            &emsp;
                            <a href="/files/replace/{{ $file->id }}" title="Reemplazar archivo"><i class="fa fa-refresh"></i></a>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-sm-12 mg10" align="center">
        {!! $files->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="fancy_table table_gray" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'files','id'=>0))
    </div>

@endsection

@section('footer')
    @parent
@endsection

@section('javascript')
    <script src="{{ asset('app/js/fix_table_header.js') }}"></script> {{-- For fixed header --}}
    <script>
        $('#alert').delay(2000).fadeOut('slow');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function(){
            $.post('/set_current_url', { url: window.location.href }, function(){});
        });
    </script>
@endsection
