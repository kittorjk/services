<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 30/11/2017
 * Time: 02:54 PM
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

    <div id="loginbox" class="mg-tp-px-50 mg-btm-px-40 col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1">

        <div class="panel panel-sky">
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    {{ $stipend ? 'Modificar solicitud de viáticos' : 'Nueva solicitud de viáticos' }}
                </div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-undo"></i>
                    </a>
                    <a href="{{ '/stipend_request?asg='.$asg }}" class="btn btn-warning"
                       title="Volver a la tabla de solicitudes de viáticos">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                @if($stipend)
                    <form id="delete" action="/stipend_request/{{ $stipend->id }}" method="post">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                    <form novalidate="novalidate" action="{{ '/stipend_request/'.$stipend->id }}" method="post"
                          class="form-horizontal">
                        <input type="hidden" name="_method" value="put">
                        @else
                            <form novalidate="novalidate" action="{{ '/stipend_request' }}" method="post" class="form-horizontal">
                                @endif
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                                <fieldset>
                                    <legend class="col-md-10">Personal</legend>

                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">

                                            <div class="form-group{{ $errors->has('employee_name') ? ' has-error' : '' }}">
                                                <label for="employee_name" class="col-md-4 control-label">(*) Nombre</label>

                                                <div class="col-md-6">
                                                    <input id="employee_name" type="text"
                                                           class="form-control complete_tech dynamic"
                                                           name="employee_name" placeholder="Nombre"
                                                           value="{{ $stipend&&$stipend->employee ?
                                                                $stipend->employee->first_name.' '.$stipend->employee->last_name :
                                                                old('employee_name') }}" required
                                                           onkeydown="autocomplete_employee(this)">

                                                    @if ($errors->has('employee_name'))
                                                        <span class="help-block">
                                                            <strong>{{ $errors->first('employee_name') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="form-group{{ $errors->has('assignment_id') ? ' has-error' : '' }}">
                                                <label for="assignment_id" class="col-md-4 control-label">(*) Asignación</label>

                                                <div class="col-md-6">
                                                    <select id="assignment_id" name="assignment_id" class="form-control">
                                                        <option value="" hidden="hidden">Asignación</option>
                                                        <option value="{{ $asg }}" title="{{ $assignment->name }}"
                                                                selected="selected">
                                                            {{ str_limit($assignment->name,200) }}
                                                        </option>
                                                    </select>

                                                    @if ($errors->has('assignment_id'))
                                                        <span class="help-block">
                                                            <strong>{{ $errors->first('assignment_id') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="form-group{{ $errors->has('site_id') ? ' has-error' : '' }}">
                                                <label for="site_ids" class="col-md-4 control-label">
                                                    Sitio
                                                    <br>
                                                    <em style="font-weight: normal">
                                                        (Seleccione varios sitios con 'ctrl')
                                                    </em>
                                                </label>

                                                <div class="col-md-6">
                                                    <select id="site_ids" name="site_ids[]" class="form-control" multiple="multiple">
                                                        <option value="">(Vacío)</option>
                                                        @foreach($sites as $site)
                                                            <option value="{{ $site->id }}" title="{{ $site->name }}"
                                                                    {{ ($stipend&&$stipend->site_ids&&
                                                                        in_array($site->id,$stipend->site_ids))||
                                                                        old('site_ids')&&in_array($site->id, old('site_ids')) ?
                                                                         'selected="selected"' : '' }}>
                                                                {{ str_limit($site->name,200) }}
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                    @if($errors->has('site_ids'))
                                                        <span class="help-block">
                                                            <strong>{{ $errors->first('site_ids') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </fieldset>

                                <fieldset>
                                    <legend class="col-md-10">Tiempo</legend>

                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">

                                            <div class="form-group{{ $errors->has('date_from') ? ' has-error' : '' }}">
                                                <label for="date_from" class="col-md-4 control-label">(*) Desde</label>

                                                <div class="col-md-3">
                                                    <input id="date_from" type="date" class="form-control" name="date_from"
                                                           step="1" min="{{ $assignment->start_date->format('Y-m-d') }}"
                                                           max="{{ $assignment->end_date->format('Y-m-d') }}"
                                                           value="{{ $stipend ? $stipend->date_from->format('Y-m-d') :
                                                            (old('date_from') ?: date('Y-m-d')) }}">

                                                    @if($errors->has('date_from'))
                                                        <span class="help-block">
                                                            <strong>{{ $errors->first('date_from') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="form-group{{ $errors->has('date_to') ? ' has-error' : '' }}">

                                                <label for="date_to" class="col-md-4 control-label">(*) Hasta</label>

                                                <div class="col-md-3">
                                                    <input id="date_to" type="date" class="form-control" name="date_to"
                                                           step="1" min="{{ $assignment->start_date->format('Y-m-d') }}"
                                                           max="{{ $assignment->end_date->format('Y-m-d') }}"
                                                           value="{{ $stipend ? $stipend->date_to->format('Y-m-d') :
                                                            (old('date_to') ?: '') }}">

                                                    @if($errors->has('date_to'))
                                                        <span class="help-block">
                                                            <strong>{{ $errors->first('date_to') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </fieldset>

                                <fieldset>
                                    <legend class="col-md-10">Montos</legend>

                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">

                                            <div class="form-group
                                                {{ $errors->has('per_day_amount')||$errors->has('hotel_amount') ?
                                                    ' has-error' : '' }}">
                                                <label for="per_day_amount" class="col-md-4 control-label">
                                                    Viático por día
                                                </label>

                                                <div class="col-md-2">
                                                    <input id="per_day_amount" type="number" class="form-control dynamic"
                                                           name="per_day_amount" step="any" min="0"
                                                           value="{{ $stipend&&$stipend->per_day_amount!=0 ?
                                                                $stipend->per_day_amount : old('per_day_amount') }}"
                                                           placeholder="Bs" required>

                                                    @if($errors->has('per_day_amount'))
                                                        <span class="help-block">
                                                            <strong>{{ $errors->first('per_day_amount') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>

                                                <label for="hotel_amount" class="col-md-2 control-label">
                                                    Alojamiento
                                                </label>

                                                <div class="col-md-2">
                                                    <input id="hotel_amount" type="number" class="form-control amount dynamic"
                                                           name="hotel_amount" step="any" min="0"
                                                           value="{{ $stipend&&$stipend->hotel_amount!=0 ?
                                                                $stipend->hotel_amount : old('hotel_amount') }}"
                                                           placeholder="Bs" required>

                                                    @if($errors->has('hotel_amount'))
                                                        <span class="help-block">
                                                            <strong>{{ $errors->first('hotel_amount') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">

                                            <div class="form-group
                                                {{ $errors->has('transport_amount')||$errors->has('gas_amount') ?
                                                    ' has-error' : '' }}">
                                                <label for="transport_amount" class="col-md-4 control-label">
                                                    Transporte (Pasajes)
                                                </label>

                                                <div class="col-md-2">
                                                    <input id="transport_amount" type="number" class="form-control amount dynamic"
                                                           name="transport_amount" step="any" min="0"
                                                           value="{{ $stipend&&$stipend->transport_amount!=0 ?
                                                                $stipend->transport_amount : old('transport_amount') }}"
                                                           placeholder="Bs" required>

                                                    @if($errors->has('transport_amount'))
                                                        <span class="help-block">
                                                            <strong>{{ $errors->first('transport_amount') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>

                                                <label for="gas_amount" class="col-md-2 control-label">
                                                    Combustible
                                                </label>

                                                <div class="col-md-2">
                                                    <input id="gas_amount" type="number" class="form-control amount dynamic"
                                                           name="gas_amount" step="any" min="0"
                                                           value="{{ $stipend&&$stipend->gas_amount!=0 ?
                                                                $stipend->gas_amount : old('gas_amount') }}"
                                                           placeholder="Bs" required>

                                                    @if($errors->has('gas_amount'))
                                                        <span class="help-block">
                                                            <strong>{{ $errors->first('gas_amount') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">

                                            <div class="form-group
                                                {{ $errors->has('taxi_amount')||$errors->has('comm_amount') ?
                                                    ' has-error' : '' }}">
                                                <label for="taxi_amount" class="col-md-4 control-label">
                                                    Taxi (Pasajes)
                                                </label>

                                                <div class="col-md-2">
                                                    <input id="taxi_amount" type="number" class="form-control amount dynamic"
                                                           name="taxi_amount" step="any" min="0"
                                                           value="{{ $stipend&&$stipend->taxi_amount!=0 ?
                                                                $stipend->taxi_amount : old('taxi_amount') }}"
                                                           placeholder="Bs" required>

                                                    @if($errors->has('taxi_amount'))
                                                        <span class="help-block">
                                                            <strong>{{ $errors->first('taxi_amount') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>

                                                <label for="comm_amount" class="col-md-2 control-label"
                                                    title="Incluye: tarjetas de recarga de crédito, llamadas desde cabinas, etc.">
                                                    Comunicaciones
                                                </label>

                                                <div class="col-md-2">
                                                    <input id="comm_amount" type="number" class="form-control amount dynamic"
                                                           name="comm_amount" step="any" min="0"
                                                           value="{{ $stipend&&$stipend->comm_amount!=0 ?
                                                                $stipend->comm_amount : old('comm_amount') }}"
                                                           placeholder="Bs" required>

                                                    @if($errors->has('comm_amount'))
                                                        <span class="help-block">
                                                            <strong>{{ $errors->first('comm_amount') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">

                                            <div class="form-group
                                                {{ $errors->has('materials_amount')||$errors->has('extras_amount') ?
                                                    ' has-error' : '' }}">
                                                <label for="materials_amount" class="col-md-4 control-label">
                                                    Compra de materiales
                                                </label>

                                                <div class="col-md-2">
                                                    <input id="materials_amount" type="number" class="form-control amount dynamic"
                                                           name="materials_amount" step="any" min="0"
                                                           value="{{ $stipend&&$stipend->materials_amount!=0 ?
                                                                $stipend->materials_amount : old('materials_amount') }}"
                                                           placeholder="Bs" required>

                                                    @if($errors->has('materials_amount'))
                                                        <span class="help-block">
                                                            <strong>{{ $errors->first('materials_amount') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>

                                                <label for="extras_amount" class="col-md-2 control-label">
                                                    Extras
                                                </label>

                                                <div class="col-md-2">
                                                    <input id="extras_amount" type="number" class="form-control amount dynamic"
                                                           name="extras_amount" step="any" min="0"
                                                           value="{{ $stipend&&$stipend->extras_amount!=0 ?
                                                                $stipend->extras_amount : old('extras_amount') }}"
                                                           placeholder="Bs" required>

                                                    @if($errors->has('extras_amount'))
                                                        <span class="help-block">
                                                            <strong>{{ $errors->first('extras_amount') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">

                                            <div class="form-group {{ $errors->has('additional') ?' has-error' : '' }}">
                                                <label for="additional" class="col-md-4 control-label"
                                                       title="Incluye: transporte, combustible, recargas de celular entre otros">
                                                    Adicionales
                                                </label>

                                                <div class="col-md-2">
                                                    <input id="additional" type="number" class="form-control dynamic"
                                                           name="additional" step="any" min="0" readonly="readonly"
                                                           value="{{ $stipend ? $stipend->additional : old('additional') }}"
                                                           placeholder="Bs" required>

                                                    @if($errors->has('additional'))
                                                        <span class="help-block">
                                                            <strong>{{ $errors->first('additional') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </fieldset>

                                <fieldset>
                                    <legend class="col-md-10">Información adicional</legend>

                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">

                                            <div class="form-group{{ $errors->has('reason') ? ' has-error' : '' }}">
                                                <label for="reason" class="col-md-4 control-label">
                                                    (*) Trabajo a realizar
                                                </label>

                                                <div class="col-md-6">
                                                    <textarea rows="3" class="form-control" id="reason"
                                                          name="reason">{{ $stipend ? $stipend->reason :
                                                            old('reason') }}</textarea>

                                                    @if($errors->has('reason'))
                                                        <span class="help-block">
                                                            <strong>{{ $errors->first('reason') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">

                                            <div class="form-group{{ $errors->has('work_area') ? ' has-error' : '' }}">
                                                <label for="work_area" class="col-md-4 control-label">Área de trabajo</label>

                                                <div class="col-md-6">
                                                    <select id="work_area" name="work_area" class="form-control">
                                                        <option value="" hidden="hidden">Seleccione un área</option>
                                                        <option value="FO" {{ ($stipend&&$stipend->work_area=='FO')||
                                                                old('work_area')=='FO' ? 'selected="selected"' :
                                                                 '' }}>FO - Fibra óptica</option>
                                                        <option value="RBS" {{ ($stipend&&$stipend->work_area=='RBS')||
                                                                old('work_area')=='RBS' ? 'selected="selected"' :
                                                                 '' }}>RBS - Radiobases</option>
                                                    </select>

                                                    @if ($errors->has('work_area'))
                                                        <span class="help-block">
                                                            <strong>{{ $errors->first('work_area') }}</strong>
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
                                        <i class="fa fa-arrow-right"></i> Solicitar
                                    </button>

                                    {{--
                                    @if($stipend&&$user->priv_level==4)
                                        <button type="submit" form="delete" class="btn btn-danger">
                                            <i class="fa fa-trash-o"></i> Eliminar
                                        </button>
                                    @endif
                                    --}}
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

        $('.amount').keyup(function(){update_amount()});

        function update_amount(){
            var additional = $("#additional"), sum = 0;

            $('.amount').each(function() {
                sum += Number($(this).val());
            });

            additional.val(sum);
        }

        $(document).ready(function(){
            $("#wait").hide();
        });

        function autocomplete_employee(e){
            $(e).autocomplete({
                type: 'post',
                serviceUrl:'/autocomplete/employees',
                dataType: 'JSON'
            });
        }
    </script>
@endsection
