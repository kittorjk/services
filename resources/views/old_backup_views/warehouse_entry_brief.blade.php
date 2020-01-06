<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 03/05/2017
 * Time: 11:23 AM
 */
?>

@extends('layouts.wh_structure')

@section('header')
    @parent
@endsection

@section('menu_options')
    <li><a href="#">&ensp;<i class="fa fa-sign-in"></i> INGRESOS <span class="caret"></span>&ensp;</a>
        <ul class="sub-menu">
            <li><a href="{{ '/wh_entry' }}"><i class="fa fa-refresh fa-fw"></i> Recargar página </a></li>
            <li><a href="{{ '/wh_entry/create' }}"><i class="fa fa-plus fa-fw"></i> Agregar ingreso </a></li>
            @if($user->priv_level==4)
                <li>
                    <a href="{{ '/excel/wh_entries' }}"><i class="fa fa-file-excel-o fa-fw"></i> Exportar a Excel </a>
                </li>
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
        <p>Registros encontrados: {{ $entries->total() }}</p>

        <table class="fancy_table table_gray tablesorter" id="fixable_table">
            <thead>
            <tr>
                <th>Fecha</th>
                <th>Almacén</th>
                <th>Material</th>
                <th>Cantidad</th>
                <th>Entregado por</th>
                <th>Entregado a</th>
                <th>Motivo</th>
                <th>Respaldo</th>
            </tr>
            </thead>
            <tbody>
            @foreach($entries as $entry)
                <tr>
                    <td>
                        <a href="/wh_entry/{{ $entry->id }}">
                            {{ date_format(new \DateTime($entry->date), 'd-m-Y') }}
                        </a>
                    </td>
                    <td><a href="/warehouse/{{ $entry->warehouse->id }}">{{ $entry->warehouse->name }}</a></td>
                    <td><a href="/material/{{ $entry->material->id }}">{{ $entry->material->name }}</a></td>
                    <td align="right">{{ $entry->qty.' ['.$entry->material->units.']' }}</td>
                    <td>{{ $entry->delivered_by }}</td>
                    <td>{{ $entry->received_by }}</td>
                    <td>{{ $entry->reason }}</td>
                    <td>
                        <?php $remaining=0; ?>
                        @foreach($entry->files as $file)
                            @if($file->type=="pdf")
                                Recibo:
                                <a href="/download/{{ $file->id }}">
                                    <img src="{{ '/imagenes/pdf-icon.png' }}" alt="PDF" />
                                </a>
                                <?php $remaining++ ?>
                            @endif
                        @endforeach
                        @if($remaining<1)
                            <a href="/files/wh_entry_receipt/{{ $entry->id }}"><i class="fa fa-upload"></i> Subir recibo</a>
                        @endif

                        @if((\Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($entry->date))<5)||$user->priv_level==4)
                            <a href="/files/wh_entry_img/{{ $entry->id }}" class="pull-right">
                                <i class="fa fa-plus"></i> {{ 'Fotos' }}
                            </a>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-sm-12 mg10" align="center">
        {!! $entries->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="fancy_table table_gray" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'wh_entries','id'=>0))
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
    </script>
@endsection
