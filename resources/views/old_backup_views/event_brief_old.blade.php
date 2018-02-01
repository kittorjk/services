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
                @if($project_info)
                    <a href="/project/{{ $project_info->id }}" class="btn btn-primary"><i class="fa fa-arrow-circle-left"></i> Volver </a>
                        <div class="btn-group">
                            <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle"><i class="fa fa-paper-plane"></i> Sitios <span class="caret"></span></button>
                            <ul class="dropdown-menu dropdown-menu-prim">
                                <li><a href="/event/{{ $project_info->id }}"><i class="fa fa-bars"></i> Resumen </a></li>
                                @if($project_info->status<=10)
                                    <li><a href="/event/{{ $project_info->id }}/0/create"><i class="fa fa-plus"></i> Agregar evento </a></li>
                                @endif
                            </ul>
                        </div>
                    <a href="/search/sites/{{ $project_info->id }}" class="btn btn-primary"><i class="fa fa-search"></i> Buscar </a>
                @endif
            </div>
            <div class="col-sm-4" align="right">
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
    @if($projects)
        <div class="col-sm-10 col-sm-offset-1 mg10 mg-tp-px-10">
            @if($projects->total()==1)
                <p>Se encontró {{ $projects->total() }} registro</p>
            @else
                <p>Se encontraron {{ $projects->total() }} registros</p>
            @endif
            <table class="fancy_table table_blue">
                <thead>
                <tr>
                    <th>Código</th>
                    <th width="50%">Proyecto</th>
                    <th>Desarrollo del proyecto</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($projects as $project)
                    <tr>
                        <td>{{ 'PR-'.str_pad($project->id, 4, "0", STR_PAD_LEFT).date_format($project->created_at,'-y') }}</td>
                        <td>{{ $project->name }}</td>
                        <td>
                            <a href="/event/{{ $project->id }}">{{ 'Ver eventos' }}</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="col-sm-12 mg10" align="center">
            {!! $projects->render() !!}
        </div>
    @elseif($project_info)
        <div class="col-sm-10 col-sm-offset-1 mg10 mg-tp-px-10">
            <table class="fancy_table table_blue">
                <thead>
                <tr>
                    <th>Código: {{ 'PR-'.str_pad($project_info->id, 4, "0", STR_PAD_LEFT).date_format($project_info->created_at,'-y') }}</th>
                    <th colspan="3">Proyecto: {{ $project_info->name }}</th>
                </tr>
                <tr>
                    <th colspan="4" style="text-align: center">Listado de sitios del proyecto</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($project_sites as $project_site)
                    <tr>
                        <td>{{ $project_site->project_site }}</td>
                        <td>{{ $project_site->event_number==1 ? $project_site->event_number.' evento' : $project_site->event_number.' eventos' }}</td>
                        <td>{{ 'Última actividad '.date_format($project_site->updated_at,'d/m/Y') }}</td>
                        <td>
                            <a href="/event/{{ $project_info->id }}/{{ str_replace(" ", "_", $project_site->project_site) }}">{{ 'Ver eventos de este sitio' }}</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

@endsection

@section('footer')
    @parent
@endsection

@section('javascript')
    <script>
        $('#alert').delay(2000).fadeOut('slow');
    </script>
@endsection
