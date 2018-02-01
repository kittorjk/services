@extends('layouts.master')

@section('header')
    @parent
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

        <div class="panel panel-sky" >
            <div class="panel-heading" align="center">
                <div class="panel-title">Información de evento</div>
            </div>
            <div class="panel-body" >
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning"><i class="fa fa-arrow-circle-left"></i> Volver</a>
                </div>
                @if (Session::has('message'))
                    <div class="alert alert-danger" align="center">{{ Session::get('message') }}</div>
                @endif

                <div class="col-sm-12 mg10 mg-tp-px-10">

                    <table class="table table-striped table-hover table-bordered">
                        <thead>
                        <tr>
                            <th width="28%">Proyecto:</th>
                            <td colspan="3">{{ $project_info->name }}</td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <th width="28%">Sitio:</th>
                            <td colspan="3">{{ $event->project_site }}</td>
                        </tr>
                        <tr>
                            <td colspan="2" rowspan="2" width="50%"></td>
                            <th width="28%">Número de evento:</th>
                            <td>{{ $event->event_number }}</td>
                        </tr>
                        <tr>
                            <th>Fecha:</th>
                            <td>{{ date_format(new \DateTime($event->event_date), 'd-m-Y') }}</td>
                        </tr>
                        <tr><td colspan="4"> </td></tr>
                        <tr>
                            <th>Evento:</th>
                            <td colspan="3">{{ $event->brief_description }}</td>
                        </tr>
                        <tr><td colspan="4"> </td></tr>
                        <tr>
                            <th colspan="4">Información detallada:</th>
                        </tr>
                        <tr>
                            <td colspan="4">{{ $event->detailed_description }}</td>
                        </tr>
                        <tr><td colspan="4"> </td></tr>
                        <tr>
                            <th>Responsable por parte del cliente:</th>
                            <td colspan="3">{{ $event->resp_client }}</td>
                        </tr>
                        <tr>
                            <th>Responsable por parte de ABROS:</th>
                            <td colspan="3">{{ $event->resp_abr }}</td>
                        </tr>
                        <tr>
                            <th>Creado por:</th>
                            <td colspan="3">{{ $event_user_name->name }}</td>
                        </tr>
                        <tr><td colspan="4"> </td></tr>
                        <tr>
                            <th colspan="4">Archivos de respaldo:</th>
                        </tr>
                            <?php $restantes='0' ?>
                            @foreach($files as $file)
                                @if($file->imageable_id == $event->id)
                                    <tr>
                                    <td colspan="2">{{ $file->name }}</td>
                                    <td colspan="2">
                                        <a href="/download/{{ $file->id }}">
                                            @if($file->type=="pdf")
                                                <img src="/imagenes/pdf-icon.png" alt="PDF" />
                                                <?php $restantes++ ?>
                                            @elseif($file->type=="docx"||$file->type=="doc")
                                                <img src="/imagenes/word-icon.png" alt="WORD" />
                                                <?php $restantes++ ?>
                                            @elseif($file->type=="xlsx"||$file->type=="xls")
                                                <img src="/imagenes/excel-icon.png" alt="EXCEL" />
                                                <?php $restantes++ ?>
                                            @elseif($file->type=="jpg"||$file->type=="jpeg"||$file->type=="png")
                                                <img src="/imagenes/image-icon.png" alt="IMAGE" />
                                                <?php $restantes++ ?>
                                            @endif
                                        </a>
                                    </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="col-sm-12 mg10" align="center">
                    @if($restantes<5)
                        <a href="/files/event/{{ $event->id }}" class="btn btn-primary"><i class="fa fa-upload"></i> Subir archivo</a>
                    @endif
                    @if($user->priv_level>=3)
                        <a href="/event/{{ $event->id }}/edit" class="btn btn-success"><i class="fa fa-pencil-square-o"></i> Modificar</a>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection

@section('footer')
@endsection
