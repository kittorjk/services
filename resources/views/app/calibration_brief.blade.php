<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 24/03/2017
 * Time: 05:26 PM
 */
?>

@extends('layouts.actives_structure')

@section('header')
    @parent
    <style>
        .dropdown-menu-prim > li > a {
            width: 210px;
            /*white-space: normal; /* Set code to a second line */
        }
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    <a href="{{ '/device' }}" class="btn btn-primary"><i class="fa fa-laptop"></i> Equipos</a>
    <div class="btn-group">
        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
            <i class="fa fa-wrench"></i> Calibraciones <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li><a href="{{ '/calibration' }}"><i class="fa fa-refresh"></i> Recargar página </a></li>
            @if($user->action->acv_cbr_mod /*($user->priv_level==2&&$user->area=='Gerencia Tecnica')||
                $user->work_type=='Almacén'||$user->priv_level>=3*/)
                <li><a href="{{ '/calibration/create' }}"><i class="fa fa-plus"></i> Registrar calibración </a></li>
            @endif
            @if($user->action->acv_cbr_exp /*$user->priv_level==4*/)
                <li class="divider"></li>
                <li><a href="{{ '/excel/calibrations' }}"><i class="fa fa-file-excel-o"></i> Exportar a Excel </a></li>
            @endif
        </ul>
    </div>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchBox">
        <i class="fa fa-search"></i> Buscar
    </button>
@endsection

@section('content')
    <div class="col-sm-12 mg10 mg-tp-px-10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10 mg-tp-px-10">
        <p>Registros de calibración encontrados: {{ $calibrations->total() }}</p>

        <table class="fancy_table table_gray" id="fixable_table">
            <thead>
            <tr>
                <th width="10%">Fecha ingreso</th>
                <th width="20%">Equipo</th>
                <th width="40%">Detalle de trabajos</th>
                <th>Estado actual</th>
                <th>Certificado</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($calibrations as $calibration)
                <tr>
                    <td>
                        {{--<a href="/calibration/{{ $calibration->id }}"></a>--}}
                        {{ date_format(new \DateTime($calibration->date_in), 'd-m-Y') }}
                    </td>
                    <td>
                        @if($calibration->device)
                            <a href="/device/{{ $calibration->device_id }}">
                                {{ $calibration->device->type.' '.$calibration->device->serial }}
                            </a>
                        @endif
                    </td>
                    <td>
                        {{ $calibration->detail }}

                        @if(($calibration->completed==0&&$user->action->acv_cbr_mod
                            /*($user->id==$calibration->user_id||$user->work_type=='Almacén'||
                            $user->priv_level>=3)*/)||$user->priv_level==4)
                            <a href="/calibration/{{ $calibration->id }}/edit" class="pull-right">
                                <i class="fa fa-edit"></i> {{ $calibration->detail ? 'modificar' : 'agregar' }}
                            </a>
                        @endif
                    </td>
                    <td>
                        {{ $calibration->completed==0 ? 'En calibración' : 'Finalizado' }}

                        @if($calibration->completed==0&&strlen($calibration->detail)>0&&$user->action->acv_cbr_mod)
                            <a href="/calibration/close/{{ $calibration->id }}" class="pull-right" title="Finalizar calibración">
                                <i class="fa fa-check"></i>
                            </a>
                        @endif
                    </td>
                    <td align="center">
                        @foreach($calibration->files as $file)
                            @include('app.info_document_options', array('file'=>$file))

                            {{--
                            <a href="/download/{{ $file->id }}">
                                @if($file->type=="pdf")
                                    <img src="{{ '/imagenes/pdf-icon.png' }}" alt="PDF" />
                                @endif
                            </a>
                            <a href="/display_file/{{ $file->id }}">Ver</a>
                            &ensp;
                            <a href="/file/{{ $file->id }}">Detalles</a>
                            --}}
                        @endforeach

                        @if($calibration->files->count()<1)
                            @if($user->id==$calibration->user_id||$user->action->acv_cbr_mod
                                /*($calibration->completed==0&&$user->id==$calibration->user_id)||
                                $user->work_type=='Almacén'||$user->priv_level>=3*/)
                                &ensp;
                                <a href="/files/calibration/{{ $calibration->id }}">
                                    <i class="fa fa-upload"></i> Cargar archivo
                                </a>
                            @endif
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-sm-12 mg10" align="center">
        {!! $calibrations->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="fancy_table table_gray" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'calibrations','id'=>0))
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
