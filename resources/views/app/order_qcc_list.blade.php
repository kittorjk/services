<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 30/01/2017
 * Time: 01:17 PM
 */ ?>

@extends('layouts.master')

@section('header')
    @parent
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1">
        <div class="panel panel-sky">
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    {{ 'Certificados de control de calidad recientes' }}
                </div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning">
                        <i class="fa fa-arrow-circle-left"></i> Volver
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                    <div class="col-sm-12">
                        <table class="table table-striped table-hover table-bordered">
                            <thead>
                            <tr>
                                <th width="15%">Recepción</th>
                                <th width="20%">Certificado</th>
                                <th>Proyecto / Asignación / Sitio</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($qccs as $qcc)
                                <tr>
                                    <td>{{ date_format($qcc->created_at,'d-m-Y') }}</td>
                                    <td>
                                        <a href="/download/{{ $qcc->id }}" title="Descargar certificado">
                                            <i class="fa fa-download"></i> {{ $qcc->name }}
                                        </a>
                                    </td>
                                    <td>
                                        {{ $qcc->imageable_type=='App\Assignment' ? $qcc->imageable->name :
                                            $qcc->imageable->assignment->name.' - '.$qcc->imageable->name }}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

            </div>
        </div>
    </div>

@endsection

@section('footer')
@endsection
