<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 01/11/2017
 * Time: 12:14 PM
 */
?>

@extends('layouts.master')

@section('header')
    @parent
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">
        <div class="panel panel-sky">
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    {{ 'Modificar item' }}
                </div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-undo"></i>
                    </a>
                    <a href="{{ '/item?cat='.$category->id }}" class="btn btn-warning" title="Volver a lista de items por categoría">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <p><em>Nota.- Los campos con * son obligatorios</em></p>

                <form id="delete" action="/item/{{ $item->id }}" method="post">
                    <input type="hidden" name="_method" value="delete">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                </form>
                <form novalidate="novalidate" action="{{ '/item/'.$item->id }}" method="post">
                    <input type="hidden" name="_method" value="put">

                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="form-group">
                        <div class="input-group">

                            <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                <div class="input-group" style="width: 100%">
                                    <label for="number" class="input-group-addon" style="width: 23%;text-align: left">
                                        Número: <span class="pull-right">*</span>
                                    </label>

                                    <input required="required" type="text" class="form-control" name="number" id="number"
                                           value="{{ old('number') ?: $item->number }}"
                                           placeholder="Número de item según lista de cliente">
                                </div>

                                <div class="input-group" style="width: 100%">
                                    <label for="client_code" class="input-group-addon" style="width: 23%;text-align: left">
                                        Código:
                                    </label>

                                    <input required="required" type="text" class="form-control" name="client_code" id="client_code"
                                           value="{{ old('client_code') ?: $item->client_code }}"
                                           placeholder="Código de item según cliente">
                                </div>

                                <div class="input-group" style="width: 100%">
                                    <label for="description" class="input-group-addon" style="width: 23%;text-align: left">
                                        Nombre: <span class="pull-right">*</span>
                                    </label>

                                    <input required="required" type="text" class="form-control" name="description" id="description"
                                           value="{{ old('description') ?: $item->description }}"
                                           placeholder="Nombre o descripción de item">
                                </div>

                                <div class="input-group" style="width: 100%">
                                    <label for="detail" class="input-group-addon" style="width: 23%;text-align: left">
                                        Detalle:
                                    </label>

                                    <textarea rows="3" required="required" class="form-control" name="detail" id="detail"
                                              placeholder="Detalle o información adicional de item">{{
                                                            old('detail') ?: $item->detail }}</textarea>
                                </div>

                                <div class="input-group" style="width: 100%">
                                    <label for="units" class="input-group-addon" style="width: 23%;text-align: left">
                                        Unidades: <span class="pull-right">*</span>
                                    </label>

                                    <input required="required" type="text" class="form-control" name="units"
                                           id="units" value="{{ old('units') ?: $item->units }}"
                                           placeholder="Unidad de medida de item">
                                </div>

                                <div class="input-group" style="width: 75%">
                                    <span class="input-group-addon" style="width: 31%/*170px*/;text-align: left">
                                        Precio unitario: <span class="pull-right">*</span>
                                    </span>

                                    <input required="required" type="number" class="form-control" name="cost_unit_central"
                                           step="any" min="0" placeholder="Precio por unidad"
                                           value="{{ old('cost_unit_central') ?: ($item->cost_unit_central!=0 ?
                                                    $item->cost_unit_central : '') }}">
                                    <span class="input-group-addon">Bs.</span>
                                </div>

                                <div class="input-group" style="width: 100%">
                                    <label for="subcategory" class="input-group-addon" style="width: 23%;text-align: left">
                                        Subcategoría: <span class="pull-right">*</span>
                                    </label>

                                    <select required="required" class="form-control" name="subcategory" id="subcategory">
                                        <option value="" hidden>Seleccione una subcategoría</option>
                                        @foreach($subcategories as $subcategory)
                                            <option value="{{ $subcategory->subcategory }}"
                                                    {{ ($item->subcategory==$subcategory->subcategory)||
                                                        old('subcategory')==$subcategory->subcategory ?
                                                        'selected="selected"' : '' }}>{{ $subcategory->subcategory }}</option>
                                        @endforeach
                                        <option value="Otro" {{ old('subcategory')=='Otro' ?
                                            'selected="selected"' : '' }}>Otro</option>
                                    </select>
                                </div>
                                <input required="required" type="text" class="form-control" name="other_subcategory"
                                       id="other_subcategory" value="{{ old('other_subcategory') }}"
                                       placeholder="Nueva subcategoría *" disabled="disabled">

                            </div>

                        </div>
                    </div>

                    @include('app.loader_gif')

                    <div class="form-group" align="center">
                        <button type="submit" class="btn btn-success"
                                onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                            <i class="fa fa-floppy-o"></i> Guardar
                        </button>

                        {{--
                        @if($user->priv_level==4)
                            <button type="submit" form="delete" class="btn btn-danger">
                                <i class="fa fa-trash-o"></i> Eliminar
                            </button>
                        @endif
                        --}}
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
        var subcategory = $('#subcategory'), other_subcategory = $('#other_subcategory');
        subcategory.change(function () {
            if (subcategory.val()==='Otro') {
                other_subcategory.removeAttr('disabled').show();
            } else {
                other_subcategory.attr('disabled', 'disabled').val('').hide();
            }
        }).trigger('change');

        $(document).ready(function(){
            $("#wait").hide();
        });
    </script>
@endsection
