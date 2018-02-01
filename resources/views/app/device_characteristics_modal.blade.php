<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 13/03/2017
 * Time: 12:19 PM
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
                {{ 'Características de equipo' }}
            </h4>
        </div>

        <div class="modal-body">

            <h4 align="center">{{ $device_info->type.' '.$device_info->model }}</h4>
            <p>{{ 'Serial: '.$device_info->serial }}</p>

            <table class="formal_table table_blue tablesorter">
                <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Valor</th>
                    <th>Unidades</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($characteristics as $characteristic)
                    <tr>
                        <td>
                            {{ $characteristic->type }}
                            @if($user->action->acv_dvc_edt&&$device_info->flags!='0000'
                                /*$user->priv_level==4||($user->work_type=='Almacén'&&$device_info->flags!='0000')*/)
                                <a href="/characteristics/device/{{ $characteristic->id }}/edit" title="Modificar">
                                    <i class="fa fa-pencil-square-o pull-right"></i>
                                </a>
                            @endif
                        </td>
                        <td>{{ $characteristic->value }}</td>
                        <td>{{ $characteristic->units }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>

        </div>

        <div class="modal-footer">
            @if($device_info&&$user->action->acv_dvc_edt /*($user->work_type=='Almacén'||$user->priv_level>=3)*/&&
                $device_info->flags!='0000')
                <a href="/characteristics/device/{{ $device_info->id }}/create" class="btn btn-success">
                    <i class="fa fa-plus"></i> Agregar
                </a>
            @endif
            @if($user->priv_level==4)
                <a href="/excel/device_characteristics/{{ $device_info ? $device_info->id : 0 }}" class="btn btn-success">
                    <i class="fa fa-file-excel-o"></i> Exportar
                </a>
            @endif
            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
        </div>

    </div>

</div>

<script>
</script>
