@extends('layouts.info_master')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/info_tabs.css") }}">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

        <div class="panel panel-sky">
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    <div class="pull-left">
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#details" data-toggle="tab"> Detalle de orden</a></li>
                            <li><a href="#asociations" data-toggle="tab"> Asociaciones</a></li>
                        </ul>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <!--<div class="panel-title">Detalles de Orden</div>-->
            </div>
            <div class="panel-body">

                <div class="tab-content">

                    <div class="tab-pane fade in active" id="details">

                        <div class="col-lg-5 mg20">
                            <a href="#" onclick="history.back();" class="btn btn-warning">
                                <i class="fa fa-arrow-circle-left"></i> Atrás
                            </a>
                            <a href="{{ '/order' }}" class="btn btn-warning">
                                <i class="fa fa-arrow-circle-up"></i> Ordenes
                            </a>
                        </div>

                        <div class="col-sm-12 mg10">
                            @include('app.session_flashed_messages', array('opt' => 0))
                        </div>

                        <div class="col-sm-12 mg10 mg-tp-px-10">
                            <table class="table table-striped table-hover table-bordered">
                                <tbody>
                                <tr>
                                    <th width="25%">Código:</th>
                                    <td colspan="3">{{ $order->type.' - '.$order->code }}</td>
                                </tr>
                                <tr>
                                    <th>Cliente:</th>
                                    <td colspan="3">{{ $order->client }}</td>
                                </tr>
                                <tr>
                                    <th width="25%">Recepción:</th>
                                    <td width="25%">{{ date_format(new \DateTime($order->date_issued), 'd-m-Y') }}</td>
                                    <th width="25%">Estado:</th>
                                    <td width="25%">{{ $order->status }}</td>
                                </tr>
                                <tr><td colspan="4"></td></tr>

                                <tr>
                                    <th colspan="4">Monto asignado:</th>
                                </tr>
                                <tr>
                                    <td>Sin impuestos:</td>
                                    <td align="right" class="important">
                                        {{ number_format($order->assigned_price,2).' Bs' }}
                                    </td>
                                    <td>Con impuestos:</td>
                                    <td align="right" class="important">
                                        {{ number_format($order->assigned_price*1.13,2).' Bs' }}
                                    </td>
                                </tr>
                                <tr><td colspan="4"></td></tr>

                                <tr>
                                    <th colspan="4">Porcentajes de pago:</th>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>Primer pago</td>
                                    <td>Segundo pago</td>
                                    <td>Tercer pago</td>
                                </tr>
                                <tr>
                                    <td>Porcentaje:</td>
                                    <td align="right" class="important">{{ $percentages[0].' %' }}</td>
                                    <td align="right" class="important">{{ $percentages[1].' %' }}</td>
                                    <td align="right" class="important">{{ $percentages[2].' %' }}</td>
                                </tr>
                                <tr>
                                    <td>Sin impuestos:</td>
                                    <td align="right" class="important">
                                        {{ number_format(($order->assigned_price*$percentages[0])/100,2).' Bs' }}
                                    </td>
                                    <td align="right" class="important">
                                        {{ number_format(($order->assigned_price*$percentages[1])/100,2).' Bs' }}
                                    </td>
                                    <td align="right" class="important">
                                        {{ number_format(($order->assigned_price*$percentages[2])/100,2).' Bs' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>Con impuestos:</td>
                                    <td align="right" class="important">
                                        {{ number_format(($order->assigned_price*$percentages[0]*1.13)/100,2).' Bs' }}
                                    </td>
                                    <td align="right" class="important">
                                        {{ number_format(($order->assigned_price*$percentages[1]*1.13)/100,2).' Bs' }}
                                    </td>
                                    <td align="right" class="important">
                                        {{ number_format(($order->assigned_price*$percentages[2]*1.13)/100,2).' Bs' }}
                                    </td>
                                </tr>
                                <tr><td colspan="4"></td></tr>

                                <tr>
                                    <th colspan="2">Monto facturado:</th>
                                    <td align="right" class="important">
                                        {{ number_format($order->billed_price,2).' Bs' }}
                                    </td>
                                    <td align="right" class="important">
                                        {{ number_format(($order->billed_price/$order->assigned_price)*100,2).' %' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th colspan="2">Monto cobrado:</th>
                                    <td align="right" class="important">
                                        {{ number_format($order->charged_price,2).' Bs' }}
                                    </td>
                                    <td align="right" class="important">
                                        {{ number_format(($order->charged_price/$order->assigned_price)*100,2).' %' }}
                                    </td>
                                </tr>
                                <tr><td colspan="4"></td></tr>

                                @if($order->detail<>'')
                                    <tr>
                                        <th colspan="4">Detalles de orden:</th>
                                    </tr>
                                    <tr>
                                        <td colspan="4">{{ $order->detail }}</td>
                                    </tr>
                                    <tr><td colspan="4"></td></tr>
                                @endif

                                <tr>
                                    <th colspan="4">Archivos:</th>
                                </tr>
                                @foreach($order->files as $file)
                                    <tr>
                                        <td>{{ date_format(new \DateTime($file->updated_at), 'd-m-Y') }}</td>
                                        <td colspan="3">
                                            {{ $file->description }}
                                            <div class="pull-right">
                                                @if($user->area=='Gerencia General'||$user->priv_level>=3)
                                                    @include('app.info_document_options', array('file'=>$file))

                                                    {{--
                                                @else
                                                    @if($file->name=='RDR_'.$order->id.'_org.pdf')
                                                        Recibido
                                                    @elseif($file->name=='RDR_'.$order->id.'_sgn.pdf')
                                                        Enviado
                                                    @endif
                                                    --}}
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                @if($order->files->count()<2&&$order->status=='Pendiente')
                                    <tr>
                                        <th colspan="4" style="text-align: center">
                                            <a href="/files/order/{{ $order->id }}">
                                                <i class="fa fa-upload"></i> Subir archivo
                                            </a>
                                        </th>
                                    </tr>
                                @endif

                                <tr><td colspan="4"></td></tr>
                                <tr>
                                    <th colspan="2">Registro creado por</th>
                                    <td colspan="2">{{ $order->user ? $order->user->name : 'N/E' }}</td>
                                </tr>

                                </tbody>
                            </table>
                        </div>
                        @if((($user->area=='Gerencia General'||$user->priv_level==3)&&$order->status<>'Cobrado'&&
                            $order->status<>'Anulado')||$user->priv_level==4)
                            <div class="col-sm-12 mg10" align="center">
                                <a href="/order/{{ $order->id }}/edit" class="btn btn-success">
                                    <i class="fa fa-pencil-square-o"></i> Modificar orden
                                </a>
                            </div>
                        @endif

                    </div>

                    <div class="tab-pane fade" id="asociations">
                        @include('app.order_associations')
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection

@section('footer')
@endsection

@section('javascript')
    <script src="{{ asset('app/js/set_current_url.js') }}"></script> {{-- For recording current url --}}
    <script>
        $('#alert').delay(2000).fadeOut('slow');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
@endsection
