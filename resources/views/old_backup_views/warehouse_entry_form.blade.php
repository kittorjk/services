<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 03/05/2017
 * Time: 12:07 PM
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
        <div class="panel panel-10gray">
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    {{ $entry ? 'Modificar registro de ingreso' : 'Registrar ingreso de material' }}
                </div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                    <a href="{{ '/wh_entry' }}" class="btn btn-warning" title="Volver a resumen de ingresos">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                @if($entry)
                    <form id="delete" action="/wh_entry/{{ $entry->id }}" method="post">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                    <form novalidate="novalidate" action="{{ '/wh_entry/'.$entry->id }}" method="post">
                        <input type="hidden" name="_method" value="put">
                        @else
                            <form novalidate="novalidate" action="{{ '/wh_entry' }}" method="post">
                                @endif
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="form-group">
                                    <div class="input-group">

                                        <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                            <select required="required" class="form-control" name="warehouse_id"
                                                    {{ $entry ? 'disabled="disabled"' : '' }}>
                                                <option value="" hidden>Seleccione un almacén</option>
                                                @foreach($warehouses as $warehouse)
                                                    <option value="{{ $warehouse->id }}"
                                                        {{ $entry&&$warehouse->id==$entry->warehouse_id ?
                                                         'selected="selected"' : '' }}>{{ $warehouse->name }}</option>
                                                @endforeach
                                            </select>

                                            <div id="material_container" class="form-group has-feedback">
                                                <input required="required" type="text" class="form-control" name="material_name"
                                                       id="material_name" value="{{ $entry ? $entry->material->name : '' }}"
                                                       placeholder="Material"
                                                        {{ $entry ? 'disabled="disabled"' : '' }}>
                                                <div class="input-group" style="width: 100%;text-align: center" id="material_check_result" align="center"></div>
                                            </div>

                                            <div class="input-group">
                                                <span class="input-group-addon" style="width:120px;text-align: left">Cantidad:</span>
                                                <input required="required" type="number" class="form-control" name="qty"
                                                       step="any" min="0" value="{{ $entry&&$entry->qty!=0 ? $entry->qty : '' }}"
                                                       placeholder="0.00">
                                                <span class="input-group-addon"><div id="material_units"></div></span>
                                            </div>

                                            <input required="required" type="text" class="form-control" name="delivered_by"
                                                   id="delivered_by" value="{{ $entry ? $entry->delivered_by :
                                                    ($user->priv_level!=4 ? $user->name : '') }}"
                                                   placeholder="Quién entrega el material">

                                            <input required="required" type="text" class="form-control" name="received_by"
                                                   id="received_by" value="{{ $entry ? $entry->received_by : '' }}"
                                                   placeholder="Quién recibe el material">

                                            <select required="required" class="form-control" name="entry_type" id="entry_type">
                                                <option value="" hidden>Seleccione el tipo de ingreso</option>
                                                @foreach($entry_types as $type)
                                                    <option value="{{ $type->entry_type }}"
                                                            {{ $entry&&$type->entry_type==$entry->entry_type ?
                                                             'selected="selected"' : '' }}>{{ $type->entry_type }}</option>
                                                @endforeach
                                                <option value="Otro">Otro</option>
                                            </select>
                                            <input required="required" type="text" class="form-control" name="other_entry_type"
                                                   id="other_entry_type" placeholder="Tipo de ingreso" disabled="disabled">

                                            <textarea rows="3" required="required" class="form-control" name="reason"
                                                      placeholder="Motivo de ingreso">{{ $entry ? $entry->reason : '' }}</textarea>

                                        </div>

                                    </div>
                                </div>

                                @include('app.loader_gif')

                                <div class="form-group" align="center">
                                    <button type="submit" class="btn btn-success"
                                            onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                                        <i class="fa fa-floppy-o"></i> Guardar
                                    </button>
                                    @if($entry&&$user->priv_level==4)
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

        function check_material_existence(e){

            var material_name=$('#material_name').val();

            if(material_name.length >0){
                $.post('/check_material_existence', { name: material_name }, function(data){
                    $("#material_check_result").html(data.message).show();
                    $("#material_units").html(data.units);
                    if(data.status=="warning"){
                        $('#material_container').addClass("has-warning").removeClass("has-success");
                    }
                    else if(data.status=="success"){
                        $('#material_container').addClass("has-success").removeClass("has-warning");
                    }
                });
            }
            else{
                $("#material_check_result").hide();
                $('#material_container').removeClass("has-warning").removeClass("has-success");
                $("#material_units").html('');
            }
        }

        $(document).ready(function(){
            $("#wait").hide();
            $("#material_check_result").hide();
            $('#material_name').focusout(check_material_existence);
        });

        $('#material_name').autocomplete({
            type: 'post',
            serviceUrl:'/autocomplete/materials',
            dataType: 'JSON',
            onSelect: check_material_existence
        });

        $('#delivered_by').autocomplete({
            type: 'post',
            serviceUrl:'/autocomplete/users',
            dataType: 'JSON'
        });

        $('#received_by').autocomplete({
            type: 'post',
            serviceUrl:'/autocomplete/users',
            dataType: 'JSON'
        });

        var $entry_type = $('#entry_type'), $other_entry_type = $('#other_entry_type');
        $entry_type.change(function () {
            if ($entry_type.val() == 'Otro') {
                $other_entry_type.removeAttr('disabled').show();
            } else {
                $other_entry_type.attr('disabled', 'disabled').val('').hide();
            }
        }).trigger('change');
    </script>
@endsection
