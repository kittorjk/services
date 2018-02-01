@extends('layouts.actives_structure')

@section('header')
    @parent
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
            <i class="fa fa-car"></i> Vehículos asignados <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li><a href="{{ '/driver' }}"><i class="fa fa-refresh fa-fw"></i> Recargar página </a></li>
            <li><a href="{{ '/driver?conf=pending' }}"><i class="fa fa-hourglass-2 fa-fw"></i> Ver asignaciones sin confirmar</a></li>
            <li><a href="{{ '/vehicle' }}"><i class="fa fa-car fa-fw"></i> Ver vehículos </a></li>
            <li><a href="{{ '/vehicle_requirement' }}"><i class="fa fa-arrow-right fa-fw"></i> Ver requerimientos</a></li>
            @if($user->action->acv_vhc_req /*$user->priv_level>=2*/)
                <li><a href="{{ '/vehicle_requirement/create' }}"><i class="fa fa-plus fa-fw"></i> Nuevo requerimiento </a></li>
            @endif
            {{--
            @if($user->work_type=='Transporte'||$user->priv_level==4)
                <li><a href="/driver/create"><i class="fa fa-plus fa-fw"></i> Asignar vehículo </a></li>
            @endif
            --}}
            @if($user->action->acv_vhc_exp /*$user->priv_level==4*/)
                <li class="divider"></li>
                <li><a href="{{ '/excel/drivers' }}"><i class="fa fa-file-excel-o fa-fw"></i> Exportar a Excel </a></li>
            @endif
        </ul>
    </div>
    <div class="btn-group">
        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
            <i class="fa fa-file-pdf"></i> Formulario de entrega <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li><a href="{{ '/download/dr-0' }}"><i class="fa fa-download"></i> Descargar </a></li>
            @if($user->action->acv_drv_upl_fmt /*$user->priv_level>=3*/)
                <li><a href="{{ '/files/driver_form/0' }}"><i class="fa fa-upload"></i> Nuevo formato </a></li>
            @endif
        </ul>
    </div>
    @if($user->work_type=='Transporte'||$user->priv_level>=2)
        <div class="btn-group">
            <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
                <i class="fa fa-drivers-license-o"></i> Licencias <span class="caret"></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-prim">
                <li><a href="{{ '/license/create' }}"><i class="fa fa-plus"></i> Agregar licencia </a></li>
                @if($user->priv_level==4)
                    <li><a href="{{ '/excel/licenses' }}"><i class="fa fa-plus"></i> Exportar licencias </a></li>
                @endif
            </ul>
        </div>
    @endif
    <!--<a href="/search/drivers/0" class="btn btn-primary"><i class="fa fa-search"></i> Buscar </a>-->
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchBox">
        <i class="fa fa-search"></i> Buscar
    </button>
@endsection

@section('content')
    <div class="col-sm-12 mg10 mg-tp-px-10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10 mg-tp-px-10">
        <p>Asignaciones de vehículo registradas: {{ $drivers->total() }}</p>

        <table class="fancy_table table_purple tablesorter" id="fixable_table">
            <thead>
            <tr>
                <th>Fecha</th>
                <th>Vehículo</th>
                <th>Placa</th>
                <th>Entregado por</th>
                <th>Entregado a</th>
                <th class="{ sorter: 'digit' }">Km entrega</th>
                <th class="{ sorter: 'digit' }">Km recorrido</th>
                <th title="Tipo de asignación">Tipo asig.</th>
                <th>Respaldos</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($drivers as $driver)
                <tr>
                    <td>
                        <a href="/driver/{{ $driver->id }}">{{ date_format(new \DateTime($driver->date), 'd-m-Y') }}</a>
                    </td>
                    <td>{{ $driver->vehicle->type.' '.$driver->vehicle->model }}</td>
                    <td>
                        <a href="/vehicle/{{ $driver->vehicle->id }}">{{ $driver->vehicle->license_plate }}</a>
                    </td>
                    <td>
                        @if($driver->deliverer->license&&($driver->deliverer->id==$user->id||$user->priv_level>=2))
                            <a href="/license/{{ $driver->deliverer->license->id }}">{{ $driver->deliverer->name }}</a>

                            <a href="#" title="Licencia de conducir" data-toggle="modal"
                               data-target="{{ '#licenseBox'.$driver->deliverer->id }}">
                                <i class="fa fa-drivers-license-o pull-right"></i>
                            </a>
                            <!-- Deliverer License Modal -->
                            <div id="{{ 'licenseBox'.$driver->deliverer->id }}" class="modal fade" role="dialog">
                                @include('app.license_modal', array('user'=>$user,'service'=>$service,
                                    'license'=>$driver->deliverer->license))
                            </div>
                        @else
                            {{ $driver->deliverer->name }}
                        @endif
                        {{--
                        @if($driver->confirmation_flags[2]==0&&($driver->who_delivers==$user->id||$user->priv_level==4))
                            <i onclick="user_confirmation(this,flag='confirm_delivery',id='{{ $driver->id }}');"
                               class="fa fa-info-circle pull-right" style="color:dodgerblue;" title="Confirmar entrega"></i>
                        @endif
                        --}}
                    </td>
                    <td>
                        @if($driver->receiver->license&&($driver->receiver->id==$user->id||$user->priv_level>=2))
                            <a href="/license/{{ $driver->receiver->license->id }}">{{ $driver->receiver->name }}</a>

                            <a href="#" title="Licencia de conducir" data-toggle="modal"
                               data-target="{{ '#licenseBox'.$driver->receiver->id }}">
                                <i class="fa fa-drivers-license-o pull-right"></i>
                            </a>
                            <!-- Receiver License Modal -->
                            <div id="{{ 'licenseBox'.$driver->receiver->id }}" class="modal fade" role="dialog">
                                @include('app.license_modal', array('user'=>$user,'service'=>$service,
                                    'license'=>$driver->receiver->license))
                            </div>
                        @else
                            {{ $driver->receiver->name }}
                        @endif

                        @if($driver->confirmation_flags[3]==0)
                            @if($driver->who_receives==$user->id||$user->priv_level==4)
                                {{--
                                    <i onclick="user_confirmation(this,flag='confirm_reception',id='{{ $driver->id }}');"
                                        class="fa fa-info-circle pull-right" style="color:dodgerblue;"
                                        title="Confirmar recepción"></i>
                                    --}}
                                <a href="{{ '/driver/confirm/'.$driver->id }}" style="text-decoration: none;"
                                   title="Confirmar recepción de vehículo" class="pull-right">
                                    <i class="fa fa-check-circle"></i>
                                </a>
                            @else
                                <i class="fa fa-warning pull-right" title="Pendiente de confirmación" style="color: darkred"></i>
                            @endif
                        @endif
                    </td>
                    <td>{{ $driver->mileage_before.' Km' }}</td>
                    <td>{{ $driver->mileage_traveled!=0 ? $driver->mileage_traveled.' Km' : '' }}</td>
                    <td>{{ $driver->requirement ? App\VehicleRequirement::$types[$driver->requirement->type] : '' }}</td>
                    <td>
                        @foreach($driver->files as $file)
                            @if($file->type=="pdf")
                                Recibo:
                                <a href="/download/{{ $file->id }}">
                                    <img src="{{ '/imagenes/pdf-icon.png' }}" alt="PDF" />
                                </a>
                            @endif
                        @endforeach
                        @if($driver->files()->where('type', 'pdf')->count()==0)
                            <a href="/files/driver_receipt/{{ $driver->id }}" title="Subir el recibo de entrega firmado">
                                <i class="fa fa-upload"></i> Recibo
                            </a>
                        @endif

                        {{--
                        {{ $driver->files->count()==1 ? '1 guardado' : ($driver->files->count()!=0 ?
                            $driver->files->count().' guardados' : '') }}
                        --}}

                        @if(($driver->confirmation_flags[3]==0&&($user->id==$driver->who_delivers||
                            $user->id==$driver->who_receives))||$user->priv_level==4)
                            <a href="/files/driver/{{ $driver->id }}" class="pull-right">
                                <i class="fa fa-plus"></i> {{ 'Fotos' }}
                            </a>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-sm-12 mg10" align="center">
        {!! $drivers->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="fancy_table table_purple" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'drivers','id'=>0))
    </div>

@endsection

@section('footer')
    @parent
@endsection

@section('javascript')
    <script src="{{ asset('app/js/fix_table_header.js') }}"></script> {{-- For fixed header --}}
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function(){
            $.post('/set_current_url', { url: window.location.href }, function(){});
        });

        $('#alert').delay(2000).fadeOut('slow');

        $(function(){
            $('#fixable_table').tablesorter({
                cssAsc: 'headerSortUp',
                cssDesc: 'headerSortDown',
                cssNone: '',
                dateFormat: 'uk'
            });
        });

        /*
        function user_confirmation(element, flag, id){
            var text = "Confirmar";
            var color = "green";

            if(flag=='confirm_delivery'){
                text = "Confirma que entregó el vehículo?";
            }
            else if(flag=='confirm_reception'){
                text = "Confirma que recibió el vehículo?";
            }

            var r = confirm(text);
            if (r == true) {
                $.post('/flag/driver', { flag: flag, id: id }, function(data){
                    //$(element).parent().find('.status').html(data);
                    element.style.color = color;
                });
            }
        }
        */
    </script>
@endsection
