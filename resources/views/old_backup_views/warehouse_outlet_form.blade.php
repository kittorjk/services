<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 03/05/2017
 * Time: 05:48 PM
 */
?>

@extends('layouts.master')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/custom_autocomplete.css") }}">
    <script src="{{ 'https://cdnjs.cloudflare.com/ajax/libs/jquery.devbridge-autocomplete/1.2.27/jquery.autocomplete.js' }}">
    </script>

    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">
        <div class="panel panel-10gray">
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    {{ $outlet ? 'Modificar registro de salida' : 'Registrar salida de material' }}
                </div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-undo"></i>
                    </a>
                    <a href="{{ '/wh_outlet' }}" class="btn btn-warning" title="Volver a resumen de salidas">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                @if($outlet)
                    <form id="delete" action="/wh_outlet/{{ $outlet->id }}" method="post">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                    <form novalidate="novalidate" action="{{ '/wh_outlet/'.$outlet->id }}" method="post">
                        <input type="hidden" name="_method" value="put">
                        @else
                            <form novalidate="novalidate" action="{{ '/wh_outlet' }}" method="post">
                                @endif
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="form-group">
                                    <div class="input-group">

                                        <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                            <select required="required" class="form-control" name="warehouse_id" id="warehouse_id"
                                                    {{ $outlet ? 'disabled="disabled"' : '' }}>
                                                <option value="" hidden>Seleccione un almacén</option>
                                                @foreach($warehouses as $warehouse)
                                                    <option value="{{ $warehouse->id }}"
                                                            {{ $outlet&&$warehouse->id==$outlet->warehouse_id ?
                                                             'selected="selected"' : '' }}>{{ $warehouse->name }}</option>
                                                @endforeach
                                            </select>

                                            <div id="material_container" class="form-group has-feedback">
                                                <input required="required" type="text" class="form-control" name="material_name"
                                                       id="material_name" value="{{ $outlet ? $outlet->material->name : '' }}"
                                                       placeholder="Material" {{ $outlet ? 'disabled="disabled"' : '' }}>
                                                <div class="input-group" style="width: 100%;text-align: center" id="material_check_result" align="center"></div>
                                            </div>

                                            <div class="input-group">
                                                <span class="input-group-addon" style="width:120px;text-align: left">Cantidad:</span>
                                                <input required="required" type="number" class="form-control" name="qty" id="qty"
                                                       step="any" min="0" value="{{ $outlet&&$outlet->qty!=0 ? $outlet->qty : '' }}"
                                                       placeholder="0.00">
                                                <span class="input-group-addon"><div id="material_units"></div></span>
                                            </div>
                                            <div class="input-group" style="width: 100%" id="material_available" align="center"></div>

                                            <input required="required" type="text" class="form-control" name="delivered_by"
                                                   id="delivered_by" value="{{ $outlet ? $outlet->delivered_by :
                                                    ($user->priv_level!=4 ? $user->name : '') }}"
                                                   placeholder="Quién entrega el material">

                                            <input required="required" type="text" class="form-control" name="received_by"
                                                   id="received_by" value="{{ $outlet ? $outlet->received_by : '' }}"
                                                   placeholder="Quién recibe el material">

                                            <select required="required" class="form-control" name="outlet_type" id="outlet_type">
                                                <option value="" hidden>Seleccione el tipo de salida</option>
                                                @foreach($outlet_types as $type)
                                                    <option value="{{ $type->outlet_type }}"
                                                            {{ $outlet&&$type->outlet_type==$outlet->outlet_type ?
                                                             'selected="selected"' : '' }}>{{ $type->outlet_type }}</option>
                                                @endforeach
                                                <option value="Otro">Otro</option>
                                            </select>
                                            <input required="required" type="text" class="form-control" name="other_outlet_type"
                                                   id="other_outlet_type" placeholder="Tipo de salida" disabled="disabled">

                                            <textarea rows="3" required="required" class="form-control" name="reason"
                                                      placeholder="Motivo de salida">{{ $outlet ? $outlet->reason : '' }}</textarea>

                                        </div>

                                    </div>
                                </div>

                                @include('app.loader_gif')

                                <div class="form-group" align="center">
                                    <button type="submit" class="btn btn-success"
                                            onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                                        <i class="fa fa-floppy-o"></i> Guardar
                                    </button>
                                    @if($outlet&&$user->priv_level==4)
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
            $("#material_available").hide();
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

        var $outlet_type = $('#outlet_type'), $other_outlet_type = $('#other_outlet_type');
        $outlet_type.change(function () {
            if ($outlet_type.val() == 'Otro') {
                $other_outlet_type.removeAttr('disabled').show();
            } else {
                $other_outlet_type.attr('disabled', 'disabled').val('').hide();
            }
        }).trigger('change');

        $("#qty").keyup(function(){

            var material_name=$('#material_name').val(), warehouse_id=$('#warehouse_id').val(),
                    qty=$('#qty').val(), material_available=$('#material_available');

            $.post('/load_material_available', { material_name: material_name, warehouse_id: warehouse_id, qty: qty },
                    function(data){
                $("#material_available").html(data).show();
            });
        });
    </script>
@endsection
