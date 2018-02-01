<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 24/07/2017
 * Time: 04:32 PM
 */
?>

@extends('layouts.master')

@section('header')
    @parent
    <style>
        input[type=date]:before {  right: 10px;  }
    </style>
    <link rel="stylesheet" href="{{ asset("app/css/custom_autocomplete.css") }}">
    <script src="{{ asset('https://cdnjs.cloudflare.com/ajax/libs/jquery.devbridge-autocomplete/1.2.27/jquery.autocomplete.js') }}">
    </script>

    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1">

        <div class="panel panel-sky">
            <div class="panel-heading" align="center">
                <div class="panel-title">{{ $viatic ? 'Modificar solicitud de viáticos' : 'Nueva solicitud de viáticos' }}</div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-undo"></i>
                    </a>
                    <a href="{{ '/rbs_viatic' }}" class="btn btn-warning" title="Volver a resumen de solicitudes">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                @if($viatic)
                    <form id="delete" action="/rbs_viatic/{{ $viatic->id }}" method="post">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                    <form novalidate="novalidate" action="{{ '/rbs_viatic/'.$viatic->id }}" method="post" class="form-horizontal">
                        <input type="hidden" name="_method" value="put">
                        @else
                            <form novalidate="novalidate" action="{{ '/rbs_viatic' }}" method="post" class="form-horizontal">
                                @endif
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                                <div class="row">
                                    <div class="col-md-12 col-sm-12">

                                        <div class="form-group{{ $errors->has('type') ? ' has-error' : '' }}">
                                            <label for="type" class="col-md-4 control-label">Tipo de solicitud</label>

                                            <div class="col-md-6" id="type">
                                                <input id="type_viatic" type="radio" name="type" value="Viático"
                                                       data-amount="100" onclick="update_amount()"
                                                        {{ ($viatic&&$viatic->type=='Viático')||old('type')=='Viático' ?
                                                         'checked="checked"' : '' }}>
                                                <label for="type_viatic" class="control-label">Viático</label>
                                                &emsp;
                                                <input id="type_stipend" type="radio" name="type" value="Estipendio"
                                                        data-amount="20" onclick="update_amount()"
                                                        {{ ($viatic&&$viatic->type=='Estipendio')||old('type')=='Estipendio' ?
                                                         'checked="checked"' : '' }}>
                                                <label for="type_stipend" class="control-label">Estipendio</label>

                                                @if($errors->has('type'))
                                                    <span class="help-block">
                                                        <strong>{{ $errors->first('type') }}</strong>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="form-group{{ $errors->has('work_description') ? ' has-error' : '' }}">
                                            <label for="work_description" class="col-md-4 control-label">
                                                Trabajo a realizar
                                            </label>

                                            <div class="col-md-6">
                                                <input required type="text" class="form-control" name="work_description"
                                                       id="work_description" placeholder="Descripción"
                                                       value="{{ $viatic ? $viatic->work_description : old('work_description') }}">

                                                @if ($errors->has('work_description'))
                                                    <span class="help-block">
                                                        <strong>{{ $errors->first('work_description') }}</strong>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="form-group{{ $errors->has('date_from')||$errors->has('date_to') ?
                                            ' has-error' : '' }}">
                                            <label for="date_from" class="col-md-4 control-label">Desde</label>

                                            <div class="col-md-3">
                                                <input id="date_from" type="date" class="form-control" name="date_from"
                                                       step="1" min="2017-01-01"
                                                       value="{{ $viatic ? $viatic->date_from :
                                                        (old('date_from') ? old('date_from') : date('Y-m-d')) }}"
                                                        onchange="get_diff_days()">

                                                @if ($errors->has('date_from'))
                                                    <span class="help-block">
                                                        <strong>{{ $errors->first('date_from') }}</strong>
                                                    </span>
                                                @endif
                                            </div>

                                            <label for="date_to" class="col-md-1 control-label">Hasta</label>

                                            <div class="col-md-3">
                                                <input id="date_to" type="date" class="form-control" name="date_to"
                                                       step="1" min="2017-01-01"
                                                       value="{{ $viatic ? $viatic->date_to :
                                                        (old('date_to') ? old('date_to') : '') }}"
                                                        onchange="get_diff_days()">

                                                @if ($errors->has('date_to'))
                                                    <span class="help-block">
                                                        <strong>{{ $errors->first('date_to') }}</strong>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <fieldset>
                                    <legend class="col-md-10">Transporte</legend>

                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">

                                            <div class="form-group{{ $errors->has('type_transport') ? ' has-error' : '' }}">
                                                <label for="type_transport" class="col-md-4 control-label">
                                                    Tipo de transporte
                                                </label>

                                                <div class="col-md-6" id="type_transport">
                                                    <input id="trans_by_air" type="radio" name="type_transport"
                                                           value="Aéreo" onclick="check_transport()"
                                                           {{ ($viatic&&$viatic->type_transport=='Aéreo')||
                                                           old('type_transport')=='Aéreo' ? 'checked="checked"' : '' }}>
                                                    <label for="trans_by_air" class="control-label">Aéreo</label>
                                                    &emsp;
                                                    <input id="trans_by_land" type="radio" name="type_transport"
                                                           value="Terrestre" onclick="check_transport()"
                                                            {{ ($viatic&&$viatic->type_transport=='Terrestre')||
                                                            old('type_transport')=='Terrestre' ? 'checked="checked"' : '' }}>
                                                    <label for="trans_by_land" class="control-label">Terrestre</label>
                                                    &emsp;
                                                    <input id="trans_rent" type="radio" name="type_transport"
                                                           value="Vehículo alquilado" onclick="check_transport()"
                                                           {{ ($viatic&&$viatic->type_transport=='Vehículo alquilado')||
                                                           old('type_transport')=='Vehículo alquilado' ?
                                                                'checked="checked"' : '' }}>
                                                    <label for="trans_rent" class="control-label">Vehículo alquilado</label>
                                                    <br>
                                                    <input id="trans_company" type="radio" name="type_transport"
                                                           value="Vehículo de la empresa" onclick="check_transport()"
                                                           {{ ($viatic&&$viatic->type_transport=='Vehículo de la empresa')||
                                                           old('type_transport')=='Vehículo de la empresa' ?
                                                           'checked="checked"' : '' }}>
                                                    <label for="trans_company" class="control-label">Vehículo de la empresa</label>
                                                    &emsp;
                                                    <input id="trans_not_required" type="radio" name="type_transport"
                                                           value="No requerido" onclick="check_transport()"
                                                           {{ ($viatic&&$viatic->type_transport=='No requerido')||
                                                           old('type_transport')=='No requerido' ? 'checked="checked"' : '' }}>
                                                    <label for="trans_not_required" class="control-label">No requiere</label>

                                                    @if($errors->has('type_transport'))
                                                        <span class="help-block">
                                                            <strong>{{ $errors->first('type_transport') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div id="rent_container">
                                                <div class="form-group{{ $errors->has('vehicle_rent_days') ? ' has-error' : '' }}">
                                                    <label for="vehicle_rent_days" class="col-md-4 control-label">
                                                        Tiempo de alquiler
                                                    </label>

                                                    <div class="col-md-6">
                                                        <input id="vehicle_rent_days" type="number" class="form-control"
                                                               name="vehicle_rent_days" step="1" min="0"
                                                               value="{{ $viatic ? $viatic->vehicle_rent_days :
                                                                (old('vehicle_rent_days') ? old('vehicle_rent_days') : '') }}"
                                                               placeholder="días" disabled="disabled">

                                                        @if ($errors->has('vehicle_rent_days'))
                                                            <span class="help-block">
                                                                <strong>{{ $errors->first('vehicle_rent_days') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="form-group{{ $errors->has('vehicle_rent_cost_day') ?
                                                    ' has-error' : '' }}">
                                                    <label for="vehicle_rent_cost_day" class="col-md-4 control-label">
                                                        Costo de alquiler
                                                    </label>

                                                    <div class="col-md-6">
                                                        <input id="vehicle_rent_cost_day" type="number" class="form-control"
                                                               name="vehicle_rent_cost_day" step="any" min="0"
                                                               value="{{ $viatic ? $viatic->vehicle_rent_cost_day :
                                                                (old('vehicle_rent_cost_day') ?
                                                                 old('vehicle_rent_cost_day') : '') }}"
                                                               placeholder="Bs" disabled="disabled">

                                                        @if($errors->has('vehicle_rent_cost_day'))
                                                            <span class="help-block">
                                                                <strong>{{ $errors->first('vehicle_rent_cost_day') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                </fieldset>

                                <fieldset>
                                    <legend class="col-md-10">Técnicos</legend>

                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">

                                            <div class="form-group{{ $errors->has('num_technicians') ? ' has-error' : '' }}">
                                                <label for="num_technicians" class="col-md-4 control-label">(*) # Técnicos</label>

                                                <div class="col-md-3">
                                                    <div class="input-group">
                                                        <input id="num_technicians" type="number" class="form-control"
                                                               name="num_technicians" step="1" min="1"
                                                               value="{{ $viatic ? $viatic->num_technicians :
                                                                (old('num_technicians') ? old('num_technicians') : '') }}"
                                                               placeholder="Número de técnicos" required>
                                                        <span class="input-group-btn">
                                                            <button class="btn btn-secondary" type="button"
                                                                    onclick="add_tech_fields(this)">
                                                                >
                                                            </button>
                                                        </span>
                                                    </div>

                                                    @if ($errors->has('num_technicians'))
                                                        <span class="help-block">
                                                            <strong>{{ $errors->first('num_technicians') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="form-group{{ $errors->has('tech[0][name]') ? ' has-error' : '' }}">
                                                <label for="tech[0][name]" class="col-md-4 control-label">(*) Nombre</label>

                                                <div class="col-md-6">
                                                    <input id="tech[0][name]" type="text"
                                                           class="form-control complete_tech dynamic"
                                                           name="tech[0][name]" placeholder="Nombre"
                                                           value="{{ $viatic&&$technicians ? $technicians[0]['name'] :
                                                                old('tech[0][name]') }}" required
                                                            onkeydown="autocomplete_tech(this)">

                                                    @if ($errors->has('tech[0][name]'))
                                                        <span class="help-block">
                                                            <strong>{{ $errors->first('tech[0][name]') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="form-group
                                                {{ $errors->has('tech[0][viatic]')||$errors->has('tech[0][extras]') ?
                                                    ' has-error' : '' }}">
                                                <label for="tech[0][viatic]" class="col-md-4 control-label">
                                                    (*) Viáticos
                                                </label>

                                                <div class="col-md-2">
                                                    <input id="tech[0][viatic]" type="number" class="form-control amount dynamic"
                                                           name="tech[0][viatic]" step="any" min="0"
                                                           value="{{ $viatic&&$technicians ?
                                                                $technicians[0]['viatic'] : old('tech[0][viatic]') }}"
                                                           placeholder="Bs" required>

                                                    @if($errors->has('tech[0][viatic]'))
                                                        <span class="help-block">
                                                            <strong>{{ $errors->first('tech[0][viatic]') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>

                                                <label for="tech[0][extras]" class="col-md-2 control-label">
                                                    Gastos extra
                                                </label>

                                                <div class="col-md-2">
                                                    <input id="tech[0][extras]" type="number" class="form-control dynamic"
                                                           name="tech[0][extras]" step="any" min="0"
                                                           value="{{ $viatic&&$technicians ?
                                                                $technicians[0]['extras'] : old('tech[0][extras]') }}"
                                                           placeholder="Bs" required>

                                                    @if($errors->has('tech[0][extras]'))
                                                        <span class="help-block">
                                                                <strong>{{ $errors->first('tech[0][extras]') }}</strong>
                                                            </span>
                                                    @endif
                                                </div>

                                            </div>

                                            <div class="ticket_container">
                                                <div class="form-group{{ $errors->has('tech[0][departure]')||
                                                    $errors->has('tech[0][return]') ? ' has-error' : '' }}">

                                                    <label for="tech[0][departure]" class="col-md-4 control-label">
                                                        Pasaje ida
                                                    </label>

                                                    <div class="col-md-2">
                                                        <input id="tech[0][departure]" type="number"
                                                               class="form-control ticket dynamic"
                                                               name="tech[0][departure]" step="any" min="0"
                                                               value="{{ $viatic&&$technicians ?
                                                                $technicians[0]['departure'] : old('tech[0][departure]') }}"
                                                               placeholder="Bs" required disabled="disabled">

                                                        @if($errors->has('tech[0][departure]'))
                                                            <span class="help-block">
                                                                <strong>{{ $errors->first('tech[0][departure]') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <label for="tech[0][return]" class="col-md-2 control-label">
                                                        Pasaje vuelta
                                                    </label>

                                                    <div class="col-md-2">
                                                        <input id="tech[0][return]" type="number"
                                                               class="form-control ticket dynamic"
                                                               name="tech[0][return]" step="any" min="0"
                                                               value="{{ $viatic&&$technicians ?
                                                                $technicians[0]['return'] : old('tech[0][return]') }}"
                                                               placeholder="Bs" disabled="disabled">

                                                        @if($errors->has('tech[0][return]'))
                                                            <span class="help-block">
                                                            <strong>{{ $errors->first('tech[0][return]') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>

                                                </div>
                                            </div>

                                            <div id="tech_fields_container">
                                                {{-- Container for dynamic fields --}}
                                            </div>

                                        </div>
                                    </div>

                                </fieldset>

                                <fieldset>

                                    <legend class="col-md-10">Sitios</legend>

                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">

                                            <div class="form-group{{ $errors->has('num_sites') ? ' has-error' : '' }}">
                                                <label for="num_sites" class="col-md-4 control-label">(*) # Sitios</label>

                                                <div class="col-md-3">
                                                    <div class="input-group">
                                                        <input id="num_sites" type="number" class="form-control" name="num_sites"
                                                               step="1" min="1" value="{{ $viatic ? $viatic->num_sites :
                                                                (old('num_sites') ? old('num_sites') : '') }}"
                                                               placeholder="Número de sitios" required>
                                                        <span class="input-group-btn">
                                                            <button class="btn btn-secondary" type="button"
                                                                    onclick="add_site_fields(this)">
                                                                >
                                                            </button>
                                                        </span>
                                                    </div>

                                                    @if($errors->has('num_sites'))
                                                        <span class="help-block">
                                                            <strong>{{ $errors->first('num_sites') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="form-group{{ $errors->has('site[0][name]') ? ' has-error' : '' }}">
                                                <label for="site[0][name]" class="col-md-4 control-label">(*) Nombre</label>

                                                <div class="col-md-6">
                                                    <input id="site[0][name]" type="text"
                                                           class="form-control complete_site dynamic"
                                                           name="site[0][name]" placeholder="Nombre de sitio"
                                                           value="{{ $viatic&&$sites ? $sites[0] :
                                                                old('site[0][name]') }}" required
                                                           onkeydown="autocomplete_site(this)">

                                                    @if($errors->has('site[0][name]'))
                                                        <span class="help-block">
                                                            <strong>{{ $errors->first('site[0][name]') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div id="site_fields_container">
                                                {{-- Container for dynamic fields --}}
                                            </div>

                                        </div>
                                    </div>

                                </fieldset>

                                <fieldset>

                                    <legend class="col-md-10">Adicionales</legend>

                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">

                                            <div class="form-group{{ $errors->has('extra_expenses_detail') ?
                                                ' has-error' : '' }}">
                                                <label for="extra_expenses_detail" class="col-md-4 control-label">
                                                    Detalle de gastos extra
                                                </label>

                                                <div class="col-md-6">
                                                    <textarea rows="3" class="form-control" id="extra_expenses_detail"
                                                        name="extra_expenses_detail">{{ $viatic ? $viatic->extra_expenses_detail :
                                                            old('extra_expenses_detail') }}</textarea>
                                                </div>
                                            </div>

                                            <div class="form-group{{ $errors->has('materials_cost') ? ' has-error' : '' }}">
                                                <label for="materials_cost" class="col-md-4 control-label">
                                                    Costo de materiales adicionales
                                                </label>

                                                <div class="col-md-6">
                                                    <input id="materials_cost" type="number" class="form-control"
                                                           name="materials_cost" step="any" min="0"
                                                           value="{{ $viatic&&$viatic->materials_cost!=0 ?
                                                            $viatic->materials_cost :
                                                            (old('materials_cost') ? old('materials_cost') : '') }}"
                                                           placeholder="Bs">

                                                    @if($errors->has('materials_cost'))
                                                        <span class="help-block">
                                                            <strong>{{ $errors->first('materials_cost') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="form-group{{ $errors->has('materials_detail') ? ' has-error' : '' }}">
                                                <label for="materials_detail" class="col-md-4 control-label">
                                                    Detalle de materiales adicionales
                                                </label>

                                                <div class="col-md-6">
                                                    <textarea rows="3" class="form-control" id="materials_detail"
                                                        name="materials_detail">{{ $viatic ? $viatic->materials_detail :
                                                            old('materials_detail') }}</textarea>
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                </fieldset>

                                @include('app.loader_gif')

                                <div class="form-group" align="center">
                                    <button type="submit" class="btn btn-success"
                                            onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                                        <i class="fa fa-arrow-right"></i> Solicitar
                                    </button>
                                    @if($viatic&&$user->priv_level==4)
                                        <button type="submit" form="delete" class="btn btn-danger">
                                            <i class="fa fa-trash-o"></i> Eliminar
                                        </button>
                                    @endif
                                </div>
                                {{ csrf_field() }}
                            </form>

                    {{-- Fields to be replicated --}}
                    <div id="clonable_tech_fields" style="display: none">

                        <div class="form-group{{ $errors->has('tech[*][name]') ? ' has-error' : '' }}">
                            <label for="tech[*][name]" class="col-md-4 control-label">(*) Nombre</label>

                            <div class="col-md-6">
                                <input id="tech[*][name]" type="text" class="form-control complete_tech dynamic"
                                       name="tech[*][name]" placeholder="Nombre"
                                       value="{{ $viatic&&$technicians&&array_key_exists(1, $technicians) ?
                                        $technicians[1]['name'] : old('tech[1][name]') }}" required
                                       onkeydown="autocomplete_tech(this)">

                                @if($errors->has('tech[*][name]'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('tech[*][name]') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('tech[*][viatic]')||$errors->has('tech[*][extras]') ?
                                                    ' has-error' : '' }}">
                            <label for="tech[*][viatic]" class="col-md-4 control-label">(*) Viáticos</label>

                            <div class="col-md-2">
                                <input id="tech[*][viatic]" type="number" class="form-control amount dynamic"
                                       name="tech[*][viatic]" step="any" min="0"
                                       value="{{ $viatic&&$technicians&&array_key_exists(1, $technicians) ?
                                            $technicians[1]['viatic'] : old('tech[1][viatic]') }}"
                                       placeholder="Bs" required>

                                @if($errors->has('tech[*][viatic]'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('tech[*][viatic]') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <label for="tech[*][extras]" class="col-md-2 control-label">Gastos extra</label>

                            <div class="col-md-2">
                                <input id="tech[*][extras]" type="number" class="form-control dynamic"
                                       name="tech[*][extras]" step="any" min="0"
                                       value="{{ $viatic&&$technicians&&array_key_exists(1, $technicians) ?
                                            $technicians[1]['extras'] : old('tech[1][extras]') }}"
                                       placeholder="Bs" required>

                                @if($errors->has('tech[*][extras]'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('tech[*][extras]') }}</strong>
                                    </span>
                                @endif
                            </div>

                        </div>

                        <div class="ticket_container">
                            <div class="form-group{{ $errors->has('tech[*][departure]')||
                                                    $errors->has('tech[*][return]') ? ' has-error' : '' }}">

                                <label for="tech[*][departure]" class="col-md-4 control-label">Pasaje ida</label>

                                <div class="col-md-2">
                                    <input id="tech[*][departure]" type="number" class="form-control ticket dynamic"
                                           name="tech[*][departure]" step="any" min="0"
                                           value="{{ $viatic&&$technicians&&array_key_exists(1, $technicians) ?
                                                $technicians[1]['departure'] : old('tech[1][departure]') }}"
                                           placeholder="Bs" required disabled="disabled">

                                    @if($errors->has('tech[*][departure]'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('tech[*][departure]') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <label for="tech[*][return]" class="col-md-2 control-label">Pasaje vuelta</label>

                                <div class="col-md-2">
                                    <input id="tech[*][return]" type="number" class="form-control ticket dynamic"
                                           name="tech[*][return]" step="any" min="0"
                                           value="{{ $viatic&&$technicians&&array_key_exists(1, $technicians) ?
                                                $technicians[1]['return'] : old('tech[1][return]') }}"
                                           placeholder="Bs" disabled="disabled">

                                    @if($errors->has('tech[*][return]'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('tech[*][return]') }}</strong>
                                        </span>
                                    @endif
                                </div>

                            </div>
                        </div>

                    </div>

                    <div id="clonable_site_fields" style="display: none">

                        <div class="form-group{{ $errors->has('site[*][name]') ? ' has-error' : '' }}">
                            <label for="site[*][name]" class="col-md-4 control-label">(*) Nombre</label>

                            <div class="col-md-6">
                                <input id="site[*][name]" type="text" class="form-control complete_site dynamic"
                                       name="site[*][name]" placeholder="Nombre de sitio"
                                       value="{{ $viatic&&$sites&&array_key_exists(1, $sites) ? $sites[1] :
                                        old('site[1][name]') }}" required
                                       onkeydown="autocomplete_site(this)">

                                @if($errors->has('site[*][name]'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('site[*][name]') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                    </div>

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

        function update_amount(){
            var type_viatic = $("#type_viatic"), type_stipend = $("#type_stipend");

            if(type_viatic.is(':checked')){
                $(".amount").val(type_viatic.data('amount'));
            }
            else if(type_stipend.is(':checked')){
                $(".amount").val(type_stipend.data('amount'));
            }
        }

        function check_transport(){
            var rent_container = $("#rent_container"), vehicle_rent_cost_day = $("#vehicle_rent_cost_day"),
                    vehicle_rent_days = $("#vehicle_rent_days");

            if($("#trans_by_air").is(':checked') || $("#trans_by_land").is(':checked')){
                $(".ticket").removeAttr('disabled');
                $(".ticket_container").show();
                rent_container.hide();
                vehicle_rent_days.attr('disabled', 'disabled').hide();
                vehicle_rent_cost_day.attr('disabled', 'disabled').hide();
            }
            else if($("#trans_rent").is(':checked')){
                rent_container.show();
                vehicle_rent_days.removeAttr('disabled').show();
                vehicle_rent_cost_day.removeAttr('disabled').show();
                $(".ticket").attr('disabled','disabled');
                $(".ticket_container").hide();
            }
            else{
                rent_container.hide();
                vehicle_rent_days.attr('disabled', 'disabled').hide();
                vehicle_rent_cost_day.attr('disabled', 'disabled').hide();
                $(".ticket").attr('disabled','disabled');
                $(".ticket_container").hide();
            }
        }

        function get_diff_days(){
            var start = $('#date_from').val(), end = $('#date_to').val(), vehicle_rent_days = $("#vehicle_rent_days");

            if(!end)
                return;

            var diff = new Date(Date.parse(end) - Date.parse(start));
            // difference in days
            var days = diff/1000/60/60/24;

            vehicle_rent_days.val(days+1); // The difference includes both from and to dates
        }

        function add_tech_fields(){
            var num_technicians=$("#num_technicians").val();
            if(num_technicians.length > 0){

                var container = $('<div>').addClass('tech_field');

                for(var i=1; i<(num_technicians);i++){

                    var clonable_tech_fields = $('#clonable_tech_fields').clone(true).removeAttr('id').removeAttr('style');

                    $('.dynamic', clonable_tech_fields).each(function() {
                        var oldName = $(this).attr('name');
                        var oldId = $(this).attr('id');
                        var oldValue = $(this).attr('value');

                        $(this).attr('name', oldName.replace('*', i));
                        $(this).attr('id', oldId.replace('*', i));
                        $(this).attr('value', oldValue.replace('1', i));
                    });

                    container.append(clonable_tech_fields);
                }

                $("#tech_fields_container").html(container);
            }
        }

        function add_site_fields(){
            var num_sites = $("#num_sites").val();
            if(num_sites.length > 0){

                var container = $('<div>').addClass('site_field');

                for(var i=1; i<(num_sites);i++){

                    var clonable_site_fields = $('#clonable_site_fields').clone(true).removeAttr('id').removeAttr('style');

                    $('.dynamic', clonable_site_fields).each(function() {
                        var oldName = $(this).attr('name');
                        var oldId = $(this).attr('id');
                        var oldValue = $(this).attr('value');

                        $(this).attr('name', oldName.replace('*', i));
                        $(this).attr('id', oldId.replace('*', i));
                        $(this).attr('value', oldValue.replace('1', i));
                    });

                    container.append(clonable_site_fields);
                }

                $("#site_fields_container").html(container);
            }
        }

        $(document).ready(function(){
            $("#wait").hide();
            $("#rent_container").hide();
            $(".ticket_container").hide();
            update_amount();
            check_transport();
            add_tech_fields();
            add_site_fields();
        });

        function autocomplete_tech(e){
            $(e).autocomplete({
                type: 'post',
                serviceUrl:'/autocomplete/technicians',
                dataType: 'JSON'
            });
        }

        function autocomplete_site(e){
            $(e).autocomplete({
                type: 'post',
                serviceUrl:'/autocomplete/rbs_sites',
                dataType: 'JSON'
            });
        }
    </script>
@endsection
