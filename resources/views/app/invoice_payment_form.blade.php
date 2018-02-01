<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 16/06/2017
 * Time: 11:35 AM
 */
?>

@extends('layouts.master')

@section('header')
    @parent
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

        <div class="panel panel-ground">
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    {{ 'Agregar o modificar información de pago - Factura '.$invoice->number }}
                </div>
            </div>
            <div class="panel-body">

                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-undo"></i>
                    </a>
                    <a href="{{ '/invoice' }}" class="btn btn-warning" title="Ir a la tabla de facturas de proveedor">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <form novalidate="novalidate" action="{{ '/invoice/payment/'.$invoice->id }}" method="post">
                    <input type="hidden" name="_method" value="put">

                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="form-group">
                        <div class="input-group">

                            <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                <div class="input-group" style="width: 100%">
                                    <label for="oc_id" class="input-group-addon" style="width: 23%;text-align: left">
                                        OC:
                                    </label>

                                    <input type="text" class="form-control" readonly="readonly" disabled="disabled"
                                           name="oc_id" id="oc_id" value="{{ $invoice->oc->code
                                           /*'OC-'.str_pad($invoice->oc->id, 5, "0", STR_PAD_LEFT)*/ }}">
                                </div>

                                <div class="input-group" style="width: 75%">
                                    <label for="amount" class="input-group-addon" style="width:31%;text-align: left">
                                        Monto facturado:
                                    </label>

                                    <input type="number" class="form-control" name="amount" id="amount" readonly="readonly"
                                           value="{{ $invoice->amount }}" disabled="disabled">
                                    <span class="input-group-addon">Bs.</span>
                                </div>

                                <div class="input-group" style="width:50%;">
                                    <label for="date_issued" class="input-group-addon" style="font-weight: normal; margin-bottom: 0;
                                        width: 23%/*138px*/;text-align: left">
                                        Fecha de emisión:
                                    </label>

                                    <span class="input-group-addon">
                                        <input type="date" name="date_issued" id="date_issued" step="1" min="2014-01-01"
                                               value="{{ $invoice->date_issued }}" readonly="readonly" disabled="disabled">
                                    </span>
                                </div>

                                {{--@if(($user->area=='Gerencia Administrativa'&&$user->priv_level>=2)||$user->priv_level==4)--}}
                                <div class="input-group" style="width: 100%">
                                    <label for="transaction_code" class="input-group-addon" style="width: 23%;text-align: left">
                                        Comprobante: <span class="pull-right">*</span>
                                    </label>

                                    <input required="required" type="text" class="form-control" name="transaction_code"
                                           id="transaction_code"
                                           value="{{ $invoice->transaction_code ? $invoice->transaction_code :
                                                old('transaction_code') }}"
                                           placeholder="Código de transacción o comprobante de pago"
                                            {{-- $invoice&&$invoice->flags[0]==1&&$user->priv_level<4 ? 'disabled' : '' --}}>
                                </div>

                                <div class="input-group" style="width:50%;">
                                    <label for="transaction_date" class="input-group-addon"
                                           style="font-weight: normal; margin-bottom: 0; width: 23%/*138px*/;text-align: left">
                                        Fecha de pago:
                                    </label>

                                    <span class="input-group-addon">
                                        <input type="date" name="transaction_date" id="transaction_date" step="1"
                                               min="{{ $invoice ? $invoice->date_issued : '2014-01-01' }}"
                                               max="{{ date("Y-m-d") }}"
                                               value="{{ $invoice->transaction_date ? $invoice->transaction_date :
                                                    old('transaction_date') }}"
                                                {{-- $invoice&&$invoice->flags[0]==1&&$user->priv_level<4 ? 'disabled' : '' --}}>
                                    </span>
                                </div>

                                <textarea rows="3" required="required" class="form-control" name="detail" id="detail"
                                          placeholder="Información adicional">{{ old('detail') }}</textarea>

                            </span>

                        </div>
                    </div>

                    @include('app.loader_gif')

                    <div class="form-group" align="center">
                        <button type="submit" class="btn btn-success"
                                onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                            <i class="fa fa-floppy-o"></i> Actualizar
                        </button>
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
        $(document).ready(function(){
            $("#wait").hide();
        });
    </script>
@endsection
