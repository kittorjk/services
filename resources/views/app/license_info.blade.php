<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 30/01/2017
 * Time: 06:03 PM
 */
?>

@extends('layouts.info_master')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

        <div class="panel panel-10gray">
            <div class="panel-heading" align="center">
                <div class="panel-title">Licencia de conducir</div>
            </div>
            <div class="panel-body">
                <div class="col-sm-12 mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning">
                        <i class="fa fa-arrow-circle-left"></i> Volver
                    </a>
                    <a href="{{ '/driver' }}" class="btn btn-warning" title="Ir a la tabla de asignaciones de vehículo">
                        <i class="fa fa-arrow-circle-up"></i> Asignaciones
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <div class="col-sm-12 mg10 mg-tp-px-10">

                    <table class="table table-striped table-hover table-bordered">
                        <tbody>
                        @if($license)
                            <tr>
                                <th width="40%">Nombre:</th>
                                <td>{{ $license->user->name }}</td>
                            </tr>
                            <tr>
                                <th>Número de licencia:</th>
                                <td>{{ $license->number }}</td>
                            </tr>
                            <tr>
                                <th>Categoría:</th>
                                <td>{{ $license->category }}</td>
                            </tr>
                            <tr>
                                <th>Fecha de vencimiento:</th>
                                <td>{{ date_format($license->exp_date,'d-m-Y') }}</td>
                            </tr>

                            <tr><td colspan="2"></td></tr>
                            <tr>
                                <th>Registro creado por</th>
                                <td>{{ $license->user ? $license->user->name : 'N/E' }}</td>
                            </tr>
                        @else
                            <tr>
                                <td align="center">Este usuario no tiene una licencia registrada</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
                @if($license && $user->action->acv_vhc_lic_mod)
                    {{--@if($user->priv_level>=2||$user->work_type=='Transporte')--}}
                        <div class="col-sm-12 mg10" align="center">
                            <a href="/license/{{ $license->id }}/edit" class="btn btn-primary">
                                <i class="fa fa-pencil-square-o"></i> Actualizar licencia
                            </a>
                        </div>
                    {{--@endif--}}
                @endif
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
