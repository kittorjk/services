@extends('layouts.master')

@section('header')
    @parent
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">

        <div class="panel panel-sky" >
            <div class="panel-heading" align="center">
                <div class="panel-title">{{ $order ? 'Modificar datos de orden' : 'Agregar nueva orden' }}</div>
            </div>
            <div class="panel-body" >
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                    <a href="{{ '/order' }}" class="btn btn-warning" title="Volver a lista de ordenes de compra">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <p><em>Nota.- Los campos con * son obligatorios</em></p>

                @if($order)
                    <form id="delete" action="/order/{{ $order->id }}" method="post">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                    <form novalidate="novalidate" action="{{ '/order/'.$order->id }}" method="post">
                        <input type="hidden" name="_method" value="put">
                        @else
                            <form novalidate="novalidate" action="{{ '/order' }}" method="post">
                                @endif
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="form-group">
                                    <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                    <div class="input-group" style="width: 100%">
                                        <label for="code" class="input-group-addon" style="width: 23%;text-align: left">
                                            Código <span class="pull-right">*</span>
                                        </label>

                                        <input required="required" type="text" class="form-control" name="code" id="code"
                                               value="{{ $order ? $order->code : old('code') }}" placeholder="Código de orden">
                                    </div>

                                    <div class="input-group" style="width: 100%">
                                        <label for="client" class="input-group-addon" style="width: 23%;text-align: left">
                                            Cliente <span class="pull-right">*</span>
                                        </label>

                                        <select required="required" class="form-control" name="client" id="client">
                                            <option value="" hidden>Seleccione el cliente y tipo de orden</option>
                                                @foreach($clients as $client)
                                                    <option value="{{ $client->client.'-'.$client->type }}"
                                                            {{ ((($order&&$order->client==$client->client)||
                                                                old('client')==$client->client)&&
                                                                $order->type==$client->type) ? 'selected="selected"' :
                                                                '' }}>{{ $client->client.' - '.$client->type }}</option>
                                                @endforeach
                                                <option value="Otro">Otro</option>
                                        </select>
                                    </div>
                                    <input required="required" type="text" class="form-control" name="other_client" id="other_client"
                                           value="{{ $order ? $order->client : '' }}" placeholder="Cliente" disabled="disabled">
                                    <input required="required" type="text" class="form-control" name="other_type" id="other_type"
                                           value="{{ $order ? $order->type : '' }}" placeholder="Tipo de orden (Ej. PC, PO, etc.)"
                                           disabled="disabled">

                                    {{--<!--
                                    <select required="required" class="form-control" name="type">
                                        <option value="" hidden>Seleccione el tipo de orden</option>
                                        <option value="OC" @if($order&&$order->type=='OC'){{ 'selected="selected"' }}@endif>
                                            OC</option>
                                        <option value="PC" @if($order&&$order->type=='PC'){{ 'selected="selected"' }}@endif>
                                            PC</option>
                                        <option value="PO" @if($order&&$order->type=='PO'){{ 'selected="selected"' }}@endif>
                                            PO</option>
                                        <option value="Contrato" @if($order&&$order->type=='Contrato')
                                            {{ 'selected="selected"' }}@endif>
                                        Contrato</option>
                                    </select>
                                    -->--}}

                                    <div class="input-group" style="width: 100%">
                                        <label for="payment_percentage" class="input-group-addon" style="width: 23%;text-align: left">
                                            Porcentajes <span class="pull-right">*</span>
                                        </label>

                                        <select required="required" class="form-control" name="payment_percentage"
                                                id="payment_percentage">
                                            <option value="" hidden>Seleccione los porcentajes de cobro</option>
                                            @foreach($payment_percentages as $payment_percentage)
                                                <option value="{{ $payment_percentage->payment_percentage }}"
                                                    {{ ($order&&$order->payment_percentage==$payment_percentage->payment_percentage)||
                                                        old('payment_percentage')==$payment_percentage->payment_percentage ?
                                                        'selected="selected"' : '' }}
                                                >{{ str_replace('-', '% - ',$payment_percentage->payment_percentage).'%' }}</option>
                                            @endforeach
                                            <option value="Otro">Otro</option>
                                        </select>
                                    </div>
                                    <input required="required" type="text" class="form-control" name="other_payment_percentage"
                                           id="other_payment_percentage" value="{{ $order ? $order->payment_percentage : '' }}"
                                           placeholder="Porcentajes de pago (xx-xx-xx)" disabled="disabled">

                                    <div class="input-group" style="width: 75%">
                                        <label for="number_of_sites" class="input-group-addon" style="width: 31%;text-align: left">
                                            Num de sitios
                                        </label>

                                        <input required="required" type="number" class="form-control" name="number_of_sites"
                                               id="number_of_sites" step="any" min="0"
                                               value="{{ $order ? $order->number_of_sites : old('number_of_sites') }}"
                                               placeholder="Número de sitios en la Orden">
                                    </div>

                                    @if($order)
                                        <div class="input-group" style="width: 100%">
                                            <label for="status" class="input-group-addon" style="width: 23%;text-align: left">
                                                Estado
                                            </label>

                                            <select required="required" class="form-control" name="status" id="status">
                                                <option value="" hidden>Seleccione un estado</option>
                                                <option value="Pendiente"
                                                        {{ ($order&&$order->status=='Pendiente')||old('status')=='Pendiente' ?
                                                            'selected="selected"' : '' }}>Pendiente</option>
                                                <option value="Cobrado"
                                                        {{ ($order&&$order->status=='Cobrado')||old('status')=='Cobrado' ?
                                                            'selected="selected"' : '' }}>Cobrado</option>
                                                <option value="Anulado"
                                                        {{ ($order&&$order->status=='Anulado')||old('status')=='Anulado' ?
                                                            'selected="selected"' : '' }}>Anulado</option>
                                            </select>
                                        </div>
                                    @endif

                                    <div class="input-group">
                                        <label for="date_issued" class="input-group-addon">
                                            Fecha de emisión *
                                        </label>
                                        <span class="input-group-addon" style="width: 26%;text-align: left">
                                            <input type="date" name="date_issued" id="date_issued" step="1"
                                                   min="2014-01-01" max="{{ date("Y-m-d") }}"
                                                   value="{{ $order ? $order->date_issued : old('date_issued') }}">
                                        </span>
                                    </div>

                                    <div class="input-group" style="width: 75%">
                                        <span class="input-group-addon" style="width: 31%;text-align: left">
                                            Monto asignado <span class="pull-right">*</span>
                                        </span>
                                        <input required="required" type="number" class="form-control" name="assigned_price"
                                               step="any" min="0" value="{{ $order ? $order->assigned_price :
                                                    old('assigned_price') }}"
                                               placeholder="Monto asignado (sin impuestos)">

                                        {{--<span class="input-group-addon">Bs.</span>--}}
                                        <div class="input-group-btn" style="width:70px;">
                                            <label for="currency"></label>
                                            <select required="required" class="form-control" name="currency" id="currency">
                                                <option value="Bs"
                                                        {{ $order||old('currency')=='Bs' ? 'selected="selected"' : '' }}>Bs</option>
                                                <option value="$us"
                                                        {{ old('currency')=='$us' ? 'selected="selected"' : '' }}>$us</option>
                                            </select>
                                        </div>
                                    </div>

                                    @if($user->priv_level==4)
                                        <div class="input-group" style="width: 75%">
                                            <span class="input-group-addon" style="width: 31%;text-align: left">Monto cobrado</span>
                                            <input required="required" type="number" class="form-control" name="charged_price"
                                                   step="any" min="0" value="{{ $order ? $order->charged_price :
                                                        old('charged_price') }}"
                                                   placeholder="Monto cobrado (sin impuestos)">

                                            {{--<span class="input-group-addon">Bs.</span>--}}
                                            <div class="input-group-btn" style="width:70px;">
                                                <label for="currency_charged"></label>
                                                <select required="required" class="form-control" name="currency_charged"
                                                        id="currency_charged">
                                                    <option value="Bs"
                                                            {{ $order||old('currency_charged')=='Bs' ?
                                                                'selected="selected"' : '' }}>Bs</option>
                                                    <option value="$us"
                                                            {{ old('currency_charged')=='$us' ?
                                                                'selected="selected"' : '' }}>$us</option>
                                                </select>
                                            </div>
                                        </div>
                                    @endif

                                    <textarea rows="3" required="required" class="form-control" name="detail"
                                              placeholder="Información adicional de orden">{{ $order ?
                                               $order->detail : old('detail') }}</textarea>

                                </span>
                                    </div>
                                </div>

                                @include('app.loader_gif')

                                <div class="form-group" align="center">
                                    <button type="submit" class="btn btn-success"
                                            onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                                        <i class="fa fa-floppy-o"></i> Guardar
                                    </button>

                                    @if($order&&$user->priv_level==4)
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
        var $payment_percentage = $('#payment_percentage'), $other_payment_percentage = $('#other_payment_percentage');
        $payment_percentage.change(function () {
            if ($payment_percentage.val()==='Otro') {
                $other_payment_percentage.removeAttr('disabled').show();
            } else {
                $other_payment_percentage.attr('disabled', 'disabled').val('').hide();
            }
        }).trigger('change');

        var $client = $('#client'), $other_client = $('#other_client'), $other_type = $('#other_type');
        $client.change(function () {
            if ($client.val()==='Otro') {
                $other_client.removeAttr('disabled').show();
                $other_type.removeAttr('disabled').show();
            } else {
                $other_client.attr('disabled', 'disabled').val('').hide();
                $other_type.attr('disabled', 'disabled').val('').hide();
            }
        }).trigger('change');

        $(document).ready(function(){
            $("#wait").hide();
        });
    </script>
@endsection
