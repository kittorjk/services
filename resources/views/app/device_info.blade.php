@extends('layouts.actives_structure')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/image_modal.css") }}">
    <style>
        .modal-dialog{
            width: 60%;
            max-height: 80%;
        }
    </style>
@endsection

@section('menu_options')
    <div class="btn-group">
        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
            <i class="fa fa-laptop"></i> Equipos <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li><a href="{{ '/device' }}"><i class="fa fa-refresh"></i> Ver equipos </a></li>
            <li><a href="{{ '/operator' }}"><i class="fa fa-arrow-right"></i> Ver asignaciones </a></li>
            <li><a href="{{ '/device_requirement' }}"><i class="fa fa-arrow-right"></i> Ver requerimientos</a></li>
            @if($user->action->acv_dvc_req /*$user->priv_level>=2*/)
                <li><a href="{{ '/device_requirement/create' }}"><i class="fa fa-plus"></i> Nuevo requerimiento </a></li>
            @endif
            @if($user->action->acv_dvc_add /*$user->work_type=='Almacén'||$user->priv_level>=3*/)
                <li><a href="{{ '/device/create' }}"><i class="fa fa-plus"></i> Agregar equipo </a></li>
            @endif
        </ul>
    </div>
    <a href="{{ '/calibration' }}" class="btn btn-primary"><i class="fa fa-wrench"></i> Calibraciones</a>
    <a href="{{ '/maintenance?dvc=true' }}" class="btn btn-primary"><i class="fa fa-wrench"></i> Equipos en mantenimiento</a>
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

        <div class="panel panel-brown">
            <div class="panel-heading" align="center">
                <div class="panel-title">Información de equipo</div>
            </div>
            <div class="panel-body">
                <div class="col-lg-12 mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning">
                        <i class="fa fa-arrow-circle-left"></i> Volver
                    </a>
                    <a href="{{ '/device' }}" class="btn btn-warning" title="Ir a la tabla de equipos">
                        <i class="fa fa-arrow-circle-up"></i> Equipos
                    </a>

                    <div class="pull-right">
                        <a href="/history/device/{{ $device->id }}" class="btn btn-primary">
                            <i class="fa fa-file-text-o"></i> Historial
                        </a>
                        <a href="{{ '/operator?dvc='.$device->id }}" class="btn btn-primary">
                            <i class="fa fa-list-ol"></i> Asignaciones
                        </a>
                        <a href="{{ '/device_failure_report?dvc='.$device->id }}" class="btn btn-primary">
                            <i class="fa fa-warning"></i> Reportes de falla
                        </a>
                    </div>
                </div>

                <div class="col-sm-12 mg10">
                    @include('app.session_flashed_messages', array('opt' => 0))
                </div>

                <div class="col-sm-12 mg10 mg-tp-px-10">

                    <table class="table table-striped table-hover table-bordered">
                        <thead>
                        <tr>
                            <th width="40%">Número de serie:</th>
                            <td>{{ $device->serial }}</td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <th>Tipo de equipo</th>
                            <td>{{ $device->type }}</td>
                        </tr>
                        <tr>
                            <th>Modelo</th>
                            <td>{{ $device->model }}</td>
                        </tr>
                        <tr><td colspan="2"></td></tr>

                        <tr>
                            <th>Propietario</th>
                            <td>{{ $device->owner }}</td>
                        </tr>
                        <tr>
                            <th>Sucursal (asignación)</th>
                            <td>{{ $device->branch_record ? $device->branch_record->name : 'N/E' }}</td>
                        </tr>
                        @if($device->value!=0)
                            <tr>
                                <th>Valor</th>
                                <td>{{ $device->value.' Bs' }}</td>
                            </tr>
                        @endif
                        <tr><td colspan="2"></td></tr>

                        <tr>
                            <th>Estado</th>
                            <td>{{ $device->status }}</td>
                        </tr>
                        <tr>
                            <th>Responsable actual</th>
                            <td>
                                {{ $device->responsible!=0 ? $device->user->name  : 'Sin asignar' }}
                                @if($device->last_operator&&$device->last_operator->confirmation_flags[3]==0)
                                    <i class="fa fa-warning" title="Pendiente de confirmación" style="color: darkred"></i>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Destino actual</th>
                            <td>{{ $device->destination }}</td>
                        </tr>

                        @if($device->condition)
                            <tr><td colspan="2"></td></tr>
                            <tr>
                                <th colspan="2">Condición actual del equipo</th>
                            </tr>
                            <tr>
                                <td colspan="2">{{ $device->condition }}</td>
                            </tr>
                        @endif

                        <tr><td colspan="2"></td></tr>
                        <tr>
                            <th colspan="2">
                                Imágenes
                                <div class="pull-right">
                                    @if(($user->action->acv_dvc_edt /*($user->work_type=='Almacén'||$user->priv_level>=3)*/&&
                                        $device->flags!='0000')||$user->priv_level==4)
                                        <a href="/files/device_img/{{ $device->id }}" title="Subir una imagen del equipo">
                                            <i class="fa fa-upload"></i> Subir
                                        </a>
                                        &ensp;
                                        @if($device->main_pic_id!=0||$exists_picture)
                                            <a href="/device/change/main_pic_id/{{ $device->id }}"
                                               title="{{ $device->main_pic_id!=0 ?
                                               'Cambiar imagen principal (visible en página resumen)' :
                                               'Seleccionar imagen principal (visible en página resumen)' }}">
                                                <i class="fa fa-refresh"></i> Cambiar
                                            </a>
                                        @endif
                                    @endif
                                </div>
                            </th>
                        </tr>
                        <tr>
                            <td colspan="2" align="center">
                                @foreach($device->files as $file)
                                    @if($file->type=='jpg'||$file->type=='jpeg'||$file->type=='png')

                                        <img src="/files/thumbnails/{{ 'thumb_'.$file->name }}" style="height: 60px;" class="pop"
                                             alt="{{ $file->description }}">

                                        {{--
                                        <img class="myImg" src="/files/{{ $file->name }}" height="60" border="0"
                                             alt="{{ $file->description }}" onclick="show_modal(this)">
                                        --}}
                                    @endif
                                @endforeach

                                {{ !$exists_picture ? 'No se subieron imágenes de este equipo' : '' }}

                                {{--
                                <div id="picModal" class="pic_modal">
                                    <span class="pic_close" id="pic_close">&times;</span>
                                    <img class="pic_modal-content" id="pic_modal_content">
                                    <div id="pic_caption"></div>
                                </div>
                                --}}

                                <div class="modal fade" id="imagemodal" tabindex="-1" role="dialog"
                                     aria-labelledby="myModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-body">
                                                <button type="button" class="close" data-dismiss="modal">
                                                    <span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
                                                </button>
                                                <img src="" class="imagepreview" style="height: 90%; max-width: 100%">
                                            </div>
                                            <div class="modal-footer captioned">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        @if($user->action->acv_dvc_edt /*$user->work_type=='Almacén'||$user->priv_level>=3*/)
                            <tr><td colspan="2"></td></tr>
                            <tr>
                                <th colspan="2">Documentos del equipo</th>
                            </tr>
                            @foreach($device->files as $file)
                                @if($file->type=='pdf')
                                    <tr>
                                        <td>{{ $file->description }}</td>
                                        <td>
                                            @include('app.info_document_options', array('file'=>$file))
                                            {{--
                                            <a href="/download/{{ $file->id }}" style="text-decoration: none">
                                                <img src="/imagenes/pdf-icon.png" alt="PDF" />
                                            </a>
                                            <a href="/display_file/{{ $file->id }}">Ver</a>
                                            {{ ' - ' }}
                                            <a href="/files/replace/{{ $file->id }}">Reemplazar archivo</a>
                                            --}}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                            @if($device->flags!='0000'||$user->priv_level==4)
                                <tr>
                                    <td colspan="2" align="center">
                                        <a href="/files/device_file/{{ $device->id }}">
                                            <i class="fa fa-upload"></i> Subir documento
                                        </a>
                                    </td>
                                </tr>
                            @endif
                        @endif

                        </tbody>
                    </table>
                </div>

                @if(($user->action->acv_dvc_edt /*($user->work_type=='Almacén'||$user->priv_level>=2)*/&&
                    $device->flags!='0000')||$user->priv_level==4)
                    <div class="col-sm-12 mg10" align="center">
                        <a href="/device/{{ $device->id }}/edit" class="btn btn-success">
                            <i class="fa fa-pencil-square-o"></i> Modificar registro
                        </a>

                        @if($user->work_type=='Almacén'||$user->priv_level==4)
                            <a href="{{ '/device/disable?dvc_id='.$device->id }}" class="btn btn-danger"
                               onclick="return confirm('Está seguro de que desea dar de baja este equipo? ' +
                                 'Una vez dado de baja el equipo ya no podrá modificarlo')"
                               title="Dar de baja este equipo (Bloquear modificaciones en este registro)">
                                <i class="fa fa-ban"></i> Dar de baja
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection

@section('footer')
@endsection

@section('javascript')
    <script>
        /*
        var modal = document.getElementById('picModal');
        // Get the image and insert it inside the modal - use its "alt" text as a caption
        var modalImg = document.getElementById("pic_modal_content");
        var captionText = document.getElementById("pic_caption");
        function show_modal(element){
            modal.style.display = "block";
            modalImg.src = element.src;
            captionText.innerHTML = element.alt;
        }
        // Get the <span> element that closes the modal
        //var span = document.getElementsByClassName("close")[0];
        var span = document.getElementById("pic_close");
        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = "none";
        };
        */

        $(function() {
            $('.pop').on('click', function() {
                var fullSizedSource = $(this).attr('src').replace('thumbnails/thumb_', '');

                $('.imagepreview').attr('src', fullSizedSource /*$(this).find('img').attr('src')*/);
                $('.captioned').html($(this).find('img').attr('alt'));
                $('#imagemodal').modal('show');
            });
        });
    </script>
@endsection
