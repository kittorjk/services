<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 20/02/2017
 * Time: 04:13 PM
 */
?>

@extends('layouts.master')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">
        <div class="panel panel-ground">
            <div class="panel-heading" align="center">
                <div class="panel-title">{{ $guarantee ? 'Modificar poliza' : 'Agregar nueva poliza' }}</div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                    <a href="{{ '/guarantee' }}" class="btn btn-warning" title="Volver a lista de CITEs">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                @if($guarantee)
                    <form id="delete" action="/guarantee/{{ $guarantee->id }}" method="post">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                    <form novalidate="novalidate" action="{{ '/guarantee/'.$guarantee->id }}" method="post">
                        <input type="hidden" name="_method" value="put">
                        @else
                            <form novalidate="novalidate" action="{{ '/guarantee' }}" method="post">
                                @endif
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="form-group">
                                    <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                        <div class="input-group" style="width: 100%">
                                            <label for="code" class="input-group-addon" style="width: 23%;text-align: left">
                                                Código:
                                            </label>

                                            <input required="required" type="text" class="form-control" name="code" id="code"
                                                   value="{{ $guarantee ? $guarantee->code : '' }}" placeholder="Número de poliza">
                                        </div>

                                        <div class="input-group" style="width: 100%">
                                            <label for="company" class="input-group-addon" style="width: 23%;text-align: left">
                                                Emisor:
                                            </label>

                                            <select required="required" class="form-control" name="company" id="company">
                                                <option value="" hidden>Seleccione la empresa que extiende la poliza</option>
                                                @foreach($companies as $company)
                                                    <option value="{{ $company->company }}"
                                                            {{ ($guarantee&&$guarantee->company==$company->company)||
                                                                old('company')==$company->company ? 'selected="selected"' :
                                                                 '' }}>{{ $company->company }}</option>
                                                @endforeach
                                                <option value="Otro">Otro</option>
                                            </select>
                                        </div>
                                        <input required="required" type="text" class="form-control" name="other_company"
                                               id="other_company" placeholder="Empresa" disabled="disabled">

                                        <div class="input-group" style="width: 100%">
                                            <label for="type" class="input-group-addon" style="width: 23%;text-align: left">
                                                Tipo:
                                            </label>

                                            <select required="required" class="form-control" name="type" id="type"
                                                    onchange="dynamic_select(this)">
                                                <option value="" hidden>Seleccione el tipo de poliza</option>
                                                <option value="Garantía (proyectos)"
                                                        {{ ($guarantee&&$guarantee->type=='Garantía (proyectos)')||
                                                            old('type')=='Garantía (proyectos)' ? 'selected="selected"' :
                                                             '' }}>Poliza de garantía (proyectos)</option>
                                                <option value="Garantía (asignaciones)"
                                                        {{ ($guarantee&&$guarantee->type=='Garantía (asignaciones)')||
                                                            old('type')=='Garantía (asignaciones)' ? 'selected="selected"' :
                                                             '' }}>Poliza de garantía (asignaciones)</option>
                                                <option value="Automotores"
                                                        {{ ($guarantee&&$guarantee->type=='Automotores')||
                                                            old('type')=='Automotores' ? 'selected="selected"' :
                                                             '' }}>Poliza de automotores</option>
                                                <option value="Administrativa"
                                                        {{ ($guarantee&&$guarantee->type=='Administrativa')||
                                                            old('type')=='Administrativa' ? 'selected="selected"' :
                                                             '' }}>Poliza administrativa (contratos)</option>
                                                <option value="Otro"
                                                        {{ ($guarantee&&$guarantee->type=='Otro')||
                                                            old('type')=='Otro' ? 'selected="selected"' :
                                                             '' }}>Otro</option>
                                            </select>
                                        </div>

                                        <div class="input-group" style="width: 100%" id="morph_container">
                                            <label for="to_morph_id" class="input-group-addon" style="width: 23%;text-align: left">
                                                Aplica a:
                                            </label>

                                            <select required="required" class="form-control" name="to_morph_id" id="to_morph_id"
                                                    data-id="{{ $guarantee ? $guarantee->guaranteeable_id : 0 }}">
                                            </select>
                                        </div>

                                        <div class="input-group" style="width: 100%" id="applied_to_container">
                                            <label for="applied_to" class="input-group-addon" style="width: 23%;text-align: left">
                                                Detalle:
                                            </label>

                                            <textarea rows="4" required="required" class="form-control"
                                                name="applied_to" id="applied_to" placeholder="Objeto de la poliza"
                                                disabled="disabled">{{ $guarantee ? $guarantee->applied_to : '' }}</textarea>
                                        </div>

                                        {{--
                                        <select required="required" class="form-control" name="assignment_id" disabled="disabled">
                                            <option value="" hidden>Seleccione un proyecto</option>
                                            @foreach($assignments as $assignment)
                                                <option value="{{ $assignment->id }}"
                                                    {{ $guarantee&&$guarantee->assignment_id==$assignment->id ?
                                                        'selected="selected"' : '' }}
                                                >{{ str_limit($assignment->name,100) }}</option>
                                            @endforeach
                                        </select>
                                        --}}

                                        <div class="input-group" style="width: 100%;text-align: center">
                                            <span class="input-group-addon">
                                                <label for="start_date">Fecha de inicio: </label>

                                                <input type="date" name="start_date" id="start_date" step="1" min="2014-01-01"
                                                       value="{{ $guarantee ? $guarantee->start_date : date('Y-m-d') }}">
                                            </span>

                                            <span class="input-group-addon">
                                                <label for="expiration_date">Fecha de vencimiento:</label>

                                                <input type="date" name="expiration_date" id="expiration_date" step="1"
                                                       min="2014-01-01"
                                                       value="{{ $guarantee ? $guarantee->expiration_date : '' }}">
                                            </span>
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

                                    @if($guarantee&&$user->priv_level==4)
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
        var $company = $('#company'), $other_company = $('#other_company');
        $company.change(function () {
            if ($company.val()==='Otro') {
                $other_company.removeAttr('disabled').show();
            } else {
                $other_company.attr('disabled', 'disabled').val('').hide();
            }
        }).trigger('change');

        $(document).ready(function(){
            $("#wait").hide();
            $("#to_morph_id").hide();
            $("#applied_to_container").hide();
            dynamic_select($("#type"));
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function dynamic_select(c){
            if($(c).val()==='Garantía (proyectos)'||$(c).val()==='Garantía (asignaciones)'){
                $.post('/dynamic_guaranteeable', { type: $(c).val(), selected_id: $("#to_morph_id").data('id') }, function(data){
                    $('#morph_container').show();
                    $("#to_morph_id").html(data).show();
                    $("#applied_to_container").hide();
                    $("#applied_to").attr('disabled', 'disabled').hide();
                });
            }
            else{
                $("#applied_to_container").show();
                $("#applied_to").removeAttr('disabled').show();
                $("#to_morph_id").hide();
                $('#morph_container').hide();
            }
        }
    </script>
@endsection
