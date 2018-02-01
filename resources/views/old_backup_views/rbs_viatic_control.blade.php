<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 28/07/2017
 * Time: 05:02 PM
 */
?>

<div class="col-lg-6 mg20">
    <a href="#" onclick="history.back();" class="btn btn-warning"><i class="fa fa-arrow-circle-left"></i> Volver</a>
</div>

@include('app.session_flashed_messages', array('opt' => 1))

<div class="col-sm-12 mg10 mg-tp-px-10">
    <table class="table table-bordered table-striped table-hover">
        <tbody>

        <tr>
            <th>Solicitud</th>
            <td>{{ $rbs_viatic->id }}</td>
            <td colspan="4"></td>
        </tr>
        <tr>
            <th>Tipo</th>
            <td colspan="2">{{ $rbs_viatic->type }}</td>
            <th>Estado</th>
            <td colspan="2">
                {{ $rbs_viatic->statuses($rbs_viatic->status) }}
            </td>
        </tr>
        <tr>
            <th>Trabajo</th>
            <td colspan="5">{{ $rbs_viatic->work_description }}</td>
        </tr>
        <tr>
            <th>Desde</th>
            <td colspan="2">{{ date_format(new \DateTime($rbs_viatic->date_from), 'd-m-Y') }}</td>
            <th>Hasta</th>
            <td colspan="2">{{ date_format(new \DateTime($rbs_viatic->date_to), 'd-m-Y') }}</td>
        </tr>

        <tr><td colspan="6"></td></tr>

        <tr>
            <th colspan="2">Personal</th>
            <th align="center">Tiempo [días]</th>
            <th>{{ $rbs_viatic->type.' [Bs]' }}</th>
            <th>Costo unit/día [Bs]</th>
            <th>Total [Bs]</th>
        </tr>
        @foreach($rbs_viatic->technician_requests as $request)
            <tr>
                <td colspan="2">{{ $request->technician ? $request->technician->name : '' }}</td>
                <td align="center">{{ $request->num_days }}</td>
                <td align="right">{{ $request->viatic_amount }}</td>
                <td align="right">{{ $request->technician ? $request->technician->cost_day : 1 }}</td>
                <td align="right">{{ $request->technician ? ($request->technician->cost_day*$request->num_days) : 0 }}</td>
            </tr>
        @endforeach
        <tr>
            <th colspan="4" style="text-align:right">Subtotal mano de obra [Bs]</th>
            <td colspan="2" align="right">{{ $rbs_viatic->sub_total_workforce }}</td>
        </tr>
        <tr>
            <th colspan="4" style="text-align:right">Subtotal viáticos [Bs]</th>
            <td colspan="2" align="right">{{ $rbs_viatic->sub_total_viatic }}</td>
        </tr>

        <tr><td colspan="6"></td></tr>

        <tr>
            <th>Mano de obra</th>
            <th>Unidad</th>
            <th>Cant. de recurso</th>
            <th>Carga</th>
            <th>Costo unit tec/día</th>
            <th>Total</th>
        </tr>
        <tr>
            <td>Project Mnager</td>
            <td align="center">%</td>
            <td align="center">1</td>
            <td align="center">{{ $parameters['pm_load'].'%' }}</td>
            <td align="right">{{ $rbs_viatic->sub_total_workforce }}</td>
            <td align="right">{{ $rbs_viatic->pm_cost }}</td>
        </tr>
        <tr>
            <td>Beneficios sociales</td>
            <td align="center">%</td>
            <td align="center">1</td>
            <td align="center">{{ $parameters['sb_load'].'%' }}</td>
            <td align="right">{{ $rbs_viatic->sub_total_workforce + $rbs_viatic->pm_cost }}</td>
            <td align="right">{{ $rbs_viatic->social_benefits }}</td>
        </tr>
        <tr>
            <td>Ropa de trabajo</td>
            <td align="center">%</td>
            <td align="center">1</td>
            <td align="center">{{ $parameters['ws_load'].'%' }}</td>
            <td align="right">{{ $rbs_viatic->sub_total_workforce + $rbs_viatic->pm_cost }}</td>
            <td align="right">{{ $rbs_viatic->work_supplies }}</td>
        </tr>
        <tr>
            <th colspan="4" style="text-align: right">Total mano de obra [Bs]</th>
            <td colspan="2" align="right">{{ $rbs_viatic->total_workforce }}</td>
        </tr>

        <tr><td colspan="6"></td></tr>

        @if($rbs_viatic->materials_cost!=0)
            <tr>
                <th colspan="4" style="text-align: right">Materiales adicionales [Bs]</th>
                <td colspan="2" align="right">{{ $rbs_viatic->materials_cost }}</td>
            </tr>
        @endif
        @if($rbs_viatic->sub_total_transport!=0)
            <tr>
                <th colspan="4" style="text-align: right">Transporte [Bs]</th>
                <td colspan="2" align="right">{{ $rbs_viatic->sub_total_transport }}</td>
            </tr>
        @endif
        @if($rbs_viatic->extra_expenses!=0)
            <tr>
                <th colspan="4" style="text-align: right">Gastos extra [Bs]</th>
                <td colspan="2" align="right">{{ $rbs_viatic->extra_expenses }}</td>
            </tr>
        @endif
        @if($rbs_viatic->materials_cost!=0||$rbs_viatic->sub_total_transport!=0||$rbs_viatic->extra_expenses!=0)
            <tr><td colspan="6"></td></tr>
        @endif

        <tr>
            <th colspan="2">Equipo</th>
            <th>Unidad</th>
            <th>Cantidad</th>
            <th>Costo unitario</th>
            <th>Total [Bs]</th>
        </tr>
        <tr>
            <td colspan="2">Herramientas menores</td>
            <td align="center">%</td>
            <td align="center">{{ $parameters['mtu_load'].'%' }}</td>
            <td align="right">{{ $rbs_viatic->total_workforce }}</td>
            <td align="right">{{ $rbs_viatic->minor_tools_cost }}</td>
        </tr>

        <tr><td colspan="6"></td></tr>

        <tr>
            <th colspan="4">Costo directo [Bs]</th>
            <td colspan="2" align="right">{{ $rbs_viatic->total_cost }}</td>
        </tr>
        <tr>
            <th># Sitios</th>
            <td align="center">{{ $rbs_viatic->num_sites }}</td>
            <th colspan="2">Costo por sitio [Bs]</th>
            <td colspan="2" align="right">
                {{ $rbs_viatic->num_sites>=1 ? $rbs_viatic->total_cost/$rbs_viatic->num_sites : '' }}
            </td>
        </tr>

        <tr><td colspan="6"></td></tr>

        <tr>
            <th colspan="6">Control</th>
        </tr>
        <tr>
            <td colspan="4">Sitio</td>
            <td colspan="2">Monto disponible [Bs]</td>
        </tr>
        @foreach($budgets as $budget)
            <tr>
                <td colspan="4">{{ $budget['name'] }}</td>
                <td colspan="2" style="color:{{ $budget['flag']=='error' ? 'darkred' : 'darkgreen' }}" align="right">
                    {{ number_format($budget['amount_available'],2) }}
                </td>
            </tr>
        @endforeach

        </tbody>
    </table>
</div>
