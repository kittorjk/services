<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 20/02/2017
 * Time: 04:49 PM
 */
?>

@extends('layouts.info_master')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/info_tabs.css") }}">
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

        <div class="panel panel-ground">
            <div class="panel-heading" align="center">
            <div class="panel-title">
                <div class="pull-left">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#details" data-toggle="tab"> Información de poliza</a></li>
                        <li><a href="#documents" data-toggle="tab"> Documentos</a></li>
                    </ul>
                </div>
                <div class="clearfix"></div>
            </div>
            </div>
            <div class="panel-body">

                <div class="tab-content">

                    <div class="tab-pane fade in active" id="details">

                        <div class="mg20">
                            <a href="#" onclick="history.back();" class="btn btn-warning">
                                <i class="fa fa-arrow-circle-left"></i> Volver
                            </a>
                            <a href="{{ '/guarantee' }}" class="btn btn-warning" title="Ir a la tabla de pólizas">
                                <i class="fa fa-arrow-circle-up"></i> Pólizas
                            </a>
                        </div>

                        <div class="col-sm-12 mg10">
                            @include('app.session_flashed_messages', array('opt' => 0))
                        </div>

                        <div class="col-sm-12 mg10 mg-tp-px-10">

                            <table class="table table-striped table-hover table-bordered">
                                <thead>
                                <tr>
                                    <th width="35%">Poliza:</th>
                                    <td>
                                        {{ $guarantee->code }}
                                    </td>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <th>Empresa emisora:</th>
                                    <td>{{ $guarantee->company }}</td>
                                </tr>
                                <tr><td colspan="2"> </td></tr>
                                <tr>
                                    <th>Objeto:</th>
                                    <td>
                                        {{ $guarantee->applied_to }}
                                        {{--
                                        {{ $guarantee->guaranteeable ? $guarantee->guaranteeable->name : '' }}
                                        --}}
                                    </td>
                                </tr>
                                <tr><td colspan="2"> </td></tr>

                                @if($guarantee->closed==1)
                                    <tr>
                                        <th>Estado:</th>
                                        <td>Archivado (no renovable)</td>
                                    </tr>
                                @endif

                                <tr>
                                    <th>Fecha de inicio:</th>
                                    <td>
                                        <span
                                            @if($guarantee->closed==1)
                                                style="color:grey"
                                            @endif
                                                >
                                            {{ date_format($guarantee->start_date,'d-m-Y') }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Fecha de vencimiento:</th>
                                    <td>
                                        <span style="
                                            @if(Carbon\Carbon::now()->diffInDays($guarantee->expiration_date,false)<=5&&
                                                $guarantee->closed==0)
                                                color:red;
                                            @elseif($guarantee->closed==1)
                                                color:grey;
                                            @endif
                                                ">
                                                {{ date_format($guarantee->expiration_date,'d-m-Y') }}

                                            @if(Carbon\Carbon::now()->diffInDays($guarantee->expiration_date,false)<0&&
                                                $guarantee->closed==0)
                                                &emsp;
                                                {{ 'Vencida' }}
                                            @endif
                                        </span>
                                    </td>
                                </tr>

                                <tr><td colspan="2"></td></tr>
                                <tr>
                                    <th>Registro creado por</th>
                                    <td>{{ $guarantee->user ? $guarantee->user->name : 'N/E' }}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                        @if(/*($user->area=='Gerencia General'&&$user->priv_level>=2&&*/$guarantee->closed==0/*)||
                            $user->priv_level==4*/)
                            <div class="col-sm-12 mg10" align="center">
                                <a href="/guarantee/{{ $guarantee->id }}/edit" class="btn btn-success">
                                    <i class="fa fa-pencil-square-o"></i> Modificar
                                </a>
                            </div>
                        @endif

                    </div>

                    <div class="tab-pane fade" id="documents">

                        <div class="col-lg-5 mg20">
                            <a href="#" onclick="history.back();" class="btn btn-warning">
                                <i class="fa fa-arrow-circle-left"></i> Volver
                            </a>
                        </div>

                        <div class="col-sm-12 mg10 mg-tp-px-10">
                            <table class="table table-striped table-hover table-bordered">
                                <thead>
                                <tr>
                                    <th width="35%">Poliza:</th>
                                    <td>
                                        {{ $guarantee->code }}
                                    </td>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <th>Empresa emisora:</th>
                                    <td>{{ $guarantee->company }}</td>
                                </tr>
                                <tr><td colspan="2"> </td></tr>

                                <tr>
                                    <th colspan="2">Archivos:</th>
                                </tr>
                                @foreach($guarantee->files as $file)
                                    <tr>
                                        <td>{{ date_format(new \DateTime($file->updated_at), 'd-m-Y') }}</td>
                                        <td colspan="3">
                                            {{ $file->description }}

                                            <div class="pull-right">
                                                @include('app.info_document_options', array('file'=>$file))
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                @if($guarantee->files->count()==0)
                                    <tr>
                                        <td colspan="2" align="center">No se cargó ningún archivo para esta poliza</td>
                                    </tr>
                                @endif

                                @if($guarantee->closed==0)
                                    <tr>
                                        <th colspan="2" style="text-align: center">
                                            <a href="/files/guarantee/{{ $guarantee->id }}">
                                                <i class="fa fa-upload"></i> Subir archivo
                                            </a>
                                        </th>
                                    </tr>
                                @endif

                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection

@section('footer')
@endsection
