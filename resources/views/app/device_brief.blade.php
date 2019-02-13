@extends('layouts.actives_structure')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/image_modal.css") }}">
    <style>
        .dropdown-menu-prim > li > a {
            width: 200px;
        }
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    <div class="btn-group">
        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
            <i class="fa fa-laptop"></i> Equipos <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li><a href="{{ '/device' }}"><i class="fa fa-refresh"></i> Recargar página </a></li>
            <li><a href="{{ '/operator' }}"><i class="fa fa-arrow-right"></i> Ver asignaciones </a></li>
            <li><a href="{{ '/device_requirement' }}"><i class="fa fa-arrow-right"></i> Ver requerimientos</a></li>
            @if($user->action->acv_dvc_req /*$user->priv_level>=2*/)
                <li><a href="{{ '/device_requirement/create' }}"><i class="fa fa-plus"></i> Nuevo requerimiento </a></li>
            @endif
            @if($user->action->acv_dvc_add /*$user->work_type=='Almacén'||$user->priv_level>=3*/)
                {{--<li><a href="{{ '/operator/create' }}"><i class="fa fa-exchange"></i> Asignar equipo </a></li>--}}
                <li><a href="{{ '/device/create' }}"><i class="fa fa-plus"></i> Agregar equipo </a></li>
            @endif
            @if($user->action->acv_dvc_exp /*$user->priv_level>=3||$user->work_type=='Almacén'*/)
                <li class="divider"></li>
                <li><a href="{{ '/excel/devices' }}"><i class="fa fa-file-excel-o"></i> Exportar a Excel </a></li>
            @endif
        </ul>
    </div>
    <a href="{{ '/calibration' }}" class="btn btn-primary"><i class="fa fa-wrench"></i> Calibraciones</a>
    <a href="{{ '/maintenance?dvc=true' }}" class="btn btn-primary"><i class="fa fa-wrench"></i> Equipos en mantenimiento</a>
    @if($user->priv_level>=2||$user->work_type=='Almacén')
        <!--<a href="/search/devices/0" class="btn btn-primary"><i class="fa fa-search"></i> Buscar </a>-->
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchBox">
            <i class="fa fa-search"></i> Buscar
        </button>
    @endif
@endsection

@section('content')
    <div class="col-sm-12 mg10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10 mg-tp-px-10">
        <p>Equipos registrados: {{ $devices->total() }}</p>

        <table class="fancy_table table_brown tablesorter" id="fixable_table">
            <thead>
            <tr>
                <th>Foto</th>
                <th># Serie</th>
                <th>Tipo</th>
                <th width="20%">Modelo</th>
                <th width="20%">Condiciones</th>
                <th>Responsable actual</th>
                <th>Asignación</th>
                <th width="22%">Estado</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($devices as $device)
                <tr>
                    <td align="center">
                        @if($device->main_pic_id!=0 && $device->main_pic)
                            <img class="myImg" src="/files/thumbnails/{{ 'thumb_'.$device->main_pic->name }}" height="50"
                                 border="0" alt="{{ $device->main_pic->description }}" onclick="show_modal(this)">
                        @endif
                        {{--
                        @foreach($device->files as $key => $file)
                            @if ($key==0)
                                <img class="myImg" src="/files/{{ $file->name }}" height="50" border="0"
                                    alt="{{ $file->description }}" onclick="show_modal(this)">
                                $count_images++
                            @endif
                        @endforeach
                        --}}
                        @if($device->main_pic_id==0&&$user->action->acv_dvc_edt /*($user->work_type=='Almacén'||$user->priv_level>=3)*/)
                            <a href="/files/device_img/{{ $device->id }}"><i class="fa fa-upload"></i> Subir foto</a>
                        @endif
                    </td>
                    <td>
                        <a href="/device/{{ $device->id }}" title="Ver información de equipo">{{ $device->serial }}</a>
                        @if($user->action->acv_dvc_edt /*$user->priv_level==4*/)
                            <a href="/device/{{ $device->id }}/edit" title="Actualizar información de equipo">
                                <i class="fa fa-pencil-square"></i>
                            </a>
                        @endif
                    </td>
                    <td>{{ $device->type }}</td>
                    <td>
                        {{ $device->model }}
                        {{-- old button for opening modal changed to <a>
                        <button type="button" class="pull-right" title="Características" data-toggle="modal"
                                data-target="{{ '#characteristicsBox'.$device->id }}">
                            <i class="fa fa-bars"></i>
                        </button>
                        --}}
                        @if($user->priv_level==4)
                            <a href="/characteristics/device/{{ $device->id }}" title="Abrir Características">
                                <i class="fa fa-window-maximize pull-right"></i>
                            </a>
                        @endif
                        <a href="#" title="Características" data-toggle="modal"
                                data-target="{{ '#characteristicsBox'.$device->id }}">
                            <i class="fa fa-list pull-right"></i>
                        </a>
                        <!-- Characteristics Modal -->
                        <div id="{{ 'characteristicsBox'.$device->id }}" class="modal fade" role="dialog">
                            @include('app.device_characteristics_modal',
                                array('user'=>$user,'service'=>$service,'device_info'=>$device,
                                    'characteristics'=>$device->characteristics))
                        </div>
                    </td>
                    <td>
                        {{ $device->condition }}
                        @if($device->failure_reports()->where('status', '<>', 2)->count()>0)
                            <br>
                            <a href="{{ '/device_failure_report?dvc='.$device->id }}" title="Ver reportes de falla de este equipo">
                                {{ $device->failure_reports()->where('status', 0)->count()==1 ? '1 falla pendiente' :
                                    $device->failure_reports()->where('status', 0)->count().' fallas pendientes' }}
                                <br>
                                {{ $device->failure_reports()->where('status', 1)->count()==1 ? '1 falla en proceso de solución' :
                                    $device->failure_reports()->where('status', 1)->count().' fallas en proceso de solución' }}
                            </a>
                        @endif
                    </td>
                    <td>
                        {!! $device->responsible!=0&&$device->status!='Baja' ?
                            $device->user->name.($device->destination=='Almacén' ?
                            '<br>(Almacén '.$device->branch.')' : '') : 'Sin asignar' !!}
                        @if($device->last_operator&&$device->last_operator->confirmation_flags[3]==0)
                            <i class="fa fa-warning pull-right" title="Pendiente de confirmación" style="color: darkred"></i>
                        @endif
                    </td>
                    <td>
                        {{ $device->responsible!=0 ?
                        ($device->last_operator ? date_format($device->last_operator->date,'d-m-Y') : 'N/E') : 'N/A' }}
                    </td>
                    <td>
                        <span class="status">
                            {{ $device->status }}
                        </span>

                        @if(($device->responsible==$user->id||$user->action->acv_mnt_add
                            /*$user->work_type=='Almacén'||$user->priv_level>=3*/)&&$device->flags!='0000' /* Baja */)

                            @if($device->flags[0]==1&&$device->status=='En mantenimiento')
                                <a href="/maintenance/{{ '?dv_id='.$device->id }}" style="color:inherit">
                                    <i class="fa fa-wrench pull-right" style="color:red;"
                                       title="Ver estado de mantenimiento">
                                    </i>
                                </a>
                            @elseif($device->flags[0]==1&&$device->status=='En calibración')
                                <a href="/calibration/{{ '?dvc='.$device->id }}" style="color:inherit">
                                    <i class="fa fa-wrench pull-right" style="color:red;"
                                       title="Ver estado de calibración">
                                    </i>
                                </a>
                            @else
                                <i onclick="flag_change(this,flag='maintenance',id='{{ $device->id }}');"
                                   class="fa fa-wrench pull-right" style="{{ $device->flags[0]==1 ? 'color:red;' : '' }}"
                                   title="{{ $device->flags[0]==1 ? 'En mantenimiento' : 'Mover a equipos en mantenimiento' }}">
                                </i>
                            @endif
                            {{--
                            <i @if($device->flags[1]==0)
                                onclick="flag_change(this,flag='req_maintenance',id='{{ $device->id }}');"
                               @endif
                                class="fa fa-flag pull-right" style="{{ $device->flags[1]==1 ? 'color:orange;' : '' }}"
                                title="{{ $device->flags[1]==1 ? 'Se ha solicitado mantenimiento' : 'Solicitar mantenimiento' }}">
                            </i>
                            --}}
                        @else
                            <i class="fa fa-wrench pull-right" style="{{ $device->flags[0]==1 ? 'color:red;' : '' }}"
                               title="{{ $device->flags[0]==1 ? 'En mantenimiento' : 'Marcar mantenimiento' }}"></i>
                        @endif

                        @if($device->responsible==$user->id||$user->action->acv_dfr_add)
                            <a href="/device/report_malfunction/{{ $device->id }}"
                               style="{{ $device->flags[1]==0 ? 'color: inherit' : ''}}">
                                <i class="fa fa-flag pull-right" style="{{ $device->flags[1]==1 ? 'color:orange' : 'color:inherit' }}"
                                   title="{{ $device->flags[1]==1 ? 'Se ha reportado una falla / mantenimiento solicitado' :
                                        'Reportar falla / Solicitar mantenimiento' }}">
                                </i>
                            </a>
                        @else
                            <i class="fa fa-flag pull-right" style="{{ $device->flags[1]==1 ? 'color:orange;' : '' }}"
                               title="{{ $device->flags[1]==1 ? 'Se ha solicitado mantenimiento' :
                                 'Solicitar mantenimiento' }}"></i>
                        @endif

                        @if(($device->responsible==$user->id||$user->action->acv_dvc_edt
                            /*$user->work_type=='Almacén'||$user->priv_level>=3*/)&&$device->flags!='0000' /* Baja */)
                            {{--
                            @if($device->flags[1]==0&&$device->flags[0]==0)
                                <a href="/device/report_malfunction/{{ $device->id }}" style="color:inherit">
                                    <i class="fa fa-flag pull-right" style="{{ $device->flags[1]==1 ? 'color:orange' : '' }}"
                                       title="{{ $device->flags[1]==1 ? 'Se ha solicitado mantenimiento' :
                                            'Solicitar mantenimiento' }}">
                                    </i>
                                </a>
                            @else
                                <i class="fa fa-flag pull-right" style="{{ $device->flags[1]==1 ? 'color:orange' : '' }}"
                                   title="{{ $device->flags[1]==1 ? 'Se ha solicitado mantenimiento' :
                                            'Solicitar mantenimiento' }}">
                                </i>
                            @endif
                            --}}

                            <i class="fa fa-flag pull-right" style="{{ $device->flags[2]==1 ? 'color:blue' : '' }}"
                                title="{{ $device->flags[2]==1 ? 'Activo' : 'En azul indica activo' }}">
                            </i>
                            {{--
                            <i @if($device->flags[3]==0)
                                onclick="flag_change(this,flag='available',id='{{ $device->id }}');"
                               @endif
                                class="fa fa-flag pull-right" style="{{ $device->flags[3]==1 ? 'color:green' : '' }}"
                                title="{{ $device->flags[3]==1 ? 'Disponible' : 'Marcar como disponible' }}" >
                            </i>
                            --}}
                            @if($device->flags[3]==0&&$device->flags[2]==1)
                                <a href="/operator/devolution/{{ $device->id }}" style="color:inherit" class="available_confirmation">
                                    <i class="fa fa-flag pull-right" style="{{ $device->flags[3]==1 ? 'color:green' : '' }}"
                                       title="{{ $device->flags[3]==1 ? 'Disponible' : 'Marcar como disponible' }}">
                                    </i>
                                </a>
                            @else
                                <i class="fa fa-flag pull-right" style="{{ $device->flags[3]==1 ? 'color:green' : '' }}"
                                   title="{{ $device->flags[3]==1 ? 'Disponible' : 'Marcar como disponible' }}">
                                </i>
                            @endif
                        @else
                            <i class="fa fa-flag pull-right" style="{{ $device->flags[2]==1 ? 'color:blue' : '' }}"
                                title="{{ $device->flags[2]==1 ? 'Activo' : 'En azul indica activo' }}"></i>
                            <i class="fa fa-flag pull-right" style="{{ $device->flags[3]==1 ? 'color:green' : '' }}"
                                title="{{ $device->flags[3]==1 ? 'Disponible' : 'Marcar como disponible' }}"></i>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-sm-12 mg10" align="center">
        {!! $devices->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="fancy_table table_brown" id="cloned"></table>
    </div>

    <!-- Modal for previewing images -->
    <div id="picModal" class="pic_modal">
        <span class="pic_close" id="pic_close">&times;</span>
        <img class="pic_modal-content" id="pic_modal_content" src="">
        <div id="pic_caption"></div>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'devices','id'=>0))
    </div>

@endsection

@section('footer')
    @parent
@endsection

@section('javascript')
    <script src="{{ asset('app/js/fix_table_header.js') }}"></script> {{-- For fixed header --}}
    <script src="{{ asset('app/js/set_current_url.js') }}"></script> {{-- For recording current url --}}
    <script>
        $('#alert').delay(2000).fadeOut('slow');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(function(){
            $('#fixable_table').tablesorter({
                cssAsc: 'headerSortUp',
                cssDesc: 'headerSortDown',
                cssNone: '',
                dateFormat: 'uk'
            });
        });

        function flag_change(element, flag, id) {
            var text = "Confirmar";
            var flag_color = "";

            if(flag==='maintenance'){
                text = "Marcar equipo en mantenimiento correctivo?";
                flag_color = "red";
            }
            else if(flag==='req_maintenance'){
                text = "Solicitar mantenimiento para este equipo?";
                flag_color = "orange";
            }
            else if(flag==='available'){
                text = "Marcar equipo como disponible?";
                flag_color = "green";
            }

            var r = confirm(text);
            if (r === true) {
                $.post('/flag/device', { flag: flag, id: id }, function(data) {
                    //alert(data);
                    $(element).parent().find('.status').html(data);
                    element.style.color = flag_color;
                });
            }
        }

        $('.available_confirmation').on('click', function () {
            var text = "Marcar equipo como disponible?";
            return confirm(text);
        });

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
        //var span = document.getElementsByClassName("close")[0];
        var span = document.getElementById("pic_close");
        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = "none";
        }
    </script>
@endsection
