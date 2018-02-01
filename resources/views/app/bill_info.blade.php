@extends('layouts.info_master')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

        <div class="panel panel-sky">
            <div class="panel-heading" align="center">
                <div class="panel-title">Información de factura</div>
            </div>
            <div class="panel-body">
                <div class="col-lg-5 mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning">
                        <i class="fa fa-arrow-circle-left"></i> Atrás
                    </a>
                    <a href="{{ '/bill' }}" class="btn btn-warning" title="Ir a la tabla de facturas">
                        <i class="fa fa-arrow-circle-up"></i> Facturas
                    </a>
                </div>

                <div class="col-sm-12 mg10">
                    @include('app.session_flashed_messages', array('opt' => 0))
                </div>

                <div class="col-sm-12 mg10 mg-tp-px-10">
                    <table class="table table-striped table-hover table-bordered">
                        <tbody>
                        <tr>
                            <th width="40%">Número de factura:</th>
                            <td colspan="3">{{ $bill->code }}</td>
                        </tr>
                        <tr>
                            <th>Fecha de emisión:</th>
                            <td colspan="3">{{ date_format(new \DateTime($bill->date_issued), 'd-m-Y') }}</td>
                        </tr>
                        <tr>
                            <th>Monto facturado:</th>
                            <td colspan="3" class="important">{{ number_format($bill->billed_price,2).' Bs' }}</td>
                        </tr>
                        <tr><td colspan="4"></td></tr>

                        @if($bill->detail)
                            <tr>
                                <th colspan="4">Detalles de factura:</th>
                            </tr>
                            <tr>
                                <td colspan="4">{{ $bill->detail }}</td>
                            </tr>
                            <tr><td colspan="4"></td></tr>
                        @endif

                        <tr>
                            <th colspan="4">
                                Ordenes:
                                @if($bill->status==0)
                                    <a href="/join/bill-to-order/{{ $bill->id }}" class="pull-right">
                                        <i class="fa fa-link"></i> Asociar orden
                                    </a>
                                @endif
                            </th>
                        </tr>
                        <tr>
                            <th colspan="4" style="padding-left: 20px;padding-right: 20px">

                                <table width="100%">
                                    <thead>
                                    <tr>
                                        <td width="4%"></td>
                                        <td width="30%">Orden</td>
                                        <td>Monto facturado por orden</td>
                                        <td align="center">Cobrado</td>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($bill->orders as $order)
                                        <tr>
                                            <td>
                                                @if($order->pivot->status==0&&$bill->status==0)
                                                    <a href="/detach/bill/{{ 'bl-'.$bill->id }}/{{ $order->id }}"
                                                       style="color: red" title="Eliminar asociación">
                                                        <i class="fa fa-times"></i>
                                                    </a>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="/order/{{ $order->id }}">
                                                    {{ $order->type.' - '.$order->code }}
                                                </a>
                                            </td>
                                            <td>{{ number_format($order->pivot->charged_amount,2).' Bs' }}</td>
                                            <td align="center">
                                                @if($order->pivot->status==0)
                                                    <i
                                                        @if($bill->status==0)
                                                            onclick="flag_status(this,flag='bill-to-order',
                                                                master_id='{{ $order->id }}',id='{{ $bill->id }}');"
                                                        @endif
                                                    class="fa fa-square-o" title="Marcar factura como cobrada"></i>
                                                @else
                                                    <i class="fa fa-check-square-o" title="Cobrado" style="color:green;"></i>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>

                            </th>
                        </tr>

                        </tbody>
                    </table>
                </div>
                @if((/*$user->area=='Gerencia General'&&*/$bill->status==0)||$user->priv_level==4)
                    <div class="col-sm-12 mg10" align="center">
                        <a href="/bill/{{ $bill->id }}/edit" class="btn btn-success">
                            <i class="fa fa-pencil-square-o"></i> Modificar datos de factura
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection

@section('footer')
@endsection

@section('javascript')
    <script>
        $('#alert').delay(2000).fadeOut('slow');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function flag_status(element, flag, master_id, id){
            var text = "Confirma que esta orden ha sido cobrada?";

            var r = confirm(text);
            if (r === true) {
                $.post('/status_update/charge', { flag: flag, master_id: master_id, id: id }, function(data){
                    element.style.color = "green";
                    $(element).toggleClass("fa-square-o fa-check-square-o");
                });
            }
        }
    </script>
@endsection
