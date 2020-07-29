<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 31/10/2017
 * Time: 11:02 AM
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
                    {{ 'Actualizar información de categoría' }}
                </div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                    <a href="{{ '/item_category' }}" class="btn btn-warning" title="Volver a lista de categorías">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <p><em>Nota.- Los campos con * son obligatorios</em></p>

                <form id="delete" action="/item_category/{{ $category->id }}" method="post">
                    <input type="hidden" name="_method" value="delete">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                </form>
                <form novalidate="novalidate" action="{{ '/item_category/'.$category->id }}" method="post">
                    <input type="hidden" name="_method" value="put">

                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="form-group">
                        <div class="input-group">

                            <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                <div class="input-group" style="width: 100%">
                                    <label for="name" class="input-group-addon" style="width: 23%;text-align: left">
                                        Nombre: <span class="pull-right">*</span>
                                    </label>

                                    <input required="required" type="text" class="form-control" name="name" id="name"
                                           value="{{ old('name') ?: $category->name }}"
                                           placeholder="Nombre de categoría">
                                </div>

                                <div class="input-group" style="width: 100%">
                                    <label for="description" class="input-group-addon" style="width: 23%;text-align: left">
                                        Descripción:
                                    </label>

                                    <input required="required" type="text" class="form-control" name="description"
                                           id="description" value="{{ old('description') ?: $category->description }}"
                                           placeholder="Detalle o información adicional de categoría">
                                </div>

                                <div class="input-group" style="width: 100%">
                                    <label for="area" class="input-group-addon" style="width: 23%;text-align: left">
                                        Área: <span class="pull-right">*</span>
                                    </label>

                                    <select required="required" class="form-control" name="area" id="area">
                                        <option value="" hidden>Seleccione un área de trabajo</option>
                                        <option value="Fibra óptica"
                                                {{ ($category->area=='Fibra óptica')||
                                                    old('area')=='Fibra óptica' ? 'selected="selected"' :
                                                     '' }}>Fibra óptica</option>
                                        <option value="Radiobases"
                                                {{ ($category->area=='Radiobases')||
                                                    old('area')=='Radiobases' ? 'selected="selected"' :
                                                     '' }}>Radiobases</option>
                                        <option value="Instalación de energía"
                                                {{ ($category->area=='Instalación de energía')||
                                                    old('area')=='Instalación de energía' ? 'selected="selected"' :
                                                     '' }}>Instalación de energía</option>
                                        <option value="Obras Civiles"
                                                {{ ($category->area=='Obras Civiles')||
                                                    old('area')=='Obras Civiles' ? 'selected="selected"' :
                                                     '' }}>Obras Civiles</option>
                                        <option value="Venta de material"
                                                {{ ($category->area=='Venta de material')||
                                                    old('area')=='Venta de material' ? 'selected="selected"' :
                                                     '' }}>Venta de material</option>
                                    </select>
                                </div>

                                <div class="input-group" style="width: 100%">
                                    <label for="client" class="input-group-addon" style="width: 23%;text-align: left">
                                        Cliente: <span class="pull-right">*</span>
                                    </label>

                                    <select required="required" class="form-control" name="client" id="client">
                                        <option value="" hidden>Seleccione un cliente</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->client }}"
                                                    {{ ($category->client==$client->client)||
                                                        old('client')==$client->client ? 'selected="selected"' :
                                                         '' }}>{{ $client->client }}</option>
                                        @endforeach
                                        <option value="Otro" {{ old('client')=='Otro' ?
                                            'selected="selected"' : '' }}>Otro</option>
                                    </select>
                                </div>
                                <input required="required" type="text" class="form-control" name="other_client"
                                       id="other_client" value="{{ old('other_client') }}"
                                       placeholder="Cliente *" disabled="disabled">

                                <div class="input-group" style="width: 100%">
                                    <label for="project_id" class="input-group-addon" style="width: 23%;text-align: left">
                                        Proyecto:
                                    </label>

                                    <select required="required" class="form-control" name="project_id" id="project_id">
                                        <option value="" hidden>Seleccione un proyecto</option>
                                        @foreach($projects as $project)
                                            <option value="{{ $project->id }}"
                                                    {{ ($category->project_id==$project->id)||
                                                        old('project_id')==$project->id ? 'selected="selected"' : '' }}
                                                    title="{{ $project->name }}">{{ str_limit($project->name, 100) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

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
        var client = $('#client'), other_client = $('#other_client');
        client.change(function () {
            if (client.val()==='Otro') {
                other_client.removeAttr('disabled').show();
            } else {
                other_client.attr('disabled', 'disabled').val('').hide();
            }
        }).trigger('change');

        $(document).ready(function(){
            $("#wait").hide();
        });
    </script>
@endsection
