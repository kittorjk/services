<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 16/02/2017
 * Time: 04:30 PM
 */
?>

@extends('layouts.info_master')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/info_tabs.css") }}">
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

        <div class="panel panel-10gray">
            <div class="panel-heading" align="center">
            <div class="panel-title">
                <div class="pull-left">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#details" data-toggle="tab"> Información de contrato</a></li>
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
                            <a href="{{ '/contract' }}" class="btn btn-warning" title="Ir a la tabla de contratos">
                                <i class="fa fa-arrow-circle-up"></i> Contratos
                            </a>
                        </div>

                        @include('app.session_flashed_messages', array('opt' => 1))

                        <div class="col-sm-12 mg10 mg-tp-px-10">

                            <table class="table table-striped table-hover table-bordered">
                                <thead>
                                <tr>
                                    <th width="40%">Código de contrato:</th>
                                    <td>{{ $contract->code }}</td>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <th>Código de cliente</th>
                                    <td>{{ $contract->client_code }}</td>
                                </tr>
                                <tr>
                                    <th>Cliente:</th>
                                    <td>{{ $contract->client }}</td>
                                </tr>
                                <tr><td colspan="2"> </td></tr>
                                <tr>
                                    <th colspan="2">Objeto del contrato</th>
                                </tr>
                                <tr>
                                    <td colspan="2">{{ $contract->objective }}</td>
                                </tr>
                                <tr><td colspan="2"> </td></tr>
                                <tr>
                                    <td>Fecha de inicio</td>
                                    <td>{{ date_format($contract->start_date,'d-m-Y') }}</td>
                                </tr>
                                <tr>
                                    <td>Fecha de vencimiento</td>
                                    <td>
                                        @if(Carbon\Carbon::now()->diffInDays($contract->expiration_date,false)<=5&&
                                            $contract->closed==0)
                                            <span style="color:red">
                                        @elseif($contract->closed==1)
                                            <span style="color:slategrey">
                                        @else
                                            <span>
                                        @endif
                                                {{ date_format($contract->expiration_date,'d-m-Y') }}
                                                @if(Carbon\Carbon::now()->diffInDays($contract->expiration_date,false)<0&&
                                                    $contract->closed==0)
                                                    {{ ' Vencido' }}
                                                    <a href="/contract/close/{{ $contract->id }}" class="confirmation"
                                                       title="Archivar contrato (No renovable / una vez archivado no podrá
                                                        ser modificado)">
                                                        <i class="fa fa-archive pull-right"></i>
                                                    </a>
                                                @endif
                                                @if($contract->closed==1)
                                                    {{ ' Archivado' }}
                                                @endif
                                            </span>
                                    </td>
                                </tr>

                                <tr><td colspan="2"></td></tr>
                                <tr>
                                    <th>Registro creado por</th>
                                    <td>{{ $contract->user ? $contract->user->name : 'N/E' }}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                        @if(($user->area=='Gerencia General'&&$user->priv_level>=2&&$contract->closed==0)||
                            $user->priv_level==4)
                            <div class="col-sm-12 mg10" align="center">
                                <a href="/contract/{{ $contract->id }}/edit" class="btn btn-success">
                                    <i class="fa fa-pencil-square-o"></i> Modificar datos
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
                        <div class="col-sm-12 mg10">
                            @if (Session::has('message'))
                                <div class="alert alert-info" align="center" id="alert">
                                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                    {{ Session::get('message') }}
                                </div>
                            @endif
                        </div>
                        <div class="col-sm-12 mg10 mg-tp-px-10">
                            <table class="table table-striped table-hover table-bordered">
                                <thead>
                                <tr>
                                    <th width="40%">Código de contrato:</th>
                                    <td>{{ $contract->code }}</td>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <th>Código de cliente</th>
                                    <td>{{ $contract->client_code }}</td>
                                </tr>
                                <tr>
                                    <th>Cliente:</th>
                                    <td>{{ $contract->client }}</td>
                                </tr>
                                <tr><td colspan="2"> </td></tr>

                                <tr>
                                    <th colspan="2">Archivos:</th>
                                </tr>
                                @foreach($contract->files as $file)
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

                                @if($contract->closed==0||$user->priv_level==4)
                                    <tr>
                                        <th colspan="2" style="text-align: center">
                                            <a href="/files/contract/{{ $contract->id }}">
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

@section('javascript')
    <script>
        $('.confirmation').on('click', function () {
            return confirm('Está seguro de que desea archivar este contrato? Una vez archivado no podrá ser modificado');
        });
    </script>
@endsection