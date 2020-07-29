@extends('layouts.master')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">
        <div class="panel panel-10gray">
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    {{ $maintenance ? 'Actualizar información de mantenimiento' : 'Agregar nuevo registro de mantenimiento' }}
                </div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                    <a href="{{ '/maintenance' }}" class="btn btn-warning" title="Volver a lista de activos en mantenimiento">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <p><em>Nota.- Los campos con * son obligatorios</em></p>

                @if($maintenance)
                    <form id="delete" action="/maintenance/{{ $maintenance->id }}" method="post">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                    <form novalidate="novalidate" action="{{ '/maintenance/'.$maintenance->id }}" method="post">
                        <input type="hidden" name="_method" value="put">
                        @else
                            <form novalidate="novalidate" action="{{ '/maintenance' }}" method="post">
                                @endif
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="form-group">
                                    <div class="input-group">

                                        <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                            <div class="input-group" style="width: 100%">
                                                <label for="active_type" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Tipo de activo: <span class="pull-right">*</span>
                                                </label>

                                                <select required="required" class="form-control" name="active_type" id="active_type"
                                                        {{ $maintenance ? 'disabled="disabled"' : '' }}>
                                                    <option value="" hidden>Seleccione el tipo de activo</option>
                                                    <option value="device"
                                                            {{ ($maintenance&&$maintenance->device_id!=0)||
                                                                old('active_type')=='device' ? 'selected="selected"' :
                                                                 '' }}>Equipo</option>
                                                    <option value="vehicle"
                                                            {{ ($maintenance&&$maintenance->vehicle_id!=0)||
                                                                old('active_type')=='vehicle' ? 'selected="selected"' :
                                                                 '' }}>Vehículo</option>
                                                </select>
                                            </div>

                                            @if($maintenance)
                                                <div class="input-group" style="width: 100%">
                                                    <label for="active_ro" class="input-group-addon"
                                                           style="width: 23%;text-align: left">
                                                        Activo:
                                                    </label>

                                                    <input type="text" class="form-control" name="active_ro" id="active_ro"
                                                           value="{{ $maintenance->active }}" readonly>
                                                </div>
                                            @endif

                                            <div id="active_container" class="input-group" style="width: 100%">
                                                <label for="active" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Activo: <span class="pull-right">*</span>
                                                </label>

                                                <select required="required" class="form-control" name="active" id="active"
                                                        {{ $maintenance ? 'disabled="disabled"' : '' }}>
                                                    <option value="" hidden>Seleccione el activo</option>
                                                </select>
                                            </div>

                                            <div class="input-group" style="width: 100%">
                                                <label for="type" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Tipo de mant.: <span class="pull-right">*</span>
                                                </label>

                                                <select required="required" class="form-control" name="type" id="type"
                                                        {{ $maintenance ? 'disabled="disabled"' : '' }}>
                                                    <option value="" hidden>Seleccione el tipo de mantenimiento</option>
                                                    <option value="Preventivo"
                                                            {{ ($maintenance&&$maintenance->type=='Preventivo')||
                                                                old('type')=='Preventivo' ? 'selected="selected"' :
                                                                 '' }}>Preventivo</option>
                                                    <option value="Correctivo"
                                                            {{ ($maintenance&&$maintenance->type=='Correctivo')||
                                                                old('type')=='Correctivo' ? 'selected="selected"' :
                                                                 '' }}>Correctivo</option>
                                                </select>
                                            </div>

                                            <div id="parameter_container" class="input-group" style="width: 100%">
                                                <label for="parameter_id" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Mant. preventivo:
                                                </label>

                                                <select required="required" class="form-control" name="parameter_id"
                                                        id="parameter_id" disabled="disabled">
                                                    <option value="" hidden>Seleccione el tipo de mantenimiento preventivo</option>
                                                    @foreach($service_parameters as $service_parameter)
                                                        <option value="{{ $service_parameter->id }}"
                                                                {{ ($maintenance&&$maintenance->parameter_id==$service_parameter->id)||
                                                                    old('parameter_id')==$service_parameter->id ?
                                                                    'selected="selected"' : '' }}>
                                                                {{ $service_parameter->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="input-group" style="width: 75%">
                                                <span class="input-group-addon" style="width:31%;text-align: left">Costo:</span>

                                                <input required="required" type="number" class="form-control" name="cost"
                                                       step="any" min="0" value="{{ $maintenance&&$maintenance->cost!=0 ?
                                                        $maintenance->cost : old('cost') }}"
                                                       placeholder="0.00">

                                                <span class="input-group-addon">Bs</span>
                                            </div>

                                            <textarea rows="4" required="required" class="form-control" name="detail"
                                                    placeholder="Detalle de mantenimiento">{{ $maintenance ?
                                                     $maintenance->detail : '' }}</textarea>

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
                                    @if($maintenance)
                                        @if($user->priv_level == 4)
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
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function update_parameter_visibility($type, $parameter_id, $active_type){
            if ($type.val()==='Preventivo' && $active_type.val()==='vehicle') {
                $parameter_id.removeAttr('disabled').show();
                $("#parameter_container").show();
            } else {
                $parameter_id.attr('disabled', 'disabled').val('').hide();
                $("#parameter_container").hide();
            }
        }

        $(document).ready(function(){
            $("#wait").hide();
            $("#active").hide();
            $("#active_container").hide();
            $("#parameter_container").hide();

            var $type = $('#type'), $parameter_id = $('#parameter_id'), $active_type = $("#active_type");

            $active_type.change(function () {
                var value = $("#active_type").find("option:selected").val();
                //$("#active_type option:selected").each(function () {
                    $.post('/dynamic_actives', { active_type: value /*$(this).val()*/ }, function(data){
                        $("#active").html(data).show();
                        $("#active_container").show();
                    });

                update_parameter_visibility($type, $parameter_id, $active_type);

                /*
                    if ($type.val() == 'Preventivo' && $active_type.val() == 'vehicle') {
                        $parameter_id.removeAttr('disabled').show();
                        $("#parameter_container").show();
                    } else {
                        $parameter_id.attr('disabled', 'disabled').val('').hide();
                        $("#parameter_container").hide();
                    }
                */
                //});
            });

            $type.change(function(){
                update_parameter_visibility($type, $parameter_id, $active_type)
            });

        });
    </script>
@endsection
