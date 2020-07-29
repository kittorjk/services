<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 24/08/2017
 * Time: 12:33 PM
 */
?>

@extends('layouts.master')

@section('header')
@parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

<div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">

    <div class="panel panel-sky">
        <div class="panel-heading" align="center">
            <div class="panel-title">
                {{ $listed_material ? 'Modificar datos de material' : 'Agregar material' }}
            </div>
        </div>
        <div class="panel-body">
            <div class="mg20">
                <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                    <i class="fa fa-arrow-left"></i>
                </a>
                <a href="/client_listed_material{{ $client ? '?client='.$client : '' }}" class="btn btn-warning"
                   title="Volver al listado de materiales">
                    <i class="fa fa-arrow-up"></i>
                </a>
            </div>

            @include('app.session_flashed_messages', array('opt' => 1))

            @if($listed_material)
                <form id="delete" action="/client_listed_material/{{ $listed_material->id }}" method="post">
                    <input type="hidden" name="_method" value="delete">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                </form>
                <form novalidate="novalidate" action="{{ '/client_listed_material/'.$listed_material->id }}" method="post">
                    <input type="hidden" name="_method" value="put">
                    @else
                    <form novalidate="novalidate" action="{{ '/client_listed_material' }}" method="post">
                        @endif
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                    <div class="input-group" style="width: 100%">
                                        <label for="client" class="input-group-addon" style="width: 23%;text-align: left">
                                            Cliente:
                                        </label>

                                        <select required="required" class="form-control" name="client" id="client">
                                            <option value="" hidden>Seleccione un cliente</option>

                                            @foreach($stored_clients as $stored_client)
                                                <option value="{{ $stored_client->client }}"
                                                        {{ ($listed_material&&$listed_material->client==$stored_client->client)||
                                                        ($client==$stored_client->client)||
                                                        (old('client')==$stored_client->client) ? 'selected="selected"' : '' }}
                                                >{{ $stored_client->client }}</option>
                                            @endforeach
                                            <option value="Otro">Otro</option>
                                        </select>
                                    </div>
                                    <input required="required" type="text" class="form-control" name="other_client"
                                           id="other_client" placeholder="Cliente" disabled="disabled">

                                    <div class="input-group" style="width: 100%">
                                        <label for="code" class="input-group-addon" style="width: 23%;text-align: left">
                                            Código:
                                        </label>

                                        <input required="required" type="text" class="form-control" name="code"
                                               id="code" value="{{ $listed_material ?
                                                $listed_material->code : old('code') }}"
                                               placeholder="Código de material según cliente">
                                    </div>

                                    <div class="input-group" style="width: 100%">
                                        <label for="name" class="input-group-addon" style="width: 23%;text-align: left">
                                            Item:
                                        </label>

                                        <input required="required" type="text" class="form-control" name="name"
                                               id="name" value="{{ $listed_material ?
                                                $listed_material->name : old('name') }}"
                                               placeholder="Nombre de material según cliente">
                                    </div>

                                    <div class="input-group" style="width: 100%">
                                        <label for="model" class="input-group-addon" style="width: 23%;text-align: left">
                                            Modelo:
                                        </label>

                                        <input required="required" type="text" class="form-control" name="model"
                                               id="model" value="{{ $listed_material ?
                                                $listed_material->model : old('model') }}"
                                               placeholder="Modelo">
                                    </div>

                                    <div class="input-group" style="width: 100%">
                                        <label for="applies_to" class="input-group-addon" style="width: 23%;text-align: left">
                                            Aplica a:
                                        </label>

                                        <input required="required" type="text" class="form-control" name="applies_to"
                                               id="applies_to" value="{{ $listed_material ?
                                                $listed_material->applies_to : old('applies_to') }}"
                                               placeholder="# de solución u opción (ej: 1-2-3)">
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
                            @if($listed_material)
                                <button type="submit" form="delete" class="btn btn-danger">
                                    <i class="fa fa-trash-o"></i> Eliminar
                                </button>
                            @endif
                        </div>
                        {{ csrf_field() }}
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
        $('#other_client').hide();
    });

</script>
@endsection
