<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 18/01/2018
 * Time: 04:28 PM
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

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">

        <div class="panel panel-sky">
            <div class="panel-heading" align="center">
                <div class="panel-title">{{ $tender ? 'Modificar registro de licitación' : 'Agregar nueva licitación' }}</div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                    <a href="{{ '/tender' }}" class="btn btn-warning" title="Volver a la tabla de licitaciones">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                @if($tender)
                    <form id="delete" action="/tender/{{ $tender->id }}" method="post">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                    <form novalidate="novalidate" action="{{ '/tender/'.$tender->id }}" method="post">
                        <input type="hidden" name="_method" value="put">
                        @else
                            <form novalidate="novalidate" action="{{ '/tender' }}" method="post" enctype="multipart/form-data">
                                @endif
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="form-group">
                                    <div class="input-group">

                                        <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                            <div class="input-group" style="width: 100%">
                                                <label for="name" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Licitación: <span class="pull-right">*</span>
                                                </label>

                                                <input required="required" type="text" class="form-control" name="name" id="name"
                                                       value="{{ $tender ? $tender->name : old('name') }}"
                                                       placeholder="Nombre o identificación de la licitación">
                                            </div>

                                            <textarea rows="3" required="required" class="form-control" name="description"
                                                      placeholder="Descripción o detalle de la licitación">{{ $tender ?
                                                 $tender->description : old('description') }}</textarea>

                                            <div class="input-group" style="width: 100%">
                                                <label for="area" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Área: <span class="pull-right">*</span>
                                                </label>

                                                <select required="required" class="form-control" name="area" id="area">
                                                    <option value="" hidden>Seleccione el área de trabajo</option>
                                                    <option value="Fibra óptica"
                                                            {{ ($tender&&$tender->area=='Fibra óptica')||
                                                                old('area')=='Fibra óptica' ?
                                                                'selected="selected"' : '' }}>Fibra óptica</option>
                                                    <option value="Radiobases"
                                                            {{ ($tender&&$tender->area=='Radiobases')||
                                                                old('area')=='Radiobases' ?
                                                                'selected="selected"' : '' }}>Radiobases</option>
                                                    <option value="Instalación de energía"
                                                            {{ ($tender&&$tender->area=='Instalación de energía')||
                                                                old('area')=='Instalación de energía' ?
                                                                'selected="selected"' : '' }}>Instalación de energía</option>
                                                    <option value="Obras Civiles"
                                                            {{ ($tender&&$tender->area=='Obras Civiles')||
                                                                old('area')=='Obras Civiles' ?
                                                                'selected="selected"' : '' }}>Obras Civiles</option>
                                                    <option value="Venta de material"
                                                            {{ ($tender&&$tender->area=='Venta de material')||
                                                                old('area')=='Venta de material' ?
                                                                'selected="selected"' : '' }}>Venta de material</option>
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
                                                                {{ ($tender&&$tender->client==$client->client)||
                                                                    old('client')==$client->client ?
                                                                    'selected="selected"' : '' }}>{{ $client->client }}</option>
                                                    @endforeach
                                                    <option value="Otro">Otro</option>
                                                </select>
                                            </div>
                                            <input required="required" type="text" class="form-control" name="other_client"
                                                   id="other_client" placeholder="Cliente *" disabled="disabled">

                                            <div class="input-group" style="width: 100%">
                                                <label for="contact_name" class="input-group-addon"
                                                       style="width: 23%;text-align: left">
                                                    Contacto:
                                                </label>

                                                <input required="required" type="text" class="form-control" name="contact_name"
                                                       id="contact_name"
                                                       value="{{ $tender&&$tender->contact ? $tender->contact->name :
                                                            old('contact_name') }}"
                                                       placeholder="Persona de contacto del cliente">
                                            </div>

                                            <div class="input-group" style="width: 80%;" id="deadline">
                                                <span class="input-group-addon" style="width: 29%;text-align: left"
                                                      title="Plazo para presentación a la licitación">
                                                    Presentación: <span class="pull-right">*</span>
                                                </span>
                                                <span class="input-group-addon">
                                                    <label for="application_deadline" style="font-weight: normal; margin-bottom: 0">
                                                        Hasta
                                                    </label>

                                                    <input type="date" name="application_deadline" id="application_deadline"
                                                           step="1" min="{{ date('Y-m-d') }}"
                                                           value="{{ $tender ? $tender->application_deadline :
                                                                old('application_deadline') }}">
                                                </span>
                                                <input required="required" type="number" class="form-control" name="days_to_deadline"
                                                       id="days_to_deadline" step="1" min="0" placeholder="Días">
                                            </div>

                                            <textarea rows="3" required="required" class="form-control" name="application_details"
                                                      id="application_details" placeholder="Detalle de presentación a convocatoria"
                                                      >{{ $tender ? $tender->application_details :
                                                       old('application_details') }}</textarea>
                                        </div>

                                    </div>
                                </div>

                                @include('app.loader_gif')

                                <div class="form-group" align="center">
                                    <button type="submit" class="btn btn-success"
                                            onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                                        <i class="fa fa-floppy-o"></i> Guardar
                                    </button>

                                    @if($tender&&$user->priv_level==4)
                                        <button type="submit" form="delete" class="btn btn-danger">
                                            <i class="fa fa-trash-o"></i> Borrar
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

        var $client = $('#client'), $other_client = $('#other_client');
        $client.change(function () {
            if ($client.val() === 'Otro') {
                $other_client.removeAttr('disabled').show();
            } else {
                $other_client.attr('disabled', 'disabled').val('').hide();
            }
        }).trigger('change');

        $(document).ready(function(){
            $("#wait").hide();
        });

        $('#contact_name').autocomplete({
            type: 'post',
            serviceUrl:'/autocomplete/contacts',
            dataType: 'JSON'
        });
    </script>
@endsection
