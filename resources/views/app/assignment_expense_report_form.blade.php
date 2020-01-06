<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 19/12/2017
 * Time: 04:36 PM
 */
?>

@extends('layouts.master')

@section('header')
    @parent
    <style>
        input[type=date]:before { right: 10px; }
    </style>

    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 mg-btm-px-40 col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1">

        <div class="panel panel-sky">
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    {{ 'Generar reporte de gastos' }}
                </div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-undo"></i>
                    </a>
                    <a href="{{ '/assignment' }}" class="btn btn-warning" title="Volver a la tabla de asignaciones">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <form novalidate="novalidate" action="{{ '/assignment/expense_report/'.$type }}" method="post"
                          class="form-horizontal">

                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    <fieldset>
                        <legend class="col-md-10">Intervalo de tiempo</legend>

                        <div class="row">
                            <div class="col-md-12 col-sm-12">

                                <div class="form-group{{ $errors->has('from') ? ' has-error' : '' }}">
                                    <label for="from" class="col-md-4 control-label">(*) Desde</label>

                                    <div class="col-md-3">
                                        <input type="date" class="form-control" name="from" id="from"
                                               step="1" value="{{ old('from') ?: date('Y-m-d') }}">

                                        @if($errors->has('from'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('from') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group{{ $errors->has('to') ? ' has-error' : '' }}">

                                    <label for="to" class="col-md-4 control-label">(*) Hasta</label>

                                    <div class="col-md-3">
                                        <input type="date" class="form-control" name="to" id="to"
                                               step="1" value="{{ old('to') ?: date('Y-m-d') }}">

                                        @if($errors->has('to'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('to') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend class="col-md-10">Filtrar por</legend>

                        <div class="row">
                            <div class="col-md-12 col-sm-12">

                                <p class="col-md-12">
                                    <em>Nota.- Los siguiente campos son opcionales</em>
                                </p>

                                <div class="form-group{{ $errors->has('id') ? ' has-error' : '' }}">
                                    <label for="id" class="col-md-4 control-label">
                                        Asignación
                                    </label>

                                    <div class="col-md-6">
                                        <select id="id" name="id" class="form-control">
                                            <option value="">Todas</option>
                                            @foreach($assignments as $assignment)
                                                <option value="{{ $assignment->id }}" title="{{ $assignment->name }}"
                                                        {{ old('id')==$assignment->id ? 'selected="selected"' : '' }}>
                                                    {{ str_limit($assignment->name,150) }}
                                                </option>
                                            @endforeach
                                        </select>

                                        @if($errors->has('id'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('id') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group{{ $errors->has('client') ? ' has-error' : '' }}">
                                    <label for="client" class="col-md-4 control-label">
                                        Cliente
                                    </label>

                                    <div class="col-md-6">
                                        <select id="client" name="client" class="form-control">
                                            <option value="">Seleccione un cliente</option>
                                            @foreach($clients as $client)
                                                <option value="{{ $client->client }}"
                                                        {{ old('client')==$client->client ? 'selected="selected"' : '' }}>
                                                        {{ $client->client }}</option>
                                            @endforeach
                                        </select>

                                        @if($errors->has('client'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('client') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group{{ $errors->has('type') ? ' has-error' : '' }}">
                                    <label for="type" class="col-md-4 control-label">
                                        Área de trabajo
                                    </label>

                                    <div class="col-md-6">
                                        <select id="type" name="type" class="form-control">
                                            <option value="">Seleccione un área de trabajo</option>
                                            @foreach($types as $type)
                                                <option value="{{ $type->type }}"
                                                        {{ old('type')==$type->type ? 'selected="selected"' : '' }}>
                                                    {{ $type->type }}</option>
                                            @endforeach
                                        </select>

                                        @if($errors->has('type'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('type') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                            </div>
                        </div>
                    </fieldset>

                    @include('app.loader_gif')

                    <div class="form-group" align="center">
                        <button type="submit" class="btn btn-success"
                                onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                            <i class="fa fa-arrow-right"></i> Generar reporte
                        </button>
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

        $(document).ready(function(){
            $("#wait").hide();
        });
    </script>
@endsection
