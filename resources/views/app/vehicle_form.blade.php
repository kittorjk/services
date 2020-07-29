@extends('layouts.master')

@section('header')
    @parent
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">
        <div class="panel panel-violet">
            <div class="panel-heading" align="center">
                <div class="panel-title">{{ $vehicle ? 'Actualizar datos de vehículo' : 'Registrar nuevo vehículo' }}</div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                    <a href="{{ '/vehicle' }}" class="btn btn-warning" title="Volver a resumen de vehículos">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                @if($vehicle)
                    {{--
                    <form id="delete" action="/vehicle/{{ $vehicle->id }}" method="post">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                    --}}
                    <form novalidate="novalidate" action="{{ '/vehicle/'.$vehicle->id }}" method="post">
                        <input type="hidden" name="_method" value="put">
                        @else
                            <form novalidate="novalidate" action="{{ '/vehicle' }}" method="post">
                                @endif
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="form-group">
                                    <div class="input-group">

                                        <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                            <div class="input-group" style="width: 100%">
                                                <label for="license_plate" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Placa: <span class="pull-right">*</span>
                                                </label>

                                                <input required="required" type="text" class="form-control" name="license_plate"
                                                       id="license_plate"
                                                       value="{{ $vehicle ? $vehicle->license_plate : old('license_plate') }}"
                                                       placeholder="Número de placa">
                                            </div>

                                            <div class="input-group" style="width: 100%">
                                                <label for="type" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Tipo:<span class="pull-right">*</span>
                                                </label>

                                                <input required="required" type="text" class="form-control" name="type" id="type"
                                                       value="{{ $vehicle ? $vehicle->type : old('type') }}"
                                                       placeholder="Tipo de vehículo">
                                            </div>

                                            <div class="input-group" style="width: 100%">
                                                <label for="model" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Modelo:
                                                </label>

                                                <input required="required" type="text" class="form-control" name="model" id="model"
                                                       value="{{ $vehicle ? $vehicle->model : old('model') }}" placeholder="Modelo">
                                            </div>

                                            <div class="input-group" style="width: 100%">
                                                <label for="owner" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Propietario: <span class="pull-right">*</span>
                                                </label>

                                                <select required="required" class="form-control" name="owner" id="owner">
                                                    <option value="" hidden>Seleccione el propietario del vehículo</option>
                                                    <option value="ABROS"
                                                            {{ ($vehicle&&$vehicle->owner=='ABROS')||old('owner')=='ABROS' ?
                                                                'selected="selected"' : '' }}>ABROS</option>
                                                    @foreach($owners as $owner)
                                                        <option value="{{ $owner->owner }}"
                                                                {{ ($vehicle&&$vehicle->owner==$owner->owner)||
                                                                    old('owner')==$owner->owner ?
                                                                    'selected="selected"' : '' }}>{{ $owner->owner }}</option>
                                                    @endforeach
                                                    <option value="Otro">Otro</option>
                                                </select>
                                            </div>
                                            <input required="required" type="text" class="form-control" name="other_owner"
                                                   id="other_owner" value="{{ $vehicle ? $vehicle->owner : '' }}"
                                                   placeholder="Propietario del vehículo *" disabled="disabled">

                                            <div class="input-group" style="width: 100%">
                                                <label for="branch_id" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Sucursal: <span class="pull-right">*</span>
                                                </label>

                                                <select required="required" class="form-control" name="branch_id" id="branch_id">
                                                    <option value="" hidden>{{ 'Seleccione la sucursal a la que'.
                                                            ' está asignado el vehículo' }}</option>
                                                    @foreach($branches as $branch)
                                                        <option value="{{ $branch->id }}"
                                                                {{ ($vehicle&&$vehicle->branch_id==$branch->id)||
                                                                    old('branch_id')==$branch->id ? 'selected="selected"' :
                                                                     '' }}>{{ $branch->name }}</option>
                                                    @endforeach
                                                    {{--
                                                    <option value="Cochabamba"
                                                            {{ ($vehicle&&$vehicle->branch=='Cochabamba')||
                                                                old('branch')=='Cochabamba' ?
                                                                'selected="selected"' : '' }}>Cochabamba</option>
                                                    <option value="La Paz"
                                                            {{ ($vehicle&&$vehicle->branch=='La Paz')||
                                                                old('branch')=='La Paz' ?
                                                                'selected="selected"' : '' }}>La Paz</option>
                                                    <option value="Santa Cruz"
                                                            {{ ($vehicle&&$vehicle->branch=='Santa Cruz')||
                                                                old('branch')=='Santa Cruz' ?
                                                                'selected="selected"' : '' }}>Santa Cruz</option>
                                                    --}}
                                                </select>
                                            </div>

                                            <div class="input-group" style="width: 75%">
                                                <span class="input-group-addon" style="width:31%; text-align: left">
                                                    Kilometraje: <span class="pull-right">*</span>
                                                </span>

                                                <input required="required" type="number" class="form-control" name="mileage"
                                                       step="any" min="0"
                                                       value="{{ $vehicle ? $vehicle->mileage : '' }}" placeholder="0.00">

                                                <span class="input-group-addon">Km</span>
                                            </div>

                                            <div class="input-group" style="width: 100%">
                                                <label for="gas_type" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Tipo combustible:
                                                </label>

                                                <select required="required" class="form-control" name="gas_type" id="gas_type">
                                                    <option value="" hidden>{{ 'Seleccione el tipo de combustible que'.
                                                            ' usa el vehículo' }}</option>
                                                    <option value="gnv"
                                                            {{ ($vehicle&&$vehicle->gas_type=='gnv')||old('gas_type')=='gnv' ?
                                                                'selected="selected"' : '' }}>GNV</option>
                                                    <option value="diesel"
                                                            {{ ($vehicle&&$vehicle->gas_type=='diesel')||old('gas_type')=='diesel' ?
                                                                'selected="selected"' : '' }}>Diesel</option>
                                                    <option value="gasolina"
                                                            {{ ($vehicle&&$vehicle->gas_type=='gasolina')||
                                                                old('gas_type')=='gasolina' ?
                                                                'selected="selected"' : '' }}>Gasolina</option>
                                                </select>
                                            </div>

                                            <div class="input-group" style="width: 75%">
                                                <span class="input-group-addon" style="width:31%; text-align: left">Cap. tanque:</span>

                                                <input required="required" type="number" class="form-control" name="gas_capacity"
                                                       step="any" min="0"
                                                       value="{{ $vehicle ? $vehicle->gas_capacity : '' }}" placeholder="0.00">

                                                <span class="input-group-addon">Lts</span>
                                            </div>

                                            {{--
                                            @if($vehicle&&$user->priv_level==4||$user->work_type=='Transporte')
                                                <div class="input-group" style="width: 100%">
                                                    <label for="status" class="input-group-addon" style="width: 23%;text-align: left">
                                                        Estado:
                                                    </label>

                                                    <select required="required" class="form-control" name="status" id="status">
                                                        <option value="" hidden>Seleccione un estado</option>
                                                        <option value="Disponible"
                                                                {{ $vehicle&&$vehicle->status=='Disponible' ?
                                                                 'selected="selected"' : '' }}>Disponible</option>
                                                        <option value="Activo"
                                                                {{ $vehicle&&$vehicle->status=='Activo' ?
                                                                 'selected="selected"' : '' }}>Activo</option>
                                                        <option value="En mantenimiento"
                                                                {{ $vehicle&&$vehicle->status=='En mantenimiento' ?
                                                                 'selected="selected"' : '' }}>En mantenimiento</option>
                                                        <option value="Baja"
                                                                {{ $vehicle&&$vehicle->status=='Baja' ?
                                                                 'selected="selected"' : '' }}>Baja</option>
                                                    </select>
                                                </div>

                                                <div id="maintenance_type_container" class="input-group" style="width: 100%">
                                                    <label for="maintenance_type" class="input-group-addon"
                                                        style="width: 23%;text-align: left">
                                                        Tipo de mant.:
                                                    </label>

                                                    <select required="required" class="form-control" name="maintenance_type"
                                                            id="maintenance_type" disabled="disabled">
                                                        <option value="" hidden>Seleccione el tipo de mantenimiento</option>
                                                        <option value="Preventivo">Preventivo</option>
                                                        <option value="Correctivo">Correctivo</option>
                                                    </select>
                                                </div>

                                                <div id="parameter_id_container" class="input-group" style="width: 100%">
                                                    <label for="parameter_id" class="input-group-addon"
                                                        style="width: 23%;text-align: left">
                                                        Categoría mant.:
                                                    </label>

                                                    <select required="required" class="form-control" name="parameter_id"
                                                            id="parameter_id" disabled="disabled">
                                                        <option value="" hidden>Seleccione el tipo de mantenimiento preventivo</option>
                                                        @foreach($service_parameters as $service_parameter)
                                                            <option value="{{ $service_parameter->id }}">
                                                                {{ $service_parameter->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endif
                                            --}}

                                            <div class="input-group" style="width: 100%">
                                                <label for="condition" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Observaciones:
                                                </label>

                                                <textarea rows="3" required="required" class="form-control" name="condition"
                                                          id="condition"
                                                          placeholder="Observaciones de la condición actual del vehículo">{{
                                                            $vehicle ? $vehicle->condition : old('condition') }}</textarea>
                                            </div>

                                        </span>

                                    </div>
                                </div>

                                @include('app.loader_gif')

                                <div class="form-group" align="center">
                                    <button type="submit" class="btn btn-success"
                                            onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                                        <i class="fa fa-floppy-o"></i> Guardar
                                    </button>

                                    {{-- Vehicle records cannot be removed, only marked as "disabled"
                                    @if($vehicle)
                                        @if($user->priv_level==4)
                                            <button type="submit" form="delete" class="btn btn-danger">
                                                <i class="fa fa-trash-o"></i> Quitar
                                            </button>
                                        @endif
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
        var owner = $('#owner'), other_owner = $('#other_owner');

        owner.change(function () {
            if (owner.val()==='Otro') {
                other_owner.removeAttr('disabled').show();
            } else {
                other_owner.attr('disabled', 'disabled').val('').hide();
            }
        }).trigger('change');

        $(document).ready(function(){
            $("#wait").hide();

            /*
            var $maintenance_type = $('#maintenance_type'), maintenance_type_container = $('#maintenance_type_container'),
                    $parameter_id = $('#parameter_id'), parameter_id_container = $('#parameter_id_container'),
                    $status = $("#status");

            maintenance_type_container.hide();
            $maintenance_type.hide();
            parameter_id_container.hide();
            $parameter_id.hide();

            $status.change(function () {
                if ($status.val() == 'En mantenimiento') {
                    $maintenance_type.removeAttr('disabled').show();
                    maintenance_type_container.show();
                }
                else{
                    $maintenance_type.attr('disabled', 'disabled').val('').hide();
                    maintenance_type_container.hide();
                }
            }).trigger('change');

            $maintenance_type.change(function () {
                if ($maintenance_type.val() == 'Preventivo') {
                    $parameter_id.removeAttr('disabled').show();
                    parameter_id_container.show();
                }
                else {
                    $parameter_id.attr('disabled', 'disabled').val('').hide();
                    parameter_id_container.hide();
                }
            }).trigger('change');
            */
        });
    </script>
@endsection
