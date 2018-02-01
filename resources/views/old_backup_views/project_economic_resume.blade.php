@extends('layouts.master')

@section('header')
    @parent
@endsection

@section('content')

<div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

    <div class="panel panel-sky" >
        <div class="panel-heading" align="center">
            <div class="panel-title">Resumen económico del proyecto</div>
        </div>
        <div class="panel-body" >
            <div class="col-lg-6 mg20">
                <a href="#" onclick="history.back();" class="btn btn-warning"><i class="fa fa-arrow-circle-left"></i> Volver</a>
            </div>
            @if (Session::has('message'))
                <div class="alert alert-danger" align="center">{{ Session::get('message') }}</div>
            @endif
            <div class="col-sm-12 mg10 mg-tp-px-10">
                <table class="table table-bordered table-striped table-hover">
                    <tbody>
                    <tr>
                        <th width="28%">Código:</th>
                        <td width="20%">{{ 'PR-'.str_pad($project->id, 4, "0", STR_PAD_LEFT).date_format($project->created_at,'-y') }}</td>
                        <th width="28%">Estado:</th>
                        <td>
                            @if($project->status<=10)
                                {{ 'Etapa '.$project->status.' de 10' }}
                            @elseif($project->status==11)
                                {{ 'Concluído' }}
                            @elseif($project->status==12)
                                {{ 'No asignado' }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Proyecto:</th>
                        <td>{{ $project->name }}</td>
                        <th>Cliente:</th>
                        <td>{{ $project->client }}</td>
                    </tr>
                    <tr><td colspan="4"></td></tr>
                    <tr>
                        <th colspan="4">Cotización:</th>
                    </tr>
                    <tr>
                        <td>Monto cotizado bruto:</td>
                        <td><p id="important">{{ number_format($project->quote_amount,2).' Bs' }}</p></td>
                        <td>Monto cotizado neto:</td>
                        <td><p id="important">{{ number_format($project->quote_amount*0.87,2).' Bs' }}</p></td>
                    </tr>
                    <tr><td colspan="4"></td></tr>
                    <tr>
                        <th colspan="4">Asignación del cliente:</th>
                    </tr>
                    <tr>
                        <td>Monto asignado bruto:</td>
                        <td><p id="important">{{ number_format($project->pc__amount,2).' Bs' }}</p></td>
                        <td>Monto asignado neto:</td>
                        <td><p id="important">{{ number_format($project->pc__amount*0.87,2).' Bs' }}</p></td>
                    </tr>
                    <tr><td colspan="4"></td></tr>
                    <tr>
                        <th colspan="4">Ejecución:</th>
                    </tr>
                    <tr>
                        <td>Monto ejecutado bruto:</td>
                        <td><p id="important">{{ number_format($project->costsh_amount,2).' Bs' }}</p></td>
                        <td>Monto ejecutado neto:</td>
                        <td><p id="important">{{ number_format($project->costsh_amount*0.87,2).' Bs' }}</p></td>
                    </tr>
                    <tr><td colspan="4"></td></tr>
                    <tr>
                        <th colspan="4">Datos de factura:</th>
                    </tr>
                    <tr>
                        @if($project->bill_number==0)
                            <td colspan="4">No existen datos de factura</td>
                        @else
                            <td>Número de factura:</td>
                            <td>{{ $project->bill_number }}</td>
                            <td>Fecha de emisión de factura:</td>
                            <td>{{ date_format(new \DateTime($project->bill_date), 'd-m-Y') }}</td>
                        @endif
                    </tr>
                    <tr><td colspan="4"></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@section('footer')
@endsection
