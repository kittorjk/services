<div class="col-lg-6 mg20">
    <a href="#" onclick="history.back();" class="btn btn-warning"><i class="fa fa-arrow-circle-left"></i> Volver</a>
</div>

@include('app.session_flashed_messages', array('opt' => 1))

<div class="col-sm-12 mg10 mg-tp-px-10">
    <table class="table table-bordered table-striped table-hover">
        <tbody>
        <tr>
            <th width="28%">Código:</th>
            <td width="24%">{{ $site->code }}</td>
        </tr>
        <tr>
            <th>Sitio:</th>
            <td colspan="3">{{ $site->name }}</td>
        </tr>
        <tr>
            <th>Cliente:</th>
            <td>{{ $site->assignment->client }}</td>
            <th width="24%">Estado:</th>
            <td width="24%">{{ $site->statuses($site->status) }}</td>
        </tr>
        <tr><td colspan="4"></td></tr>

        <tr>
            <th></th>
            <th>Sin impuestos</th>
            <th>Con impuestos</th>
            <th>Porcentaje</th>
        </tr>
        <tr>
            <td>Monto asignado:</td>
            <td class="important" align="right">{{ number_format($site->assigned_price,2).' Bs' }}</td>
            <td class="important" align="right">{{ number_format($site->assigned_price*1.13,2).' Bs' }}</td>
            <td class="important" align="right">{{ '-' }}</td>
        </tr>
        <tr>
            <td>Monto cotizado:</td>
            <td class="important" align="right">{{ number_format($site->quote_price,2).' Bs' }}</td>
            <td class="important" align="right">{{ number_format($site->quote_price*1.13,2).' Bs' }}</td>
            <td class="important" align="right">
                {{ $site->assigned_price==0 ? '-' : number_format(($site->quote_price/$site->assigned_price)*100,2).' %' }}
            </td>
        </tr>
        <tr>
            <td>Monto ejecutado:</td>
            <td class="important" align="right">{{ number_format($site->executed_price,2).' Bs' }}</td>
            <td class="important" align="right">{{ number_format($site->executed_price*1.13,2).' Bs' }}</td>
            <td class="important" align="right">
                {{ $site->assigned_price==0 ? '-' : number_format(($site->executed_price/$site->assigned_price)*100,2).' %' }}
            </td>
        </tr>
        <tr>
            <td>Monto cobrado:</td>
            <td class="important" align="right">{{ number_format($site->charged_price,2).' Bs' }}</td>
            <td class="important" align="right">{{ number_format($site->charged_price*1.13,2).' Bs' }}</td>
            <td class="important" align="right">
                {{ $site->assigned_price==0 ? '-' : number_format(($site->charged_price/$site->assigned_price)*100,2).' %' }}
            </td>
        </tr>
        </tbody>
    </table>
</div>
