<div class="col-lg-6 mg20">
    <a href="#" onclick="history.back();" class="btn btn-warning"><i class="fa fa-arrow-circle-left"></i> Volver</a>
</div>

@include('app.session_flashed_messages', array('opt' => 1))

<div class="col-sm-12 mg10 mg-tp-px-10">
    <table class="table table-bordered table-striped table-hover">
        <tbody>
        <tr>
            <th width="28%">Código:</th>
            <td colspan="3">{{ $task->code }}</td>
        </tr>
        <tr>
            <th>Item:</th>
            <td colspan="3">{{ $task->name }}</td>
        </tr>
        <tr>
            <th>Cliente:</th>
            <td>{{ $task->site->assignment->client }}</td>
            <th>Estado:</th>
            <td>{{ $task->statuses($task->status) }}</td>
        </tr>
        <tr><td colspan="4"></td></tr>

        <tr>
            <th colspan="2">Cantidad proyectada:</th>
            <td colspan="2" class="important">{{ $task->total_expected.' ['.$task->units.']' }}</td>
        </tr>
        <tr>
            <th colspan="2">Precio por unidad:</th>
            <td colspan="2" class="important">{{ number_format($task->quote_price,2).' Bs' }}</td>
        </tr>
        <tr><td colspan="4"></td></tr>

        <tr>
            <td></td>
            <td>Sin impuestos</td>
            <td>Con impuestos</td>
            <td>Porcentaje</td>
        </tr>
        <tr>
            <th>Monto cotizado:</th>
            <td align="right" class="important">{{ number_format($task->assigned_price,2).' Bs' }}</td>
            <td align="right" class="important">{{ number_format($task->assigned_price*1.13,2).' Bs' }}</td>
            <td align="right" class="important">{{ '-' }}</td>
        </tr>
        <tr>
            <th>Monto ejecutado:</th>
            <td align="right" class="important">{{ number_format($task->executed_price,2).' Bs' }}</td>
            <td align="right" class="important">{{ number_format($task->executed_price*1.13,2).' Bs' }}</td>
            <td align="right" class="important">
                {{ $task->assigned_price==0 ? '-' :
                    number_format(($task->executed_price/$task->assigned_price)*100,2).' %' }}
            </td>
        </tr>
        {{--
        <tr>
            <td>Monto cobrado:</td>
            <td><p id="important">{{ number_format($task->charged_price,2).' Bs' }}</p></td>
            <td><p id="important">{{ number_format($task->charged_price*0.87,2).' Bs' }}</p></td>
            <td>
                <p id="important">
                    {{ $task->assigned_price==0 ? number_format(($task->charged_price/1)*100,2).' %' :
                        number_format(($task->charged_price/$task->assigned_price)*100,2).' %' }}
                </p>
            </td>
        </tr>
        --}}

        <tr>
            <td colspan="2"></td>
            <th>% de avance</th>
            <td align="right" class="important">
                {{ $task->assigned_price==0 ? '-' :
                    number_format(($task->executed_price/$task->assigned_price)*100,2).' %' }}
            </td>
        </tr>
        </tbody>
    </table>
</div>
