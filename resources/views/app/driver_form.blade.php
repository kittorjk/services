@extends('layouts.master')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/custom_autocomplete.css") }}">
    <script src="{{ asset('https://cdnjs.cloudflare.com/ajax/libs/jquery.devbridge-autocomplete/1.2.27/jquery.autocomplete.js') }}">
    </script>

    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">
        <div class="panel panel-violet">
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    {{ $driver ? 'Modificar registro de asignación' : 'Registrar asignación de vehículo' }}
                </div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                    <a href="{{ '/driver' }}" class="btn btn-warning" title="Volver al resumen de asignaciones">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <p><em>Nota.- los campos con * son obligatorios</em></p>

                @if($driver)
                    <form id="delete" action="/driver/{{ $driver->id }}" method="post">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                    <form novalidate="novalidate" action="{{ '/driver/'.$driver->id }}" method="post">
                        <input type="hidden" name="_method" value="put">
                        @else
                            <form novalidate="novalidate" action="{{ '/driver' }}" method="post">
                                @endif
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="form-group">
                                    <div class="input-group">

                                        <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                            <div class="input-group" style="width: 100%">
                                                <label for="vehicle_requirement_id" class="input-group-addon"
                                                       style="width: 23%;text-align: left">
                                                    Requerimiento: <span class="pull-right">*</span>
                                                </label>

                                                <select required="required" class="form-control" name="vehicle_requirement_id"
                                                        id="vehicle_requirement_id">
                                                    <option value="" hidden>Seleccione un requerimiento de vehículo</option>
                                                    @if($requirement)
                                                        <option value="{{ $requirement->id }}"
                                                                {{ $driver||$requirement ? 'selected="selected"' : '' }}
                                                        >{{ $requirement->code }}</option>
                                                    @endif
                                                </select>
                                            </div>

                                            <div class="input-group" style="width: 100%">
                                                <label for="vehicle_id" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Vehículo: <span class="pull-right">*</span>
                                                </label>

                                                <select required="required" class="form-control" name="vehicle_id" id="vehicle_id">
                                                    <option value="" hidden>Seleccione un vehículo</option>
                                                    @if($requirement&&$requirement->vehicle)
                                                        <option value="{{ $requirement->vehicle->id }}"
                                                                {{ $driver||$requirement->vehicle ? 'selected="selected"' : '' }}
                                                                >{{ $requirement->vehicle->type.' '.
                                                                $requirement->vehicle->license_plate }}</option>
                                                    @endif
                                                </select>
                                            </div>

                                            <div id="deliverer_container" class="input-group has-feedback" style="width: 100%">
                                                <label for="deliverer_name" class="input-group-addon"
                                                       style="width: 23%;text-align: left">
                                                    Quien entrega: <span class="pull-right">*</span>
                                                </label>

                                                <input required="required" type="text" class="form-control" name="deliverer_name"
                                                       id="deliverer_name"
                                                       value="{{ $driver ? $driver->deliverer->name :
                                                        ($requirement&&$requirement->person_from ?
                                                         $requirement->person_from->name : '')
                                                       /*($user->priv_level<2 ? $user->name : '')*/ }}"
                                                       {{-- $driver ? $driver->deliverer->name : ($user->priv_level<2 ? $user->name : '') --}}
                                                       placeholder="Quién entrega el vehículo" readonly="readonly">
                                            </div>
                                            {{--
                                                <div class="input-group" style="width: 100%;text-align: center"
                                                id="deliverer_check_result" align="center"></div>
                                                --}}

                                            <div id="receiver_container" class="input-group has-feedback" style="width: 100%">
                                                <label for="receiver_name" class="input-group-addon"
                                                       style="width:23%;text-align: left">
                                                    Quien recibe: <span class="pull-right">*</span>
                                                </label>

                                                <input required="required" type="text" class="form-control" name="receiver_name"
                                                       id="receiver_name"
                                                       value="{{ $driver ? $driver->receiver->name :
                                                        ($requirement&&$requirement->person_for ?
                                                         $requirement->person_for->name : '') }}"
                                                       placeholder="Quién recibe el vehículo" readonly="readonly">
                                            </div>
                                            {{--
                                                <div class="input-group" style="width: 100%;text-align: center"
                                                id="receiver_check_result" align="center"></div>
                                                --}}

                                            <div class="input-group" style="width: 100%">
                                                <label for="project_code" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Proyecto:
                                                </label>

                                                <input required="required" type="text" class="form-control" name="project_code"
                                                       value="{{ $driver&&$driver->assignment ? $driver->assignment->code
                                                       /*'PR-'.str_pad($driver->assignment->id, 4, "0", STR_PAD_LEFT).
                                                       date_format($driver->assignment->created_at,'-y')*/ : old('project_code') }}"
                                                       placeholder="Código de proyecto">
                                            </div>

                                            <div class="input-group" style="width: 100%">
                                                <label for="project_type" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Área: <span class="pull-right">*</span>
                                                </label>

                                                <select required="required" class="form-control" name="project_type" id="project_type">
                                                    <option value="" hidden>Seleccione el área de trabajo</option>
                                                    <option value="Fibra óptica"
                                                            {{ ($driver&&$driver->project_type=='Fibra óptica')||
                                                                ($requirement&&$requirement->person_from&&
                                                                $requirement->person_from->work_type=='Fibra óptica')||
                                                                old('project_type')=='Fibra óptica' ?
                                                                'selected="selected"' : '' }}>Fibra óptica</option>
                                                    <option value="Radiobases"
                                                            {{ ($driver&&$driver->project_type=='Radiobases')||
                                                                ($requirement&&$requirement->person_from&&
                                                                $requirement->person_from->work_type=='Radiobases')||
                                                                old('project_type')=='Radiobases' ?
                                                                'selected="selected"' : '' }}>Radiobases</option>
                                                    <option value="Instalación de energía"
                                                            {{ ($driver&&$driver->project_type=='Instalación de energía')||
                                                                ($requirement&&$requirement->person_from&&
                                                                $requirement->person_from->work_type=='Instalación de energía')||
                                                                old('project_type')=='Instalación de energía' ?
                                                                'selected="selected"' : '' }}>Instalación de energía</option>
                                                    <option value="Obras Civiles"
                                                            {{ ($driver&&$driver->project_type=='Obras Civiles')||
                                                                ($requirement&&$requirement->person_from&&
                                                                $requirement->person_from->work_type=='Obras Civiles')||
                                                                old('project_type')=='Obras Civiles' ?
                                                                'selected="selected"' : '' }}>Obras Civiles</option>
                                                    <option value="Venta de material"
                                                            {{ ($driver&&$driver->project_type=='Venta de material')||
                                                                ($requirement&&$requirement->person_from&&
                                                                $requirement->person_from->work_type=='Venta de material')||
                                                                old('project_type')=='Venta de material' ?
                                                                'selected="selected"' : '' }}>Venta de material</option>
                                                </select>
                                            </div>

                                            <div class="input-group" style="width: 100%">
                                                <label for="destination" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Destino: <span class="pull-right">*</span>
                                                </label>

                                                <input required="required" type="text" class="form-control" name="destination"
                                                       id="destination"
                                                       value="{{ $driver ? $driver->destination :
                                                        ($requirement&&($requirement->type=='devolution'||
                                                         $requirement->type=='transfer_branch') ? $requirement->branch_destination :
                                                          old('destination')) }}"
                                                       placeholder="A dónde se lleva el vehículo">
                                            </div>

                                            <div class="input-group" style="width: 75%">
                                                <span class="input-group-addon" style="width:31%; text-align: left">
                                                    Kilometraje: <span class="pull-right">*</span>
                                                </span>

                                                <input required="required" type="number" class="form-control" name="mileage_before"
                                                       step="any" min="0"
                                                       value="{{ $driver ? $driver->mileage_before : old('mileage_before') }}"
                                                       placeholder="{{ $requirement->vehicle ?
                                                        'Último registro '.$requirement->vehicle->mileage : 0.00 }}">
                                                <span class="input-group-addon">Km</span>
                                            </div>

                                            <div class="input-group" style="width: 100%">
                                                <label for="reason" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Obs. de entrega:
                                                </label>

                                                <textarea rows="3" required="required" class="form-control" name="reason" id="reason"
                                                          placeholder="Observaciones al momento de entrega del vehículo">{{ $driver ?
                                                     $driver->reason : old('reason') }}</textarea>
                                            </div>

                                            <div class="input-group" style="width: 100%">
                                                <label for="observations" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Obs. de vehículo:
                                                </label>

                                                <textarea rows="3" required="required" class="form-control" name="observations"
                                                          placeholder="Observaciones de vehículo">{{ $driver ?
                                                     $driver->observations : old('observations') }}</textarea>
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

                                    @if($driver&&$user->priv_level==4)
                                        <button type="submit" form="delete" class="btn btn-danger">
                                            <i class="fa fa-trash-o"></i> Eliminar
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
        /*
        function check_existence(e){

            var deliverer_name=$('#deliverer_name').val();
            var receiver_name=$('#receiver_name').val();

            if(deliverer_name.length >0){
                $.post('/check_existence', { resp_name: deliverer_name }, function(data){
                    $("#deliverer_check_result").html(data.message).show();
                    if(data.status=="warning"){
                        $('#deliverer_container').addClass("has-warning").removeClass("has-success");
                    }
                    else if(data.status=="success"){
                        $('#deliverer_container').addClass("has-success").removeClass("has-warning");
                    }
                });
            }
            else{
                $("#deliverer_check_result").hide();
                $('#deliverer_container').removeClass("has-warning").removeClass("has-success");
            }

            if(receiver_name.length >0){
                $.post('/check_existence', { resp_name: receiver_name }, function(data){
                    $("#receiver_check_result").html(data.message).show();
                    if(data.status=="warning"){
                        $('#receiver_container').addClass("has-warning").removeClass("has-success");
                    }
                    else if(data.status=="success"){
                        $('#receiver_container').addClass("has-success").removeClass("has-warning");
                    }
                });
            }
            else{
                $("#receiver_check_result").hide();
                $('#receiver_container').removeClass("has-warning").removeClass("has-success");
            }
        }
        */

        $(document).ready(function(){
            $("#wait").hide();
            /*
            $("#deliverer_check_result").hide();
            $("#receiver_check_result").hide();
            $('#deliverer_name').focusout(check_existence);
            $('#receiver_name').focusout(check_existence);
            */
        });

        /*
        $('#deliverer_name').autocomplete({
            type: 'post',
            serviceUrl:'/autocomplete/users',
            dataType: 'JSON',
            onSelect: check_existence
        });

        $('#receiver_name').autocomplete({
            type: 'post',
            serviceUrl:'/autocomplete/users',
            dataType: 'JSON',
            onSelect: check_existence
        });
        */
    </script>
@endsection
