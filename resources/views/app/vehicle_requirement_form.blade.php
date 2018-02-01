<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 11/09/2017
 * Time: 06:33 PM
 */
?>

@extends('layouts.master')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/custom_autocomplete.css") }}">
    <script src="{{ asset('https://cdnjs.cloudflare.com/ajax/libs/jquery.devbridge-autocomplete/1.2.27/jquery.autocomplete.js') }}">
    </script>

    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">
        <div class="panel panel-violet">
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    {{ $requirement ? 'Modificar requerimiento' : 'Registrar requerimiento de vehículo' }}
                </div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-undo"></i>
                    </a>
                    <a href="{{ '/vehicle_requirement' }}" class="btn btn-warning" title="Volver a resumen de requerimientos">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                @if($requirement)
                    <form id="delete" action="/vehicle_requirement/{{ $requirement->id }}" method="post">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                    <form novalidate="novalidate" action="{{ '/vehicle_requirement/'.$requirement->id }}" method="post">
                        <input type="hidden" name="_method" value="put">
                        @else
                            <form novalidate="novalidate" action="{{ '/vehicle_requirement' }}" method="post">
                                @endif
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="form-group">
                                    <div class="input-group">

                                        <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                            <div class="input-group" style="width: 75%">
                                                <label for="type" class="input-group-addon" style="width: 31%;text-align: left">
                                                    T. requerimiento:
                                                </label>

                                                <select required="required" class="form-control" name="type" id="type">
                                                    <option value="" hidden>Seleccione el tipo de requerimiento</option>
                                                    @foreach(App\VehicleRequirement::$types as $key => $type)
                                                        <option value="{{ $key }}"
                                                            {{ ($requirement&&$requirement->type==$key)||old('type')==$key ?
                                                                'selected="selected"' : '' }}>{{ $type }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div id="branch_origin_container" class="input-group" style="width: 75%">
                                                <label for="branch_origin" class="input-group-addon" style="width: 31%;text-align: left">
                                                    Orígen:
                                                </label>

                                                <select required="required" class="form-control" name="branch_origin"
                                                        id="branch_origin" disabled="disabled">
                                                    <option value="" hidden>Seleccione la sucursal de orígen</option>
                                                    @foreach($branches as $branch)
                                                        <option value="{{ $branch->city }}"
                                                            {{ ($requirement&&$requirement->branch_origin==$branch->city)||
                                                                old('branch_origin')==$branch->city ?
                                                                 'selected="selected"' : '' }}>{{ $branch->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="input-group" style="width: 75%">
                                                <label for="vehicle_type" class="input-group-addon" style="width: 31%;text-align: left">
                                                    Tipo de vehículo:
                                                </label>

                                                <select required="required" class="form-control" name="vehicle_type" id="vehicle_type">
                                                    <option value="" hidden>Seleccione un tipo de vehículo</option>
                                                    @foreach($vehicle_types as $vehicle_type)
                                                        <option value="{{ $vehicle_type->type }}"
                                                            {{ ($requirement&&$requirement->vehicle->type==$vehicle_type->type)||
                                                                old('vehicle_type')==$vehicle_type->type ?
                                                                 'selected="selected"' : '' }}>{{ $vehicle_type->type }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div id="vehicle_container" class="input-group" style="width: 75%">
                                                <label for="vehicle_id" class="input-group-addon" style="width: 31%;text-align: left">
                                                    Vehículo:
                                                </label>

                                                <select required="required" class="form-control" name="vehicle_id" id="vehicle_id"
                                                        data-value="{{ $requirement ? $requirement->vehicle_id : '' }}">
                                                    <option value="" hidden>Seleccione un vehículo</option>
                                                    {{-- Dynamically loaded items go here --}}
                                                </select>
                                            </div>

                                            <div id="from_container" class="form-group has-feedback">
                                                <div class="input-group" style="width: 100%">
                                                    <label for="from_name" class="input-group-addon" style="width: 23%;text-align: left">
                                                        Resp. actual:
                                                    </label>

                                                    <input required="required" type="text" class="form-control" name="from_name"
                                                           id="from_name"
                                                           value="{{ $requirement&&$requirement->person_from ?
                                                                $requirement->person_from->name : old('from_name') }}"
                                                           placeholder="Persona que entregará el vehículo"
                                                           readonly="readonly">
                                                </div>

                                                <div class="input-group" style="width: 100%;text-align: center" id="from_check" align="center"></div>
                                            </div>

                                            <div id="branch_destination_container" class="input-group" style="width: 75%">
                                                <label for="branch_destination" class="input-group-addon"
                                                       style="width: 31%;text-align: left">
                                                    Destino:
                                                </label>

                                                <select required="required" class="form-control" name="branch_destination"
                                                        id="branch_destination" disabled="disabled">
                                                    <option value="" hidden>Seleccione la sucursal de destino</option>
                                                    @foreach($branches as $branch)
                                                        <option value="{{ $branch->city }}"
                                                            {{ ($requirement&&$requirement->branch_destination==$branch->city)||
                                                                old('branch_destination')==$branch->city ?
                                                                'selected="selected"' : '' }}>{{ $branch->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div id="for_container" class="form-group has-feedback">
                                                <div class="input-group" style="width: 100%">
                                                    <label for="for_name" class="input-group-addon" style="width: 23%;text-align: left">
                                                        Entregar a:
                                                    </label>

                                                    <input required="required" type="text" class="form-control" name="for_name"
                                                           id="for_name"
                                                           value="{{ $requirement&&$requirement->person_for ?
                                                                $requirement->person_for->name : old('for_name') }}"
                                                           placeholder="Persona que recibirá el vehículo"
                                                           disabled="disabled">
                                                </div>

                                                <div class="input-group" style="width: 100%;text-align: center" id="for_check" align="center"></div>
                                            </div>

                                            <textarea rows="3" required="required" class="form-control" name="reason"
                                                      id="reason" placeholder="Motivo de requerimiento">{{ $requirement ?
                                                       $requirement->reason : old('reason') }}</textarea>

                                        </div>

                                    </div>
                                </div>

                                @include('app.loader_gif')

                                <div class="form-group" align="center">
                                    <button type="submit" class="btn btn-success"
                                            onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                                        <i class="fa fa-send"></i> Enviar requerimiento
                                    </button>

                                    @if($requirement||$user->priv_level==4)
                                        <button type="submit" form="delete" class="btn btn-danger">
                                            <i class="fa fa-trash-o"></i> Eliminar
                                        </button>
                                    @endif
                                </div>
                            </form>
            </div>
        </div>
    </div>

@endsection

@section('footer')
@endsection

@section('javascript')
    <script src="{{ asset('app/js/prevent_enter_form_submit.js') }}"></script> {{-- Avoid submitting form on enter press --}}
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function enable_field_to(){
            var type = $('#type'), branch_destination_container = $('#branch_destination_container'),
                    for_container = $('#for_container'), for_name = $('#for_name'), branch_destination = $('#branch_destination'),
                    branch_origin_container = $('#branch_origin_container'), branch_origin = $('#branch_origin');

            if(type.val()==='borrow'||type.val()==='transfer_tech'){
                for_container.show();
                for_name.removeAttr('disabled').show();
                branch_destination.hide();
                branch_destination_container.attr('disabled', 'disabled').hide();
            }
            else if(type.val()==='transfer_branch'||type.val()==='devolution'){
                for_container.hide();
                for_name.attr('disabled', 'disabled').hide();
                branch_destination.removeAttr('disabled').show();
                branch_destination_container.show();
            }

            if(type.val()==='borrow'||type.val()==='transfer_branch'){
                branch_origin_container.show();
                branch_origin.removeAttr('disabled').show();
            }
            else{
                branch_origin_container.hide();
                branch_origin.attr('disabled', 'disabled').hide();
            }
        }

        function load_vehicles(){

            $.post('/dynamic_requirement/vehicle', { req_type: $('#type').val(), active_type: $('#vehicle_type').val(),
                prev_value: $('#vehicle_id').data('value'), branch: $('#branch_origin').val() }, function(data){

                $('#vehicle_container').show();
                $("#vehicle_id").html(data).show();
            });
        }

        function load_responsible(){

            $.post('/load_name/vehicle_requirement_form', { query_id: $('#vehicle_id').val() }, function(data){
                $('#from_name').val(data);
            });
        }

        function check_existence(){

            var from_name=$('#from_name').val(), for_name=$('#for_name').val();

            if(from_name.length >0){
                $.post('/check_existence', { resp_name: from_name }, function(data){
                    $("#from_check").html(data.message).show();
                    if(data.status==="warning"){
                        $('#from_container').addClass("has-warning").removeClass("has-success");
                    }
                    else if(data.status==="success"){
                        $('#from_container').addClass("has-success").removeClass("has-warning");
                    }
                });
            }
            else{
                $("#from_check").hide();
                $('#from_container').removeClass("has-warning").removeClass("has-success");
            }

            if(for_name.length >0){
                $.post('/check_existence', { resp_name: for_name }, function(data){
                    $("#for_check").html(data.message).show();
                    if(data.status==="warning"){
                        $('#for_container').addClass("has-warning").removeClass("has-success");
                    }
                    else if(data.status==="success"){
                        $('#for_container').addClass("has-success").removeClass("has-warning");
                    }
                });
            }
            else{
                $("#for_check").hide();
                $('#for_container').removeClass("has-warning").removeClass("has-success");
            }
        }

        $('#vehicle_type').change(load_vehicles);
        $('#vehicle_id').change(load_responsible);
        $('#type').change(enable_field_to);
        $('#branch_origin').change(load_vehicles);

        $(document).ready(function(){
            $("#wait").hide();
            $("#from_check").hide();
            $("#for_check").hide();
            $('#from_name').focusout(check_existence);
            $('#for_name').focusout(check_existence);
            $('#for_container').hide();
            $('#branch_destination_container').hide();
            enable_field_to();
            load_vehicles();
        });

        $('#from_name').autocomplete({
            type: 'post',
            serviceUrl:'/autocomplete/users',
            dataType: 'JSON',
            onSelect: check_existence
        });

        $('#for_name').autocomplete({
            type: 'post',
            serviceUrl:'/autocomplete/users',
            dataType: 'JSON',
            onSelect: check_existence
        });
    </script>
@endsection
