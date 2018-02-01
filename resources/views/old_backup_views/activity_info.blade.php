@extends('layouts.master')

@section('header')
    @parent
    <script type="text/javascript"
            src="http://viralpatel.net/blogs/demo/jquery/jquery.shorten.1.0.js"></script>
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

        <div class="panel panel-sky" >
            <div class="panel-heading" align="center">
                <div class="panel-title">Detalle de actividad</div>
            </div>
            <div class="panel-body" >
                <div class="col-lg-4 mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning"><i class="fa fa-arrow-circle-left"></i> Volver</a>
                </div>
                @if (Session::has('message'))
                    <div class="alert alert-danger" align="center">{{ Session::get('message') }}</div>
                @endif

                <div class="col-sm-12 mg10 mg-tp-px-10">
                    <table class="table table-striped table-hover table-bordered">
                        <tbody>
                        <tr>
                            <th width="35%">
                                <a href="/assignment/{{ $activity->site->assignment->id }}">Proyecto:</a>
                            </th>
                            <td colspan="3">
                                <div class="comment">
                                    {{ $activity->site->assignment->name }}
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <a href="/site/{{ $activity->site->id }}/show">Sitio:</a>
                            </th>
                            <td colspan="3">
                                <div class="comment">
                                    {{ $activity->site->name }}
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>Item:</th>
                            <td colspan="3">
                                @if($activity->task_id!=0)
                                    <a href="/task/{{ $activity->task->id }}/show">{{ $activity->task->name }}</a>
                                @else
                                    {{ 'N/A' }}
                                @endif
                            </td>
                        </tr>
                        <tr><td colspan="4"></td></tr>

                        <tr>
                            <th>Actividad / evento:</th>
                            <td colspan="3">{{ $activity->number.'. '.$activity->type }}</td>
                        </tr>
                        <tr>
                            <th>Fecha:</th>
                            <td colspan="3">{{ date_format(new \DateTime($activity->start_date), 'd-m-Y') }}</td>
                        </tr>
                        <tr><td colspan="4"></td></tr>

                        @if($activity->description)
                        <tr>
                            <th colspan="4">Información detallada:</th>
                        </tr>
                        <tr>
                            <td colspan="4">
                                <div class="comment">
                                    {{ $activity->description }}
                                </div>
                            </td>
                        </tr>
                        <tr><td colspan="4"></td></tr>
                        @endif

                        @if($activity->task_id!=0&&$activity->task->responsible!=0)
                        <tr>
                            <th>Responsable de tarea:</th>
                            <td colspan="3">{{ $activity->task->responsible }}</td>
                        </tr>
                        @endif

                        <tr>
                            <th>Supervisor ABROS:</th>
                            <td colspan="3">{{ $activity->site->resp_id!=0 ? $activity->site->user->name : 'No asignado' }}</td>
                        </tr>
                        <tr>
                            <th>Supervisor del cliente:</th>
                            <td colspan="3">{{ $activity->site->contact->name }}</td>
                        </tr>
                        <tr><td colspan="4"></td></tr>

                        @if($activity->oc_id!=0)
                            <tr>
                                <th>Orden de compra:</th>
                                <td colspan="3">
                                    <a href="/oc/{{ $activity->oc_id }}">{{ 'OC-'.str_pad($activity->oc_id, 5, "0", STR_PAD_LEFT) }}</a>
                                </td>
                            </tr>
                        @endif

                        @if($activity->cite_id!=0)
                            <tr>
                                <th>CITE:</th>
                                <td colspan="3">
                                    {{ $activity->cite->title.'-'.str_pad($activity->cite->num_cite, 3, "0", STR_PAD_LEFT).date_format($activity->cite->created_at,'-Y') }}
                                </td>
                            </tr>
                        @endif

                        <tr>
                            <th colspan="4">Archivos de respaldo:</th>
                        </tr>
                        <?php $restantes='0' ?>
                        @foreach($files as $file)
                            @if($file->imageable_id == $activity->id)
                                <tr>
                                    <td colspan="2">{{ $file->description ? $file->description : $file->name }}</td>
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
                            <a href="/files/activity/{{ $activity->id }}" class="btn btn-primary"><i class="fa fa-upload"></i> Subir archivo</a>
                        @endif
                        @if(($user->priv_level==3&&$activity->task->status<>'Conluido')||$user->priv_level==4)
                            <a href="/activity/{{ 'tk-'.$activity->id }}/edit" class="btn btn-success"><i class="fa fa-pencil-square-o"></i> Modificar actividad</a>
                        @endif
                    </div>
            </div>
        </div>
    </div>

@endsection

@section('footer')
@endsection

@section('javascript')
<script>

    $(".comment").shorten({
        "showChars" : 80,
        "moreText"	: "ver más",
        "lessText"	: "ocultar"
    });

</script>
@endsection
