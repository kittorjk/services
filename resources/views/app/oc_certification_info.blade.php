<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 22/03/2017
 * Time: 10:07 AM
 */
?>

@extends('layouts.ocs_structure')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/info_tabs.css") }}">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    <li><a href="#">&ensp;<i class="fa fa-file-text-o"></i> CERTIFICADOS <span class="caret"></span>&ensp;</a>
        <ul class="sub-menu">
            <li><a href="{{ '/oc_certificate' }}"><i class="fa fa-bars"></i> Ver todo </a></li>
        </ul>
    </li>
    <li>
        <a data-toggle="modal" href="#searchBox">&ensp;<i class="fa fa-search"></i> BUSCAR&ensp;</a>
    </li>
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

        <div class="panel panel-orange">
            <div class="panel-heading" align="center">
            <div class="panel-title">
                <div class="pull-left">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#details" data-toggle="tab"> Información de certificado</a></li>
                    </ul>
                </div>
                <div class="clearfix"></div>
            </div>
            </div>
            <div class="panel-body">

                <div class="tab-content">

                    <div class="tab-pane fade in active" id="details">

                        <div class="col-lg-6 mg20">
                            <a href="#" onclick="history.back();" class="btn btn-warning">
                                <i class="fa fa-arrow-left"></i> Volver
                            </a>
                            <a href="{{ '/oc_certificate' }}" class="btn btn-warning" title="Ir a la tabla de certificados de OC">
                                <i class="fa fa-arrow-up"></i> Certificados
                            </a>
                        </div>

                        <div class="col-lg-6" align="right">
                            <a href="/excel/oc_certification/{{ $certificate->id }}" class="btn btn-success">
                                <i class="fa fa-download"></i> Descargar Certificado
                            </a>
                        </div>

                        <div class="col-sm-12 mg10">
                            @include('app.session_flashed_messages', array('opt' => 0))
                        </div>

                        <div class="col-sm-12 mg10 mg-tp-px-10">

                            <table class="table table-striped table-hover table-bordered">
                                <thead>
                                <tr>
                                    <th width="25%">Código:</th>
                                    <td colspan="3">{{ $certificate->code }}</td>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td colspan="2" rowspan="3" width="40%"></td>
                                    <th width="30%">OC:</th>
                                    <td>{{ $certificate->oc->code }}</td>
                                </tr>
                                <tr>
                                    <th>Proveedor:</th>
                                    <td>
                                        <a href="/provider/{{ $certificate->oc->provider_id }}">
                                            {{ $certificate->oc->provider }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Monto certificado:</th>
                                    <td>{{ number_format($certificate->amount,2).' Bs' }}</td>
                                </tr>
                                <tr><td colspan="4"></td></tr>

                                <tr>
                                    <th colspan="2">Fecha comunicación de entrega:</th>
                                    <td colspan="2">{{ date_format($certificate->date_ack,'d-m-Y') }}</td>
                                </tr>
                                <tr>
                                    <th colspan="2">Fecha aceptación:</th>
                                    <td colspan="2">{{ date_format($certificate->date_acceptance,'d-m-Y') }}</td>
                                </tr>
                                <tr>
                                    <th colspan="2">Responsable aceptación:</th>
                                    <td colspan="2">
                                        {{ $certificate->user->priv_level==4 ? 'Administrador' : $certificate->user->name }}
                                    </td>
                                </tr>
                                <tr>
                                    <th colspan="2">Tipo de aceptación:</th>
                                    <td colspan="2">
                                        {{ $certificate->type_reception.($certificate->type_reception=='Parcial' ?
                                             ' ('.$certificate->num_reception.')' : '') }}
                                    </td>
                                </tr>

                                @if($certificate->observations)
                                    <tr><td colspan="4"></td></tr>
                                    <tr>
                                        <th colspan="4">Observaciones</th>
                                    </tr>
                                    <tr>
                                        <td colspan="4">
                                            {{ $certificate->observations }}
                                        </td>
                                    </tr>
                                @endif

                                <tr><td colspan="4"> </td></tr>
                                <tr>
                                    <th colspan="2">Certificado firmado:</th>
                                    <td colspan="2">
                                        <?php $signed_file_exists = false; ?>
                                        @foreach($certificate->files as $file)
                                            @if(substr($file->name,0,3) == 'CFD' || substr($file->name,0,4) == 'CTDF' /* old name */)
                                                @include('app.info_document_options', array('file'=>$file))
                                                {{--
                                                <a href="/download/{{ $file->id }}">
                                                    <img src="/imagenes/pdf-icon.png" alt="PDF"/>
                                                </a>
                                                <a href="/file/{{ $file->id }}">Detalles</a>
                                                &emsp;
                                                <a href="/display_file/{{ $file->id }}">Ver</a>
                                                --}}
                                                <?php $signed_file_exists = true; ?>
                                            @endif
                                        @endforeach
                                        @if(!$signed_file_exists)
                                            <a href="/files/oc_certificate/{{ $certificate->id }}">
                                                <i class="fa fa-upload"></i> Subir archivo
                                            </a>
                                        @endif
                                    </td>
                                </tr>

                                <tr><td colspan="4"></td></tr>
                                <tr>
                                    <th colspan="4">Archivos de respaldo:</th>
                                </tr>
                                @foreach($certificate->files as $file)
                                    @if(substr($file->name,0,4)=='CTD-')
                                        <tr>
                                            <td colspan="2">
                                                {{ $file->description }}
                                            </td>
                                            <td colspan="2">
                                                @include('app.info_document_options', array('file'=>$file))
                                                {{--
                                                <a href="/download/{{ $file->id }}">
                                                    <img src="/imagenes/pdf-icon.png" alt="PDF"/>
                                                </a>
                                                <a href="/file/{{ $file->id }}">Detalles</a>
                                                &emsp;
                                                <a href="/display_file/{{ $file->id }}">Ver</a>
                                                --}}
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                                <tr>
                                    <td colspan="4" align="center">
                                        <a href="/files/oc_certification_backup/{{ $certificate->id }}">
                                            <i class="fa fa-upload"></i> Subir respaldo
                                        </a>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        @if (($certificate->oc->payment_status != 'Concluido' && ($user->id == $certificate->user_id || $user->action->oc_ctf_edt
                            /*$user->priv_level==3*/)) || $user->priv_level == 4)
                          <div class="col-sm-12 mg10" align="center">
                            <a href="/oc_certificate/{{ $certificate->id }}/edit" class="btn btn-primary">
                              <i class="fa fa-pencil-square-o"></i> Modificar certificado
                            </a>
                          </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'oc_certificates','id'=>0))
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
