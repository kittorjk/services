@extends('layouts.master')

@section('header')
    @parent
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

        <div class="panel panel-sky" >
            <div class="panel-heading" align="center">
                <div class="panel-title">{{ $event ? 'Editar información de evento' : 'Agregar información de evento' }}</div>
            </div>
            <div class="panel-body" >
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning"><i class="fa fa-arrow-circle-left"></i> Volver</a>
                </div>
                @if (Session::has('message'))
                    <div class="alert alert-danger" align="center">{{ Session::get('message') }}</div>
                @endif
                @if($event)
                    <form id="delete" action="/event/{{ $event->id }}" method="post">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                    <form novalidate="novalidate" action="{{ '/event/'.$event->id }}" method="post">
                        <input type="hidden" name="_method" value="put">
                        @else
                            <form novalidate="novalidate" action="{{ '/event' }}" method="post">
                                @endif
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="form-group">
                                    <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                <select required="required" class="form-control" name="project_site" id="project_site">
                                    <option value="" hidden>Seleccione un sitio</option>
                                    @foreach($project_sites as $project_site)
                                        <option value="{{ $project_site->project_site }}"
                                        @if($project_site->project_site==$site_name){{ 'selected="selected"' }}@endif
                                        >{{ $project_site->project_site }}</option>
                                    @endforeach
                                    <option value="Otro">Agregar sitio</option>
                                </select>
                                <input required="required" type="text" class="form-control" name="other_project_site" id="other_project_site" placeholder="Nuevo sitio" disabled="disabled">

                                <span class="input-group-addon">
                                    <label>Fecha del evento: </label>
                                    <input type="date" name="event_date" step="1" min="2014-01-01" value="{{ $current_date }}">
                                </span>

                                <input required="required" type="text" class="form-control" name="brief_description" value="{{ $event ? $event->brief_description : '' }}" placeholder="Breve resumen">
                                <textarea rows="6" required="required" class="form-control" name="detailed_description" placeholder="Información detallada del evento">{{ $event ? $event->detailed_description : '' }}</textarea>

                                <input required="required" type="text" class="form-control" name="resp_client" value="{{ $event ? $event->resp_client : '' }}" placeholder="Responsable por parte del cliente">
                                <input required="required" type="text" class="form-control" name="resp_abr" value="{{ $event ? $event->resp_abr : '' }}" placeholder="Responsable por parte de ABROS">

                                <input type="hidden" name="project_id" value="{{ $project_id }}">

                                </span>
                                    </div>
                                </div>
                                <div class="form-group" align="center">
                                    <button type="submit" class="btn btn-success"><i class="fa fa-floppy-o"></i> Guardar </button>
                                    @if($event)
                                        @if($user->priv_level == 4)
                                            <button type="submit" form="delete" class="btn btn-danger"><i class="fa fa-trash-o"></i> Eliminar </button>
                                        @endif
                                    @endif
                                </div>
                            </form>
            </div>
        </div>
    </div>

@endsection

@section('footer')
@endsection

@section('javascript')
    <script>
        var $project_site = $('#project_site'), $other_project_site = $('#other_project_site');
        $project_site.change(function () {
            if ($project_site.val() == 'Otro') {
                $other_project_site.removeAttr('disabled').show();
            } else {
                $other_project_site.attr('disabled', 'disabled').val('').hide();
            }
        }).trigger('change');
    </script>
@endsection
