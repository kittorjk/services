@extends('layouts.master')

@section('header')
    @parent
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">
        <div class="panel panel-brown">
            <div class="panel-heading" align="center">
                <div class="panel-title">{{ $device ? 'Actualizar datos de equipo' : 'Registrar un nuevo equipo' }}</div>
            </div>
            <div class="panel-body" >
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                    <a href="{{ '/device' }}" class="btn btn-warning" title="Volver al resumen de equipos">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                @if($device)
                    {{--
                    <form id="delete" action="/device/{{ $device->id }}" method="post">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                    --}}
                    <form novalidate="novalidate" action="{{ '/device/'.$device->id }}" method="post">
                        <input type="hidden" name="_method" value="put">
                        @else
                            <form novalidate="novalidate" action="{{ '/device' }}" method="post">
                                @endif
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="form-group">
                                    <div class="input-group">

                                        <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                            <div class="input-group" style="width: 100%">
                                                <label for="serial" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Serial: <span class="pull-right">*</span>
                                                </label>

                                                <input required="required" type="text" class="form-control" name="serial"
                                                       id="serial" value="{{ $device ? $device->serial : '' }}"
                                                       placeholder="Número de serie">
                                            </div>

                                            <div class="input-group" style="width: 100%">
                                                <label for="type" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Tipo: <span class="pull-right">*</span>
                                                </label>

                                                <input required="required" type="text" class="form-control" name="type" id="type"
                                                       value="{{ $device ? $device->type : '' }}" placeholder="Tipo de equipo">
                                            </div>

                                            <div class="input-group" style="width: 100%">
                                                <label for="model" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Modelo: <span class="pull-right">*</span>
                                                </label>

                                                <input required="required" type="text" class="form-control" name="model" id="model"
                                                       value="{{ $device ? $device->model : '' }}" placeholder="Modelo">
                                            </div>

                                            <div class="input-group" style="width: 100%">
                                                <label for="owner" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Propietario: <span class="pull-right">*</span>
                                                </label>

                                                <select required="required" class="form-control" name="owner" id="owner">
                                                    <option value="" hidden>Seleccione el propietario del equipo</option>
                                                    <option value="ABROS"
                                                            {{ $device&&$device->owner=='ABROS' ?
                                                                'selected="selected"' : '' }}>ABROS</option>
                                                    @foreach($owners as $owner)
                                                        <option value="{{ $owner->owner }}"
                                                            {{ $device&&$owner->owner==$device->owner ?
                                                             'selected="selected"' : '' }}>{{ $owner->owner }}</option>
                                                    @endforeach
                                                    <option value="Otro">Otro</option>
                                                </select>
                                            </div>
                                            <input required="required" type="text" class="form-control" name="other_owner"
                                                   id="other_owner" value="{{ $device ? $device->owner : '' }}"
                                                   placeholder="Propietario del equipo" disabled="disabled">

                                            <div class="input-group" style="width: 100%">
                                                <label for="branch_id" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Oficina: <span class="pull-right">*</span>
                                                </label>

                                                <select required="required" class="form-control" name="branch_id" id="branch_id">
                                                    <option value="" hidden>{{ 'Seleccione la sucursal a la que está'.
                                                            ' asignado el equipo' }}</option>
                                                    @foreach($branches as $branch)
                                                        <option value="{{ $branch->id }}" title="{{ $branch->name }}"
                                                                {{ $device&&$device->branch_id==$branch->id ?
                                                                 'selected="selected"' : '' }}>{{ $branch->city }}</option>
                                                    @endforeach
                                                    {{--
                                                    <option value="Cochabamba"
                                                            {{ $device&&$device->branch=='Cochabamba' ?
                                                             'selected="selected"' : '' }}>Cochabamba</option>
                                                    <option value="La Paz"
                                                            {{ $device&&$device->branch=='La Paz' ?
                                                             'selected="selected"' : '' }}>La Paz</option>
                                                    <option value="Santa Cruz"
                                                            {{ $device&&$device->branch=='Santa Cruz' ?
                                                             'selected="selected"' : '' }}>Santa Cruz</option>
                                                     --}}
                                                </select>
                                            </div>

                                            <div class="input-group" style="width: 75%">
                                                <span class="input-group-addon" style="width:31%; text-align: left">Valor:</span>

                                                <input required="required" type="number" class="form-control" name="value"
                                                       step="any" min="0"
                                                       value="{{ $device&&$device->value!=0 ? $device->value : '' }}"
                                                       placeholder="0.00">

                                                <span class="input-group-addon">Bs</span>
                                            </div>

                                            {{--
                                            @if($device&&$user->priv_level==4)
                                                <div class="input-group" style="width: 100%">
                                                    <label for="status" class="input-group-addon" style="width: 23%;text-align: left">
                                                        Estado:
                                                    </label>

                                                    <select required="required" class="form-control" name="status" id="status">
                                                        <option value="" hidden>Seleccione un estado</option>
                                                        <option value="Disponible"
                                                                {{ $device&&$device->status=='Disponible' ?
                                                                 'selected="selected"' : '' }}>Disponible</option>
                                                        <option value="Activo"
                                                                {{ $device&&$device->status=='Activo' ?
                                                                 'selected="selected"' : '' }}>Activo</option>
                                                        <option value="En mantenimiento"
                                                                {{ $device&&$device->status=='En mantenimiento' ?
                                                                 'selected="selected"' : '' }}>En mantenimiento</option>
                                                        <option value="Baja"
                                                                {{ $device&&$device->status=='Baja' ?
                                                                 'selected="selected"' : '' }}>Baja</option>
                                                    </select>
                                                </div>
                                            @endif
                                            --}}

                                            <div class="input-group" style="width: 100%">
                                                <label for="condition" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Observaciones:
                                                </label>

                                                <textarea rows="3" required="required" class="form-control" name="condition"
                                                          placeholder="Observaciones de la condición actual del equipo">{{
                                                            $device ? $device->condition : '' }}</textarea>
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

                                    {{--
                                    @if($device)
                                        @if($user->priv_level==4)
                                            <button type="submit" form="delete" class="btn btn-danger">
                                                <i class="fa fa-trash-o"></i> Eliminar
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
        var $owner = $('#owner'), $other_owner = $('#other_owner');
        $owner.change(function () {
            if ($owner.val()==='Otro') {
                $other_owner.removeAttr('disabled').show();
            } else {
                $other_owner.attr('disabled', 'disabled').val('').hide();
            }
        }).trigger('change');

        $(document).ready(function(){
            $("#wait").hide();
        });
    </script>
@endsection
