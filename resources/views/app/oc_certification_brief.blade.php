<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 21/03/2017
 * Time: 03:01 PM
 */
?>

@extends('layouts.ocs_structure')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    <li><a href="#">&ensp;<i class="fa fa-file-text-o"></i> CERTIFICADOS <span class="caret"></span>&ensp;</a>
        <ul class="sub-menu">
            <li><a href="{{ '/oc_certificate' }}"><i class="fa fa-bars"></i> Ver todo </a></li>
            {{--
            <li><a href="/oc_certificate/create"><i class="fa fa-plus"></i> Nuevo certificado </a></li>
            --}}
            @if($user->action->oc_ctf_exp /*$user->priv_level==4*/)
                {{--
                <li><a href="/delete/oc_certificate"><i class="fa fa-trash-o"></i> Borrar un archivo </a></li>
                --}}
                <li>
                    <a href="{{ '/excel/oc_certifications' }}"><i class="fa fa-file-excel-o"></i> Exportar tabla</a>
                </li>
            @endif
        </ul>
    </li>
    <li>
        <a data-toggle="modal" href="#searchBox">&ensp;<i class="fa fa-search"></i> BUSCAR&ensp;</a>
    </li>
@endsection

@section('content')
    <div class="col-sm-12 mg10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10 mg-tp-px-5">
        <p>Certificados encontrados: {{ $certificates->total() }}</p>

        <table class="fancy_table table_orange" id="fixable_table">
            <thead>
            <tr>
                <th width="14%">Código</th>
                <th width="10%">Fecha</th>
                <th width="10%">Nº OC</th>
                <th>Proveedor</th>
                <th>Aceptación</th>
                <th width="25%">Acciones</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($certificates as $certificate)
                <tr>
                    <td>
                        <a href="/oc_certificate/{{ $certificate->id }}">
                            {{ $certificate->code }}
                            {{-- 'CFD-'.date_format($certificate->created_at,'ymd').'-'.
                                str_pad($certificate->id, 3, "0", STR_PAD_LEFT) --}}
                        </a>

                        @if($user->action->oc_ctf_edt)
                            <a href="/oc_certificate/{{ $certificate->id }}/edit" class="pull-right"
                               title="Modificar datos de certificado">
                                <i class="fa fa-pencil-square"></i>
                            </a>
                        @endif
                    </td>
                    <td align="center">{{ date_format($certificate->created_at,'d-m-Y') }}</td>
                    <td align="center">
                        <a href="/oc/{{ $certificate->oc->id }}">{{ $certificate->oc->code }}</a>
                    </td>
                    <td>{{ $certificate->oc->provider }}</td>
                    <td>{{ $certificate->type_reception }}</td>
                    <td>
                        <a href="/excel/oc_certification/{{ $certificate->id }}"
                            title="Descargar el certificado generado por el sistema">
                            <i class="fa fa-download"></i> Descargar
                        </a>
                        &ensp;
                        <a href="/files/oc_certification_backup/{{ $certificate->id }}"
                            title="Cargar un archivo de respaldo al sistema">
                            <i class="fa fa-upload"></i> Respaldos
                        </a>
                        &ensp;
                        @if($certificate->date_print_ack=='0000-00-00 00:00:00')
                            <a href="{{ route('oc_certificate_print_ack',$certificate->code) }}" class="confirm_print_ack"
                                title="Registrar entrega de copia impresa a encargado administrativo">
                                <i class="fa fa-send"></i> Entrega
                            </a>
                        @else
                            <span style="color:green" title="Certificado impreso entregado a encargado administrativo">
                                <i class="fa fa-check"></i> Entregado
                            </span>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-sm-12 mg10" align="center">
        {!! $certificates->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="fancy_table table_orange" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'oc_certificates','id'=>0))
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

        $('.confirm_print_ack').on('click', function () {
            return confirm('Está seguro de que desea registrar la entrega de este certificado en formato impreso ' +
                    'al encargado administrativo?');
        });
    </script>
@endsection
