<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 14/02/2017
 * Time: 12:46 PM
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
        <div class="panel panel-info" >
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    {{ 'Vehículos que requieren mantenimiento' }}
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
                            <th>Vehículo</th>
                            <th>Último mantenimiento</th>
                            <th>Requiere</th>
                        </tr>
                        @foreach($vehicles as $vehicle)
                            @if($vehicle->flags[1]==1||
                                ($vehicle->last_mant20000&&(($vehicle->mileage-$vehicle->last_mant20000->usage)>19900))||
                                (!$vehicle->last_mant20000&&$vehicle->mileage>19900)||
                                ($vehicle->last_mant10000&&(($vehicle->mileage-$vehicle->last_mant10000->usage)>9900))||
                                (!$vehicle->last_mant10000&&$vehicle->mileage>9900)||
                                ($vehicle->last_mant5000&&(($vehicle->mileage-$vehicle->last_mant5000->usage)>4900))||
                                (!$vehicle->last_mant5000&&$vehicle->mileage>4900)||
                                ($vehicle->last_mant2500&&(($vehicle->mileage-$vehicle->last_mant2500->usage)>2400))||
                                (!$vehicle->last_mant2500&&$vehicle->mileage>2400))

                                <tr>
                                    <td>
                                        <a href="/vehicle/{{ $vehicle->id }}">
                                            {{ $vehicle->type.' '.$vehicle->license_plate }}
                                        </a>
                                    </td>
                                    <td>
                                        @if($vehicle->last_maintenance)
                                            <a href="/maintenance/{{ $vehicle->last_maintenance->id }}">
                                                {{ date_format($vehicle->last_maintenance->date,'d-m-Y') }}
                                            </a>
                                            {{ $vehicle->last_maintenance->type=='Correctivo' ? 'Correctivo' : 'Preventivo '.
                                            ($vehicle->last_maintenance->parameter ?
                                             $vehicle->last_maintenance->parameter->name : '') }}
                                        @else
                                            {{ 'Nunca' }}
                                        @endif
                                    </td>
                                    <td>
                                        <form method="post" action="{{ '/maintenance/request/vehicle' }}" id="maintenance_request"
                                              accept-charset="UTF-8" enctype="multipart/form-data"
                                              onsubmit="return confirm('Confirma que este vehículo fue puesto en mantenimiento?');">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <input type="hidden" name="active" value="{{ $vehicle->license_plate }}">
                                            <input type="hidden" name="usage" value="{{ $vehicle->mileage }}">
                                            <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">

                                            @if($vehicle->flags[1]==1)
                                                {{ 'Manto. Correctivo' }}
                                                <input type="hidden" name="type" value="Correctivo">
                                            @elseif(($vehicle->last_mant20000&&
                                                (($vehicle->mileage-$vehicle->last_mant20000->usage)>19900))||
                                                (!$vehicle->last_mant20000&&$vehicle->mileage>19900))
                                                {{ 'Manto. Preventivo c/20000 Km' }}
                                                <input type="hidden" name="type" value="Preventivo">
                                                <input type="hidden" name="parameter_id" value="5">
                                            @elseif(($vehicle->last_mant10000&&
                                                (($vehicle->mileage-$vehicle->last_mant10000->usage)>9900))||
                                                (!$vehicle->last_mant10000&&$vehicle->mileage>9900))
                                                {{ 'Manto. Preventivo c/10000 Km' }}
                                                <input type="hidden" name="type" value="Preventivo">
                                                <input type="hidden" name="parameter_id" value="4">
                                            @elseif(($vehicle->last_mant5000&&
                                                (($vehicle->mileage-$vehicle->last_mant5000->usage)>4900))||
                                                (!$vehicle->last_mant5000&&$vehicle->mileage>4900))
                                                {{ 'Manto. Preventivo c/5000 Km' }}
                                                <input type="hidden" name="type" value="Preventivo">
                                                <input type="hidden" name="parameter_id" value="3">
                                            @elseif(($vehicle->last_mant2500&&
                                                (($vehicle->mileage-$vehicle->last_mant2500->usage)>2400))||
                                                (!$vehicle->last_mant2500&&$vehicle->mileage>2400))
                                                {{ 'Manto. Preventivo c/2500 Km' }}
                                                <input type="hidden" name="type" value="Preventivo">
                                                <input type="hidden" name="parameter_id" value="2">
                                            @endif

                                            @if($user->action->acv_mnt_add)
                                                <button type="submit" class="btn btn-primary submit_button pull-right"
                                                        title="poner este vehículo en mantenimiento">
                                                    <i class="fa fa-wrench"></i>
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
