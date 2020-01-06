<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 03/05/2017
 * Time: 09:55 AM
 */
?>

@extends('layouts.master')

@section('header')
    @parent
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">
        <div class="panel panel-10gray">
            <div class="panel-heading" align="center">
                <div class="panel-title">{{ $material ? 'Actualizar datos de material' : 'Registrar un nuevo material' }}</div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-undo"></i>
                    </a>
                    <a href="{{ '/material' }}" class="btn btn-warning" title="Volver a lista de materiales">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                @if($material)
                    <form id="delete" action="/material/{{ $material->id }}" method="post">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                    <form novalidate="novalidate" action="{{ '/material/'.$material->id }}" method="post">
                        <input type="hidden" name="_method" value="put">
                        @else
                            <form novalidate="novalidate" action="{{ '/material' }}" method="post">
                                @endif
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="form-group">
                                    <div class="input-group">

                                        <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                            <input required="required" type="text" class="form-control" name="name"
                                                   value="{{ $material ? $material->name : '' }}" placeholder="Nombre">
                                            <input required="required" type="text" class="form-control" name="type"
                                                   value="{{ $material ? $material->type : '' }}" placeholder="Tipo">
                                            <input required="required" type="text" class="form-control" name="units"
                                                   value="{{ $material ? $material->units : '' }}" placeholder="Unidades">

                                            <textarea rows="3" required="required" class="form-control" name="description"
                                                      placeholder="Descripción">{{ $material ?
                                                       $material->description : '' }}</textarea>

                                            <div class="input-group">
                                                <span class="input-group-addon" style="width:120px;text-align: left">Costo por unidad:</span>
                                                <input required="required" type="number" class="form-control" name="cost_unit"
                                                       step="any" min="0"
                                                       value="{{ $material&&$material->cost_unit!=0 ? $material->cost_unit : '' }}"
                                                       placeholder="0.00">
                                                <span class="input-group-addon">Bs</span>
                                            </div>

                                            <input required="required" type="text" class="form-control" name="brand"
                                                   value="{{ $material ? $material->brand : '' }}" placeholder="Marca">
                                            <input required="required" type="text" class="form-control" name="supplier"
                                                   value="{{ $material ? $material->supplier : '' }}" placeholder="Proveedor">

                                            <select required="required" class="form-control" name="category" id="category">
                                                <option value="" hidden>Seleccione una categoría de materiales</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->category }}"
                                                        {{ $material&&$category->category==$material->category ?
                                                         'selected="selected"' : '' }}>{{ $category->category }}</option>
                                                @endforeach
                                                <option value="Otro">Otro</option>
                                            </select>
                                            <input required="required" type="text" class="form-control" name="other_category"
                                                   id="other_category" placeholder="Categoría" disabled="disabled">

                                        </div>

                                    </div>
                                </div>

                                @include('app.loader_gif')

                                <div class="form-group" align="center">
                                    <button type="submit" class="btn btn-success"
                                            onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                                        <i class="fa fa-floppy-o"></i> Guardar
                                    </button>
                                    @if($material&&$user->priv_level==4)
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
        var $category = $('#category'), $other_category = $('#other_category');
        $category.change(function () {
            if ($category.val() == 'Otro') {
                $other_category.removeAttr('disabled').show();
            } else {
                $other_category.attr('disabled', 'disabled').val('').hide();
            }
        }).trigger('change');

        $(document).ready(function(){
            $("#wait").hide();
        });
    </script>
@endsection
