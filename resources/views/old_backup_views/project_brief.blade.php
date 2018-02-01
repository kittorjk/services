@extends('layouts.master')

@section('header')
    @parent
@endsection

@section('content')
<div class="col-sm-12 mg20 mg-tp-px-10">
    <div class="row">
        <div class="col-sm-8">
            @if($user->priv_level==4)
                <a href="/" class="btn btn-primary"><i class="fa fa-home"></i> Inicio </a>
            @endif
            <div class="btn-group">
                <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle"><i class="fa fa-cogs"></i> Proyectos <span class="caret"></span></button>
                <ul class="dropdown-menu dropdown-menu-prim">
                    <li><a href="/project"><i class="fa fa-bars"></i> Resumen </a></li>
                    <li><a href="/project/create"><i class="fa fa-plus"></i> Nuevo Proyecto </a></li>
                    @if($user->priv_level==4)
                        <li><a href="/delete/project"><i class="fa fa-trash-o"></i> Borrar archivo</a></li>
                    @endif
                    @if($user->priv_level>=3)
                        <li class="divider"></li>
                        <li><a href="/excel/projects"><i class="fa fa-file-excel-o"></i> Exportar a Excel </a></li>
                    @endif
                </ul>
            </div>
            @if($user->priv_level>=2)
                <a href="/search/projects/0" class="btn btn-primary"><i class="fa fa-search"></i> Buscar </a>
            @endif
            <a href="/contact" class="btn btn-primary"><i class="fa fa-phone"></i> Contactos </a>
        </div>
        <div class="col-sm-4" align="right">
            <a href="/user/{{ $user->id }}/edit" class="btn btn-warning"><i class="fa fa-pencil-square-o"></i> Actualizar cuenta </a>
            <div class="btn-group">
                <button type="button" data-toggle="dropdown" class="btn btn-danger dropdown-toggle"><i class="fa fa-user"></i> Mi cuenta <span class="caret"></span></button>
                <ul class="dropdown-menu dropdown-menu-right dropdown-menu-dang">
                    <li><a href="/user/{{ $user->id }}/edit"><i class="fa fa-pencil-square-o"></i> Actualizar datos </a></li>
                    <li class="divider"></li>
                    <li><a href="/logout/{{ $service }}"><i class="fa fa-sign-out"></i> Cerrar sesión</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<div class="col-sm-12 mg10 mg-tp-px-10">
    @if (Session::has('message'))
        <div class="alert alert-info" align="center" id="alert">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>{{ Session::get('message') }}
        </div>
    @endif
</div>
<div class="col-sm-12 mg10 mg-tp-px-10">
    @if($projects->total()==1)
        <p>Se encontró {{ $projects->total() }} registro</p>
    @else
        <p>Se encontraron {{ $projects->total() }} registros</p>
    @endif
    <table class="fancy_table table_blue tablesorter" id="ordenar_tabla">
        <thead>
        <tr>
            <th>Código</th>
            <th width="20%">Proyecto</th>
            <th>Cliente</th>
            @if($user->priv_level>=3)
                <th>Fase</th>
            @endif
            <th width="20%">Última actividad</th>
            <th width="20%">Próxima actividad</th>
            @if(($user->area=='Gerencia Tecnica'&&$user->priv_level==2)||$user->priv_level>=3)
                <th class="{sorter: 'digit'}">Tiempo restante</th>
            @endif
        </tr>
        </thead>
        <tbody>
        @foreach ($projects as $project)
            <tr>
                <td>
                    <a href="/project/{{ $project->id }}">{{ 'PR-'.str_pad($project->id, 4, "0", STR_PAD_LEFT).date_format($project->created_at,'-y') }}</a>
                </td>
                <td>{{ $project->name }}</td>
                <td>{{ $project->client }}</td>
                @if($user->priv_level>=3)
                <td>
                    @if(0<=$project->status&&$project->status<4){{ 'Relevamiento' }}
                    @elseif(4<=$project->status&&$project->status<=7)
                        @if($project->ini_date->diffInDays($current_date,false)>0){{ 'Ejecución' }}
                        @else{{ 'En espera de ejecución' }}
                        @endif
                    @elseif(8<=$project->status&&$project->status<=10){{ 'Cobro' }}
                    @elseif($project->status==11){{ 'Concluído' }}
                    @elseif($project->status==12){{ 'No asignado' }}
                    @endif
                </td>
                @endif
                <td>
                    {{ $etapa[$project->status] }}
                </td>
                <td>
                    @if($project->status<=8)
                        <a href="/files/{{ $project->id }}/project">{{ $next_step[$project->status] }}</a>
                        @if($project->status==2&&$user->priv_level>=3)
                            {{ ' / ' }}
                            <a href="/action/{{ $project->id }}/end" >{{ $next_step[11] }}</a>
                        @endif
                    @elseif($project->status==9)
                        <a href="/action/{{ $project->id }}/bill" >{{ $next_step[$project->status] }}</a>
                    @elseif($project->status==10)
                        <a href="/action/{{ $project->id }}/end" >{{ $next_step[$project->status] }}</a>
                    @else
                        {{ 'No existen actividades pendientes' }}
                    @endif
                </td>
                @if(($user->area=='Gerencia Tecnica'&&$user->priv_level==2)||$user->priv_level>=3)
                    <td>
                        @if($project->status==1)
                            @foreach($files as $file)
                                @if($project->asig_file_id==$file->id)
                                    @if($project->asig_deadline-$file->created_at->diffInDays($current_date)<=1)
                                        <span class="label label-danger uniform_width">
                                        @if($project->asig_deadline-$file->created_at->diffInDays($current_date)==1)
                                            {{
                                            $days_remaining = $project->asig_deadline-$file->created_at->diffInDays($current_date).' dia'
                                            }}
                                        @elseif($project->asig_deadline-$file->created_at->diffInDays($current_date)==0)
                                            {{
                                            $days_remaining = 'Vence hoy'
                                            }}
                                        @elseif($project->asig_deadline-$file->created_at->diffInDays($current_date)<0)
                                            {{
                                            $days_remaining = abs($project->asig_deadline-$file->created_at->diffInDays($current_date)).' dia(s) vencido'
                                            }}
                                        @endif
                                    @else
                                        @if($project->asig_deadline-$file->created_at->diffInDays($current_date)<=3)
                                            <span class="label label-warning uniform_width">
                                        @elseif($project->asig_deadline-$file->created_at->diffInDays($current_date)<=5)
                                            <span class="label label-yellow uniform_width">
                                        @else
                                            <span class="label label-apple uniform_width">
                                        @endif
                                        {{
                                        $days_remaining = $project->asig_deadline-$file->created_at->diffInDays($current_date).' dias'
                                        }}
                                        </span>
                                    @endif
                                @endif
                            @endforeach
                        @elseif($project->status>=4&&$project->status<=10)
                            @if($project->ini_date->diffInDays($current_date,false)>0)
                                @if($project->pc_deadline-$project->ini_date->diffInDays($current_date)<=1)
                                    <span class="label label-danger uniform_width">
                                    @if($project->pc_deadline-$project->ini_date->diffInDays($current_date)==1)
                                        {{
                                        $days_remaining = $project->pc_deadline-$project->ini_date->diffInDays($current_date).' dia'
                                        }}
                                    @elseif($project->pc_deadline-$project->ini_date->diffInDays($current_date)==0)
                                        {{
                                        $days_remaining = 'Vence hoy'
                                        }}
                                    @elseif($project->pc_deadline-$project->ini_date->diffInDays($current_date)<0)
                                        {{
                                        $days_remaining = abs($project->pc_deadline-$project->ini_date->diffInDays($current_date)).' dia(s) vencido'
                                        }}
                                    @endif
                                    </span>
                                @else
                                    @if($project->pc_deadline-$project->ini_date->diffInDays($current_date)<=3)
                                        <span class="label label-danger uniform_width">
                                    @elseif($project->pc_deadline-$project->ini_date->diffInDays($current_date)<=5)
                                        <span class="label label-warning uniform_width">
                                    @elseif($project->pc_deadline-$project->ini_date->diffInDays($current_date)<=10)
                                        <span class="label label-yellow uniform_width">
                                    @else
                                        <span class="label label-apple uniform_width">
                                    @endif
                                    {{
                                    $days_remaining = $project->pc_deadline-$project->ini_date->diffInDays($current_date).' dias'
                                    }}
                                    </span>
                                @endif
                            @else
                                <span class="label label-blue uniform_width">
                                {{
                                $days_remaining = $project->pc_deadline+$project->ini_date->diffInDays($current_date).' dias'
                                }}
                                </span>
                            @endif
                        @else
                            <span class="label label-gray uniform_width">
                            {{ 'N/A' }}
                            </span>
                        @endif
                    </td>
                @endif
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<div class="col-sm-12 mg10" align="center">
    {!! $projects->render() !!}
</div>

@endsection

@section('footer')
    @parent
@endsection

@section('javascript')
    <script>
        $('#alert').delay(2000).fadeOut('slow');
        $(function(){
            $('#ordenar_tabla').tablesorter({
                cssAsc: 'headerSortUp',
                cssDesc: 'headerSortDown',
                cssNone: ''
            });
        });
    </script>
@endsection
