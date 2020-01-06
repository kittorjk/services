@extends('layouts.actives_structure')

@section('header')
    @parent
    <style>
        .dropdown-menu-prim > li > a {
            width: 230px;
            /*white-space: normal; /* Set code to a second line */
        }
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    <div class="btn-group">
        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
            <i class="fa fa-wrench"></i> Opciones de mantenimiento <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            {{--<li><a href="{{ '/maintenance' }}"><i class="fa fa-refresh"></i> Recargar página </a></li>--}}
            <li><a href="{{ Request::fullurl() }}"><i class="fa fa-refresh fa-fw"></i> Recargar página </a></li>
            <li><a href="{{ '/maintenance?vhc=true' }}"><i class="fa fa-car fa-fw"></i> Vehículos en mantenimiento </a></li>
            <li><a href="{{ '/maintenance?dvc=true' }}"><i class="fa fa-laptop fa-fw"></i> Equipos en mantenimiento </a></li>
            @if($user->action->acv_mnt_add
                /*$user->work_type=='Almacén'||$user->work_type=='Transporte'||$user->priv_level>=3*/)
                <li><a href="{{ '/maintenance/create' }}"><i class="fa fa-plus fa-fw"></i> Registrar mantenimiento</a></li>
            @endif
            @if($user->action->acv_mnt_exp /*$user->priv_level==4*/)
                <li class="divider"></li>
                <li><a href="{{ '/excel/maintenances' }}"><i class="fa fa-file-excel-o fa-fw"></i> Exportar a Excel</a></li>
            @endif
        </ul>
    </div>
    <!--<a href="/search/maintenances/0" class="btn btn-primary"><i class="fa fa-search"></i> Buscar </a>-->
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchBox">
        <i class="fa fa-search"></i> Buscar
    </button>
@endsection

@section('content')
    <div class="col-sm-12 mg10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10 mg-tp-px-10">
        <p>Registros de mantenimiento encontrados: {{ $maintenances->total() }}</p>

        <table class="fancy_table table_gray" id="fixable_table">
            <thead>
            <tr>
                <th>Puesto en manto.</th>
                <th>Activo</th>
                <th>Tipo de Manto.</th>
                <th width="25%">Detalle de trabajos</th>
                <th>Estado de manto.</th>
                <th>Responsable</th>
                <th width="10%">Reportes</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($maintenances as $maintenance)
                <tr>
                    <td>
                        <a href="/maintenance/{{ $maintenance->id }}">
                            {{ date_format(new \DateTime($maintenance->created_at), 'd-m-Y') }}
                        </a>
                    </td>
                    <td>
                        @if($maintenance->vehicle_id)
                            <a href="/vehicle/{{ $maintenance->vehicle_id }}">
                                {{ $maintenance->vehicle->type.' '.$maintenance->vehicle->license_plate }}
                            </a>
                        @elseif($maintenance->device_id)
                            <a href="/device/{{ $maintenance->device_id }}">
                                {{ $maintenance->device->type.' '.$maintenance->device->serial }}
                            </a>
                        @endif
                    </td>
                    <td>
                        {{ $maintenance->type }}
                        @if($maintenance->parameter_id!=0)
                            <a href="/service_parameter/{{ $maintenance->parameter->id }}">
                                {{ '('.$maintenance->parameter->name.')' }}
                            </a>
                        @endif
                    </td>
                    <td>
                        {{ $maintenance->detail ? $maintenance->detail : '' }}

                        @if(($maintenance->completed==0&&($user->id==$maintenance->user_id||
                            ($maintenance->device_id!=0&&$user->work_type=='Almacén'&&$user->action->acv_mnt_edt)||
                            ($maintenance->vehicle_id!=0&&$user->work_type=='Transporte'&&$user->action->acv_mnt_edt)))||
                            $user->priv_level==4)
                            <a href="/maintenance/{{ $maintenance->id }}/edit" class="pull-right"
                                title="{{ $maintenance->detail ?
                                 'Modifique o aumente detalles de los trabajos de mantenimiento' :
                                 'Agregue detalles de los trabajos de mantenimiento realizados' }}">
                                <i class="fa fa-edit"></i> {{ $maintenance->detail ? 'editar' : 'agregar' }}
                            </a>
                        @endif
                    </td>
                    <td>
                        {{ $maintenance->completed==1 ? 'Terminado' : 'En proceso' }}

                        @if($maintenance->completed==0&&strlen($maintenance->detail)>0&&$user->action->acv_mnt_edt)
                            <a href="/maintenance/close/{{ $maintenance->id }}" class="pull-right"
                               title="Dar por terminados los trabajos de mantenimiento">
                                <i class="fa fa-check"></i>
                            </a>
                        @endif
                    </td>
                    <td>{{ $maintenance->user ? $maintenance->user->name : 'N/E' }}</td>
                    <td>
                        @foreach($maintenance->files as $file)
                            @include('app.info_document_options', array('file'=>$file))

                            {{--
                            <a href="/download/{{ $file->id }}">
                                @if($file->type=="pdf")
                                    <img src="{{ '/imagenes/pdf-icon.png' }}" alt="PDF" />
                                @elseif($file->type=="jpg"||$file->type=="jpeg"||$file->type=="png")
                                    <img src="{{ '/imagenes/image-icon.png' }}" alt="IMAGE" />
                                @endif
                            </a>
                            @if($file->type=="pdf")
                                <a href="/display_file/{{ $file->id }}">Ver</a>
                            @endif
                            --}}
                        @endforeach

                        @if($maintenance->files->count()<1)
                            @if(($maintenance->completed==0&&($user->id==$maintenance->user_id||
                                ($maintenance->device_id!=0&&$user->work_type=='Almacén'&&$user->action->acv_mnt_edt)||
                                ($maintenance->vehicle_id!=0&&$user->work_type=='Transporte'&&$user->action->acv_mnt_edt)))||
                                $user->priv_level==4)
                                <a href="/files/maintenance/{{ $maintenance->id }}">
                                    <i class="fa fa-upload"></i> Subir reporte
                                </a>
                            @endif
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-sm-12 mg10" align="center">
        {!! $maintenances->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="fancy_table table_gray" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'maintenances','id'=>0))
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
    </script>
@endsection
