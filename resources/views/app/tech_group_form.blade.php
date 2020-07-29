<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 22/08/2017
 * Time: 03:08 PM
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
                <div class="panel-title">{{ $group ? 'Modificar información de grupo' : 'Crear grupo de trabajo' }}</div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                    <a href="{{ '/tech_group' }}" class="btn btn-warning" title="Volver a resumen de grupos de trabajo">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                @if($group)
                    <form id="delete" action="/tech_group/{{ $group->id }}" method="post">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                    <form novalidate="novalidate" action="{{ '/tech_group/'.$group->id }}" method="post" class="form-horizontal">
                        <input type="hidden" name="_method" value="put">
                        @else
                            <form novalidate="novalidate" action="{{ '/tech_group' }}" method="post" class="form-horizontal">
                                @endif
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                                <div class="row">
                                    <div class="col-md-12 col-sm-12">

                                        <div class="form-group{{ $errors->has('group_area') ? ' has-error' : '' }}">
                                            <label for="group_area" class="col-md-4 control-label">
                                                Área
                                            </label>

                                            <div class="col-md-6" id="group_area">
                                                <input id="group_area_fo" type="radio" name="group_area"
                                                       value="fo" onclick="retrieve_group_number()"
                                                        {{ ($group&&$group->group_area=='fo')||
                                                        old('group_area')=='fo' ? 'checked="checked"' : '' }}>
                                                <label for="group_area_fo" class="control-label">Fibra óptica</label>
                                                &emsp;
                                                <input id="group_area_rbs" type="radio" name="group_area"
                                                       value="rbs" onclick="retrieve_group_number()"
                                                        {{ ($group&&$group->group_area=='rbs')||
                                                        old('group_area')=='rbs' ? 'checked="checked"' : '' }}>
                                                <label for="group_area_rbs" class="control-label">Radiobases</label>


                                                @if($errors->has('group_area'))
                                                    <span class="help-block">
                                                        <strong>{{ $errors->first('group_area') }}</strong>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="form-group{{ $errors->has('group_number') ? ' has-error' : '' }}">
                                            <label for="group_number" class="col-md-4 control-label">
                                                Grupo #
                                            </label>

                                            <div class="col-md-6">
                                                <input id="group_number" type="text" class="form-control dynamic"
                                                       readonly="readonly" name="group_number" placeholder="Número de grupo"
                                                       value="{{ $group ? $group->group_number : old('group_number') }}"
                                                       required>

                                                @if ($errors->has('group_number'))
                                                    <span class="help-block">
                                                        <strong>{{ $errors->first('group_number') }}</strong>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <fieldset>
                                    <legend class="col-md-10">Técnicos</legend>

                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">

                                            <div class="form-group{{ $errors->has('group_head_name') ? ' has-error' : '' }}">
                                                <label for="group_head_name" class="col-md-4 control-label">
                                                    (*) Jefe de grupo
                                                </label>

                                                <div class="col-md-6">
                                                    <input id="group_head_name" type="text" class="form-control"
                                                           name="group_head_name"
                                                           value="{{ $group&&$group->group_head ? $group->group_head->name :
                                                                (old('group_head_name') ? old('group_head_name') : '') }}"
                                                           placeholder="Nombre" required
                                                           onkeydown="autocomplete_tech(this)">

                                                    @if ($errors->has('group_head_name'))
                                                        <span class="help-block">
                                                            <strong>{{ $errors->first('group_head_name') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            @for($i=2;$i<=5;$i++)
                                                <div class="form-group{{ $errors->has('tech_'.$i.'_name') ? ' has-error' : '' }}">
                                                    <label for="{{ 'tech_'.$i.'_name' }}" class="col-md-4 control-label">
                                                        {{ 'Integrante '.($i) }}
                                                    </label>

                                                    <div class="col-md-6">
                                                        <input id="{{ 'tech_'.$i.'_name' }}" type="text"
                                                               class="form-control complete_tech dynamic"
                                                               name="{{ 'tech_'.$i.'_name' }}" placeholder="Nombre"
                                                               value="{{ $group&&$group->{'tech_'.$i} ?
                                                                $group->{'tech_'.$i}->name : old('tech_'.$i.'_name') }}"
                                                               required onkeydown="autocomplete_tech(this)">

                                                        @if ($errors->has('tech_'.$i.'_name'))
                                                            <span class="help-block">
                                                                <strong>{{ $errors->first('tech_'.$i.'_name') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endfor

                                        </div>
                                    </div>

                                </fieldset>

                                <fieldset>
                                    <legend class="col-md-10">Información adicional</legend>

                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">

                                            <div class="form-group">
                                                <label for="observations" class="col-md-4 control-label">
                                                    Observaciones
                                                </label>

                                                <div class="col-md-6">
                                                    <textarea rows="3" class="form-control" id="observations"
                                                              name="observations">{{ $group ? $group->observations :
                                                            old('observations') }}</textarea>
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                </fieldset>

                                @include('app.loader_gif')

                                <div class="form-group" align="center">
                                    <button type="submit" class="btn btn-success"
                                            onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                                        <i class="fa fa-save"></i> Guardar
                                    </button>
                                    @if($group&&$user->priv_level==4)
                                        <button type="submit" form="delete" class="btn btn-danger">
                                            <i class="fa fa-archive"></i> Archivar
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

        function retrieve(){
            /*
            var type_viatic = $("#type_viatic"), type_stipend = $("#type_stipend");

            if(type_viatic.is(':checked')){
                $(".amount").val(type_viatic.data('amount'));
            }
            else if(type_stipend.is(':checked')){
                $(".amount").val(type_stipend.data('amount'));
            }
            */
        }

        function retrieve_group_number(){
            var group_number = $("#group_number");

            if($("#group_area_fo").is(':checked')){
                $.post('/retrieve/group_number', { hint: 'area', option: 'fo' }, function(data){
                    group_number.val(data.value);
                });
            }
            else if($("#group_area_rbs").is(':checked')){
                $.post('/retrieve/group_number', { hint:'area', option: 'rbs' }, function(data){
                    group_number.val(data.value);
                });
            }
            else{
                group_number.val(0);
            }
        }

        $(document).ready(function(){
            $("#wait").hide();
        });

        function autocomplete_tech(e){
            $(e).autocomplete({
                type: 'post',
                serviceUrl:'/autocomplete/technicians',
                dataType: 'JSON'
            });
        }
    </script>
@endsection
