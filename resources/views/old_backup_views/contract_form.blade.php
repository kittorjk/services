<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 16/02/2017
 * Time: 03:53 PM
 */
?>

@extends('layouts.master')

@section('header')
    @parent
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">
        <div class="panel panel-10gray">
            <div class="panel-heading" align="center">
                <div class="panel-title">{{ $contract ? 'Modificar información de contrato' : 'Insertar nuevo contrato' }}</div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                    <a href="{{ '/contract' }}" class="btn btn-warning" title="Volver a la tabla de contratos">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                @if($contract)
                    <form id="delete" action="/contract/{{ $contract->id }}" method="post">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                    <form novalidate="novalidate" action="{{ '/contract/'.$contract->id }}" method="post">
                        <input type="hidden" name="_method" value="put">
                        @else
                            <form novalidate="novalidate" action="{{ '/contract' }}" method="post">
                                @endif
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="form-group">
                                    <div class="input-group">

                                        <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                            <div class="input-group" style="width: 100%">
                                                <label for="client_code" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Código:
                                                </label>

                                                <input required="required" type="text" class="form-control" name="client_code"
                                                       id="client_code"
                                                       value="{{ $contract ? $contract->client_code : '' }}"
                                                       placeholder="Código de contrato de cliente">
                                            </div>

                                            <div class="input-group" style="width: 100%">
                                                <label for="client" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Cliente:
                                                </label>

                                                <select required="required" class="form-control" name="client" id="client">
                                                    <option value="" hidden>Seleccione un cliente</option>
                                                    @foreach($clients as $client)
                                                        <option value="{{ $client->client }}"
                                                                {{ $contract&&$contract->client==$client->client ?
                                                                 'selected="selected"' : '' }}>{{ $client->client }}</option>
                                                    @endforeach
                                                    <option value="Otro">Otro</option>
                                                </select>
                                            </div>
                                            <input required="required" type="text" class="form-control" name="other_client"
                                                   id="other_client" placeholder="Cliente" disabled="disabled">

                                            <textarea rows="3" required="required" class="form-control" name="objective"
                                                placeholder="Objeto del contrato">{{ $contract ?
                                                 $contract->objective : '' }}</textarea>

                                            <div class="input-group" style="width: 100%;text-align: center">
                                                <span class="input-group-addon">
                                                    <label for="start_date">Fecha de inicio: </label>
                                                    <input type="date" name="start_date" id="start_date" step="1" min="2014-01-01"
                                                           value="{{ $contract ? $contract->start_date : date('Y-m-d') }}">
                                                </span>
                                                <span class="input-group-addon">
                                                    <label for="expiration_date">Fecha de vencimiento: </label>
                                                    <input type="date" name="expiration_date" id="expiration_date"
                                                           step="1" min="2014-01-01"
                                                           value="{{ $contract ? $contract->expiration_date : '' }}">
                                                </span>
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

                                    @if($contract&&$user->priv_level==4)
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
        var $client = $('#client'), $other_client = $('#other_client');
        $client.change(function () {
            if ($client.val()==='Otro') {
                $other_client.removeAttr('disabled').show();
            } else {
                $other_client.attr('disabled', 'disabled').val('').hide();
            }
        }).trigger('change');

        $(document).ready(function(){
            $("#wait").hide();
        });
    </script>
@endsection
