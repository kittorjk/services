@extends('layouts.actives_structure')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/image_modal.css") }}">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    <div class="btn-group">
        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
            <i class="fa fa-exchange"></i> Asignación de equipos <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li><a href="{{ '/operator' }}"><i class="fa fa-refresh"></i> Ver asignaciones </a></li>
            <li>
                <a href="{{ '/operator?conf=pending' }}"><i class="fa fa-hourglass-2 fa-fw"></i> Ver asignaciones sin confirmar</a>
            </li>
            <li><a href="{{ '/device' }}"><i class="fa fa-arrow-right"></i> Ver equipos </a></li>
            <li><a href="{{ '/device_requirement' }}"><i class="fa fa-arrow-right"></i> Ver requerimientos</a></li>
            @if($user->action->acv_dvc_req /*$user->priv_level>=2*/)
                <li><a href="{{ '/device_requirement/create' }}"><i class="fa fa-plus"></i> Nuevo requerimiento </a></li>
            @endif
        </ul>
    </div>
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

        <div class="panel panel-brown">
            <div class="panel-heading" align="center">
                <div class="panel-title">Detalle de asignación de equipo</div>
            </div>
            <div class="panel-body">
                <div class="col-sm-12 mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning">
                        <i class="fa fa-arrow-circle-left"></i> Volver
                    </a>
                    <a href="{{ '/operator' }}" class="btn btn-warning" title="Ir a la tabla de asignaciones de equipo">
                        <i class="fa fa-arrow-circle-up"></i> Asignaciones
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <div class="col-sm-12 mg10 mg-tp-px-10">

                    <table class="table table-striped table-hover table-bordered">
                        <thead>
                        @if($operator->requirement)
                            <tr>
                                <th>Requerimiento:</th>
                                <td>{{ $operator->requirement->code }}</td>
                            </tr>
                        @endif
                        <tr>
                            <th width="40%">Equipo:</th>
                            <td>{{ $operator->device->type.' '.$operator->device->model }}</td>
                        </tr>
                        <tr>
                            <th>Número de serie:</th>
                            <td>{{ $operator->device->serial }}</td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr><td colspan="2"> </td></tr>
                        <tr>
                            <th>Entregado por:</th>
                            <td>{{ $operator->deliverer->name }}</td>
                        </tr>
                        <tr>
                            <th>Entregado a:</th>
                            <td>{{ $operator->receiver->name }}</td>
                        </tr>
                        <tr>
                            <th>Fecha:</th>
                            <td>{{ $operator->date }}</td>
                        </tr>
                        @if($operator->destination!='')
                            <tr>
                                <th>Destino:</th>
                                <td>{{ $operator->destination }}</td>
                            </tr>
                        @endif

                        @if($operator->reason)
                            <tr><td colspan="2"></td></tr>
                            <tr>
                                <th colspan="2">Observaciones de entrega</th>
                            </tr>
                            <tr>
                                <td colspan="2">{{ $operator->reason }}</td>
                            </tr>
                        @endif

                        @if($operator->observations)
                            <tr><td colspan="2"></td></tr>
                            <tr>
                                <th colspan="2">Observaciones del equipo</th>
                            </tr>
                            <tr>
                                <td colspan="2">{{ $operator->observations }}</td>
                            </tr>
                        @endif

                        @if($operator->confirmation_flags[3]==1&&$operator->date_confirmed!='0000-00-00 00:00:00')
                            <tr><td colspan="2"></td></tr>
                            <tr>
                                <th>Fecha de confirmación</th>
                                <td>{{ $operator->date_confirmed }}</td>
                            </tr>
                            <tr>
                                <th colspan="2">Obs. de recepción</th>
                            </tr>
                            <tr>
                                <td colspan="2">{{ $operator->confirmation_obs }}</td>
                            </tr>
                        @endif

                        <tr><td colspan="2"></td></tr>
                        <tr>
                            <th>Recibo firmado</th>
                            <td>
                                @foreach($operator->files as $file)
                                    @if($file->type=="pdf")
                                        @include('app.info_document_options', array('file'=>$file))
                                        {{--
                                            <a href="/download/{{ $file->id }}">
                                                <img src="/imagenes/pdf-icon.png" alt="PDF" />
                                            </a>
                                            <a href="/file/{{ $file->id }}">Detalles</a>
                                            &emsp;
                                            <a href="/display_file/{{ $file->id }}">Ver</a>
                                        --}}
                                    @endif
                                @endforeach
                                @if($operator->files()->where('type', 'pdf')->count()==0)
                                    <a href="/files/operator_receipt/{{ $operator->id }}">
                                        <i class="fa fa-upload"></i> Recibo firmado
                                    </a>
                                @endif
                            </td>
                        </tr>

                        <tr><td colspan="2"></td></tr>
                        <tr>
                            <th colspan="2">
                                Imágenes de respaldo
                                @if(($operator->confirmation_flags[3]==0&&($user->id==$operator->who_delivers||
                                    $user->id==$operator->who_receives))||$user->priv_level==4)
                                    <a href="/files/operator/{{ $operator->id }}" class="pull-right">
                                        <i class="fa fa-upload"></i> Subir
                                    </a>
                                @endif
                            </th>
                        </tr>
                        <tr>
                            <td colspan="2" align="center">
                                <?php $exist_pictures = false; ?>
                                @foreach($operator->files as $file)
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
                            </td>
                        </tr>

                        <tr><td colspan="2"></td></tr>
                        <tr>
                            <th>Registro creado por</th>
                            <td>{{ $operator->user ? $operator->user->name : 'N/E' }}</td>
                        </tr>

                        </tbody>
                    </table>
                </div>
                @if($user->priv_level==4)
                    <div class="col-sm-12 mg10" align="center">
                        <a href="/operator/{{ $operator->id }}/edit" class="btn btn-success">
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
    <script src="{{ asset('app/js/set_current_url.js') }}"></script> {{-- For recording current url --}}
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#alert').delay(2000).fadeOut('slow');
        
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
