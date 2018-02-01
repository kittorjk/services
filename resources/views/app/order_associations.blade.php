<div class="col-lg-4 mg20">
    <a href="{{ '/order' }}" class="btn btn-warning"><i class="fa fa-arrow-circle-left"></i> Volver</a>
</div>

@include('app.session_flashed_messages', array('opt' => 1))

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
        {{--
        @if($order->detail<>'')
            <tr>
                <th colspan="4">Detalles de orden:</th>
            </tr>
            <tr>
                <td colspan="4">{{ $order->detail }}</td>
            </tr>
            <tr><td colspan="4"></td></tr>
        @endif
        --}}
        <tr>
            <th colspan="4">
                {{ 'Sitios asociados ('.$order->sites->count().' de '.$order->number_of_sites.')' }}
                @if($order->status=='Pendiente')
                    <a href="/join/order-to-site/{{ $order->id }}" class="pull-right">
                        <i class="fa fa-link"></i> Asociar sitio o proyecto
                    </a>
                @endif
            </th>
        </tr>
        <tr>
            <th colspan="4" style="padding-left: 20px; padding-right: 20px">
                <table width="100%">
                    <tbody>
                    <tr>
                        <td width="4%" style="padding-bottom: 40px"></td>
                        <td width="32%">Sitio</td>
                        <td>Asignación</td>
                        <td>Monto asignado</td>
                    </tr>
                    @foreach($order->sites as $site)
                        <tr>
                            <td>
                                @if($order->status=='Pendiente')
                                    <a href="/detach_from_order/{{ 'st-'.$site->id }}/{{ $order->id }}" style="color: red"
                                       title="Eliminar asociación"><i class="fa fa-times"></i></a>
                                @endif
                            </td>
                            <td>
                                <a href="/task/{{ $site->id }}" title="{{ $site->name }}">
                                    {{ str_limit($site->name,25) }}
                                </a>
                            </td>
                            <td>
                                <a href="/site/{{ $site->assignment->id }}" title="{{ $site->assignment->name }}">
                                    {{ str_limit($site->assignment->name,25) }}
                                </a>
                            </td>
                            <td align="right">{{ number_format($site->pivot->assigned_amount,2).' Bs' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </th>
        </tr>
        <tr><td colspan="4"></td></tr>

        <tr>
            <th colspan="4">
                Facturas asociadas:
                @if($order->status=='Pendiente')
                    <a href="/join/order-to-bill/{{ $order->id }}" class="pull-right">
                        <i class="fa fa-link"></i>{{ ' Asociar factura' }}
                    </a>
                @endif
            </th>
        </tr>
        <tr>
            <th colspan="4" style="padding-left: 20px; padding-right: 20px">
                <table width="100%">
                    <tbody>
                    <tr>
                        <td width="4%" style="padding-bottom: 40px"></td>
                        <td width="32%">Factura</td>
                        <td width="40%">Fecha emisión</td>
                        <td>Monto facturado</td>
                    </tr>
                    @foreach($order->bills as $bill)
                        <tr>
                            <td>
                                @if($order->status=='Pendiente')
                                    <a href="/detach_from_order/{{ 'bl-'.$bill->id }}/{{ $order->id }}" style="color: red"
                                       title="Eliminar asociación"><i class="fa fa-times"></i></a>
                                @endif
                            </td>
                            <td><a href="/bill/{{ $bill->id }}">{{ $bill->code }}</a></td>
                            <td>{{ date_format(new \DateTime($bill->date_issued), 'd-m-Y') }}</td>
                            <td align="right">{{ number_format($bill->pivot->charged_amount,2).' Bs' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </th>
        </tr>

        </tbody>
    </table>
</div>
