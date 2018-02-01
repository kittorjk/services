@extends('layouts.projects_structure')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="stylesheet" href="{{ asset("app/css/custom_table.css") }}">
    <link rel="stylesheet" href="{{ asset("app/css/progress_bar.css") }}">
@endsection

@section('menu_options')
    @include('app.project_navigation_button', array('user'=>$user))
    <div class="btn-group">
        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
            <i class="fa fa-file-text"></i> Ordenes de Compra <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li><a href="{{ '/order' }}"><i class="fa fa-bars fa-fw"></i> Resumen </a></li>
            <li><a href="{{ '/order/create' }}"><i class="fa fa-plus fa-fw"></i> Nueva orden </a></li>
            <li><a href="{{ '/order?stat=Pendiente' }}"><i class="fa fa-list-ul fa-fw"></i> Ver órdenes pendientes</a></li>
            <li><a href="{{ '/order?stat=Cobrado' }}"><i class="fa fa-check fa-fw"></i> Ver órdenes cobradas</a></li>
            <li><a href="{{ '/order?stat=Anulado' }}"><i class="fa fa-ban fa-fw"></i> Ver órdenes anuladas</a></li>
            {{--
            @if($user->priv_level==4)
                <li><a href="/delete/order"><i class="fa fa-trash-o"></i> Borrar archivo</a></li>
            @endif
            --}}
            @if($user->priv_level>=3)
                <li class="divider"></li>
                <li><a href="{{ '/excel/orders' }}"><i class="fa fa-file-excel-o fa-fw"></i> Exportar a Excel </a></li>
            @endif
        </ul>
    </div>
    @if($user->priv_level>=2)
        <!--<a href="/search/orders/0" class="btn btn-primary"><i class="fa fa-search"></i> Buscar </a>-->
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchBox">
            <i class="fa fa-search"></i> Buscar
        </button>
    @endif
@endsection

@section('content')
    @if($recent_qcc!=0)
        <div class="col-sm-12 mg10">
            <div class="alert alert-info" align="center">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <i class="fa fa-info-circle fa-2x pull-left"></i>
                <a href="{{ '/recent_qcc' }}">
                    {{ $recent_qcc==1 ? 'Un certificado de control de calidad fue subido en los últimos 7 días' :
                        $recent_qcc.' certificados de control de calidad fueron subidos en los últimos 7 días' }}
                </a>
            </div>
        </div>
    @endif

    <div class="col-sm-12 mg10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10">
        <p>
            {{ $orders->total()==1 ? 'Se encontró: 1 registro' : 'Se encontraron: '.$orders->total().' registros' }}
        </p>

        <table class="formal_table table_blue tablesorter" id="fixable_table">
            <thead>
            <tr>
                <th>Número de orden</th>
                <th>Cliente</th>
                <th>Estado</th>
                @if(($user->area=='Gerencia General'&&$user->priv_level==2)||$user->priv_level>=3)
                    <th width="10%">% cobrado</th>
                    <th width="12%" class="{sorter: 'digit'}">Tiempo transcurrido</th>
                @endif
                <th width="12%">Archivos</th>
                <th width="18%">Asociaciones</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($orders as $order)
                <tr class="accordion-toggle" data-toggle="collapse" data-parent="#accordion"
                    data-target="{{ '#collapse'.$order->id }}">
                    <td>
                        @if($order->status=='Pendiente')
                            <a href="/order_upstat/{{ 'ch-'.$order->id }}" class="confirmation" style="color: inherit">
                                <i class="fa fa-check pull-left" title="Marcar orden como cobrada"></i>
                            </a>
                            <a href="/order_upstat/{{ 'nl-'.$order->id }}" class="confirmation" style="color: inherit">
                                <i class="fa fa-ban pull-left" title="Anular Orden"></i>
                            </a>
                        @endif
                        &emsp;
                        <a href="/order/{{ $order->id }}">{{ $order->type.' - '.$order->code }}</a>
                    </td>
                    <td>{{ $order->client }}</td>
                    <td>{{ $order->status }}</td>

                    @if(($user->area=='Gerencia General'&&$user->priv_level==2)||$user->priv_level>=3)
                        <td align="center">
                            <div class="progress">
                                <div class="progress-bar progress-bar-success" style="{{ 'width: '.number_format(($order->charged_price/$order->assigned_price)*100,2).'%' }}">
                                    <span>{{ number_format(($order->charged_price/$order->assigned_price)*100,2).' %' }}</span>
                                </div>
                            </div>
                        </td>
                        <td align="center">
                            @if($order->status!='Cobrado'&&$order->status!='Anulado')
                                @if($current_date->diffInDays($order->date_issued,false)<=0)
                                    @if($current_date->diffInDays($order->date_issued)==1)
                                        <span class="label label-apple uniform_width" style="font-size: 12px">
                                            {{ '1 dia' }}
                                        </span>
                                    @else
                                        @if($current_date->diffInDays($order->date_issued)<=20)
                                            <span class="label label-apple uniform_width" style="font-size: 12px">
                                        @elseif($current_date->diffInDays($order->date_issued)<=40)
                                            <span class="label label-yellow uniform_width" style="font-size: 12px">
                                        @elseif($current_date->diffInDays($order->date_issued)<=60)
                                            <span class="label label-warning uniform_width" style="font-size: 12px">
                                        @elseif($current_date->diffInDays($order->date_issued)>=60)
                                            <span class="label label-danger uniform_width" style="font-size: 12px">
                                        @endif
                                            {{ $current_date->diffInDays($order->date_issued).' dias' }}
                                            </span>
                                    @endif
                                @endif
                            @else
                                <span class="label label-gray uniform_width" style="font-size: 12px">
                                    {{ $order->date_issued->diffInDays($order->updated_at).' dias' }}
                                </span>
                            @endif
                        </td>
                    @endif

                    <td>
                        @if($order->status=='Pendiente')
                            <a href="/files/order/{{ $order->id }}"><i class="fa fa-upload"></i> Subir archivo</a>
                        @endif
                    </td>
                    <td>
                        Ver sitios / facturas
                        {{--<!--
                        @foreach($order->sites as $site)
                            <a href="/site/{{ $site->id }}">{{ $site->assignment->name.' - '.$site->name }}</a><br>
                        @endforeach

                        <a href="/order_asoc/{{ $order->id }}">Ver sitios / facturas</a>

                        @foreach($order->bills as $bill)
                            <a href="/bill/{{ $bill->id }}">{{ $bill->code }}</a><br>
                        @endforeach
                        -->--}}
                        <a data-toggle="collapse" data-parent="#accordion" href="{{ '#collapse'.$order->id }}">
                            <i class="indicator glyphicon glyphicon-chevron-right pull-right"></i>
                        </a>
                    </td>
                </tr>
                <tr style="background-color: transparent" class="tablesorter-childRow expand-child">
                    <td colspan="{{ ($user->area=='Gerencia General'&&$user->priv_level==2)||$user->priv_level>=3 ? '7' : '5' }}"
                        style="padding: 0">
                        <div id="{{ 'collapse'.$order->id }}" class="panel-collapse collapse mg-tp-px-10 col-sm-10 col-sm-offset-1">

                            <div class="col-sm-7">
                                <table class="table table_red">
                                    <tr>
                                        <th style="text-align: center">
                                            {{ 'Sitios asociados ('.$order->sites->count().' de '.$order->number_of_sites.')' }}
                                        </th>
                                    </tr>
                                    <tr>
                                        <td style="background-color: white">
                                            <table width="100%">
                                                <thead>
                                                <tr>
                                                    <td width="4%"></td>
                                                    <td>Sitio</td>
                                                    <td>Asignación</td>
                                                    <td>Monto asignado</td>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($order->sites as $site)
                                                    <tr>
                                                        <td>
                                                            @if($site->pivot->status==0&&$order->status=='Pendiente')
                                                                <a href="/detach/order/{{ 'st-'.$site->id }}/{{ $order->id }}"
                                                                   title="Eliminar asociación" class="confirm_detach"
                                                                   style="color: red">
                                                                    <i class="fa fa-times"></i>
                                                                </a>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <a href="/task/{{ $site->id }}">
                                                                {{ str_limit($site->name,50) }}
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <a href="/site/{{ $site->assignment->id }}"
                                                               title="{{ $site->assignment->name }}">
                                                                {{ str_limit($site->assignment->name,20) }}
                                                            </a>
                                                        </td>
                                                        <td>
                                                            {{ $site->pivot->assigned_amount.' Bs' }}
                                                            @if($site->pivot->status==0)
                                                                <i class="fa fa-square-o pull-right" title="Marcar como cobrado"
                                                                    @if($order->status=='Pendiente')
                                                                        onclick="flag_status(this,flag='order-to-site',
                                                                            master_id='{{ $order->id }}',id='{{ $site->id }}');"
                                                                    @endif
                                                                ></i>
                                                            @else
                                                                <i class="fa fa-check-square-o pull-right"
                                                                   title="Cobrado" style="color:green;"></i>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                @if($order->status=='Pendiente')
                                                    <tr>
                                                        <td colspan="4" align="right">
                                                            <a href="/join/order-to-site/{{ $order->id }}">
                                                                <i class="fa fa-link"></i> Asociar sitio
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endif
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <div class="col-sm-5">
                                <table class="table table_red">
                                    <tr>
                                        <th style="text-align: center">Facturas asociadas</th>
                                    </tr>
                                    <tr>
                                        <td style="background-color: white">
                                            <table width="100%">
                                                <thead>
                                                <tr>
                                                    <td width="4%"></td>
                                                    <td>Número</td>
                                                    <td>Fecha</td>
                                                    <td>Monto facturado</td>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($order->bills as $bill)
                                                    <tr>
                                                        <td>
                                                            @if($bill->pivot->status==0&&$order->status=='Pendiente')
                                                                <a href="/detach/order/{{ 'bl-'.$bill->id }}/{{ $order->id }}"
                                                                   title="Eliminar asociación" class="confirm_detach"
                                                                   style="color: red">
                                                                    <i class="fa fa-times"></i>
                                                                </a>
                                                            @endif
                                                        </td>
                                                        <td><a href="/bill/{{ $bill->id }}">{{ $bill->code }}</a></td>
                                                        <td>{{ date_format(new \DateTime($bill->date_issued), 'd-m-Y') }}</td>
                                                        <td>
                                                            {{ $bill->pivot->charged_amount.' Bs' }}
                                                            @if($bill->pivot->status==0)
                                                                <i class="fa fa-square-o pull-right" title="Marcar como cobrado"
                                                                    @if($order->status=='Pendiente')
                                                                        onclick="flag_status(this,flag='order-to-bill',
                                                                            master_id='{{ $order->id }}',id='{{ $bill->id }}');"
                                                                    @endif
                                                                ></i>
                                                            @else
                                                                <i class="fa fa-check-square-o pull-right" title="Cobrado"
                                                                   style="color:green;"></i>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                @if($order->status=='Pendiente')
                                                    <tr>
                                                        <td colspan="4" align="right">
                                                            <a href="/join/order-to-bill/{{ $order->id }}">
                                                                <i class="fa fa-link"></i> Asociar factura
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endif
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

    </div>
    <div class="col-sm-12 mg10" align="center">
        {!! $orders->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="formal_table table_blue" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'orders','id'=>0))
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
                cssNone: ''
            });
        });

        $('.confirmation').on('click', function () {
            return confirm('Está seguro de que desea cambiar el estado de esta orden?');
        });

        $('.confirm_detach').on('click', function () {
            return confirm('Está seguro de que desea eliminar esta asociación?');
        });

        function flag_status(element, flag, master_id, id){
            var text = "Confirmar";

            if(flag==='order-to-site'){
                text = "Confirma que este sitio ha sido cobrado?";
            }
            else if(flag==='order-to-bill'){
                text = "Confirma que esta factura ha sido cobrada?";
            }

            var r = confirm(text);
            if (r===true) {
                $.post('/status_update/charge', { flag: flag, master_id: master_id, id: id }, function(data){
                    element.style.color = "green";
                    $(element).toggleClass("fa-square-o fa-check-square-o");
                });
            }
        }

        $('.collapse').on('show.bs.collapse', function () {
            $('.collapse.in').collapse('hide');
            $(this).closest('tr').prev(".accordion-toggle").find('.indicator')
                    .toggleClass("glyphicon-chevron-down glyphicon-chevron-right");

        }).on('hide.bs.collapse', function () {
            $(this).closest('tr').prev(".accordion-toggle").find('.indicator')
                    .toggleClass("glyphicon-chevron-right glyphicon-chevron-down");
        });
    </script>
@endsection
