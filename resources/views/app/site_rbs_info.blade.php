<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 23/08/2017
 * Time: 04:48 PM
 */
?>

<div class="col-lg-6 mg20">
    <a href="#" onclick="history.back();" class="btn btn-warning"><i class="fa fa-arrow-circle-left"></i> Volver</a>
</div>

<div class="col-sm-12 mg10">
    @include('app.session_flashed_messages', array('opt' => 1))
</div>

<div class="col-sm-12 mg10 mg-tp-px-10">
    <table class="table table-bordered table-striped table-hover">
        <tbody>
        <tr>
            <th width="28%">Código:</th>
            <td width="24%">{{ $rbs_char->site->code }}</td>
        </tr>
        <tr>
            <th>Sitio:</th>
            <td colspan="3">{{ $rbs_char->site->name }}</td>
        </tr>
        <tr>
            <th>Cliente:</th>
            <td>{{ $rbs_char->site->assignment->client }}</td>
            <th width="24%">Estado:</th>
            <td width="24%">{{ $rbs_char->site->statuses($rbs_char->site->status) }}</td>
        </tr>
        <tr><td colspan="4"></td></tr>

        <tr>
            <th>Tipo estación</th>
            <td colspan="3">{{ $rbs_char->type_station }}</td>
        </tr>
        <tr>
            <th>Tipo RBS</th>
            <td colspan="3">{{ $rbs_char->type_rbs }}</td>
        </tr>
        @if($rbs_char->height>0)
            <tr>
                <th>Altura</th>
                <td colspan="3">{{ $rbs_char->height.' metros' }}</td>
            </tr>
        @endif
        @if($rbs_char->number_floors>0)
            <tr>
                <th># Pisos</th>
                <td colspan="3">{{ $rbs_char->number_floors.' pisos' }}</td>
            </tr>
        @endif
        <tr>
            <th>Solución</th>
            <td colspan="3">
                {{ $rbs_char->solution }}
                &emsp;
                <a href="/excel/client_listed_material/{{ $rbs_char->id }}">
                    <i class="fa fa-download"></i> Inventario de materiales
                </a>
            </td>
        </tr>

        @if($rbs_char->tech_group)
            <tr><td colspan="4"></td></tr>
            <tr>
                <th># Grupo</th>
                <td colspan="3">{{ $rbs_char->tech_group->group_number }}</td>
            </tr>
            <tr>
                <th>jefe de grupo</th>
                <td colspan="3">{{ $rbs_char->tech_group->group_head ? $rbs_char->tech_group->group_head->name : '' }}</td>
            </tr>
            <tr>
                <th>Integrantes</th>
                <td colspan="3">
                    {!! $rbs_char->tech_group->tech_2 ? $rbs_char->tech_group->tech_2->name.'<br>' : '' !!}
                    {!! $rbs_char->tech_group->tech_3 ? $rbs_char->tech_group->tech_3->name.'<br>' : '' !!}
                    {!! $rbs_char->tech_group->tech_4 ? $rbs_char->tech_group->tech_4->name.'<br>' : '' !!}
                    {!! $rbs_char->tech_group->tech_5 ? $rbs_char->tech_group->tech_5->name.'<br>' : '' !!}
                </td>
            </tr>
        @endif
        </tbody>
    </table>
</div>
