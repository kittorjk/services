<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 20/09/2017
 * Time: 03:14 PM
 */
?>

@extends('layouts.master')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/custom_autocomplete.css") }}">
    <script src="{{ asset("https://cdnjs.cloudflare.com/ajax/libs/jquery.devbridge-autocomplete/1.2.27/jquery.autocomplete.js") }}">
    </script>

    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">
        <div class="panel panel-brown">
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    {{ $assignation ? 'Modificar registro de asignación' : 'Registrar asignación de línea corporativa' }}
                </div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-undo"></i>
                    </a>
                    <a href="{{ '/line_assignation' }}" class="btn btn-warning" title="Volver a resumen de asignación de líneas">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <p><em>Nota.- Los campos con * son obligatorios</em></p>

                @if($assignation)
                    <form id="delete" action="{{ '/line_assignation/'.$assignation->id }}" method="post">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                    <form novalidate="novalidate" action="{{ '/line_assignation/'.$assignation->id }}" method="post">
                        <input type="hidden" name="_method" value="put">
                        @else
                            <form novalidate="novalidate" action="{{ '/line_assignation' }}" method="post">
                                @endif
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="form-group">
                                    <div class="input-group">

                                        <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                            <div class="input-group" style="width: 100%">
                                                <label for="corp_line_requirement_id" class="input-group-addon"
                                                       style="width: 23%;text-align: left">
                                                    Requerimiento:
                                                </label>

                                                <select required="required" class="form-control" name="corp_line_requirement_id"
                                                        id="corp_line_requirement_id">
                                                    <option value="" hidden>Seleccione un requerimiento</option>
                                                    @foreach($requirements as $requirement)
                                                        <option value="{{ $requirement->id }}"
                                                                {{ ($assignation&&$assignation->corp_line_requirement_id==
                                                                    $requirement->id)||($req&&$req==$requirement->id)||
                                                                    (old('corp_line_requirement_id')==$requirement->id) ?
                                                                    'selected="selected"' : '' }}
                                                        >{{ $requirement->code }}</option>
                                                    @endforeach
                                                    <option value="{{ 0 }}" {{ $assignation&&$assignation->corp_line_requirement_id==0 ?
                                                        'selected="selected"' : '' }}>{{ 'Sin requerimiento' }}</option>
                                                </select>
                                            </div>

                                            <div class="input-group" style="width: 100%">
                                                <label for="corp_line_id" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Línea: <span class="pull-right">*</span>
                                                </label>

                                                <select required="required" class="form-control" name="corp_line_id"
                                                        id="corp_line_id">
                                                    <option value="" hidden>Seleccione una línea corporativa</option>
                                                    @foreach($lines as $line)
                                                        <option value="{{ $line->id }}"
                                                                {{ ($assignation&&$assignation->corp_line_id==$line->id)||
                                                                     ($ln&&$ln==$line->id)||(old('corp_line_id')==$line->id) ?
                                                                     'selected="selected"' : '' }}
                                                        >{{ $line->number }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div id="resp_container" class="form-group has-feedback">
                                                <div class="input-group" style="width: 100%">
                                                    <label for="resp_after_name" class="input-group-addon"
                                                           style="width: 23%;text-align: left">
                                                        Entregado a: <span class="pull-right">*</span>
                                                    </label>

                                                    <input required="required" type="text" class="form-control"
                                                           name="resp_after_name" id="resp_after_name"
                                                           value="{{ $assignation&&$assignation->resp_after ?
                                                                $assignation->resp_after->name : '' }}"
                                                           placeholder="Persona a la que se asigna la línea"
                                                           readonly="readonly">
                                                </div>

                                                <div class="input-group" style="width: 100%;text-align: center" id="result" align="center"></div>
                                            </div>

                                            <div class="input-group" style="width: 100%">
                                                <label for="service_area" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Área de servicio: <span class="pull-right">*</span>
                                                </label>

                                                <input required="required" type="text" class="form-control" name="service_area"
                                                       id="service_area"
                                                       value="{{ $assignation ? $assignation->service_area : old('service_area') }}"
                                                       placeholder="Oficina o ciudad a la que se destina la línea">
                                            </div>

                                            <div class="input-group" style="width: 100%">
                                                <label for="observations" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Observaciones:
                                                </label>

                                                <textarea rows="3" required="required" class="form-control" name="observations"
                                                          id="observations"
                                                          placeholder="Observaciones acerca de la asignación">{{ $assignation ?
                                                     $assignation->observations : old('observations') }}</textarea>
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

                                    {{--
                                    @if($assignation&&$user->priv_level==4)
                                        <button type="submit" form="delete" class="btn btn-danger">
                                            <i class="fa fa-trash-o"></i> Eliminar
                                        </button>
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

        function check_existence(){
            var resp_after_name = $('#resp_after_name').val();

            if(resp_after_name.length >0){
                $.post('/check_existence', { resp_name: resp_after_name }, function(data){
                    $("#result").html(data.message).show();
                    if(data.status==="warning"){
                        $('#resp_container').addClass("has-warning").removeClass("has-success");
                    }
                    else if(data.status==="success"){
                        $('#resp_container').addClass("has-success").removeClass("has-warning");
                    }
                });
            }
            else{
                $("#result").hide();
                $('#resp_container').removeClass("has-warning").removeClass("has-success");
            }
        }

        $(document).ready(function(){
            $("#wait").hide();
            $("#result").hide();
            $('#resp_after_name').focusout(check_existence);
            load_responsible();
        });

        $('#resp_after_name').autocomplete({
            type: 'post',
            serviceUrl:'/autocomplete/users',
            dataType: 'JSON',
            onSelect: check_existence
        });

        $('#corp_line_requirement_id').change(load_responsible);

        function load_responsible(){
            var id = $('#corp_line_requirement_id').val(), resp_after_name = $('#resp_after_name');

            $.post('/load_name/line_assignation_form', { query_id: id }, function(data){
                resp_after_name.val(data);
            });

            if(id===0){
                resp_after_name.removeAttr('readonly');
            }
            else{
                resp_after_name.attr('readonly', 'readonly');
            }
        }
    </script>
@endsection
