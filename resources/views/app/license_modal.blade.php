<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 11/04/2017
 * Time: 11:06 AM
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
            <h4 class="modal-title">
                {{ 'Licencia de conducir' }}
            </h4>
        </div>

        <div class="modal-body">

            @include('app.session_flashed_messages', array('opt' => 1))

            <table class="table table-striped table-hover table-bordered">
                <tbody>
                @if($license)
                    <tr>
                        <th width="40%">Nombre:</th>
                        <th>{{ $license->user->name }}</th>
                    </tr>
                    <tr>
                        <td>Número</td>
                        <td>{{ $license->number }}</td>
                    </tr>
                    <tr>
                        <td>Categoría:</td>
                        <td>{{ $license->category }}</td>
                    </tr>
                    <tr>
                        <td>Fecha de vencimiento:</td>
                        <td>{{ \Carbon\Carbon::parse($license->exp_date)->format('d-m-Y') }}</td>
                    </tr>
                @else
                    <tr>
                        <td align="center">Este usuario no tiene una licencia registrada</td>
                    </tr>
                @endif
                </tbody>
            </table>

        </div>

        <div class="modal-footer">
            @if($user->action->acv_vhc_lic_mod)
                @if($license)
                    {{--@if($user->priv_level>=2||$user->work_type=='Transporte')--}}
                        <a href="/license/{{ $license->id }}/edit" class="btn btn-primary">
                            <i class="fa fa-pencil-square-o"></i> Actualizar licencia
                        </a>
                    {{--@endif--}}
                @endif
                {{--@if($user->priv_level==4)--}}
                    <a href="{{ '/excel/licenses' }}" class="btn btn-success">
                        <i class="fa fa-file-excel-o"></i> Exportar tabla
                    </a>
                {{--@endif--}}
            @endif
            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
        </div>

    </div>
</div>

<script>
</script>
