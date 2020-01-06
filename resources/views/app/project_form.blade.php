<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 30/03/2017
 * Time: 03:36 PM
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
                <div class="panel-title">
                    {{ $project ? 'Modificar información de contrato' : 'Agregar nuevo contrato' }}
                </div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-undo"></i>
                    </a>
                    <a href="{{ '/project' }}" class="btn btn-warning" title="Volver a la tabla de contratos">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                @if($project)
                    <form id="delete" action="/project/{{ $project->id }}" method="post">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                    <form novalidate="novalidate" action="{{ '/project/'.$project->id }}" method="post">
                        <input type="hidden" name="_method" value="put">
                @elseif($tender)
                    <form novalidate="novalidate" action="{{ '/tender/add_contract/'.$tender->id }}" method="post"
                          enctype="multipart/form-data">
                        @else
                            <form novalidate="novalidate" action="{{ '/project' }}" method="post" enctype="multipart/form-data">
                                @endif
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="form-group">
                                    <div class="input-group">

                                        <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                        {{--
                                        <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i>
                                        --}}
                                            <div class="input-group" style="width: 100%">
                                                <label for="name" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Contrato: <span class="pull-right">*</span>
                                                </label>

                                                <input required="required" type="text" class="form-control" name="name" id="name"
                                                       value="{{ $project ? $project->name : old('name') }}"
                                                       placeholder="Nombre de contrato">
                                            </div>

                                            <textarea rows="3" required="required" class="form-control" name="description"
                                                placeholder="Descripción del contrato">{{ $project ?
                                                 $project->description : old('description') }}</textarea>

                                            @if(!$tender)
                                                <div class="input-group" style="width: 100%">
                                                    <label for="type" class="input-group-addon" style="width: 23%;text-align: left">
                                                        Área: <span class="pull-right">*</span>
                                                    </label>

                                                    <select required="required" class="form-control" name="type" id="type">
                                                        <option value="" hidden>Seleccione el área de trabajo</option>
                                                        <option value="Fibra óptica"
                                                                {{ ($project&&$project->type=='Fibra óptica')||
                                                                    old('type')=='Fibra óptica' ?
                                                                    'selected="selected"' : '' }}>Fibra óptica</option>
                                                        <option value="Radiobases"
                                                                {{ ($project&&$project->type=='Radiobases')||
                                                                    old('type')=='Radiobases' ?
                                                                    'selected="selected"' : '' }}>Radiobases</option>
                                                        <option value="Instalación de energía"
                                                                {{ ($project&&$project->type=='Instalación de energía')||
                                                                    old('type')=='Instalación de energía' ?
                                                                    'selected="selected"' : '' }}>Instalación de energía</option>
                                                        <option value="Obras Civiles"
                                                                {{ ($project&&$project->type=='Obras Civiles')||
                                                                    old('type')=='Obras Civiles' ?
                                                                    'selected="selected"' : '' }}>Obras Civiles</option>
                                                        <option value="Venta de material"
                                                                {{ ($project&&$project->type=='Venta de material')||
                                                                    old('type')=='Venta de material' ?
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
                                                                    {{ ($project&&$project->client==$client->client)||
                                                                        old('client')==$client->client ?
                                                                        'selected="selected"' : '' }}>{{ $client->client }}</option>
                                                        @endforeach
                                                        <option value="Otro">Otro</option>
                                                    </select>
                                                </div>
                                                <input required="required" type="text" class="form-control" name="other_client"
                                                    id="other_client" placeholder="Cliente *" disabled="disabled">
                                            @endif

                                            <div class="input-group" style="width: 100%">
                                                <label for="contact_name" class="input-group-addon"
                                                       style="width: 23%;text-align: left">
                                                    Contacto: <span class="pull-right">*</span>
                                                </label>

                                                <input required="required" type="text" class="form-control" name="contact_name"
                                                       id="contact_name"
                                                       value="{{ $project&&$project->contact ? $project->contact->name :
                                                            old('contact_name') }}"
                                                       placeholder="Persona de contacto del cliente">
                                            </div>

                                            @if(!$tender)
                                                <div class="input-group" style="width: 100%">
                                                    <label for="award" class="input-group-addon" style="width: 23%;text-align: left">
                                                        Adjudicación: <span class="pull-right">*</span>
                                                    </label>

                                                    <select required="required" class="form-control" name="award" id="award">
                                                        <option value="" hidden>Seleccione el tipo de adjudicación</option>
                                                        {{--
                                                        <option value="Licitación"
                                                                {{ ($project&&$project->award=='Licitación')||
                                                                    old('award')=='Licitación' ?
                                                                    'selected="selected"' : '' }}>Licitación</option>
                                                                    --}}
                                                        @foreach($awards as $award)
                                                            <option value="{{ $award->award }}"
                                                                    {{ ($project&&$project->award==$award->award)||
                                                                        old('award')==$award->award ?
                                                                        'selected="selected"' : '' }}>{{ $award->award }}</option>
                                                        @endforeach
                                                        <option value="Otro">Otro</option>
                                                    </select>
                                                </div>
                                                <input required="required" type="text" class="form-control" name="other_award"
                                                    id="other_award" placeholder="Tipo de adjudicación *" disabled="disabled">
                                            @endif

                                            {{--
                                            <div class="input-group" style="width: 80%;" id="deadline">
                                                <span class="input-group-addon" style="width: 29%;text-align: left"
                                                      title="Plazo para presentación a la licitación">
                                                    Presentación:
                                                </span>
                                                <span class="input-group-addon">
                                                    <label for="application_deadline" style="font-weight: normal; margin-bottom: 0">
                                                        Hasta
                                                    </label>

                                                    <input type="date" name="application_deadline" id="application_deadline"
                                                           step="1" min="{{ date('Y-m-d') }}"
                                                           value="{{ $project ? $project->application_deadline :
                                                                old('application_deadline') }}"
                                                           disabled="disabled">
                                                </span>
                                                <input required="required" type="number" class="form-control" name="days_to_deadline"
                                                       id="days_to_deadline" step="1" min="0" placeholder="Días"
                                                       disabled="disabled">
                                            </div>

                                            <textarea rows="3" required="required" class="form-control" name="application_details"
                                                      id="application_details" placeholder="Detalle de presentación a convocatoria"
                                                      disabled="disabled">{{ $project ?
                                                       $project->application_details : old('application_details') }}</textarea>
                                            --}}

                                            <div class="input-group" style="width: 100%;text-align: center" id="valid_dates">
                                                <span class="input-group-addon" style="width: 23%;text-align: left">
                                                    Validez: <span class="pull-right">*</span>
                                                </span>
                                                <span class="input-group-addon">
                                                    <label for="valid_from" style="font-weight: normal; margin-bottom: 0">Desde:</label>
                                                    <input type="date" name="valid_from" id="valid_from" step="1" min="2014-01-01"
                                                           value="{{ $project ? $project->valid_from :
                                                                (old('valid_from') ?: date('Y-m-d')) }}">

                                                    <label for="valid_to" style="font-weight: normal; margin-bottom: 0">Hasta:</label>
                                                    <input type="date" name="valid_to" id="valid_to" step="1" min="2014-01-01"
                                                           value="{{ $project ? $project->valid_to : old('valid_to') }}">
                                                </span>
                                                <input required="required" type="number" class="form-control" name="valid_days"
                                                       step="1" min="0" placeholder="Días" value="{{ old('valid_days') }}">
                                            </div>

                                        {{--
                                        </span>
                                        --}}
                                        </div>

                                    </div>
                                </div>

                                @include('app.loader_gif')

                                <div class="form-group" align="center">
                                    <button type="submit" class="btn btn-success"
                                            onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                                        <i class="fa fa-floppy-o"></i> Guardar
                                    </button>

                                    @if($project&&$user->priv_level==4)
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

        var $award = $('#award'), $other_award = $('#other_award'); /*$deadline = $('#deadline'),
                $application_deadline = $('#application_deadline'), $days_to_deadline = $('#days_to_deadline'),
                $application_details = $('#application_details')*/

        $award.change(function () {
            if ($award.val() === 'Otro') {
                $other_award.removeAttr('disabled').show();
                //$application_deadline.attr('disabled', 'disabled').val('').hide();
                //$application_details.attr('disabled', 'disabled').val('').hide();
                //$days_to_deadline.attr('disabled', 'disabled').val('').hide();
                //$deadline.hide();
            }
            /*
            else if ($award.val() === 'Licitación') {
                $other_award.attr('disabled', 'disabled').val('').hide();
                $application_deadline.removeAttr('disabled').show();
                $application_details.removeAttr('disabled').show();
                $days_to_deadline.removeAttr('disabled').show();
                $deadline.show();
            }
            */
            else {
                $other_award.attr('disabled', 'disabled').val('').hide();
                //$application_deadline.attr('disabled', 'disabled').val('').hide();
                //$application_details.attr('disabled', 'disabled').val('').hide();
                //$days_to_deadline.attr('disabled', 'disabled').val('').hide();
                //$deadline.hide();
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
