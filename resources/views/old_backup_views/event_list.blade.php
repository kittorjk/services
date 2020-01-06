@extends('layouts.master')

@section('header')
    @parent
@endsection

@section('content')
    <div class="col-sm-12 mg20 mg-tp-px-10">
        <div class="row">
            <div class="col-sm-8">
                @if($user->priv_level==4)
                    <a href="/" class="btn btn-primary"><i class="fa fa-home"></i> Inicio </a>
                @endif
                <a href="/event/{{ $project_info->id }}" class="btn btn-primary"><i class="fa fa-arrow-circle-left"></i> Volver </a>
                <div class="btn-group">
                    <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle"><i class="fa fa-thumb-tack"></i> Eventos <span class="caret"></span></button>
                    <ul class="dropdown-menu dropdown-menu-prim">
                        <li><a href="/event/{{ $project_info->id }}/{{ str_replace(" ", "_", $site_name) }}"><i class="fa fa-bars"></i> Resumen </a></li>
                        @if($project_info->status<=10)
                        <li><a href="/event/{{ $project_info->id }}/{{ str_replace(" ", "_", $site_name) }}/create"><i class="fa fa-plus"></i> Agregar evento </a></li>
                        @endif
                        @if($user->priv_level>=3)
                            <li><a href="/delete/event"><i class="fa fa-trash-o"></i> Borrar un archivo </a></li>
                        @endif
                    </ul>
                </div>
                @if($user->priv_level>=2)
                    <a href="/search/projects" class="btn btn-primary"><i class="fa fa-search"></i> Buscar </a>
                @endif
            </div>
            <div class="col-sm-4" align="right">
                <a href="/user/{{ $user->id }}/edit" class="btn btn-warning"><i class="fa fa-pencil-square-o"></i> Actualizar cuenta </a>
                <div class="btn-group">
                    <button type="button" data-toggle="dropdown" class="btn btn-danger dropdown-toggle"><i class="fa fa-user"></i> Mi cuenta <span class="caret"></span></button>
                    <ul class="dropdown-menu dropdown-menu-right dropdown-menu-dang">
                        <li><a href="/user/{{ $user->id }}/edit"><i class="fa fa-pencil-square-o"></i> Actualizar datos </a></li>
                        <li class="divider"></li>
                        <li><a href="/logout/{{ $service }}"><i class="fa fa-sign-out"></i> Cerrar sesión</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-12 mg10 mg-tp-px-10">
        @if (Session::has('message'))
            <div class="alert alert-info" align="center" id="alert">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>{{ Session::get('message') }}
            </div>
        @endif
    </div>

        <div class="col-sm-12 mg10 mg-tp-px-10">
            @if($events->total()==1)
                <p>Se encontró {{ $events->total() }} evento</p>
            @else
                <p>Se encontraron {{ $events->total() }} eventos</p>
            @endif
            <table class="fancy_table table_blue">
                <thead>
                <tr>
                    <th>Código: {{ 'PR-'.str_pad($project_info->id, 4, "0", STR_PAD_LEFT).date_format($project_info->created_at,'-y') }}</th>
                    <th colspan="2">Proyecto: {{ $project_info->name }}</th>
                    <th>{{ 'Sitio: '.$site_name }}</th>
                </tr>
                <tr>
                    <th width="20%">#</th>
                    <th>Fecha</th>
                    <th>Evento</th>
                    <th width="20%"></th>
                </tr>
                </thead>
                <tbody>
                @foreach ($events as $event)
                    <tr>
                        <td>{{ $event->event_number }}</td>
                        <td>{{ date_format(new \DateTime($event->event_date), 'd-m-Y') }}</td>
                        <td>{{ $event->brief_description }}</td>
                        <td>
                            <a href="/event/details/{{ $event->id }}">{{ 'Ver en una vista aparte' }}</a>
                            <a data-toggle="collapse" data-parent="#accordion" href="{{ '#collapse'.$event->event_number }}"><i class="indicator glyphicon glyphicon-chevron-down pull-right"></i></a>
                        </td>
                    </tr>
                        <tr>
                            <td colspan="4" style="padding: 0">
                                <div id="{{ 'collapse'.$event->event_number }}" class="panel-collapse collapse mg-tp-px-10 col-sm-12">

                                    <table class="table table_sky" style=":tr td hover{background: transparent}">
                                        <tr>
                                            <th>Detalle del evento:</th>
                                            <th width="25%">Archivos de respaldo:</th>
                                        </tr>
                                        <tr>
                                            <td rowspan="8">{{ $event->detailed_description }}</td>
                                            <td style="text-align: center">
                                                <?php $restantes='0' ?>
                                                @foreach($files as $file)
                                                    @if($file->imageable_id == $event->id)
                                                        <a href="/download/{{ $file->id }}" style="text-decoration:none">
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
                                                    @endif
                                                @endforeach
                                                @if($restantes<5)
                                                    <a href="/files/event/{{ $event->id }}">Subir archivo</a>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Responsable por parte del Cliente:</th>
                                        </tr>
                                        <tr>
                                            <td>
                                                {{ $event->resp_client }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Responsable por parte de ABROS:</th>
                                        </tr>
                                        <tr>
                                            <td>
                                                {{ $event->resp_abr }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Creado por:</th>
                                        </tr>
                                        <tr>
                                            <td>
                                                @foreach($user_names as $user_name)
                                                    @if($user_name->id==$event->user_id)
                                                        {{ $user_name->name }}
                                                    @endif
                                                @endforeach
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: right">
                                                @if($user->priv_level>=3)
                                                <a href="/event/{{ $event->id }}/edit" class="btn btn-success"><i class="fa fa-pencil-square-o"></i> Modificar </a>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>

                                </div>
                            </td>
                        </tr>
                @endforeach
                </tbody>
            </table>

        </div>
        <div class="col-sm-12 mg10" align="center">
            {!! $events->render() !!}
        </div>

@endsection

@section('footer')
    @parent
@endsection

@section('javascript')
    <script>
        $('#alert').delay(2000).fadeOut('slow');
        $('.collapse').on('show.bs.collapse', function () {
            $('.collapse.in').collapse('hide');
        });
        $('.indicator').on('click', function() {
            $(this).toggleClass('glyphicon-chevron-down glyphicon-chevron-up');
        });
    </script>
@endsection
