@extends('layouts.projects_structure')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/info_tabs.css") }}">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    <a href="{{ '/assignment' }}" class="btn btn-primary" title="Ir a resumen de asignaciones">
        <i class="fa fa-arrow-up"></i> Asig.
    </a>
    @include('app.project_navigation_button', array('user'=>$user))
    <div class="btn-group">
        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
            <i class="fa fa-map-marker"></i> Sitios <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li>
                <a href="/site/{{ $site->assignment->id }}">
                    <i class="fa fa-bars fa-fw"></i> Resumen
                </a>
            </li>
            @if(($site->assignment && $site->assignment->status != $site->assignment->last_stat()/*'Concluído'*/ &&
                $site->assignment->status != 0/*'No asignado'*/ && $user->priv_level >= 1) || $user->priv_level == 4)
                <li>
                    <a href="/site/{{ $site->assignment ? $site->assignment->id : 0 }}/create">
                        <i class="fa fa-plus fa-fw"></i> Nuevo Sitio
                    </a>
                </li>
            @endif
            <li class="dropdown-submenu">
                <a href="#" data-toggle="dropdown"><i class="fa fa-list fa-fw"></i> Lista de materiales de cliente</a>
                <ul class="dropdown-menu dropdown-menu-prim">
                    <li>
                        <a href="{{ '/client_listed_material?client='.($site->assignment ? $site->assignment->client : 'all') }}">
                            <i class="fa fa-list fa-fw"></i> Ver materiales
                        </a>
                    </li>
                    <li>
                        <a href="/import/client_listed_materials/{{ $site->assignment ? $site->assignment->id : 0 }}">
                            <i class="fa fa-upload fa-fw"></i> Importar lista de materiales
                        </a>
                    </li>
                </ul>
            </li>

            @if($site->assignment)
                <li>
                    <a href="/excel/report/per-assignment-progress/{{ $site->assignment->id }}">
                        <i class="fa fa-file-excel-o fa-fw"></i> Reporte de avance general
                    </a>
                </li>
                <li>
                    <a href="/excel/load_format/tracking-report/{{ $site->assignment->id }}">
                        <i class=" fa fa-file-excel-o fa-fw"></i> Tracking report
                    </a>
                </li>
                @if($user->action->prj_vtc_rep /*$user->priv_level>=3*/)
                    <li>
                        <a href="{{ '/site/expense_report/stipend/'.$site->assignment->id }}">
                            <i class="fa fa-money fa-fw"></i> Reporte de gastos
                        </a>
                    </li>
                @endif
            @endif
        </ul>
    </div>

    @if($user->priv_level>=2)
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchBox">
            <i class="fa fa-search"></i> Buscar
        </button>
    @endif
    
    <a href="{{ '/stipend_request?asg='.($site->assignment ? $site->assignment->id : 0) }}" class="btn btn-primary">
        <i class="fa fa-money"></i> Viáticos
    </a>
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

        <div class="panel panel-sky">
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    <div class="pull-left">
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#details" data-toggle="tab"> Información general</a></li>
                            @if($user->action->prj_vw_eco
                                /*(($user->area=='Gerencia General'||$user->area=='Gerencia Administrativa')&&
                                    $user->priv_level==2)||$user->priv_level>=3*/)
                                <li><a href="#payments" data-toggle="tab"> Pagos</a></li>
                            @endif
                            <li><a href="#documents" data-toggle="tab"> Documentos</a></li>
                            @if($site->assignment->type=='Radiobases')
                                @if($site->rbs_char)
                                    <li><a href="#rbs_info" data-toggle="tab">RBS</a></li>
                                @endif
                            @endif
                            <li><a href="#viatics" data-toggle="tab">Viáticos</a></li>
                        </ul>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <!--<div class="panel-title">Información de sitio</div>-->
            </div>
            <div class="panel-body">

                <div class="tab-content">

                    <div class="tab-pane fade in active" id="details">

                        <div class="col-lg-5 mg20">
                            <a href="#" onclick="history.back();" class="btn btn-warning">
                                <i class="fa fa-arrow-circle-left"></i> Atrás
                            </a>
                            <a href="/site/{{ $site->assignment->id }}" class="btn btn-warning">
                                <i class="fa fa-arrow-circle-up"></i> Sitios
                            </a>
                        </div>
                        @if($user->action->prj_st_exp /*$user->area=='Gerencia Tecnica'&&$user->priv_level==2||
                            $user->priv_level>=3*/)
                            <div class="col-lg-7" align="right">
                                <a href="/excel/info/site/{{ $site->id }}" class="btn btn-success">
                                    <i class="fa fa-file-excel-o"></i> Exportar a Excel
                                </a>
                            </div>
                        @endif

                        <div class="col-sm-12 mg10">
                            @include('app.session_flashed_messages', array('opt' => 0))
                        </div>

                        <div class="col-sm-12 mg10 mg-tp-px-10">
                            <table class="table table-striped table-hover table-bordered">
                                <tbody>
                                <tr>
                                    <th width="25%">Código:</th>
                                    <td width="25%">{{ $site->code }}</td>
                                </tr>
                                <tr>
                                    <th>Sitio:</th>
                                    <td colspan="3">{{ $site->name }}</td>
                                </tr>
                                <tr>
                                    <th>Asignación:</th>
                                    <td colspan="3">
                                        <a href="/assignment/{{ $site->assignment->id }}">{{ $site->assignment->name }}</a>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Cliente:</th>
                                    <td>{{ $site->assignment->client }}</td>
                                    <th width="25%">Estado:</th>
                                    <td width="25%">{{ $site->statuses($site->status) }}</td>
                                </tr>
                                @if($site->du_id != '' || $site->isdp_account != '')
                                    <tr>
                                        <th>DU ID:</th>
                                        <td>{{ $site->du_id }}</td>
                                        <th width="25%">Cuenta de ISDP:</th>
                                        <td width="25%">{{ $site->isdp_account }}</td>
                                    </tr>
                                @endif
                                <tr><td colspan="4"></td></tr>

                                <tr>
                                    <th>Tipo de sitio</th>
                                    <td>{{ $site->site_type }}</td>
                                    <th>Tipo de trabajo</th>
                                    <td>{{ $site->work_type }}</td>
                                </tr>
                                <tr><td colspan="4"></td></tr>

                                <tr>
                                    <th>Inicio asignado:</th>
                                    <td>
                                        {{ $site->start_line=='0000-00-00 00:00:00' ?
                                            'No especificado' : date_format(new \DateTime($site->start_line), 'd-m-Y') }}
                                    </td>
                                    <th>Deadline:</th>
                                    <td>
                                        {{ $site->deadline=='0000-00-00 00:00:00' ?
                                            'No especificado' : date_format(new \DateTime($site->deadline), 'd-m-Y') }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Fecha inicio:</th>
                                    <td>{{ date_format(new \DateTime($site->start_date), 'd-m-Y') }}</td>
                                    <th>Fecha fin:</th>
                                    <td>{{ date_format(new \DateTime($site->end_date), 'd-m-Y') }}</td>
                                </tr>
                                <tr><td colspan="4"></td></tr>

                                @if($site->latitude!=0&&$site->longitude!=0)
                                    <tr>
                                        <th colspan="2" rowspan="2">
                                            {{ 'Coordenadas '.$site->origin_name }}<br>
                                            Orígen (Tx)&ensp;
                                            <a href="http://maps.google.com/?daddr={{ $site->latitude.','.$site->longitude }}"
                                                target="_blank" style="font-weight: normal" title="Ver coordenadas de sitio en google maps">
                                                <i class="fa fa-map-marker"></i> Ver en mapa
                                            </a>
                                        </th>
                                        <td>Latitud</td>
                                        <td align="center">{{ $site->latitude }}</td>
                                    </tr>
                                    <tr>
                                        <td>Longitud</td>
                                        <td align="center">{{ $site->longitude }}</td>
                                    </tr>
                                @endif
                                @if($site->lat_destination!=0&&$site->long_destination!=0)
                                    <tr>
                                        <th colspan="2" rowspan="2">
                                            {{ 'Coordenadas '.$site->destination_name }}<br>
                                            Destino (Rx)&ensp;
                                            <a href="http://maps.google.com/?daddr={{ $site->lat_destination.','.
                                                $site->long_destination }}"
                                               target="_blank" style="font-weight: normal" title="Ver coordenadas de sitio en google maps">
                                                <i class="fa fa-map-marker"></i> Ver en mapa
                                            </a>
                                        </th>
                                        <td>Latitud</td>
                                        <td align="center">{{ $site->lat_destination }}</td>
                                    </tr>
                                    <tr>
                                        <td>Longitud</td>
                                        <td align="center">{{ $site->long_destination }}</td>
                                    </tr>
                                @endif

                                <tr>
                                    <th>Departamento</th>
                                    <td colspan="3">{{ $site->department }}</td>
                                </tr>
                                <tr>
                                    <th>Localidad</th>
                                    <td colspan="3">{{ $site->municipality }}</td>
                                </tr>
                                <tr>
                                    <th>Tipo</th>
                                    <td colspan="3">{{ $site->type_municipality }}</td>
                                </tr>
                                <tr><td colspan="4"></td></tr>

                                <tr>
                                    <th>Resp. ABROS</th>
                                    <td colspan="3">{{ $site->responsible ? $site->responsible->name : 'Sin asignar' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ 'Resp. '.$site->assignment->client }}</th>
                                    <td colspan="3">
                                        @if($site->contact)
                                            <a href="/contact/{{ $site->contact->id }}">{{ $site->contact->name }}</a>
                                        @endif
                                    </td>
                                </tr>
                                <tr><td colspan="4"></td></tr>

                                @if($user->area=='Gerencia Tecnica'||$user->priv_level>=3)
                                    <tr>
                                        <th colspan="4">Desarrollo del sitio:</th>
                                    </tr>
                                    <tr>
                                        <td colspan="4" style="text-align:center">
                                            <a href="/task/{{ $site->id }}">{{ 'Ver items' }}</a>
                                            @if(($user->area=='Gerencia Tecnica'&&$user->priv_level>=1)||$user->priv_level>=3)
                                            &emsp;{{ ' | ' }}&emsp;
                                            <a href="/event/site/{{ $site->id }}">{{ 'Ver eventos' }}</a>
                                            &emsp;{{ ' | ' }}&emsp;
                                            <a href="{{ '/dead_interval?st_id='.$site->id }}">{{ 'Ver tiempos muertos' }}</a>
                                            @endif
                                        </td>
                                    </tr>
                                    @if($site->observations)
                                        <tr><td colspan="4"></td></tr>
                                        <tr>
                                            <th>Observaciones</th>
                                            <td colspan="3">{{ $site->observations }}</td>
                                        </tr>
                                    @endif
                                    <tr><td colspan="4"></td></tr>
                                @endif

                                <tr>
                                    <th colspan="4">
                                        Ordenes asociadas:
                                        {{--
                                        @if(!in_array($site->status,array($site->last_stat(),0)))
                                            <a href="/join/site-to-order/{{ $site->id }}" class="pull-right">
                                                <i class="fa fa-link"></i> Asociar una orden
                                            </a>
                                        @endif
                                        --}}
                                    </th>
                                </tr>
                                <tr>
                                    <th colspan="4" style="padding-left: 20px; text-align:center">
                                        <table width="100%">
                                            <thead>
                                            <tr>
                                                {{--<td width="4%"></td>--}}
                                                <td width="25%">Orden</td>
                                                <td>Monto asignado</td>
                                                <td align="center">Cobrado</td>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @if($site->order)
                                                <tr>
                                                    <td>
                                                        <a href="/order/{{ $site->order->id }}">
                                                            {{ $site->order->type.' - '.$site->order->code }}
                                                        </a>
                                                    </td>
                                                    <td align="right">{{ $site->order->assigned_price.' Bs' }}</td>
                                                    <td align="center">
                                                        @if($site->order->status == 'Pendiente')
                                                            <i
                                                            @if(!in_array($site->status,
                                                                array($site->last_stat()/*'Concluído'*/,0/*'No asignado'*/)))
                                                                onclick="flag_status(this,flag='site-to-order',
                                                                    master_id='{{ $site->order->id }}',id='{{ $site->id }}');"
                                                            @endif
                                                            class="fa fa-square-o"
                                                            title="{{ !in_array($site->status,
                                                                array($site->last_stat()/*'Concluído'*/,0/*'No asignado'*/)) ?
                                                                'Marcar como cobrado' : 'Pendiente de cobro' }}"></i>
                                                        @else
                                                            <i class="fa fa-check-square-o" title="Cobrado" style="color:green;"></i>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                            {{--
                                            @foreach($site->orders as $order)
                                                <tr>
                                                    <td>
                                                        @if($order->pivot->status==0&&
                                                            !in_array($site->status,
                                                                array($site->last_stat(), 0)))
                                                            <a href="/detach/site/{{ 'st-'.$site->id }}/{{ $order->id }}"
                                                               style="color: red" title="Eliminar asociación">
                                                                <i class="fa fa-times"></i>
                                                            </a>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="/order/{{ $order->id }}">
                                                            {{ $order->type.' - '.$order->code }}
                                                        </a>
                                                    </td>
                                                    <td align="right">{{ $order->pivot->assigned_amount.' Bs' }}</td>
                                                    <td align="center">
                                                        @if($order->pivot->status==0)
                                                            <i
                                                            @if(!in_array($site->status,
                                                                array($site->last_stat(),0)))
                                                                onclick="flag_status(this,flag='site-to-order',
                                                                    master_id='{{ $order->id }}',id='{{ $site->id }}');"
                                                            @endif
                                                            class="fa fa-square-o"
                                                            title="{{ !in_array($site->status,
                                                                array($site->last_stat(),0)) ?
                                                                'Marcar como cobrado' : 'No cobrado' }}"></i>
                                                        @else
                                                            <i class="fa fa-check-square-o" title="Cobrado" style="color:green;"></i>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                            --}}
                                            </tbody>
                                        </table>
                                    </th>
                                </tr>

                                <tr><td colspan="4"></td></tr>
                                <tr>
                                    <th colspan="2">Registro creado por</th>
                                    <td colspan="2">{{ $site->user ? $site->user->name : 'N/E' }}</td>
                                </tr>

                                </tbody>
                            </table>
                        </div>
                        @if(($user->action->prj_st_edt /*$user->priv_level>=2*/&&
                            (!in_array($site->status,array($site->last_stat()/*'Concluído'*/,0/*'No asignado'*/))))||
                            $user->priv_level==4)
                            <div class="col-sm-12 mg10" align="center">
                                <a href="/site/{{ $site->id }}/edit" class="btn btn-success">
                                    <i class="fa fa-pencil-square-o"></i> Modificar registro
                                </a>
                            </div>
                        @endif

                    </div>

                    @if($user->action->prj_vw_eco
                        /*(($user->area=='Gerencia General'||$user->area=='Gerencia Administrativa')&&
                            $user->priv_level==2)||$user->priv_level>=3*/)
                        <div class="tab-pane fade" id="payments">
                            @include('app.site_financial_details')
                        </div>
                    @endif

                    <div class="tab-pane fade" id="documents">

                        <div class="col-lg-5 mg20">
                            <a href="#" onclick="history.back();" class="btn btn-warning">
                                <i class="fa fa-arrow-circle-left"></i> Volver
                            </a>
                        </div>

                        <div class="col-sm-12 mg10 mg-tp-px-10">
                            <table class="table table-striped table-hover table-bordered">
                                <tbody>
                                <tr>
                                    <th width="25%">Código:</th>
                                    <td width="25%">{{ $site->code }}</td>
                                </tr>
                                <tr>
                                    <th>Sitio:</th>
                                    <td colspan="3">{{ $site->name }}</td>
                                </tr>
                                <tr>
                                    <th>Cliente:</th>
                                    <td>{{ $site->assignment->client }}</td>
                                    <th width="25%">Estado:</th>
                                    <td width="25%">{{ $site->statuses($site->status) }}</td>
                                </tr>
                                <tr><td colspan="4"></td></tr>

                                <tr>
                                    <th colspan="4">Archivos:</th>
                                </tr>
                                @foreach($site->files as $file)
                                    <tr>
                                        <td>{{ date_format(new \DateTime($file->updated_at), 'd-m-Y') }}</td>
                                        <td colspan="3">
                                            {{ $file->description }}

                                            <div class="pull-right">
                                                @include('app.info_document_options', array('file'=>$file))

                                                {{--
                                                @if($file->name=='ST_'.$site->id.'_asig.pdf')
                                                    @if($user->area=='Gerencia General'||$user->priv_level>=3)

                                                    @else
                                                        Asignación recibida
                                                    @endif
                                                @elseif($file->name=='ST_'.$site->id.'_ctz.xls'||
                                                        $file->name=='ST_'.$site->id.'_ctz.xlsx')
                                                    @if(($user->area=='Gerencia Tecnica'&&$user->priv_level==2)||
                                                        $user->priv_level>=3)

                                                    @else
                                                        Cotización enviada
                                                    @endif
                                                @elseif($file->name=='ST_'.$site->id.'_qty_org.xls'||
                                                        $file->name=='ST_'.$site->id.'_qty_org.xlsx')
                                                    @if(($user->area=='Gerencia Tecnica'&&$user->priv_level==2)||
                                                        $user->priv_level>=3)

                                                    @else
                                                        Planilla de cantidades original enviada
                                                    @endif
                                                @elseif($file->name=='ST_'.$site->id.'_qty_sgn.pdf')
                                                    @if(($user->area=='Gerencia Tecnica'&&$user->priv_level==2)||
                                                        $user->priv_level>=3)

                                                    @else
                                                        Planilla de cantidades firmada recibida
                                                    @endif
                                                @elseif($file->name=='ST_'.$site->id.'_cst_org.xls'||
                                                        $file->name=='ST_'.$site->id.'_cst_org.xlsx')
                                                    @if(($user->area=='Gerencia Tecnica'&&$user->priv_level==2)||
                                                        $user->priv_level>=3)

                                                    @else
                                                        Planilla económica original enviada
                                                    @endif
                                                @elseif($file->name=='ST_'.$site->id.'_cst_sgn.pdf')
                                                    @if(($user->area=='Gerencia Tecnica'&&$user->priv_level==2)||
                                                        $user->priv_level>=3)

                                                    @else
                                                        Planilla económica firmada recibida
                                                    @endif
                                                @elseif($file->name=='ST_'.$site->id.'_qcc.pdf')
                                                    @if(($user->area=='Gerencia Tecnica'&&$user->priv_level==2)||
                                                        $user->priv_level>=3)

                                                    @else
                                                        Certificado de control de calidad recibido
                                                    @endif
                                                @elseif($file->name=='ST_'.$site->id.'_sch.xls'||
                                                        $file->name=='ST_'.$site->id.'_sch.xlsx')
                                                    @if($user->area=='Gerencia Tecnica'||$user->priv_level>=3)

                                                    @endif
                                                @endif
                                                --}}
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach

                                @if(/*$site->files->count()<8&&*/
                                    (!in_array($site->status,array($site->last_stat()/*'Concluído'*/,0/*'No asignado'*/))))
                                    <tr>
                                        <th colspan="4" style="text-align: center">
                                            <a href="/files/site/{{ $site->id }}">
                                                <i class="fa fa-upload"></i> Subir archivo
                                            </a>
                                        </th>
                                    </tr>
                                @endif

                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if($site->assignment->type=='Radiobases')
                        @if($site->rbs_char)
                            <div class="tab-pane fade" id="rbs_info">
                                @include('app.site_rbs_info', array('rbs_char' => $site->rbs_char))
                            </div>
                        @endif
                    @endif

                    <div class="tab-pane fade" id="viatics">

                        <div class="col-lg-5 mg20">
                            <a href="#" onclick="history.back();" class="btn btn-warning">
                                <i class="fa fa-arrow-circle-left"></i> Volver
                            </a>
                        </div>

                        <div class="col-sm-12 mg10 mg-tp-px-10">
                            <table class="table table-striped table-hover table-bordered">
                                <tbody>
                                <tr>
                                    <th width="25%">Código:</th>
                                    <td width="25%">{{ $site->code }}</td>
                                </tr>
                                <tr>
                                    <th>Sitio:</th>
                                    <td colspan="3">{{ $site->name }}</td>
                                </tr>
                                <tr>
                                    <th>Cliente:</th>
                                    <td>{{ $site->assignment->client }}</td>
                                    <th width="25%">Estado:</th>
                                    <td width="25%">{{ $site->statuses($site->status) }}</td>
                                </tr>
                                <tr><td colspan="4"></td></tr>

                                <tr>
                                    <th colspan="4">Solicitudes de viáticos:</th>
                                </tr>
                                <tr>
                                    <td>Código</td>
                                    <td>Fecha</td>
                                    <td colspan="2">Estado</td>
                                    {{--<td>Depósito</td>--}}
                                </tr>
                                @foreach($site->stipend_requests as $stipend_request)
                                    <tr>
                                        <td>
                                            <a href="/stipend_request/{{ $stipend_request->id }}"
                                               title="Ver información de solicitud de viáticos">
                                                {{ $stipend_request->code }}
                                            </a>
                                        </td>
                                        <td>{{ date_format($stipend_request->created_at, 'd-m-Y') }}</td>
                                        <td colspan="2">{{ App\StipendRequest::$stats[$stipend_request->status] }}</td>
                                        {{--
                                        <td>
                                            php $total = 0;
                                            foreach($viatic->technician_requests as $request)
                                                php $total += $request->total_deposit
                                            endforeach
                                            {{ number_format($total/($viatic->num_sites==0 ? 1 : $viatic->num_sites)).' Bs' }}
                                        </td>
                                        --}}
                                    </tr>
                                @endforeach
                                @if($site->stipend_requests->count()==0)
                                    <tr>
                                        <td colspan="4" align=center>No existen solicitudes para este sitio</td>
                                    </tr>
                                @endif

                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @if($site->assignment)
            @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'sites','id'=>$site->assignment->id))
            {{-- @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'sites','id'=>0)) --}}
        @endif
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

        function flag_status(element, flag, master_id, id){
            var text = "Confirma que esta orden ha sido cobrada?";

            var r = confirm(text);
            if (r === true) {
                $.post('/status_update/charge', { flag: flag, master_id: master_id, id: id }, function(/*data*/){
                    element.style.color = "green";
                    $(element).toggleClass("fa-square-o fa-check-square-o");
                });
            }
        }
    </script>
@endsection
