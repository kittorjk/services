@extends('layouts.info_master')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/image_modal.css") }}">
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

        <div class="panel panel-violet">
            <div class="panel-heading" align="center">
                <div class="panel-title">Información de asignación de vehículo</div>
            </div>
            <div class="panel-body">
                <div class="col-sm-12 mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning">
                        <i class="fa fa-arrow-circle-left"></i> Volver
                    </a>
                    <a href="{{ '/driver' }}" class="btn btn-warning" title="Ir a la tabla de asignaciones de vehículo">
                        <i class="fa fa-arrow-circle-up"></i> Asignaciones
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <div class="col-sm-12 mg10 mg-tp-px-10">

                    <table class="table table-striped table-hover table-bordered">
                        <thead>
                        @if($driver->requirement)
                            <tr>
                                <th>Requerimiento:</th>
                                <td>
                                    <a href="{{ '/vehicle_requirement/'.$driver->requirement->id }}">
                                        {{ $driver->requirement->code }}
                                    </a>
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <th width="40%">Vehículo</th>
                            <td>
                                {{ $driver->vehicle->type.' '.$driver->vehicle->model }}
                            </td>
                        </tr>
                        <tr>
                            <th>Placa</th>
                            <td>
                                <a href="{{ '/vehicle/'.$driver->vehicle->id }}">
                                    {{ $driver->vehicle->license_plate }}
                                </a>
                            </td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr><td colspan="2"></td></tr>

                        <tr>
                            <th>Entregado por:</th>
                            <td>{{ $driver->deliverer->name }}</td>
                        </tr>
                        <tr>
                            <th>Entregado a:</th>
                            <td>{{ $driver->receiver->name }}</td>
                        </tr>
                        <tr>
                            <th>Fecha:</th>
                            <td>{{ $driver->date }}</td>
                        </tr>
                        <tr>
                            <th>Destino:</th>
                            <td>{{ $driver->destination }}</td>
                        </tr>
                        <tr>
                            <th>Kilometraje de entrega</th>
                            <td>{{ $driver->mileage_before.' Km' }}</td>
                        </tr>
                        @if($driver->mileage_traveled!=0&&$driver->mileage_after!=0)
                            <tr>
                                <th>Kilometraje final</th>
                                <td>{{ $driver->mileage_after.' Km' }}</td>
                            </tr>
                            <tr>
                                <th>Kilómetros recorridos</th>
                                <td>{{ $driver->mileage_traveled.' Km' }}</td>
                            </tr>
                        @endif

                        @if($driver->reason)
                            <tr><td colspan="2"></td></tr>
                            <tr>
                                <th colspan="2">Motivo:</th>
                            </tr>
                            <tr>
                                <td colspan="2">{{ $driver->reason }}</td>
                            </tr>
                        @endif

                        @if($driver->observations)
                            <tr><td colspan="2"> </td></tr>
                            <tr>
                                <th colspan="2">Observaciones</th>
                            </tr>
                            <tr>
                                <td colspan="2">{{ $driver->observations }}</td>
                            </tr>
                        @endif

                        @if($driver->confirmation_flags[3]==1&&$driver->date_confirmed!='0000-00-00 00:00:00')
                            <tr><td colspan="2"></td></tr>
                            <tr>
                                <th>Fecha de confirmación</th>
                                <td>{{ $driver->date_confirmed }}</td>
                            </tr>
                            @if($driver->confirmation_obs)
                                <tr>
                                    <th colspan="2">Obs. de recepción</th>
                                </tr>
                                <tr>
                                    <td colspan="2">{{ $driver->confirmation_obs }}</td>
                                </tr>
                            @endif
                        @endif

                        <tr><td colspan="2"></td></tr>
                        <tr>
                            <th>Recibo firmado</th>
                            <td>
                                @foreach($driver->files as $file)
                                    @if($file->type=="pdf")
                                        @include('app.info_document_options', array('file'=>$file))
                                        {{--
                                        <a href="/download/{{ $file->id }}">
                                            <img src="{{ '/imagenes/pdf-icon.png' }}" alt="PDF" />
                                        </a>
                                        <a href="/file/{{ $file->id }}">Detalles</a>
                                        &emsp;
                                        <a href="/display_file/{{ $file->id }}">Ver</a>
                                        --}}
                                    @endif
                                @endforeach
                                @if($driver->files()->where('type', 'pdf')->count()==0)
                                    <a href="/files/driver_receipt/{{ $driver->id }}">
                                        <i class="fa fa-upload"></i> Recibo firmado
                                    </a>
                                @endif
                            </td>
                        </tr>

                        <tr><td colspan="2"></td></tr>
                        <tr>
                            <th colspan="2">
                                Imagenes de respaldo
                                @if($user->id==$driver->who_delivers||$user->id==$driver->who_receives||
                                    $user->priv_level==4)
                                    <a href="/files/driver/{{ $driver->id }}" class="pull-right">
                                        <i class="fa fa-upload"></i> Subir
                                    </a>
                                @endif
                            </th>
                        </tr>
                        <tr>
                            <td colspan="2" align="center">
                                <?php $exist_pictures = false; ?>
                                @foreach($driver->files as $file)
                                    @if($file->type!='pdf')
                                        <img class="myImg" src="/files/thumbnails/{{ 'thumb_'.$file->name }}" height="50"
                                             border="0" alt="{{ $file->description }}" onclick="show_modal(this)">
                                        <?php $exist_pictures = true; ?>
                                    @endif
                                @endforeach

                                {{ !$exist_pictures ? 'No se subieron imágenes de ésta asignación' : '' }}

                                <div id="picModal" class="pic_modal">
                                    <span class="pic_close" id="pic_close">&times;</span>
                                    <img class="pic_modal-content" id="pic_modal_content" src="">
                                    <div id="pic_caption"></div>
                                </div>
                                {{--
                                <div id="myModal" class="modal">
                                    <span class="close">&times;</span>
                                    <img class="modal-content" id="modal_content">
                                    <div id="caption"></div>
                                </div>
                                --}}
                            </td>
                        </tr>

                        <tr><td colspan="2"></td></tr>
                        <tr>
                            <th>Registro creado por:</th>
                            <td>{{ $driver->user ? $driver->user->name : 'N/E' }}</td>
                        </tr>

                        </tbody>
                    </table>
                </div>

                @if($user->action->acv_vhc_edt /*$user->priv_level>=3*/)
                    <div class="col-sm-12 mg10" align="center">
                        <a href="/driver/{{ $driver->id }}/edit" class="btn btn-success">
                            <i class="fa fa-pencil-square-o"></i> Modificar datos
                        </a>
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
        /* Old sample code to adapt
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
