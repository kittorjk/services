<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 23/08/2017
 * Time: 11:42 AM
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
                    {{ $rbs_char ? 'Actualizar caracteríaticas de sitio' : 'Agregar características de sitio' }}
                </div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-undo"></i>
                    </a>
                    <a href="/site{{ $site ? '/'.$site->assignment_id : '' }}" class="btn btn-warning"
                       title="Volver a resumen de sitios">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                @if($rbs_char)
                    <form id="delete" action="/rbs_site_characteristics/{{ $rbs_char->id }}" method="post">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                    <form novalidate="novalidate" action="{{ '/rbs_site_characteristics/'.$rbs_char->id }}" method="post">
                        <input type="hidden" name="_method" value="put">
                        @else
                            <form novalidate="novalidate" action="{{ '/rbs_site_characteristics' }}" method="post">
                                @endif
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                                <div class="form-group">
                                    <div class="input-group">
                                <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                    <div class="input-group" style="width: 100%">
                                        <label for="site_id" class="input-group-addon" style="width: 23%;text-align: left">
                                            Sitio:
                                        </label>

                                        <select required="required" class="form-control" name="site_id" id="site_id">
                                            <option value="" hidden>Seleccione un sitio</option>
                                            <option value="{{ $site->id }}" selected="selected">{{ $site->name }}</option>
                                        </select>

                                        {{--
                                        <input required="required" type="text" class="form-control" name="site_name"
                                               id="site_name" value="{{ $rbs_char&&$rbs_char->site ?
                                                $rbs_char->site->name : $site->name }}"
                                               placeholder="Nombre de sitio" readonly="readonly">

                                        <input type="hidden" name="site_id" value="{{ $site->id }}">
                                        --}}
                                    </div>

                                    <div class="input-group" style="width: 100%">
                                        <label for="type_station" class="input-group-addon" style="width: 23%;text-align: left">
                                            Tipo estación:
                                        </label>

                                        <select required="required" class="form-control" name="type_station" id="type_station">
                                            <option value="" hidden>Seleccione el tipo de estación</option>

                                            <option value="Macro"
                                                {{ ($rbs_char&&$rbs_char->type_station=='Macro')||(old("type_station")=='Macro') ?
                                                 'selected="selected"' : '' }}>Macro</option>
                                            <option value="Micro"
                                                {{ ($rbs_char&&$rbs_char->type_station=='Micro')||(old('type_station')=='Micro') ?
                                                 'selected="selected"' : '' }}>Micro</option>
                                        </select>
                                    </div>

                                    <div class="input-group" style="width: 100%">
                                        <label for="solution" class="input-group-addon" style="width: 23%;text-align: left">
                                            Escenario:
                                        </label>

                                        <input required="required" type="text" class="form-control" name="solution"
                                               id="solution" value="{{ $rbs_char ? $rbs_char->solution : old('solution') }}"
                                               placeholder="Escenario #">
                                    </div>

                                    <div class="input-group" style="width: 100%">
                                        <label for="type_rbs" class="input-group-addon" style="width: 23%;text-align: left">
                                            Tipo RBS:
                                        </label>

                                        <select required="required" class="form-control" name="type_rbs" id="type_rbs">
                                            <option value="" hidden>Seleccione el tipo de radiobase</option>

                                            <option value="Roof top"
                                                {{ ($rbs_char&&$rbs_char->type_rbs=='Roof top')||(old('type_rbs')=='Roof top') ?
                                                    'selected="selected"' : '' }}>Roof top</option>
                                            <option value="Tower"
                                                {{ ($rbs_char&&$rbs_char->type_rbs=='Tower')||(old('type_rbs')=='Tower') ?
                                                    'selected="selected"' : '' }}>Tower</option>
                                        </select>
                                    </div>

                                    <div class="input-group" style="width: 75%" id="height_container">
                                        <span class="input-group-addon" style="width: 31%;text-align: left">Altura:</span>

                                        <input required="required" type="number" class="form-control" name="height"
                                               id="height" step="1" min="0"
                                               value="{{ $rbs_char ? $rbs_char->height : old('height') }}"
                                               placeholder="0" disabled="disabled">

                                        <span class="input-group-addon">metros</span>
                                    </div>

                                    <div class="input-group" style="width: 75%" id="number_floors_container">
                                        <span class="input-group-addon" style="width: 31%;text-align: left"># Pisos:</span>

                                        <input required="required" type="number" class="form-control" name="number_floors"
                                               id="number_floors" step="1" min="1"
                                               value="{{ $rbs_char ? $rbs_char->number_floors : old('number_floors') }}"
                                               placeholder="1" disabled="disabled">

                                        <span class="input-group-addon">pisos</span>
                                    </div>

                                    <div class="input-group" style="width: 100%">
                                        <label for="tech_group_id" class="input-group-addon" style="width: 23%;text-align: left">
                                            Grupo asignado:
                                        </label>

                                        <select required="required" class="form-control" name="tech_group_id" id="tech_group_id">
                                            <option value="" hidden>Seleccione el grupo de trabajo para este sitio</option>

                                            @foreach($tech_groups as $tech_group)
                                                <option value="{{ $tech_group->id }}"
                                                    {{ ($rbs_char&&$rbs_char->tech_group_id==$tech_group->id)||
                                                        (old('tech_group_id')==$tech_group->id) ? 'selected="selected"' : '' }}
                                                >{{ 'Grupo '.$tech_group->group_number.
                                                ($tech_group->group_head ?
                                                 ' - Jefe de grupo: '.$tech_group->group_head->name : '') }}</option>
                                            @endforeach
                                        </select>
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

                                    @if($rbs_char&&$user->priv_level==4)
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

        function enable_altitude_fields(){

            var type_rbs = $('#type_rbs'), height_container = $('#height_container'), height = $('#height'),
                    number_floors_container = $('#number_floors_container'), number_floors = $('#number_floors');

            if (type_rbs.val()==='Roof top') {
                number_floors.removeAttr('disabled').show();
                number_floors_container.show();
                height.attr('disabled', 'disabled').val('').hide();
                height_container.hide();
            }
            else if(type_rbs.val()==='Tower'){
                number_floors.attr('disabled', 'disabled').val('').hide();
                number_floors_container.hide();
                height.removeAttr('disabled').show();
                height_container.show();
            }
            else {
                number_floors.attr('disabled', 'disabled').val('').hide();
                number_floors_container.hide();
                height.attr('disabled', 'disabled').val('').hide();
                height_container.hide();
            }
        }

        $(document).ready(function(){
            $("#wait").hide();
            $("#height_container").hide();
            $('#number_floors_container').hide();
            enable_altitude_fields();
        });

        $('#type_rbs').change(enable_altitude_fields);

    </script>
@endsection
