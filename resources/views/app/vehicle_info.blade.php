@extends('layouts.actives_structure')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/image_modal.css") }}">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    <div class="btn-group">
        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
            <i class="fa fa-car"></i> Vehiculos <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li><a href="{{ '/vehicle' }}"><i class="fa fa-bars fa-fw"></i> Ver todo</a></li>
            <li><a href="{{ '/driver' }}"><i class="fa fa-list-ul fa-fw"></i> Ver asignaciones</a></li>
            <li><a href="{{ '/vehicle_requirement' }}"><i class="fa fa-arrow-right fa-fw"></i> Ver requerimientos</a></li>
            @if($user->action->acv_vhc_req /*$user->priv_level>=2*/)
                <li><a href="{{ '/vehicle_requirement/create' }}"><i class="fa fa-plus fa-fw"></i> Nuevo requerimiento </a></li>
            @endif
            @if($user->action->acv_vhc_add /*$user->priv_level>=2||$user->work_type=='Transporte'*/)
                <li><a href="{{ '/vehicle/create' }}"><i class="fa fa-plus fa-fw"></i> Agregar vehículo</a></li>
            @endif
        </ul>
    </div>
    <a href="{{ '/maintenance?vhc=true' }}" class="btn btn-primary"><i class="fa fa-wrench"></i> Vehículos en mantenimiento</a>
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

        <div class="panel panel-violet">
            <div class="panel-heading" align="center">
                <div class="panel-title">Información de vehículo</div>
            </div>
            <div class="panel-body">
                <div class="col-lg-12 mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning">
                        <i class="fa fa-arrow-circle-left"></i> Volver
                    </a>
                    <a href="{{ '/vehicle' }}" class="btn btn-warning" title="Ir a la tabla de vehículos">
                        <i class="fa fa-arrow-circle-up"></i> Vehículos
                    </a>

                    <div class="pull-right">
                        <a href="/history/vehicle/{{ $vehicle->id }}" class="btn btn-primary">
                            <i class="fa fa-list-ul"></i> Historial
                        </a>
                        <a href="{{ '/driver?vhc='.$vehicle->id }}" class="btn btn-primary">
                            <i class="fa fa-list-ol"></i> Asignaciones
                        </a>
                        <a href="{{ '/vehicle_failure_report?vhc='.$vehicle->id }}" class="btn btn-primary">
                            <i class="fa fa-warning"></i> Reportes de falla
                        </a>
                    </div>
                </div>

                {{--
                <div class="col-lg-8" align="right">

                </div>
                --}}

                <div class="col-sm-12 mg10">
                    @include('app.session_flashed_messages', array('opt' => 0))
                </div>

                <div class="col-sm-12 mg10 mg-tp-px-10">
                    <table class="table table-striped table-hover table-bordered">
                        <thead>
                        <tr>
                            <th width="40%">Placa:</th>
                            <td>{{ $vehicle->license_plate }}</td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <th>Tipo</th>
                            <td>{{ $vehicle->type }}</td>
                        </tr>
                        <tr>
                            <th>Modelo</th>
                            <td>{{ $vehicle->model }}</td>
                        </tr>
                        <tr><td colspan="2"> </td></tr>
                        <tr>
                            <th>Propietario</th>
                            <td>{{ $vehicle->owner }}</td>
                        </tr>
                        <tr>
                            <th>Sucursal (asignación)</th>
                            <td>{{ $vehicle->branch_record ? $vehicle->branch_record->name : 'N/E' }}</td>
                        </tr>

                        <tr><td colspan="2"></td></tr>
                        <tr>
                            <th>Kilometraje</th>
                            <td>{{ $vehicle->mileage.' Km' }}</td>
                        </tr>
                        @if($vehicle->gas_type!='')
                            <tr>
                                <th>Tipo de combustible</th>
                                <td>{{ $vehicle->gas_type }}</td>
                            </tr>
                        @endif
                        @if($vehicle->gas_capacity>0)
                            <tr>
                                <th>Capacidad combustible</th>
                                <td>{{ $vehicle->gas_capacity.' Lts' }}</td>
                            </tr>
                        @endif
                        <tr>
                            <th>Estado</th>
                            <td>{{ $vehicle->status }}</td>
                        </tr>
                        <tr>
                            <th>Responsable actual</th>
                            <td>
                                {{ $vehicle->responsible!=0 ? $vehicle->user->name  : 'Sin asignar' }}
                                @if($vehicle->last_driver&&$vehicle->last_driver->confirmation_flags[3]==0)
                                    <i class="fa fa-warning" title="Pendiente de confirmación" style="color: darkred"></i>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Destino actual</th>
                            <td>{{ $vehicle->destination }}</td>
                        </tr>

                        @if($vehicle->condition)
                            <tr><td colspan="2"> </td></tr>
                            <tr>
                                <th colspan="2">Condición actual del vehículo</th>
                            </tr>
                            <tr>
                                <td colspan="2">{{ $vehicle->condition }}</td>
                            </tr>
                        @endif

                        <tr><td colspan="2"></td></tr>
                        <tr>
                            <th colspan="2">
                                Imágenes
                                <div class="pull-right">
                                    @if(($user->action->acv_vhc_edt /*($user->work_type=='Transporte'||$user->priv_level>=3)*/&&
                                        $vehicle->flags!='0000')||$user->priv_level==4)
                                        <a href="/files/vehicle_img/{{ $vehicle->id }}"
                                           title="Subir una imagen del vehículo">
                                            <i class="fa fa-upload"></i> Subir
                                        </a>
                                        &ensp;
                                        @if($vehicle->main_pic_id!=0||$exists_picture)
                                            <a href="/vehicle/change/main_pic_id/{{ $vehicle->id }}"
                                               title="{{ $vehicle->main_pic_id!=0 ?
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

                                @foreach($vehicle->files as $file)
                                    @if($file->type=='jpg'||$file->type=='jpeg'||$file->type=='png')
                                        <img class="myImg" src="/files/thumbnails/{{ 'thumb_'.$file->name }}" height="50"
                                             border="0" alt="{{ $file->description }}" onclick="show_modal(this)">
                                    @endif
                                @endforeach

                                {{ !$exists_picture ? 'No se subieron imágenes de este vehículo' : '' }}

                                <div id="picModal" class="pic_modal">
                                    <span class="pic_close" id="pic_close">&times;</span>
                                    <img class="pic_modal-content" id="pic_modal_content" src="">
                                    <div id="pic_caption"></div>
                                </div>
                            </td>
                        </tr>

                        @if($user->action->acv_vhc_edt /*$user->priv_level>=2||$user->work_type=='Transporte'*/)
                            <tr><td colspan="2"></td></tr>
                            <tr>
                                <th colspan="2">Documentos del vehículo</th>
                            </tr>
                            @foreach($vehicle->files as $file)
                                @if($file->type=='pdf')
                                    @if (strpos($file->name, 'VGI')===false)
                                        <tr>
                                            <td>{{ $file->description }}</td>
                                            <td>
                                                @include('app.info_document_options', array('file'=>$file))
                                                {{--
                                                <a href="/download/{{ $file->id }}" style="text-decoration: none">
                                                    <img src="/imagenes/pdf-icon.png" alt="PDF" />
                                                </a>
                                                &emsp;
                                                <a href="/display_file/{{ $file->id }}" title="Abrir archivo">Ver</a>
                                                &emsp;
                                                {{--<a href="/files/replace/{{ $file->id }}">Reemplazar archivo</a>
                                                <a href="/file/{{ $file->id }}" title="Información de archivo">Detalles</a>
                                                --}}
                                            </td>
                                        </tr>
                                    @endif
                                @endif
                            @endforeach
                            @if($vehicle->flags!='0000'||$user->priv_level==4)
                                <tr>
                                    <td colspan="2" align="center">
                                        <a href="/files/vehicle_file/{{ $vehicle->id }}">
                                            <i class="fa fa-upload"></i> Subir documento
                                        </a>
                                    </td>
                                </tr>
                            @endif
                        @endif

                        @if($vehicle->gas_type=='gnv' && ($user->priv_level>=2 || $user->work_type=='Transporte' || $user->work_type=='Director Regional'))
                            @if($vehicle->vhc_gas_inspection)
                                <tr>
                                    <td>{{ $vehicle->vhc_gas_inspection->description }}</td>
                                    <td>
                                        @include('app.info_document_options', array('file'=>$vehicle->vhc_gas_inspection))
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" align="right">
                                        {{ 'Vence el '.date_format($vehicle->gas_inspection_exp, 'd-m-Y') }}
                                        &emsp;
                                        <a href="/files/replace/{{ $file->id }}">
                                            <i class="fa fa-refresh"></i> Renovar
                                        </a>
                                    </td>
                                </tr>
                            @else
                                @if($vehicle->flags!='0000'||$user->priv_level==4)
                                    <tr>
                                        <td colspan="2" align="center">
                                            <a href="/files/vhc_gas_inspection/{{ $vehicle->id }}">
                                                <i class="fa fa-upload"></i> Subir documento de inspección
                                            </a>
                                        </td>
                                    </tr>
                                @endif
                            @endif
                        @endif

                        @if($user->priv_level>=3||$user->area=='Gerencia General')
                            <tr>
                                @if($vehicle->policy)
                                    <td>Poliza</td>
                                    <td>
                                        <a href="/guarantee/{{ $vehicle->policy->id }}" title="Ver información de poliza">
                                            {{ $vehicle->policy->code }}
                                        </a>
                                        &emsp;
                                        <a href="/vehicle/link/policy/{{ $vehicle->id }}" title="Modificar">
                                            <i class="fa fa-pencil-square"></i>
                                        </a>
                                    </td>
                                @else
                                    @if($vehicle->flags!='0000'||$user->priv_level==4)
                                        <td colspan="2" align="center">
                                            <a href="/vehicle/link/policy/{{ $vehicle->id }}">
                                                <i class="fa fa-link"></i> Enlazar poliza
                                            </a>
                                        </td>
                                    @endif
                                @endif
                            </tr>
                        @endif

                        </tbody>
                    </table>
                </div>
                @if(($user->action->acv_vhc_edt /*($user->work_type=='Transporte'||$user->priv_level>=2)*/&&
                    $vehicle->flags!='0000')||$user->priv_level==4)
                    <div class="col-sm-12 mg10" align="center">
                        <a href="/vehicle/{{ $vehicle->id }}/edit" class="btn btn-success">
                            <i class="fa fa-pencil-square-o"></i> Modificar / Actualizar datos
                        </a>

                        @if($user->work_type=='Transporte' || $user->work_type=='Director Regional' || $user->priv_level==4)
                            <a href="{{ '/vehicle/disable?vhc_id='.$vehicle->id }}" class="btn btn-danger"
                                onclick="return confirm('Está seguro de que desea dar de baja este vehículo? ' +
                                 'Una vez dado de baja el vehículo ya no podrá modificarlo')"
                                title="Dar de baja este vehículo (Bloquear modificaciones en este registro)">
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
    <script src="{{ asset('app/js/set_current_url.js') }}"></script> {{-- For recording current url --}}
    <script>
        $('#alert').delay(2000).fadeOut('slow');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        /* Old code to adapt to current system
        var modal = document.getElementById('myModal');
        // Get the image and insert it inside the modal - use its "alt" text as a caption
        var modalImg = document.getElementById("modal_content");
        var captionText = document.getElementById("caption");
        function show_modal(element){
        modal.style.display = "block";
        modalImg.src = element.src;
        captionText.innerHTML = element.alt;
        }
        // Get the <span> element that closes the modal
            var span = document.getElementsByClassName("close")[0];
            // When the user clicks on <span> (x), close the modal
            span.onclick = function() {
                modal.style.display = "none";
            }
        */

        var modal = document.getElementById('picModal');
        // Get the image and insert it inside the modal - use its "alt" text as a caption
        var modalImg = document.getElementById("pic_modal_content");
        var captionText = document.getElementById("pic_caption");
        function show_modal(element){
            var fullSizedSource = element.src.replace('thumbnails/thumb_', '');

            modal.style.display = "block";
            modalImg.src = fullSizedSource;
            captionText.innerHTML = element.alt;
        }
        // Get the <span> element that closes the modal
        var span = document.getElementById("pic_close");
        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = "none";
        }
    </script>
@endsection
