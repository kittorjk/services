<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 10/02/2017
 * Time: 05:03 PM
 */
?>

@extends('layouts.master')

@section('header')
    @parent
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
        <div class="panel panel-10gray" >
            <div class="panel-heading" align="center">
                <div class="panel-title">{{ $service_parameter ? 'Actualizar parámetro' : 'Crear nuevo parámetro' }}</div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                    <a href="{{ '/service_parameter' }}" class="btn btn-warning" title="Volver a resumen de parámetros de sistema">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                @if($service_parameter)
                    <form novalidate="novalidate" action="{{ '/service_parameter/'.$service_parameter->id }}" method="post">
                        <input type="hidden" name="_method" value="put">
                        @else
                            <form novalidate="novalidate" action="{{ '/service_parameter' }}" method="post">
                                @endif
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="form-group">
                                    <div class="input-group">

                                        <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                            <div class="input-group" style="width: 100%">
                                                <label for="name" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Nombre: <span class="pull-right">*</span>
                                                </label>

                                                <input required="required" type="text" class="form-control" name="name" id="name"
                                                       value="{{ $service_parameter ? $service_parameter->name : old('name') }}"
                                                       placeholder="Nombre de parámetro"
                                                        {{ $service_parameter ? 'disabled="disabled"' : '' }}>
                                            </div>

                                            <div class="input-group" style="width: 100%">
                                                <label for="description" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Descripción: <span class="pull-right">*</span>
                                                </label>

                                                <input required="required" type="text" class="form-control" name="description"
                                                       id="description" value="{{ $service_parameter ? $service_parameter->description :
                                                        old('description') }}" placeholder="Descripción de parámetro">
                                            </div>

                                            <div class="input-group" style="width: 100%">
                                                <label for="group" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Grupo: <span class="pull-right">*</span>
                                                </label>

                                                <select required="required" class="form-control" name="group" id="group"
                                                        {{ $service_parameter ? 'disabled="disabled"' : '' }}>
                                                    <option value="" hidden>Seleccione un grupo</option>
                                                    @foreach($groups as $group)
                                                        <option value="{{ $group->group }}"
                                                                {{ ($service_parameter&&$service_parameter->group==$group->group)||
                                                                    old('group')==$group->group ? 'selected="selected"' : '' }}
                                                                >{{ $group->group }}</option>
                                                    @endforeach
                                                    <option value="Otro">Otro</option>
                                                </select>
                                            </div>
                                            <input required="required" type="text" class="form-control" name="other_group"
                                                   id="other_group" placeholder="Nuevo grupo *" disabled="disabled">

                                            <div class="input-group col-sm-offset-1">
                                                <br><label>Seleccione el tipo de contenido: </label><br>

                                                <input class="radio-inline" type="radio" name="content_type" id="literal"
                                                       value="literal" {{ $service_parameter&&$service_parameter->literal_content!="" ?
                                                        'checked="checked"' : '' }}>
                                                        <label for="literal" style="font-weight: normal">Literal (texto)</label>
                                                &emsp;
                                                <input class="radio-inline" type="radio" name="content_type" id="numeric"
                                                       value="numeric" {{ $service_parameter&&$service_parameter->numeric_content!=0 ?
                                                        'checked="checked"' : '' }}>
                                                        <label for="numeric" style="font-weight: normal">Numérico</label>
                                            </div>

                                            <div class="input-group col-sm-offset-1" id="literal_container" style="width:50%">
                                                <br>
                                                <textarea rows="5" required="required" class="form-control" name="literal_content"
                                                    id="literal_content" placeholder="Ingrese el contenido aquí"
                                                    disabled="disabled">{{ $service_parameter ?
                                                     $service_parameter->literal_content : '' }}</textarea>
                                            </div>

                                            <div class="input-group col-sm-offset-1" id="numeric_container" style="width:50%">
                                                <br>
                                                <div class="input-group" style="width:100%">
                                                    <span class="input-group-addon" style="text-align: left">Valor:</span>
                                                    <input required="required" type="number" class="form-control" name="numeric_content"
                                                           id="numeric_content" step="any" min="0"
                                                           value="{{ $service_parameter&&$service_parameter->numeric_content!=0 ?
                                                            $service_parameter->numeric_content : '' }}"
                                                           placeholder="0.00" disabled="disabled">
                                                </div>
                                                <input required="required" type="text" class="form-control" name="units" id="units"
                                                       value="{{ $service_parameter ? $service_parameter->units : '' }}"
                                                       placeholder="Unidades" disabled="disabled">
                                            </div>

                                        </div>

                                    </div>
                                </div>

                                @include('app.loader_gif')

                                <div class="form-group" align="center">
                                    <button type="submit" class="btn btn-success"
                                            onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                                        <i class="fa fa-floppy-o"></i> Insertar al sistema
                                    </button>
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
        $(document).ready(function(){
            $("#wait").hide();
            $("#literal_container").hide();
            $("#numeric_container").hide();
        });

        var $group = $('#group'), $other_group = $('#other_group');
        $group.change(function () {
            if ($group.val()==='Otro') {
                $other_group.removeAttr('disabled').show();
            } else {
                $other_group.attr('disabled', 'disabled').val('').hide();
            }
        }).trigger('change');

        var $literal_content = $('#literal_content'), $literal_container = $('#literal_container'),
                $numeric_content = $("#numeric_content"), $numeric_container = $("#numeric_container"),
                $units = $("#units"), $literal = $("#literal"), $numeric = $("#numeric");

        $('input[type=radio][name=content_type]').click(function(){
            if ($literal.prop('checked')) {
                $literal_container.show();
                $literal_content.removeAttr('disabled').show();
                $numeric_container.hide();
                $numeric_content.attr('disabled','disabled').hide();
                $units.attr('disabled','disabled').hide();
            }
            else if ($numeric.prop('checked')) {
                $numeric_container.show();
                $numeric_content.removeAttr('disabled').show();
                $units.removeAttr('disabled').show();
                $literal_container.hide();
                $literal_content.attr('disabled', 'disabled').hide();
            }
        }).trigger('click');

    </script>
@endsection
