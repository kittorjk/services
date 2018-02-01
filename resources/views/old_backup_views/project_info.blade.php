@extends('layouts.master')

@section('header')
    @parent
@endsection

@section('content')

<div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

    <div class="panel panel-sky" >
        <div class="panel-heading" align="center">
            <div class="panel-title">Información de proyecto</div>
        </div>
        <div class="panel-body" >
            <div class="col-lg-4 mg20">
                <a href="/project" class="btn btn-warning"><i class="fa fa-arrow-circle-left"></i> Volver</a>
            </div>
            <div class="col-lg-8" align="right">
                @if($user->area=='Gerencia Tecnica'&&$user->priv_level==2||$user->priv_level>=3)
                    <a href="/excel/project/{{ $project->id }}" class="btn btn-success"><i class="fa fa-file-excel-o"></i> Exportar a Excel </a>
                @endif
                @if($project->status>=2&&$user->priv_level>=3)
                    <a href="/ec_resume/{{ $project->id }}" class="btn btn-success"><i class="fa fa-money"></i> Resumen económico</a>
                @endif
            </div>
            @if (Session::has('message'))
                <div class="alert alert-danger" align="center">{{ Session::get('message') }}</div>
            @endif

            <div class="col-sm-12 mg10 mg-tp-px-10">
                <table class="table table-striped table-hover table-bordered">
                    <tbody>
                    <tr>
                        <th width="28%">Código:</th>
                        <td width="20%">{{ 'PR-'.str_pad($project->id, 4, "0", STR_PAD_LEFT).date_format($project->created_at,'-y') }}</td>
                        <th width="28%">Estado:</th>
                        <td>
                            @if($project->status<=10)
                                {{ 'Etapa '.$project->status.' de 10' }}
                            @elseif($project->status==11)
                                {{ 'Concluído' }}
                            @elseif($project->status==12)
                                {{ 'No asignado' }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Proyecto:</th>
                        <td>{{ $project->name }}</td>
                        <th>Cliente:</th>
                        <td>{{ $project->client }}</td>
                    </tr>
                    <tr><td colspan="4"></td></tr>

                    @if($user->area=='Gerencia Tecnica'||$user->priv_level>=3)
                        <tr>
                            <th colspan="4">Desarollo del proyecto:</th>
                        </tr>
                        <tr>
                            <th colspan="4" style="text-align:center"><a href="/event/{{ $project->id }}">{{ 'Ver eventos' }}</a></th>
                        </tr>
                        <tr><td colspan="4"></td></tr>
                    @endif

                    @if($project->asig_file_id<>0)
                    <tr>
                        <th colspan="4">Asignación:</th>
                    </tr>
                        @if($user->area=='Gerencia General'||$user->priv_level>=3)
                    <tr>
                        <td>Código:</td>
                        <td>{{ $project->asig_num }}</td>
                        <td>Plazo para envio de cotización:</td>
                        <td>{{ $project->asig_deadline.' días' }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">Documento de asignación:</td>
                        <td colspan="2">
                            <a href="/download/{{ $project->asig_file_id }}"><img src="/imagenes/pdf-icon.png" alt="PDF"/></a>
                            @if($project->quote_file_id==0)
                            <a href="/files/{{ $project->id }}/proj_rmp">Reemplazar archivo</a>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                        <td>{{ 'Archivo subido el' }}</td>
                        <td>
                            @foreach($dates_of_files as $date_of_file)
                                @if($project->asig_file_id==$date_of_file->id)
                                    {{ date_format(new \DateTime($date_of_file->created_at), 'd-m-Y') }}
                                @endif
                            @endforeach
                        </td>
                    </tr>
                        @else
                            <tr><td colspan="4">Documento recibido</td></tr>
                        @endif
                    <tr><td colspan="4"></td></tr>
                    @endif
                    @if($project->quote_file_id<>0)
                    <tr>
                        <th colspan="4">Cotización:</th>
                    </tr>
                        @if(($user->area=='Gerencia Tecnica'&&$user->priv_level==2)||$user->priv_level>=3)
                    <tr>
                        <td>Monto cotizado (bruto):</td>
                        <td>{{ number_format($project->quote_amount,2).' Bs' }}</td>
                        <td>Monto cotizado (neto):</td>
                        <td>{{ number_format($project->quote_amount*0.87,2).' Bs' }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">Documento de cotización:</td>
                        <td colspan="2">
                            <a href="/download/{{ $project->quote_file_id }}"><img src="/imagenes/excel-icon.png" alt="EXCEL"/></a>
                            @if($project->pc_org_id==0&&$project->status<12)
                                <a href="/files/{{ $project->id }}/proj_rmp">Reemplazar archivo</a>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                        <td>{{ 'Archivo subido el' }}</td>
                        <td>
                            @foreach($dates_of_files as $date_of_file)
                                @if($project->quote_file_id==$date_of_file->id)
                                    {{ date_format(new \DateTime($date_of_file->created_at), 'd-m-Y') }}
                                @endif
                            @endforeach
                        </td>
                    </tr>
                        @else
                            <tr><td colspan="4">Cotización enviada</td></tr>
                        @endif
                    <tr><td colspan="4"></td></tr>
                    @endif
                    @if($project->pc_org_id<>0)
                    <tr>
                        <th colspan="4">Pedido de compra:</th>
                    </tr>
                        @if($project->pc_sgn_id==0)
                            @if($user->area=='Gerencia General'||$user->priv_level>=3)
                        <tr>
                            <td colspan="2">Pedido de compra original recibido:</td>
                            <td colspan="2">
                                <a href="/download/{{ $project->pc_org_id }}"><img src="/imagenes/pdf-icon.png" alt="PDF"/></a>
                                <a href="/files/{{ $project->id }}/proj_rmp">Reemplazar archivo</a>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"></td>
                            <td>{{ 'Archivo subido el' }}</td>
                            <td>
                                @foreach($dates_of_files as $date_of_file)
                                    @if($project->pc_org_id==$date_of_file->id)
                                        {{ date_format(new \DateTime($date_of_file->created_at), 'd-m-Y') }}
                                    @endif
                                @endforeach
                            </td>
                        </tr>
                            @else
                                <tr><td colspan="4">Pedido de compra recibido</td></tr>
                            @endif
                        @endif
                        @if($project->pc_sgn_id<>0)
                            @if($user->area=='Gerencia General'||$user->priv_level>=3)
                        <tr>
                            <td>Monto asignado por el cliente (bruto):</td>
                            <td>{{ number_format($project->pc__amount,2).' Bs' }}</td>
                            <td>Monto asignado por el cliente (neto):</td>
                            <td>{{ number_format($project->pc__amount*0.87,2).' Bs' }}</td>
                        </tr>
                        <tr>
                            <td>Fecha de inicio de trabajos:</td>
                            <td>{{ date_format(new \DateTime($project->ini_date), 'd-m-Y') }}</td>
                            <td>Plazo para conclusión de proyecto:</td>
                            <td>{{ $project->pc_deadline.' días' }}</td>
                        </tr>
                        <tr>
                            <td>Observaciones de inicio:</td>
                            <td colspan="3">{{ $project->ini_obs }}</td>
                        </tr>
                        <tr>
                            <td colspan="2">Pedido de compra original recibido:</td>
                            <td colspan="2"><a href="/download/{{ $project->pc_org_id }}"><img src="/imagenes/pdf-icon.png" alt="PDF"/></a></td>
                        </tr>
                        <tr>
                            <td colspan="2"></td>
                            <td>{{ 'Archivo subido el' }}</td>
                            <td>
                                @foreach($dates_of_files as $date_of_file)
                                    @if($project->pc_org_id==$date_of_file->id)
                                        {{ date_format(new \DateTime($date_of_file->created_at), 'd-m-Y') }}
                                    @endif
                                @endforeach
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">Pedido de compra firmado enviado:</td>
                            <td colspan="2">
                                <a href="/download/{{ $project->pc_sgn_id }}"><img src="/imagenes/pdf-icon.png" alt="PDF"/></a>
                                @if($project->matsh_org_id==0)
                                    <a href="/files/{{ $project->id }}/proj_rmp">Reemplazar archivo</a>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"></td>
                            <td>{{ 'Archivo subido el' }}</td>
                            <td>
                                @foreach($dates_of_files as $date_of_file)
                                    @if($project->pc_sgn_id==$date_of_file->id)
                                        {{ date_format(new \DateTime($date_of_file->created_at), 'd-m-Y') }}
                                    @endif
                                @endforeach
                            </td>
                        </tr>
                            @else
                                <tr><td colspan="4">Pedido de compra firmado y enviado</td></tr>
                            @endif
                        @endif
                    <tr><td colspan="4"></td></tr>
                    @endif

                    @if($project->status>=3&&($user->area=='Gerencia Tecnica'||$user->priv_level>=3))
                        <tr>
                            <th colspan="4">Cronograma:</th>
                        </tr>
                        @if($project->sch_file_id==0&&$project->status<=10)
                            <tr>
                                <th colspan="4" style="text-align: center"><a href="/files/{{ $project->id }}/schedule">Subir archivo</a></th>
                            </tr>
                        @elseif($project->sch_file_id!=0)
                            <tr>
                                <td colspan="2"></td>
                                <td colspan="2">
                                    <a href="/download/{{ $project->sch_file_id }}"><img src="/imagenes/excel-icon.png" alt="EXCEL"/></a>
                                    @if($project->status<=10)
                                        <a href="/files/{{ $project->id }}/schedule">Reemplazar archivo</a>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2"></td>
                                <td>{{ 'Archivo subido el' }}</td>
                                <td>
                                    @foreach($dates_of_files as $date_of_file)
                                        @if($project->sch_file_id==$date_of_file->id)
                                            {{ date_format(new \DateTime($date_of_file->created_at), 'd-m-Y') }}
                                        @endif
                                    @endforeach
                                </td>
                            </tr>
                        @endif
                        <tr><td colspan="4"></td></tr>
                    @endif

                    @if($project->status>=4&&($user->area=='Gerencia General'||$user->priv_level>=3))
                        <tr>
                            <th colspan="4">Poliza de garantía:</th>
                        </tr>
                        @if($project->wty_file_id==0&&$project->status<=10)
                            <tr>
                                <th colspan="4" style="text-align: center"><a href="/files/{{ $project->id }}/warranty">Subir archivo</a></th>
                            </tr>
                        @elseif($project->wty_file_id!=0)
                            <tr>
                                <td colspan="2"></td>
                                <td colspan="2">
                                    <a href="/download/{{ $project->wty_file_id }}"><img src="/imagenes/pdf-icon.png" alt="PDF"/></a>
                                    @if($project->status<=10)
                                        <a href="/files/{{ $project->id }}/warranty">Reemplazar archivo</a>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2"></td>
                                <td>{{ 'Archivo subido el' }}</td>
                                <td>
                                    @foreach($dates_of_files as $date_of_file)
                                        @if($project->wty_file_id==$date_of_file->id)
                                            {{ date_format(new \DateTime($date_of_file->created_at), 'd-m-Y') }}
                                        @endif
                                    @endforeach
                                </td>
                            </tr>
                        @endif
                        <tr><td colspan="4"></td></tr>
                    @endif

                @if(($user->area=='Gerencia Tecnica'&&$user->priv_level==2)||$user->priv_level>=3)
                    @if($project->matsh_org_id<>0)
                    <tr>
                        <th colspan="4">Planilla de cantidades:</th>
                    </tr>
                    <tr>
                        <td colspan="2">Planilla original:</td>
                        <td colspan="2">
                            <a href="/download/{{ $project->matsh_org_id }}"><img src="/imagenes/excel-icon.png" alt="EXCEL"/></a>
                            @if($project->matsh_sgn_id==0)
                                <a href="/files/{{ $project->id }}/proj_rmp">Reemplazar archivo</a>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                        <td>{{ 'Archivo subido el' }}</td>
                        <td>
                            @foreach($dates_of_files as $date_of_file)
                                @if($project->matsh_org_id==$date_of_file->id)
                                    {{ date_format(new \DateTime($date_of_file->created_at), 'd-m-Y') }}
                                @endif
                            @endforeach
                        </td>
                    </tr>
                        @if($project->matsh_sgn_id<>0)
                        <tr>
                            <td colspan="2">Planilla firmada:</td>
                            <td colspan="2">
                                <a href="/download/{{ $project->matsh_sgn_id }}"><img src="/imagenes/pdf-icon.png" alt="PDF"/></a>
                                @if($project->costsh_org_id==0)
                                    <a href="/files/{{ $project->id }}/proj_rmp">Reemplazar archivo</a>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"></td>
                            <td>{{ 'Archivo subido el' }}</td>
                            <td>
                                @foreach($dates_of_files as $date_of_file)
                                    @if($project->matsh_sgn_id==$date_of_file->id)
                                        {{ date_format(new \DateTime($date_of_file->created_at), 'd-m-Y') }}
                                    @endif
                                @endforeach
                            </td>
                        </tr>
                        @endif
                    <tr><td colspan="4"></td></tr>
                    @endif
                    @if($project->costsh_org_id<>0)
                    <tr>
                        <th colspan="4">Planilla económica:</th>
                    </tr>
                    <tr>
                        <td>Monto ejecutado (bruto):</td>
                        <td>{{ number_format($project->costsh_amount,2).' Bs' }}</td>
                        <td>Monto ejecutado (neto):</td>
                        <td>{{ number_format($project->costsh_amount*0.87,2).' Bs' }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">Planilla original:</td>
                        <td colspan="2">
                            <a href="/download/{{ $project->costsh_org_id }}"><img src="/imagenes/excel-icon.png" alt="EXCEL"/></a>
                            @if($project->costsh_sgn_id==0)
                                <a href="/files/{{ $project->id }}/proj_rmp">Reemplazar archivo</a>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                        <td>{{ 'Archivo subido el' }}</td>
                        <td>
                            @foreach($dates_of_files as $date_of_file)
                                @if($project->costsh_org_id==$date_of_file->id)
                                    {{ date_format(new \DateTime($date_of_file->created_at), 'd-m-Y') }}
                                @endif
                            @endforeach
                        </td>
                    </tr>
                    @if($project->costsh_sgn_id<>0)
                    <tr>
                        <td colspan="2">Planilla firmada:</td>
                        <td colspan="2">
                            <a href="/download/{{ $project->costsh_sgn_id }}"><img src="/imagenes/pdf-icon.png" alt="PDF"/></a>
                            @if($project->qcc_file_id==0)
                                <a href="/files/{{ $project->id }}/proj_rmp">Reemplazar archivo</a>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                        <td>{{ 'Archivo subido el' }}</td>
                        <td>
                            @foreach($dates_of_files as $date_of_file)
                                @if($project->costsh_sgn_id==$date_of_file->id)
                                    {{ date_format(new \DateTime($date_of_file->created_at), 'd-m-Y') }}
                                @endif
                            @endforeach
                        </td>
                    </tr>
                    @endif
                    <tr><td colspan="4"></td></tr>
                    @endif
                    @if($project->qcc_file_id<>0)
                    <tr>
                        <th colspan="4">Certificado de control de calidad:</th>
                    </tr>
                    <tr>
                        <td colspan="2">Certificado:</td>
                        <td colspan="2">
                            <a href="/download/{{ $project->qcc_file_id }}"><img src="/imagenes/pdf-icon.png" alt="PDF"/></a>
                            @if($project->bill_number==0)
                                <a href="/files/{{ $project->id }}/proj_rmp">Reemplazar archivo</a>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                        <td>{{ 'Archivo subido el' }}</td>
                        <td>
                            @foreach($dates_of_files as $date_of_file)
                                @if($project->qcc_file_id==$date_of_file->id)
                                    {{ date_format(new \DateTime($date_of_file->created_at), 'd-m-Y') }}
                                @endif
                            @endforeach
                        </td>
                    </tr>
                    <tr><td colspan="4"></td></tr>
                    @endif
                @endif
                    @if($project->bill_number<>0)
                    <tr>
                        <th colspan="4">Datos de factura:</th>
                    </tr>
                        @if(($user->area=='Gerencia General'&&$user->priv_level==2)||$user->priv_level>=3)
                    <tr>
                        <td>Número de factura:</td>
                        <td>{{ $project->bill_number }}</td>
                        <td>Fecha de emisión de factura:</td>
                        <td>{{ date_format(new \DateTime($project->bill_date), 'd-m-Y') }}</td>
                    </tr>
                        @else
                            <tr><td colspan="4">Facturado</td></tr>
                        @endif
                    @endif
                    </tbody>
                </table>
            </div>
            @if(($user->priv_level==3&&$project->status<=10)||$user->priv_level==4)
                <div class="col-sm-12 mg10" align="center">
                    <a href="/project/{{ $project->id }}/edit" class="btn btn-success"><i class="fa fa-pencil-square-o"></i> Modificar datos de Proyecto </a>
                    @if($user->priv_level==4)
                        <a href="/action/{{ $project->id }}/stat_chg" class="btn btn-warning"><i class="fa fa-exclamation-triangle"></i> Cambiar estado</a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

@section('footer')
@endsection
