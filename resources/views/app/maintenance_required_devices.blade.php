<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 29/03/2017
 * Time: 02:34 PM
 */
?>

@extends('layouts.master')

@section('header')
    @parent
    <style>
        .submit_button, .submit_button:hover {
            background:none!important;
            border:none;
            padding:0!important;
            font: inherit;
            cursor: pointer;
            color: black;
        }
        .submit_button:hover {
            color: darkgrey;
            text-decoration: underline;
        }
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 mg-btm-px-40 col-md-8 col-md-offset-2 col-sm-8 col-sm-offset-2">
        <div class="panel panel-info">
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    {{ 'Equipos que requieren mantenimiento' }}
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
                        <tbody>
                        <tr>
                            <th width="30%">Equipo</th>
                            <th width="25%">Último mantenimiento</th>
                            <th>Condición</th>
                        </tr>
                        @foreach($devices as $device)
                            @if($device->flags[1]==1)
                                <tr>
                                    <td>
                                        <a href="/device/{{ $device->id }}">{{ $device->type.' '.$device->serial }}</a>
                                    </td>
                                    <td>
                                        @if($device->last_maintenance)
                                            <a href="/maintenance/{{ $device->last_maintenance->id }}">
                                                {{ date_format($device->last_maintenance->date,'d-m-Y') }}
                                            </a>
                                        @else
                                            {{ 'Nunca' }}
                                        @endif
                                    </td>
                                    <td>
                                        <form method="post" action="{{ '/maintenance/request/device' }}" id="maintenance_request"
                                              accept-charset="UTF-8" enctype="multipart/form-data"
                                              onsubmit="return confirm('Confirma que este equipo fue puesto en mantenimiento?');">

                                            {{ $device->condition }}

                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <input type="hidden" name="active" value="{{ $device->serial }}">
                                            <input type="hidden" name="device_id" value="{{ $device->id }}">
                                            <input type="hidden" name="type" value="Correctivo">

                                            @if($user->action->acv_mnt_add)
                                                <button type="submit" class="btn btn-primary submit_button pull-right"
                                                        title="Registrar mantenimiento correctivo de este equipo">
                                                    <i class="fa fa-wrench"></i> Poner en manto.
                                                </button>
                                            @endif
                                        </form>
                                    </td>
                                </tr>
                            @endif
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
