<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 25/08/2017
 * Time: 11:38 AM
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
            <h4 class="modal-title">{{ 'Archivos de respaldo de tiempo muerto' }}</h4>
        </div>

        <div class="modal-body">

            <p>{{ $dead_interval->reason }}</p>

            <p>
                {{ $dead_interval->files->count()==1 ? 'Un archivo cargado' :
                    $dead_interval->files->count().' archivos cargados' }}
            </p>

            <table class="table table-striped table-hover table-bordered">
                <thead>
                <tr>
                    <th colspan="2">Archivos</th>
                </tr>
                </thead>
                <tbody>
                @foreach($dead_interval->files as $file)
                    <tr>
                        <td width="25%">{{ date_format(new \DateTime($file->updated_at), 'd-m-Y') }}</td>
                        <td width="75%">
                            {{ $file->description }}

                            <div class="pull-right">
                                @include('app.info_document_options', array('file'=>$file))
                            </div>
                        </td>
                    </tr>
                @endforeach
                @if($dead_interval->files->count()==0)
                    <tr>
                        <td colspan="2" align="center">No se encontraron archivos para este tiempo muerto</td>
                    </tr>
                @endif
                {{--@if($dead_interval->closed==0)--}}
                    <tr>
                        <td colspan="2" align="center">
                            <a href="/files/dead_interval/{{ $dead_interval->id }}">
                                <i class="fa fa-upload"></i> Subir archivo
                            </a>
                        </td>
                    </tr>
                {{--@endif--}}
                </tbody>
            </table>

        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
        </div>

    </div>

</div>
