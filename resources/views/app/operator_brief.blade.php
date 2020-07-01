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
            <i class="fa fa-exchange"></i> Asignación de equipos <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            {{--<li><a href="{{ '/operator' }}"><i class="fa fa-refresh"></i> Recargar página </a></li>--}}
            <li><a href="" onclick="window.location.reload();"><i class="fa fa-refresh"></i> Recargar página </a></li>
            <li>
                <a href="{{ '/operator?conf=pending' }}"><i class="fa fa-hourglass-2 fa-fw"></i> Ver asignaciones sin confirmar</a>
            </li>
            <li><a href="{{ '/device' }}"><i class="fa fa-arrow-right"></i> Ver equipos </a></li>
            <li><a href="{{ '/device_requirement' }}"><i class="fa fa-arrow-right"></i> Ver requerimientos</a></li>
            @if($user->action->acv_dvc_req /*$user->priv_level>=2*/)
                <li><a href="{{ '/device_requirement/create' }}"><i class="fa fa-plus"></i> Nuevo requerimiento </a></li>
            @endif
            {{--
            @if($user->work_type=='Almacén'||$user->priv_level>=2)
                <li><a href="/operator/create"><i class="fa fa-plus"></i> Asignar equipo </a></li>
            @endif
            --}}
            @if($user->action->acv_dvc_exp /*$user->priv_level==4*/)
                <li class="divider"></li>
                <li><a href="{{ '/excel/operators' }}"><i class="fa fa-file-excel-o"></i> Exportar a Excel</a></li>
            @endif
        </ul>
    </div>
    <!--<a href="/search/operators/0" class="btn btn-primary"><i class="fa fa-search"></i> Buscar </a>-->
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchBox">
        <i class="fa fa-search"></i> Buscar
    </button>
@endsection

@section('content')
    <div class="col-sm-12 mg10 mg-tp-px-10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10 mg-tp-px-10">
        <p>Registros de asignación encontrados: {{ $operators->total() }}</p>

        <table class="fancy_table table_brown tablesorter" id="fixable_table">
            <thead>
            <tr>
                <th>Fecha</th>
                <th width="25%">Equipo</th>
                <th># Serie</th>
                <th>Entregado por</th>
                <th>Entregado a</th>
                <th>Tipo de asignación</th>
                <th>Respaldo</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($operators as $operator)
                <tr>
                    <td>
                        <a href="/operator/{{ $operator->id }}">
                            {{ date_format(new \DateTime($operator->date), 'd-m-Y') }}
                        </a>
                    </td>
                    <td>
                        <a href="/device/{{ $operator->device->id }}">
                            {{ $operator->device->type.' '.$operator->device->model }}
                        </a>
                    </td>
                    <td>{{ $operator->device->serial }}</td>
                    <td>
                        {{ $operator->deliverer->name }}
                        {{--
                        @if($operator->confirmation_flags[2]==0&&($operator->who_delivers==$user->id||$user->priv_level==4))
                            <i onclick="user_confirmation(this,flag='confirm_delivery',id='{{ $operator->id }}');"
                               class="fa fa-info-circle pull-right" style="color:dodgerblue;" title="Confirmar entrega"></i>
                        @endif
                        --}}
                    </td>
                    <td>
                        {{ $operator->receiver->name }}
                        @if($operator->confirmation_flags[3]==0)
                            @if($operator->who_receives==$user->id||$user->priv_level==4)
                                {{--
                                <i onclick="user_confirmation(this,flag='confirm_reception',id='{{ $operator->id }}');"
                                    class="fa fa-info-circle pull-right" style="color:dodgerblue;" title="Confirmar"></i>
                               --}}
                                <a href="{{ '/operator/confirm/'.$operator->id }}" style="text-decoration: none;"
                                   title="Confirmar recepción de equipo" class="pull-right">
                                    <i class="fa fa-check-circle"></i>
                                </a>
                            @else
                                <i class="fa fa-warning pull-right" title="Pendiente de confirmación" style="color: darkred"></i>
                            @endif
                        @endif
                    </td>
                    <td>{{ $operator->requirement ? App\DeviceRequirement::$types[$operator->requirement->type] : '' }}</td>
                    <td>
                        @foreach($operator->files as $file)
                            @if($file->type=="pdf")
                                Recibo:
                                <a href="/download/{{ $file->id }}">
                                    <img src="{{ '/imagenes/pdf-icon.png' }}" alt="PDF" />
                                </a>
                            @endif
                        @endforeach
                        @if($operator->files()->where('type', 'pdf')->count()==0)
                            <a href="/files/operator_receipt/{{ $operator->id }}"><i class="fa fa-upload"></i> Recibo firmado</a>
                        @endif

                        {{--
                        {{ $operator->files->count()==1 ? '1 guardado' : ($operator->files->count()!=0 ?
                            $operator->files->count().' guardados' : '') }}
                        --}}

                        @if(($operator->confirmation_flags[3]==0&&($user->id==$operator->who_delivers||
                            $user->id==$operator->who_receives))||$user->priv_level==4)
                            &ensp;
                            <a href="/files/operator/{{ $operator->id }}" class="pull-right">
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
        {!! $operators->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="fancy_table table_brown" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'operators','id'=>0))
    </div>

@endsection

@section('footer')
    @parent
@endsection

@section('javascript')
    <script src="{{ asset('app/js/fix_table_header.js') }}"></script> {{-- For fixed header --}}
    <script src="{{ asset('app/js/set_current_url.js') }}"></script> {{-- For recording current url --}}
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
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
                text = "Confirma que entregó el equipo?";
            }
            else if(flag=='confirm_reception'){
                text = "Confirma que recibió el equipo?";
            }

            var r = confirm(text);
            if (r == true) {
                $.post('/flag/operator', { flag: flag, id: id }, function(data){
                    //$(element).parent().find('.status').html(data);
                    element.style.color = color;
                });
            }
        }
        */
    </script>
@endsection
