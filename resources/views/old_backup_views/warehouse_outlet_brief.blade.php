<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 03/05/2017
 * Time: 05:35 PM
 */
?>

@extends('layouts.wh_structure')

@section('header')
    @parent
@endsection

@section('menu_options')
    <li><a href="#">&ensp;<i class="fa fa-sign-out"></i> SALIDAS <span class="caret"></span>&ensp;</a>
        <ul class="sub-menu">
            {{--<li><a href="{{ '/wh_outlet' }}"><i class="fa fa-refresh fa-fw"></i> Recargar página </a></li>--}}
            <li><a href="" onclick="window.location.reload();"><i class="fa fa-refresh fa-fw"></i> Recargar página </a></li>
            <li><a href="{{ '/wh_outlet/create' }}"><i class="fa fa-plus fa-fw"></i> Registrar salida </a></li>
            @if($user->priv_level==4)
                <li>
                    <a href="{{ '/excel/wh_outlets' }}"><i class="fa fa-file-excel-o fa-fw"></i> Exportar a Excel </a>
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
        <p>Registros encontrados: {{ $outlets->total() }}</p>

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
            @foreach($outlets as $outlet)
                <tr>
                    <td>
                        <a href="/wh_outlet/{{ $outlet->id }}">
                            {{ date_format(new \DateTime($outlet->date), 'd-m-Y') }}
                        </a>
                    </td>
                    <td><a href="/warehouse/{{ $outlet->warehouse->id }}">{{ $outlet->warehouse->name }}</a></td>
                    <td><a href="/material/{{ $outlet->material->id }}">{{ $outlet->material->name }}</a></td>
                    <td align="right">{{ $outlet->qty.' ['.$outlet->material->units.']' }}</td>
                    <td>{{ $outlet->delivered_by }}</td>
                    <td>{{ $outlet->received_by }}</td>
                    <td>{{ $outlet->reason }}</td>
                    <td>
                        <?php $remaining=0; ?>
                        @foreach($outlet->files as $file)
                            @if($file->type=="pdf")
                                Recibo:
                                <a href="/download/{{ $file->id }}">
                                    <img src="{{ '/imagenes/pdf-icon.png' }}" alt="PDF" />
                                </a>
                                <?php $remaining++ ?>
                            @endif
                        @endforeach
                        @if($remaining<1)
                            <a href="/files/wh_outlet_receipt/{{ $outlet->id }}"><i class="fa fa-upload"></i> Subir recibo</a>
                        @endif

                        {{-- images can be uploaded up to five days after the registration of the outlet --}}
                        @if((\Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($outlet->date))<5)||$user->priv_level==4)
                            <a href="/files/wh_outlet_img/{{ $outlet->id }}" class="pull-right">
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
        {!! $outlets->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="fancy_table table_gray" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'wh_outlets','id'=>0))
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
