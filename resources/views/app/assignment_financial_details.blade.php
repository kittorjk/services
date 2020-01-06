<div class="col-lg-6 mg20">
    <a href="#" onclick="history.back();" class="btn btn-warning"><i class="fa fa-arrow-circle-left"></i> Volver</a>
</div>

@include('app.session_flashed_messages', array('opt' => 1))

<div class="col-sm-12 mg10 mg-tp-px-10">
    <table class="table table-bordered table-striped table-hover">
        <tbody>
        <tr>
            <th width="25%">Código:</th>
            <td width="25%">{{ $assignment->code }}</td>
        </tr>
        <tr>
          <th>
            <span title="Centro de costos">C.C.:</span>
          </th>
          <td>{{ $assignment->cost_center > 0 ? $assignment->cost_center : 'N/E' }}</td>
        </tr>
        <tr>
            <th>Asignación:</th>
            <td colspan="3">{{ $assignment->name }}</td>
        </tr>
        <tr>
            <th>Cliente:</th>
            <td>{{ $assignment->client }}</td>
            <th width="25%">Estado:</th>
            <td width="25%">{{ $assignment->statuses($assignment->status) }}</td>
        </tr>
        <tr><td colspan="4"></td></tr>

        <tr>
            <th></th>
            <td>Sin impuestos</td>
            <td>Con impuestos</td>
            <td>Porcentaje</td>
        </tr>
        <tr>
            <th>Monto asignado:</th>
            <td align="right" class="important">{{ number_format($assignment->assigned_price,2).' Bs' }}</td>
            <td align="right" class="important">{{ number_format($assignment->assigned_price*1.13,2).' Bs' }}</td>
            <td align="right" class="important">{{ '-' }}</td>
        </tr>
        <tr>
            <th>Monto cotizado:</th>
            <td align="right" class="important">{{ number_format($assignment->quote_price,2).' Bs' }}</td>
            <td align="right" class="important">{{ number_format($assignment->quote_price*1.13,2).' Bs' }}</td>
            <td align="right" class="important">
                {{ $assignment->assigned_price==0 ? '-' :
                    number_format(($assignment->quote_price/$assignment->assigned_price)*100,2).' %' }}
            </td>
        </tr>
        <tr>
            <th>Monto ejecutado:</th>
            <td align="right" class="important">{{ number_format($assignment->executed_price,2).' Bs' }}</td>
            <td align="right" class="important">{{ number_format($assignment->executed_price*1.13,2).' Bs' }}</td>
            <td align="right" class="important">
                {{ $assignment->assigned_price==0 ? '-' :
                    number_format(($assignment->executed_price/$assignment->assigned_price)*100,2).' %' }}
            </td>
        </tr>
        <tr>
            <th>Monto cobrado:</th>
            <td align="right" class="important">{{ number_format($assignment->charged_price,2).' Bs' }}</td>
            <td align="right" class="important">{{ number_format($assignment->charged_price*1.13,2).' Bs' }}</td>
            <td align="right" class="important">
                {{ $assignment->assigned_price==0 ? '-' :
                    number_format(($assignment->charged_price/$assignment->assigned_price)*100,2).' %' }}
            </td>
        </tr>
        </tbody>
    </table>
</div>
