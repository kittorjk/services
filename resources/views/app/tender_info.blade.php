<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 19/01/2018
 * Time: 11:01 AM
 */
?>

@extends('layouts.info_master')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/info_tabs.css") }}">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

        <div class="panel panel-sky">
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    <div class="pull-left">
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#details" data-toggle="tab"> Información general</a></li>
                            <li><a href="#documents" data-toggle="tab"> Documentos</a></li>
                        </ul>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>

            <div class="panel-body">

                <div class="tab-content">

                    <div class="tab-pane fade in active" id="details">

                        <div class="col-lg-5 mg20">
                            <a href="#" onclick="history.back();" class="btn btn-warning">
                                <i class="fa fa-arrow-circle-left"></i> Atrás
                            </a>
                            <a href="{{ '/tender' }}" class="btn btn-warning" title="Ir a la tabla de licitaciones">
                                <i class="fa fa-arrow-circle-up"></i> Licitaciones
                            </a>
                        </div>

                        <div class="col-lg-7" align="right"></div>

                        <div class="col-sm-12 mg10">
                            @include('app.session_flashed_messages', array('opt' => 0))
                        </div>

                        <div class="col-sm-12 mg10 mg-tp-px-10">
                            <table class="table table-striped table-hover table-bordered">
                                <tbody>
                                <tr>
                                    <th width="25%">Código:</th>
                                    <td width="25%">{{ $tender->code }}</td>
                                </tr>
                                <tr>
                                    <th>Licitación:</th>
                                    <td colspan="3">{{ $tender->name }}</td>
                                </tr>
                                <tr>
                                    <th>Estado:</th>
                                    <td colspan="3">{{ $tender->status }}</td>
                                </tr>
                                <tr><td colspan="4"></td></tr>

                                @if($tender->description!='')
                                    <tr>
                                        <th colspan="4">Descripción:</th>
                                    </tr>
                                    <tr>
                                        <td colspan="4">{{ $tender->description }}</td>
                                    </tr>
                                    <tr><td colspan="4"></td></tr>
                                @endif

                                <tr>
                                    <th width="25%">Area de trabajo:</th>
                                    <td>{{ $tender->area }}</td>
                                    <th>Cliente:</th>
                                    <td>{{ $tender->client }}</td>
                                </tr>
                                <tr><td colspan="4"></td></tr>

                                <tr>
                                    <th width="25%">Plazo de presentación:</th>
                                    <td colspan="3" style="vertical-align: middle;">
                                        {{ $tender->status=='No asignado'||$tender->status=='No presentado' ? 'No aplica' :
                                            $tender->application_deadline.
                                            ($tender->applied==1 ? ' (Documentación presentada)' : ' (Pendiente)') }}
                                    </td>
                                </tr>
                                @if($tender->application_details)
                                    <tr>
                                        <th width="25%">Detalle de licitación:</th>
                                        <td colspan="3" style="vertical-align: middle;">
                                            {!! str_replace('\n','<br/>',$tender->application_details) !!}
                                        </td>
                                    </tr>
                                @endif

                                <tr><td colspan="4"></td></tr>

                                @if($tender->contact)
                                    <tr>
                                        <th>Persona de contacto</th>
                                        <td colspan="3">{{ $tender->contact->name }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <th>Agregado por:</th>
                                    <td colspan="3">
                                        {{ $tender->user ? $tender->user->name : 'N/A' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Agregado en:</th>
                                    <td colspan="3">
                                        {{ date_format($tender->created_at,'d-m-Y') }}
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        @if($tender->user_id==$user->id||$user->priv_level==4)
                            <div class="col-sm-12 mg10" align="center">
                                <a href="/tender/{{ $tender->id }}/edit" class="btn btn-success">
                                    <i class="fa fa-pencil-square-o"></i> Modificar registro
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
                                <tbody>
                                <tr>
                                    <th width="25%">Código:</th>
                                    <td width="25%">{{ $tender->code }}</td>
                                    <td colspan="2"></td>
                                </tr>
                                <tr>
                                    <th>Licitación:</th>
                                    <td colspan="3">{{ $tender->name }}</td>
                                </tr>
                                <tr>
                                    <th>Estado:</th>
                                    <td colspan="3">{{ $tender->status }}</td>
                                </tr>
                                <tr><td colspan="4"></td></tr>

                                <tr>
                                    <th colspan="4">Archivos:</th>
                                </tr>
                                @foreach($tender->files as $file)
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

                                @if($tender->files->count()==0)
                                    <tr>
                                        <td colspan="4" align="center">No se cargó ningún documento.</td>
                                    </tr>
                                @endif

                                <tr>
                                    <th colspan="4" style="text-align: center">
                                        <a href="/files/tender/{{ $tender->id }}">
                                            <i class="fa fa-upload"></i> Subir archivo
                                        </a>
                                    </th>
                                </tr>
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
