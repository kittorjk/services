@extends('layouts.master')

@section('header')
    @parent
@endsection

@section('content')

<div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">

    <div class="panel panel-sky" >
        <div class="panel-heading" align="center">
            <div class="panel-title">{{ $project ? 'Actualizar información de proyecto' : 'Agregar nuevo proyecto' }}</div>
        </div>
        <div class="panel-body" >
            <div class="mg20">
                <a href="#" onclick="history.back();" class="btn btn-warning"><i class="fa fa-arrow-circle-left"></i> Volver</a>
            </div>
            @if (Session::has('message'))
                <div class="alert alert-danger" align="center">{{ Session::get('message') }}</div>
            @endif
            @if($project)
                <form id="delete" action="/project/{{ $project->id }}" method="post">
                    <input type="hidden" name="_method" value="delete">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                </form>
                <form novalidate="novalidate" action="{{ '/project/'.$project->id }}" method="post">
                    <input type="hidden" name="_method" value="put">
                    @else
                        <form novalidate="novalidate" action="{{ '/project' }}" method="post">
                            @endif
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <div class="form-group">
                                <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i>
                                    <input type="hidden" name="action_flag" value="{{ $action_flag }}">
                                    @if($action_flag&&$action_flag=='end')
                                        <br>
                                        <h4>Esta seguro de que desea dar por concluído el proyecto?</h4>
                                        <input type="checkbox" class="form-control" name="end_project" value="1" checked>
                                    @elseif($action_flag&&$action_flag=='bill')
                                        <input required="required" type="number" class="form-control" name="bill_number" step="1" min="1" placeholder="Número de factura">
                                        <span class="input-group-addon">
                                            <label>Fecha de facturación: </label>
                                            <input type="date" name="bill_date" step="1" min="2014-01-01" value="">
                                        </span>
                                    @elseif($action_flag&&$action_flag=='stat_chg')
                                        <br>
                                        <h5><b>Advertencia:</b> cambiar el estado sobreescribirá los datos que se introduzcan posteriormente!</h5>
                                        <select required="required" class="form-control" name="new_status">
                                            @for($i = 0;$i<13;$i++)
                                                <option value="{{ $i }}"
                                                @if($project&&$project->status==$i){{ 'selected="selected"' }}@endif
                                                >{{ $i }}</option>
                                            @endfor
                                        </select>
                                    @else
                                        <input required="required" type="text" class="form-control" name="name" value="{{ $project ? $project->name : '' }}" placeholder="Nombre de Proyecto">
                                        <select required="required" class="form-control" name="client" id="client">
                                            <option value="" hidden>Seleccione cliente</option>
                                            @foreach($clients as $client)
                                                <option value="{{ $client->client }}"
                                                @if($project&&$client->client==$project->client){{ 'selected="selected"' }}@endif
                                                >{{ $client->client }}</option>
                                            @endforeach
                                            <option value="Otro">Otro</option>
                                        </select>
                                        <input required="required" type="text" class="form-control" name="other_client" id="other_client" value="{{ $project ? $project->client : '' }}" placeholder="Cliente" disabled="disabled">
                                        @if($project&&$project->status>=4)
                                            <span class="input-group-addon">
                                                <label>Fecha de inicio: </label>
                                                <input type="date" name="ini_date" step="1" min="2014-01-01" value="{{ $project ? $project->ini_date : '' }}">
                                            </span>
                                            <input required="required" type="number" class="form-control" name="pc_deadline" step="1" min="1" value="{{ $project ? $project->pc_deadline : '' }}" placeholder="Días restantes para terminar el proyecto">
                                            <textarea rows="3" required="required" class="form-control" name="ini_obs" placeholder="Observaciones de inicio de proyecto">{{ $project ? $project->ini_obs : '' }}</textarea>
                                        @endif
                                        @if($project&&$project->status>=10)
                                            <input required="required" type="number" class="form-control" name="bill_number" step="1" min="1" value="{{ $project ? $project->bill_number : '' }}" placeholder="Número de factura">
                                            <span class="input-group-addon">
                                                <label>Fecha de facturación: </label>
                                                <input type="date" name="bill_date" step="1" min="2014-01-01" value="{{ $project ? $project->bill_date : '' }}">
                                            </span>
                                        @endif
                                    @endif
                                </span>
                                </div>
                            </div>
                            <div class="form-group" align="center">
                                <button type="submit" class="btn btn-success"><i class="fa fa-floppy-o"></i> Guardar </button>
                                @if($project)
                                    @if($user->priv_level == 4)
                                        <button type="submit" form="delete" class="btn btn-danger"><i class="fa fa-trash-o"></i> Quitar </button>
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
    var $proy_name = $('#proy_name'), $other_proy_name = $('#other_proy_name');
    $proy_name.change(function () {
        if ($proy_name.val() == 'Otro') {
            $other_proy_name.removeAttr('disabled').show();
        } else {
            $other_proy_name.attr('disabled', 'disabled').val('').hide();
        }
    }).trigger('change');
    var $client = $('#client'), $other_client = $('#other_client');
    $client.change(function () {
        if ($client.val() == 'Otro') {
            $other_client.removeAttr('disabled').show();
        } else {
            $other_client.attr('disabled', 'disabled').val('').hide();
        }
    }).trigger('change');
</script>
@endsection
