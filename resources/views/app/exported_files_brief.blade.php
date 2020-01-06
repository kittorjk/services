<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 11/07/2017
 * Time: 05:57 PM
 */
?>

@extends('layouts.adm_structure')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    <li><a href="#">&ensp;<i class="fa fa-file"></i> EXPORTADOS <span class="caret"></span>&ensp;</a>
        <ul class="sub-menu">
            <li><a href="{{ route('exported_files') }}"><i class="fa fa-refresh"></i> Recargar página </a></li>
            {{--
                <li><a href="/excel/files"><i class="fa fa-file-excel-o"></i> Exportar lista</a></li>
            --}}
        </ul>
    </li>
    <li><a data-toggle="modal" href="#searchBox">&ensp;<i class="fa fa-search"></i> BUSCAR&ensp;</a></li>
@endsection

@section('content')
    <div class="col-sm-12 mg10 mg-tp-px-10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10 mg-tp-px-10">
        <p>Registros encontrados: {{ $records->total() }}</p>

        <table class="fancy_table table_gray" id="fixable_table">
            <thead>
            <tr>
                <th width="10%">Fecha</th>
                <th width="25%">URL</th>
                <th width="30%">Descripción</th>
                <th>Tipo modelo</th>
                <th>ID modelo</th>
                <th>Exportado por</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($records as $record)
                <tr>
                    <td>{{ date_format($record->created_at,'d/m/Y') }}</td>
                    <td>
                        @if($user->priv_level==4)
                            <a href="{{ $record->url }}" title="Ir a la página de exportación">{{ $record->url }}</a>
                        @else
                            {{ $record->url }}
                        @endif
                    </td>
                    <td>{{ $record->description }}</td>
                    <td>{{ $record->exportable_type }}</td>
                    <td>{{ $record->exportable_id==0 ? '' : $record->exportable_id }}</td>
                    <td>{{ $record->user->name }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-sm-12 mg10" align="center">
        {!! $records->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="fancy_table table_gray" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'exported_files','id'=>0))
    </div>

@endsection

@section('footer')
    @parent
@endsection

@section('javascript')
    <script src="{{ asset('app/js/fix_table_header.js') }}"></script> {{-- For fixed header --}}
    <script src="{{ asset('app/js/set_current_url.js') }}"></script> {{-- For recording current url --}}
    <script>
        $('#alert').delay(2000).fadeOut('slow');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
@endsection
