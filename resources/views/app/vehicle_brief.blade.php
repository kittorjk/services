@extends('layouts.actives_structure')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/image_modal.css") }}">
    <style>
        .dropdown-menu-prim > li > a {
            width: 200px;
            /*white-space: normal; /* Set content to a second line */
        }
    </style>
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
                {{--<li><a href="/driver/create"><i class="fa fa-exchange fa-fw"></i> Asignar vehículo</a></li>--}}
                <li><a href="{{ '/vehicle/create' }}"><i class="fa fa-plus fa-fw"></i> Agregar vehículo</a></li>
            @endif
            @if($user->action->acv_vhc_exp /*$user->priv_level==4*/)
                <li class="divider"></li>
                <li><a href="{{ '/excel/vehicles' }}"><i class="fa fa-file-excel-o fa-fw"></i> Exportar a Excel</a></li>
            @endif
        </ul>
    </div>
    <a href="{{ '/maintenance?vhc=true' }}" class="btn btn-primary"><i class="fa fa-wrench"></i> Vehículos en mantenimiento</a>
    @if($user->priv_level>=2||$user->work_type=='Transporte')
        <!--<a href="/search/vehicles/0" class="btn btn-primary"><i class="fa fa-search"></i> Buscar </a>-->
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchBox">
            <i class="fa fa-search"></i> Buscar
        </button>
    @endif
@endsection

@section('content')
    <div class="col-sm-12 mg10 mg-tp-px-10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10">
        <p>Registros encontrados: {{ $vehicles->total() }}</p>

        <table class="fancy_table table_purple tablesorter" id="fixable_table">
            <thead>
            <tr>
                <th>Foto</th>
                <th>Placa</th>
                <th>Tipo</th>
                <th>Modelo</th>
                <th class="{ sorter: 'digit' }">Kilometraje</th>
                <th>Resp. actual</th>
                <th width="13%">Libro de vehículo</th>
                <th width="22%">Estado</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($vehicles as $vehicle)
                <tr>
                    <td align="center">
                        @if($vehicle->main_pic_id!=0)
                            <img class="myImg" src="/files/thumbnails/{{ 'thumb_'.$vehicle->main_pic->name }}" height="50"
                                 border="0" alt="{{ $vehicle->main_pic->description }}" onclick="show_modal(this)">
                        @endif

                        {{-- Code for presenting the first file as thumbnail
                        @foreach($vehicle->files as $key => $file)
                            <a href="/files/{{ $file->name }}">
                                @if($key==0)
                                    <img class="myImg" src="/files/{{ $file->name }}" height="50" border="0"
                                        alt="{{ $file->description }}" onclick="show_modal(this)">
                                @endif
                            </a>
                        @endforeach
                        --}}

                        @if($vehicle->main_pic_id==0&&$user->action->acv_vhc_edt
                            /*($user->work_type=='Transporte'||$user->priv_level>=2)*/)
                            <a href="/files/vehicle_img/{{ $vehicle->id }}"><i class="fa fa-upload"></i> Subir foto</a>
                        @endif
                    </td>
                    <td>
                        <a href="/vehicle/{{ $vehicle->id }}" title="Ver información de vehículo">
                            {{ $vehicle->license_plate }}
                        </a>

                        @if($user->action->acv_vhc_edt /*$user->priv_level==4*/)
                            <a href="/vehicle/{{ $vehicle->id }}/edit" title="Actualizar información de vehículo">
                                <i class="fa fa-pencil-square"></i>
                            </a>
                        @endif
                    </td>
                    <td>{{ $vehicle->type }}</td>
                    <td>{{ $vehicle->model }}</td>
                    <td align="right" style="{{
                            $vehicle->flags != '0000' /*Baja*/ &&
                            (($vehicle->last_mant20000 && (($vehicle->mileage-$vehicle->last_mant20000->usage) > 19900)) ||
                            (!$vehicle->last_mant20000 && $vehicle->mileage > 19900) ||
                            ($vehicle->last_mant10000 && (($vehicle->mileage-$vehicle->last_mant10000->usage) > 9900)) ||
                            (!$vehicle->last_mant10000 && $vehicle->mileage > 9900) ||
                            ($vehicle->last_mant5000 && (($vehicle->mileage-$vehicle->last_mant5000->usage) > 4900)) ||
                            (!$vehicle->last_mant5000 && $vehicle->mileage > 4900) ||
                            ($vehicle->last_mant2500 && (($vehicle->mileage-$vehicle->last_mant2500->usage) > 2400)) ||
                            (!$vehicle->last_mant2500 && $vehicle->mileage > 2400)) ?
                            'color:darkorange;' : '' }}

                            /*(($vehicle->mileage/2500)-floor($vehicle->mileage/2500))>=0.95*/
                            {{--
                        @elseif((($vehicle->mileage/2500)-floor($vehicle->mileage/2500))>=0.85)
                            color:orange;
                            --}}
                        ">
                        {{ $vehicle->mileage.' Km' }}
                    </td>
                    <td>
                        <span class="responsable">
                            {!! $vehicle->responsible != 0 && $vehicle->status != 'Baja' ?
                                $vehicle->user->name.($vehicle->destination == 'Garaje' ?
                                '<br>(Garaje '.$vehicle->branch.')' : '') : 'Sin asignar' !!}
                        </span>
                        {{-- $vehicle->responsible!=0 ? $vehicle->user->name  : 'Sin asignar' --}}
                        @if(($vehicle->last_driver && $vehicle->last_driver->confirmation_flags[3] == 0) &&
                            $vehicle->flags != '0000'/*Baja*/)
                            <i class="fa fa-warning pull-right" title="Pendiente de confirmación" style="color: darkred"></i>
                        @endif
                    </td>
                    <td>
                        <a href="/vehicle_condition/{{ $vehicle->id }}" title="Ver registros en el libro de vehículo">
                            {{ $vehicle->condition_records->count()==1 ?
                                '1 registro' : $vehicle->condition_records->count().' registros' }}
                        </a>
                        @if($vehicle->failure_reports()->where('status', '<>', 2)->count()>0)
                            <br>
                            <a href="{{ '/vehicle_failure_report?vhc='.$vehicle->id }}" title="Ver reportes de falla de este vehículo">
                                {{ $vehicle->failure_reports()->where('status', 0)->count()==1 ? '1 falla pendiente' :
                                    $vehicle->failure_reports()->where('status', 0)->count().' fallas pendientes' }}
                                <br>
                                {{ $vehicle->failure_reports()->where('status', 1)->count()==1 ? '1 falla en proceso de solución' :
                                    $vehicle->failure_reports()->where('status', 1)->count().' fallas en proceso de solución' }}
                            </a>
                        @endif
                    </td>
                    <td>
                        <span class="status">{{ $vehicle->status }}</span>

                        @if ($user->action->acv_mnt_add && $vehicle->flags[0] == 0)
                            <i onclick="flag_change(this,flag='maintenance',id='{{ $vehicle->id }}');"
                                class="fa fa-wrench pull-right" style="{{ $vehicle->flags[0]==1 ? 'color:red;' : '' }}"
                                title="{{ 'Mover a vehículos en mantenimiento' }}">
                             </i>
                        @elseif (($vehicle->responsible == $user->id || $user->action->acv_mnt_add
                            /*$user->priv_level==4||$user->work_type=='Transporte'*/) && $vehicle->flags[0] == 1)
                            <a href="{{ '/maintenance?vh_id='.$vehicle->id }}" style="color:inherit">
                                <i class="fa fa-wrench pull-right" style="color:red;"
                                    title="Ver estado de mantenimiento">
                                </i>
                            </a>
                            {{-- on click flag function
                            <i @if($vehicle->flags[1]==0)
                                onclick="flag_change(this,flag='req_maintenance',id='{{ $vehicle->id }}');"
                               @endif
                               class="fa fa-flag pull-right" style="{{ $vehicle->flags[1]==1 ? 'color:orange;' : '' }}"
                               title="{{ $vehicle->flags[1]==1 ? 'Se ha solicitado mantenimiento' : 'Solicitar mantenimiento' }}">
                            </i>
                            --}}
                        @else
                            <i class="fa fa-wrench pull-right" style="{{ $vehicle->flags[0] == 1 ? 'color:red;' : '' }}"
                               title="{{ $vehicle->flags[0] == 1 ? 'En mantenimiento' : 'Este ícono se marcará de color rojo cuando el vehículo esté en mantenimiento' }}">
                            </i>
                        @endif

                        @if($vehicle->responsible==$user->id || $user->action->acv_vfr_add)
                            <a href="/vehicle/report_malfunction/{{ $vehicle->id }}"
                               style="{{ $vehicle->flags[1] == 0 ? 'color: inherit' : ''}}">
                                <i class="fa fa-flag pull-right" style="{{ $vehicle->flags[1] == 1 ? 'color:orange' : 'color:inherit' }}"
                                   title="{{ $vehicle->flags[1] == 1 ? 'Se ha reportado una falla / mantenimiento solicitado' :
                                            'Reportar falla / Solicitar mantenimiento' }}">
                                </i>
                            </a>
                        @else
                            <i class="fa fa-flag pull-right" style="{{ $vehicle->flags[1] == 1 ? 'color:orange;' : '' }}"
                               title="{{ $vehicle->flags[1] == 1 ? 'Se ha solicitado mantenimiento' : 'Solicitar mantenimiento' }}">
                            </i>
                        @endif

                        @if(($vehicle->responsible == $user->id || $user->priv_level == 4 || $user->work_type == 'Transporte') &&
                            $vehicle->flags != '0000' /*Baja*/)
                            {{--
                            @if($vehicle->flags[1]==0&&$vehicle->flags[0]==0)
                                <a href="/vehicle/report_malfunction/{{ $vehicle->id }}" style="color:inherit">
                                    <i class="fa fa-flag pull-right" title="Reportar falla / Solicitar mantenimiento"></i>
                                </a>
                            @else
                                <i class="fa fa-flag pull-right" style="{{ $vehicle->flags[1]==1 ? 'color:orange' : '' }}"
                                   title="{{ $vehicle->flags[1]==1 ? 'Se ha reportado una falla / solicitado mantenimiento' :
                                            'Reportar falla / Solicitar mantenimiento' }}">
                                </i>
                            @endif
                            --}}

                            <i class="fa fa-flag pull-right"
                               title="{{ $vehicle->flags[2]==1 ? 'Activo' : 'Color azul indica vehículo activo' }}"
                               style="{{ $vehicle->flags[2]==1 ? 'color:blue' : '' }}">
                            </i>
                            {{-- on click flag function
                            <i @if($vehicle->flags[3]==0)
                                onclick="flag_change(this,flag='available',id='{{ $vehicle->id }}');"
                               @endif
                               class="fa fa-flag pull-right"
                               title="{{ $vehicle->flags[3]==1 ? 'Disponible' : 'Marcar como disponible' }}"
                               style="{{ $vehicle->flags[3]==1 ? 'color:green' : '' }}">
                            </i>
                            --}}
                            @if($vehicle->flags[3]==0&&$vehicle->flags[2]==1)
                                <a href="/driver/devolution/{{ $vehicle->id }}" style="color:inherit">
                                    <i class="fa fa-flag pull-right" title="Marcar como disponible"></i>
                                </a>
                            @else
                                <i class="fa fa-flag pull-right" style="{{ $vehicle->flags[3]==1 ? 'color:green' : '' }}"
                                   title="{{ $vehicle->flags[3]==1 ? 'Disponible' : 'Marcar como disponible' }}">
                                </i>
                            @endif
                        @else
                            <i class="fa fa-flag pull-right" style="{{ $vehicle->flags[2]==1 ? 'color:blue' : '' }}"
                               title="{{ $vehicle->flags[2]==1 ? 'Activo' : 'No está en uso' }}">
                            </i>
                            <i class="fa fa-flag pull-right" style="{{ $vehicle->flags[3]==1 ? 'color:green' : '' }}"
                               title="{{ $vehicle->flags[3]==1 ? 'Disponible' : 'Marcar como disponible' }}">
                            </i>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-sm-12 mg10" align="center">
        {!! $vehicles->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="fancy_table table_purple" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'vehicles','id'=>0))
    </div>

    <!-- Image preview Modal -->
    <div id="picModal" class="pic_modal">
        <span class="pic_close" id="pic_close">&times;</span>
        <img class="pic_modal-content" id="pic_modal_content" src="">
        <div id="pic_caption"></div>
    </div>

@endsection

@section('footer')
    @parent
@endsection

@section('javascript')
    <script src="{{ asset('app/js/fix_table_header.js') }}"></script> {{-- For fixed header --}}
    <script>
        $('#alert').delay(2000).fadeOut('slow');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function(){
            $.post('/set_current_url', { url: window.location.href }, function(){});
        });

        $(function(){
            $('#fixable_table').tablesorter({
                cssAsc: 'headerSortUp',
                cssDesc: 'headerSortDown',
                cssNone: ''
            });
        });

        function flag_change(element, flag, id){
            var text = "Confirmar";
            var flag_color = "";

            if(flag==='maintenance'){
                text = "Marcar vehículo en mantenimiento correctivo?";
                flag_color = "red";
            }
            else if(flag==='req_maintenance'){
                text = "Solicitar mantenimiento para este vehículo?";
                flag_color = "orange";
            }
            else if(flag==='available'){
                text = "Marcar vehículo como disponible?";
                flag_color = "green";
            }

            var r = confirm(text);
            if (r === true) {
                $.post('/flag/vehicle', { flag: flag, id: id }, function(data){
                    //alert(data);
                    $(element).parent().find('.status').html(data.estado);
                    $(element).parent().parent().find('.responsable').html(data.responsable);
                    element.style.color = flag_color;
                });
            }
        }
        /*
        var modal = document.getElementById('picModal');
        // Get the image and insert it inside the modal - use its "alt" text as a caption
        var modalImg = document.getElementById("modal_content");
        var captionText = document.getElementById("caption");
        function show_modal(element){
            modal.style.display = "block";
            modalImg.src = element.src;
            captionText.innerHTML = element.alt;
        }
        // Get the <span> element that closes the modal
        var span = document.getElementById("pic_close");
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
