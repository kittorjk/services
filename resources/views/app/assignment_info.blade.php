@extends('layouts.info_master')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/info_tabs.css") }}">
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
                                <li><a href="#payments" data-toggle="tab"> Estado de pagos</a></li>
                            @endif
                            <li><a href="#documents" data-toggle="tab"> Documentos</a></li>
                        </ul>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <!--<div class="panel-title">Información de proyecto</div>-->
            </div>
            <div class="panel-body">

                <div class="tab-content">

                    <div class="tab-pane fade in active" id="details">

                        <div class="col-lg-5 mg20">
                            <a href="#" onclick="history.back();" class="btn btn-warning">
                                <i class="fa fa-arrow-circle-left"></i> Atrás
                            </a>
                            <a href="{{ '/assignment' }}" class="btn btn-warning" title="Ir a la tabla de asignaciones">
                                <i class="fa fa-arrow-circle-up"></i> Asignaciones
                            </a>
                        </div>

                        {{-- Assignment export not yet implemented --}}
                        {{--
                        <div class="col-lg-7" align="right">
                            @if($user->area=='Gerencia Tecnica'&&$user->priv_level==2||$user->priv_level>=3)
                                <a href="/excel/assignment/{{ $assignment->id }}" class="btn btn-success">
                                    <i class="fa fa-file-excel-o"></i> Exportar a Excel
                                </a>
                            @endif
                                Obsolete button
                                @if((($user->area=='Gerencia General'||$user->area=='Gerencia Administrativa')&&
                                    $user->priv_level==2)||$user->priv_level>=3)
                                    <a href="/project_fnc/{{ $assignment->id }}" class="btn btn-success">
                                        <i class="fa fa-money"></i> Resumen económico
                                    </a>
                                @endif

                        </div>
                        --}}

                        <div class="col-sm-12 mg10">
                            @include('app.session_flashed_messages', array('opt' => 0))
                        </div>

                        <div class="col-sm-12 mg10 mg-tp-px-10">
                            <table class="table table-striped table-hover table-bordered">
                                <tbody>
                                <tr>
                                    <th width="25%">Código:</th>
                                    <td width="25%">{{ $assignment->code }}</td>
                                </tr>
                                <tr>
                                    <th>Asignación:</th>
                                    <td colspan="3">{{ $assignment->name }}</td>
                                </tr>
                                @if($assignment->project)
                                    <tr>
                                        <th>Proyecto:</th>
                                        <td colspan="3">
                                            <a href="/project/{{ $assignment->project->id }}">
                                                {{ $assignment->project->name }}
                                            </a>
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <th>Cliente:</th>
                                    <td>{{ $assignment->client }}</td>
                                    <th width="25%">Estado:</th>
                                    <td>{{ $assignment->statuses($assignment->status) }}</td>
                                </tr>
                                <tr><td colspan="4"></td></tr>

                                @if($assignment->statuses($assignment->status)=='Relevamiento')
                                    <tr>
                                        <th>Inicio de relevamiento:</th>
                                        <td>{{ date_format(new \DateTime($assignment->quote_from), 'd-m-Y') }}</td>
                                        <th>Fin de relevamiento:</th>
                                        <td>{{ date_format(new \DateTime($assignment->quote_to), 'd-m-Y') }}</td>
                                    </tr>
                                    <tr><td colspan="4"></td></tr>
                                @elseif($assignment->statuses($assignment->status)!='Cotización')
                                    <tr>
                                        <th>Inicio asignado:</th>
                                        <td>
                                            {{ $assignment->start_line=='0000-00-00 00:00:00' ?
                                                'No especificado' : date_format(new \DateTime($assignment->start_line), 'd-m-Y') }}
                                        </td>
                                        <th>Deadline:</th>
                                        <td>
                                            {{ $assignment->deadline=='0000-00-00 00:00:00' ?
                                                'No especificado' : date_format(new \DateTime($assignment->deadline), 'd-m-Y') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Inicio de ejecución:</th>
                                        <td>{{ date_format(new \DateTime($assignment->start_date), 'd-m-Y') }}</td>
                                        <th>Fin de ejecución:</th>
                                        <td>
                                            {{ $assignment->end_date=='0000-00-00 00:00:00' ? 'N/E' :
                                                date_format(new \DateTime($assignment->end_date), 'd-m-Y') }}
                                        </td>
                                    </tr>
                                    <tr><td colspan="4"></td></tr>
                                @endif

                                <tr>
                                    <th colspan="2">Sucursal encargada</th>
                                    <td colspan="2">
                                        {{ $assignment->branch_record ? $assignment->branch_record->name : 'N/E' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th colspan="2">Responsable ABROS</th>
                                    <td colspan="2">
                                        {{ $assignment->responsible ? $assignment->responsible->name : 'No asignado' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th colspan="2">{{ 'Responsable '.$assignment->client }}</th>
                                    <td colspan="2">
                                        @if($assignment->contact)
                                            <a href="/contact/{{ $assignment->contact->id }}">
                                                {{ $assignment->contact->name }}
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                                <tr><td colspan="4"></td></tr>

                                <tr>
                                    <th colspan="4">Desarrollo de la asignación:</th>
                                </tr>
                                <tr>
                                    <td colspan="4" style="text-align:center">
                                        <a href="/site/{{ $assignment->id }}">{{ 'Ver sitios' }}</a>
                                        @if(($user->area=='Gerencia Tecnica'&&$user->priv_level>=1)||$user->priv_level>=3)
                                        &emsp;{{ ' | ' }}&emsp;
                                        <a href="/event/assignment/{{ $assignment->id }}">{{ 'Ver eventos' }}</a>
                                        &emsp;{{ ' | ' }}&emsp;
                                        <a href="{{ '/dead_interval?assig_id='.$assignment->id }}">{{ 'Ver tiempos muertos' }}</a>
                                        @endif
                                    </td>
                                </tr>

                                @if($assignment->observations)
                                    <tr><td colspan="4"></td></tr>
                                    <tr>
                                        <th>Observaciones</th>
                                        <td colspan="3">{{ $assignment->observations }}</td>
                                    </tr>
                                @endif

                                <tr><td colspan="4"></td></tr>
                                <tr>
                                    <th colspan="2">Registro creado por</th>
                                    <td colspan="2">{{ $assignment->user ? $assignment->user->name : 'N/E' }}</td>
                                </tr>

                                </tbody>
                            </table>
                        </div>
                        @if((/*$user->priv_level>=2*/$user->action->prj_asg_edt&&
                            $assignment->status<$last_stat/*'Concluído'*/&&$assignment->status>0/*'No asignado'*/)||
                            $user->priv_level==4)
                            <div class="col-sm-12 mg10" align="center">
                                <a href="/assignment/{{ $assignment->id }}/edit" class="btn btn-success">
                                    <i class="fa fa-pencil-square-o"></i> Modificar asignación
                                </a>
                            </div>
                        @endif

                    </div>

                    @if($user->action->prj_vw_eco
                        /*(($user->area=='Gerencia General'||$user->area=='Gerencia Administrativa')&&
                        $user->priv_level==2)||$user->priv_level>=3*/)
                        <div class="tab-pane fade" id="payments">
                            @include('app.assignment_financial_details')
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
                                    <td width="25%">{{ $assignment->code }}</td>
                                </tr>
                                <tr>
                                    <th>Asignación:</th>
                                    <td colspan="3">{{ $assignment->name }}</td>
                                </tr>
                                <tr>
                                    <th>Cliente:</th>
                                    <td>{{ $assignment->client }}</td>
                                    <th width="25%">Estado:</th>
                                    <td>{{ $assignment->statuses($assignment->status) }}</td>
                                </tr>
                                <tr><td colspan="4"></td></tr>

                                <tr>
                                    <th colspan="4">Archivos:</th>
                                </tr>
                                @foreach($assignment->files as $file)
                                    <tr>
                                        <td>{{ date_format(new \DateTime($file->updated_at), 'd-m-Y') }}</td>
                                        <td colspan="3">
                                            {{ $file->description }}

                                            <div class="pull-right">
                                                @include('app.info_document_options', array('file'=>$file))

                                                {{--
                                                @if($file->name=='ASG_'.$assignment->id.'_asig.pdf')
                                                    @if($user->area=='Gerencia General'||$user->priv_level>=3)

                                                    @else
                                                        Documento recibido
                                                    @endif
                                                @elseif($file->name=='ASG_'.$assignment->id.'_ctz.xls'||
                                                        $file->name=='ASG_'.$assignment->id.'_ctz.xlsx')
                                                    @if(($user->area=='Gerencia Tecnica'&&$user->priv_level==2)||
                                                        $user->priv_level>=3)

                                                    @else
                                                        Cotización enviada
                                                    @endif
                                                @elseif($file->name=='ASG_'.$assignment->id.'_qty_org.xls'||
                                                        $file->name=='ASG_'.$assignment->id.'_qty_org.xlsx')
                                                    @if(($user->area=='Gerencia Tecnica'&&$user->priv_level==2)||
                                                        $user->priv_level>=3)

                                                    @else
                                                        Planilla de cantidades original enviada
                                                    @endif
                                                @elseif($file->name=='ASG_'.$assignment->id.'_qty_sgn.pdf')
                                                    @if(($user->area=='Gerencia Tecnica'&&$user->priv_level==2)||
                                                        $user->priv_level>=3)

                                                    @else
                                                        Planilla de cantidades firmada recibida
                                                    @endif
                                                @elseif($file->name=='ASG_'.$assignment->id.'_cst_org.xls'||
                                                        $file->name=='ASG_'.$assignment->id.'_cst_org.xlsx')
                                                    @if(($user->area=='Gerencia Tecnica'&&$user->priv_level==2)||
                                                        $user->priv_level>=3)

                                                    @else
                                                        Planilla económica original enviada
                                                    @endif
                                                @elseif($file->name=='ASG_'.$assignment->id.'_cst_sgn.pdf')
                                                    @if(($user->area=='Gerencia Tecnica'&&$user->priv_level==2)||
                                                        $user->priv_level>=3)

                                                    @else
                                                        Planilla económica firmada recibida
                                                    @endif
                                                @elseif($file->name=='ASG_'.$assignment->id.'_qcc.pdf')
                                                    @if(($user->area=='Gerencia Tecnica'&&$user->priv_level==2)||
                                                        $user->priv_level>=3)

                                                    @else
                                                        Certificado de control de calidad recibido
                                                    @endif
                                                @elseif($file->name=='ASG_'.$assignment->id.'_sch.xls'||
                                                        $file->name=='ASG_'.$assignment->id.'_sch.xlsx')
                                                    @if($user->area=='Gerencia Tecnica'||$user->priv_level>=3)

                                                    @endif
                                                @elseif($file->name=='ASG_'.$assignment->id.'_wty.pdf')
                                                    @if($user->area=='Gerencia General'||$user->priv_level>=3)

                                                    @endif
                                                @endif
                                                --}}
                                            </div>
                                        </td>
                                    </tr>
                                    {{--
                                    @if($file->name=='ASG_'.$assignment->id.'_wty.pdf')
                                        <tr>
                                            <td colspan="2"></td>
                                            <td>Vence</td>
                                            <td>
                                                {{ $assignment->guarantee ?
                                                    date_format(new \DateTime($assignment->guarantee->expiration_date), 'd-m-Y') :
                                                     '' }}
                                                @if($user->area=='Gerencia General'||$user->priv_level>=3)
                                                    &ensp;
                                                    <a href="/guarantee/{{ $assignment->guarantee->id }}/edit">
                                                        <i class="fa fa-pencil-square-o"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                    --}}
                                @endforeach
                                @foreach($assignment->guarantees as $guarantee)
                                    <tr>
                                        <td>{{ date_format(new \DateTime($guarantee->start_date), 'd-m-Y') }}</td>
                                        <td colspan="2">Poliza de garantía</td>
                                        <td><a href="/guarantee/{{ $guarantee->id }}">{{ $guarantee->code }}</a></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"></td>
                                        <td>Vence</td>
                                        <td>
                                            {{ date_format(new \DateTime($guarantee->expiration_date), 'd-m-Y') }}
                                            @if(($user->area=='Gerencia General'||$user->priv_level>=3)&&$guarantee->closed==0)
                                                &ensp;
                                                <a href="/guarantee/{{ $guarantee->id }}/edit">
                                                    <i class="fa fa-pencil-square-o"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                @if(/*$assignment->files->count()<8&&*/
                                    (!in_array($assignment->status,array(0,$last_stat)/*('Concluído','No asignado')*/)))
                                    <tr>
                                        <th colspan="4" style="text-align: center">
                                            <a href="/files/assignment/{{ $assignment->id }}">
                                                <i class="fa fa-upload"></i> Subir archivo
                                            </a>
                                        </th>
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

@endsection

@section('footer')
@endsection

@section('javascript')
    <script>
        $('#alert').delay(2000).fadeOut('slow');
    </script>
@endsection
