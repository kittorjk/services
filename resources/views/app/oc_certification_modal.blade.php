<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 17/03/2017
 * Time: 11:53 AM
 */
?>

<link rel="stylesheet" href="{{ asset("app/css/custom_table.css") }}">
<style>
    .modal-footer {
        background-color: #f4f4f4;
    }
</style>

<div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content" style="overflow:hidden;">

        <div class="modal-header alert-info">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">{{ 'Certificación - '.$oc->code }}</h4>
        </div>

        <div class="modal-body">

            <p>
                {{ $oc->certificates->count()==1 ? 'Se ha emitido el siguiente certificado para esta OC' :
                 'Se han emitido los siguientes certificados para esta OC' }}
            </p>

            <!--<table class="formal_table table_blue">-->
            @foreach($oc->certificates as $certificate)
            <table class="table table-striped table-hover table-bordered">
                <thead>
                <tr>
                    <th>Código</th>
                    <td width="60%">
                        <a href="/oc_certificate/{{ $certificate->id }}">{{ $certificate->code }}</a>
                    </td>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <th>Monto certificado</th>
                    <td>{{ number_format($certificate->amount,2).' Bs' }}</td>
                </tr>
                {{--
                <tr>
                    <th>Monto cancelado a la fecha</th>
                    <td>{{ number_format($oc->payed_amount,2).' Bs' }}</td>
                </tr>
                --}}
                <tr>
                    <th>Trabajo certificado por</th>
                    <td>
                        {{ $certificate->user->priv_level==4 ? 'Administrador' : $certificate->user->name }}
                    </td>
                </tr>
                <tr>
                    <th>Fecha</th>
                    <td>{{ date_format($certificate->created_at,'d/m/Y') }}</td>
                </tr>
                </tbody>
            </table>
            @endforeach

        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
        </div>

    </div>

</div>
