<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 18/05/2017
 * Time: 10:46 AM
 */
?>

<link rel="stylesheet" href="{{ asset("app/css/custom_table.css") }}">
<style>
    .modal-footer {
        background-color: #f4f4f4;
    }
</style>

<div class="modal-dialog modal-lg">

    <!-- Modal content-->
    <div class="modal-content" style="overflow:hidden;">

        <div class="modal-header alert-info">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">
                {{ 'Contenido de correo' }}
            </h4>
        </div>

        <div class="modal-body">

            <p>{{ 'de: '.$email->sent_by }}</p>
            <p>{{ 'para: '.$email->sent_to }}</p>
            <p>{{ 'cc: '.$email->sent_cc }}</p>

            <table class="formal_table table_blue tablesorter">
                <thead>
                <tr>
                    <th width="15%">Asunto</th>
                    <td>{{ $email->subject }}</td>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td colspan="2">{!! $email->content !!}</td>
                </tr>
                </tbody>
            </table>

        </div>

        <div class="modal-footer">
            {{-- Insert option to generate .eml file if necessary
            <a href="/excel/email/{{ $email->id }}" class="btn btn-success">
                <i class="fa fa-file-excel-o"></i> Exportar
            </a>
            --}}
            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
        </div>

    </div>

</div>

<script>
</script>
