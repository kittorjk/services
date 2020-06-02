<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 21/03/2017
 * Time: 03:40 PM
 */
?>

@extends('layouts.master')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

        <div class="panel panel-orange" >
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    {{ $certificate ? 'Modificar certificado' : 'Agregar certificado de aceptación' }}
                </div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                    <a href="{{ '/oc' }}" class="btn btn-warning" title="Volver a la lista de OCs">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                @if($certificate)
                    <form id="delete" action="/oc_certificate/{{ $certificate->id }}" method="post">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                    <form novalidate="novalidate" action="{{ '/oc_certificate/'.$certificate->id }}" method="post">
                        <input type="hidden" name="_method" value="put">
                            @else
                                <form novalidate="novalidate" action="{{ '/oc_certificate' }}" method="post">
                                    @endif
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <div class="form-group">
                                        <div class="input-group">

                                            <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                                <div class="input-group" style="width: 62%">
                                                    <label for="oc_id" class="input-group-addon" style="width: 37%;text-align: right">
                                                        OC-
                                                    </label>

                                                    <input required="required" type="number" class="form-control" name="oc_id"
                                                           id="oc_id" step="1" min="1" placeholder="00000"
                                                           value="{{ $certificate ? $certificate->oc_id :
                                                            ($preselected_id ?: old('oc_id')) }}"
                                                           {{ $preselected_id ? 'readonly="readonly"' : '' }}>
                                                </div>

                                                <div class="input-group" style="width: 62%">
                                                    <label for="amount" class="input-group-addon" style="width: 37%;text-align: left">
                                                        Monto certificado
                                                    </label>

                                                    <input required="required" type="number" class="form-control" name="amount"
                                                           id="amount" step="any" min="0" placeholder="0.00"
                                                           value="{{ $certificate ? $certificate->amount : old('amount') }}">

                                                    <span class="input-group-addon">Bs</span>
                                                </div>

                                                <div class="input-group" style="width: 100%" id="oc_values" align="center"></div>

                                                <div class="input-group" style="width: 100%">
                                                    <label for="type_reception" class="input-group-addon"
                                                           style="width: 23%;text-align: left">
                                                        Tipo aceptación
                                                    </label>

                                                    <select required="required" class="form-control" name="type_reception"
                                                        id="type_reception">
                                                        <option value="" hidden>Seleccione el tipo de aceptación</option>
                                                        <option value="Adelanto"
                                                                {{ ($certificate && $certificate->type_reception == 'Adelanto') ||
                                                                    old('type_reception') == 'Adelanto' ?
                                                                    'selected="selected"' : '' }}>Adelanto</option>
                                                        <option value="Parcial"
                                                                {{ ($certificate && $certificate->type_reception == 'Parcial') ||
                                                                    old('type_reception') == 'Parcial' ?
                                                                    'selected="selected"' : '' }}>Aceptación parcial</option>
                                                        <option value="Total"
                                                                {{ ($certificate && $certificate->type_reception == 'Total') ||
                                                                    old('type_reception') == 'Total' ?
                                                                    'selected="selected"' : '' }}>Aceptación total</option>
                                                    </select>
                                                </div>

                                                <div class="input-group" style="width: 100%;">
                                                    <label for="date_ack" class="input-group-addon"
                                                           style="font-weight: normal; margin-bottom: 0; width: 23%; text-align:right">
                                                        Fecha de entrega:
                                                    </label>

                                                    <div class="input-group-addon">
                                                        <input type="date" name="date_ack" id="date_ack" step="1" min="2014-01-01"
                                                           value="{{ $certificate ? $certificate->date_ack : old('date_ack') }}">
                                                    </div>
                                                </div>

                                                <div class="input-group" style="width: 100%;">
                                                    <label for="date_acceptance" class="input-group-addon"
                                                            style="font-weight: normal; margin-bottom: 0; width: 23%; text-align:right">
                                                        Fecha de aceptación:
                                                    </label>

                                                    <div class="input-group-addon">
                                                        <input type="date" name="date_acceptance" id="date_acceptance"
                                                               step="1" min="2014-01-01"
                                                               value="{{ $certificate ? $certificate->date_acceptance :
                                                               (old('date_acceptance') ?: date('Y-m-d')) }}">
                                                    </div>
                                                </div>

                                                <textarea rows="3" required="required" class="form-control" name="observations"
                                                          id="observations"
                                                          placeholder="Observaciones de la aceptación del bien/servicio">{{
                                                          $certificate ? $certificate->observations : old('observations') }}</textarea>

                                            </div>

                                        </div>
                                    </div>

                                    @include('app.loader_gif')

                                    <div class="form-group" align="center">
                                        <button type="submit" class="btn btn-success"
                                                onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                                            <i class="fa fa-floppy-o"></i> Guardar
                                        </button>

                                        @if($certificate&&$user->action->oc_ctf_del /*$user->priv_level==4*/)
                                            <button type="submit" form="delete" class="btn btn-danger">
                                                <i class="fa fa-trash-o"></i> Quitar
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

        function show_oc_values() {
            $.post('/load_oc_amount_values', { oc_id: $("#oc_id").val(), amount: $("#amount").val(), concept: $("#type_reception").val() },
            function(data) {
                $("#oc_values").html(data).show();
            });
        }

        $(document).ready(function(){
            $("#wait").hide();
            $("#oc_values").hide();

            $("#amount").keyup(function () {
                show_oc_values();
            });

            $("#type_reception").change(function () {
                show_oc_values();
            }).trigger('change');
        });
    </script>
@endsection
