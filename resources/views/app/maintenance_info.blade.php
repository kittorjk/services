@extends('layouts.actives_structure')

@section('header')
    @parent
@endsection

@section('menu_options')
    <div class="btn-group">
        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
            <i class="fa fa-wrench"></i> Opciones de mantenimiento <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li><a href="{{ Request::fullurl() }}"><i class="fa fa-refresh fa-fw"></i> Recargar página </a></li>
            <li><a href="{{ '/maintenance?vhc=true' }}"><i class="fa fa-car fa-fw"></i> Vehículos en mantenimiento </a></li>
            <li><a href="{{ '/maintenance?dvc=true' }}"><i class="fa fa-laptop fa-fw"></i> Equipos en mantenimiento </a></li>
            @if($user->action->acv_mnt_add
                /*$user->work_type=='Almacén'||$user->work_type=='Transporte'||$user->priv_level>=3*/)
                <li><a href="{{ '/maintenance/create' }}"><i class="fa fa-plus fa-fw"></i> Registrar mantenimiento</a></li>
            @endif
        </ul>
    </div>
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

        <div class="panel panel-10gray">
            <div class="panel-heading" align="center">
                <div class="panel-title">Información de mantenimiento de activo</div>
            </div>
            <div class="panel-body">
                <div class="col-sm-12 mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning">
                        <i class="fa fa-arrow-circle-left"></i> Volver
                    </a>
                    <a href="{{ '/maintenance' }}" class="btn btn-warning" title="Ir a la tabla de activos en mantenimiento">
                        <i class="fa fa-arrow-circle-up"></i> En mantenimiento
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <div class="col-sm-12 mg10 mg-tp-px-10">

                    <table class="table table-striped table-hover table-bordered">
                        <thead>
                        <tr>
                            <th width="40%">Activo</th>
                            <td>
                                {{ $maintenance->vehicle_id ? $maintenance->vehicle->type.' '.$maintenance->vehicle->model :
                                    ($maintenance->device_id ? $maintenance->device->type : '') }}
                            </td>
                        </tr>
                        <tr>
                            <th>
                                {{ $maintenance->vehicle_id ? 'Número de placa' :
                                 ($maintenance->device_id ? 'Número de serie' : '') }}
                            </th>
                            <td>
                                {{ $maintenance->vehicle_id ? $maintenance->vehicle->license_plate :
                                    ($maintenance->device_id ? $maintenance->device->serial : '') }}
                            </td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr><td colspan="2"> </td></tr>
                        <tr>
                            <th>Puesto en manto.</th>
                            <td>
                                {{ date_format($maintenance->created_at,'d-m-Y').
                                ($maintenance->completed==0 ? ' (Actualmente en manto.)' : '') }}
                            </td>
                        </tr>
                        @if($maintenance->completed==1)
                            <tr>
                                <th>Salió de manto.</th>
                                <td>{{ date_format($maintenance->date,'d-m-Y') }}</td>
                            </tr>
                            <tr>
                                <th>Tiempo no disponible</th>
                                <td>{{ $maintenance->created_at->diffInDays($maintenance->date).' días' }}</td>
                            </tr>
                        @endif
                        <tr>
                            <th>Responsable</th>
                            <td>{{ $maintenance->user->name }}</td>
                        </tr>
                        @if($maintenance->detail)
                            <tr>
                                <th>Detalle de trabajos realizados</th>
                                <td>{{ $maintenance->detail }}</td>
                            </tr>
                        @endif
                        @if($maintenance->cost!=0)
                            <tr>
                                <th>Costo</th>
                                <td>{{ $maintenance->cost }}</td>
                            </tr>
                        @endif
                        <tr><td colspan="2"> </td></tr>

                        <tr>
                            <th>Archivo de Respaldo</th>
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

                        </tbody>
                    </table>
                </div>

                @if($user->action->acv_mnt_edt /*$user->priv_level==4*/)
                    <div class="col-sm-12 mg10" align="center">
                        <a href="/maintenance/{{ $maintenance->id }}/edit" class="btn btn-success">
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
