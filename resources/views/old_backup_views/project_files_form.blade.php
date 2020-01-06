@extends('layouts.master')

@section('header')
    @parent
@endsection

@section('content')

<div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
    <div class="panel panel-sky" >
        <div class="panel-heading" align="center">
            <div class="panel-title">Actualizar información de proyecto</div>
        </div>
        <div class="panel-body" >
            <div class="mg20">
                <a href="#" onclick="history.back();" class="btn btn-warning"><i class="fa fa-arrow-circle-left"></i> Volver</a>
            </div>
            @if (Session::has('message'))
                <div class="alert alert-danger" align="center">{{ Session::get('message') }}</div>
            @endif
            <form method="post" action="/files/{{ $id }}/project" accept-charset="UTF-8" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-cloud-upload"></i>
                                @if($status==0)
                                    <input required="required" type="text" class="form-control" name="asig_num" value="{{ $project ? $project->asig_num : '' }}" placeholder="Código de documento de asignación de cliente">
                                    <input required="required" type="number" class="form-control" name="asig_deadline" step="1" min="1" value="{{ $project ? $project->asig_deadline : '' }}" placeholder="Días restantes para cotización">
                                @elseif($status==1)
                                    <input required="required" type="number" class="form-control" name="quote_amount" step="any" min="0" value="{{ $project ? $project->quote_amount : '' }}" placeholder="Monto cotizado (bruto)">
                                @elseif($status==3)
                                    <input required="required" type="number" class="form-control" name="pc__amount" step="any" min="0" value="{{ $project ? $project->pc__amount : '' }}" placeholder="Monto asignado por cliente (bruto)">
                                    <span class="input-group-addon">
                                        <label>Fecha de inicio: </label>
                                        <input type="date" name="ini_date" step="1" min="2014-01-01" value="">
                                    </span>
                                    <input required="required" type="number" class="form-control" name="pc_deadline" step="1" min="1" value="{{ $project ? $project->pc_deadline : '' }}" placeholder="Días restantes para terminar el proyecto">
                                    <textarea rows="3" required="required" class="form-control" name="ini_obs" placeholder="Observaciones de inicio de proyecto">{{ $project ? $project->ini_obs : '' }}</textarea>
                                @elseif($status==6)
                                    <input required="required" type="number" class="form-control" name="costsh_amount" step="any" min="0" value="{{ $project ? $project->costsh_amount : '' }}" placeholder="Monto ejecutado (bruto)">
                                @endif
                            <input type="file" class="form-control" name="file" >
                            <input type="hidden" name="status" value="{{ $status }}">
                            </span>
                        </div>
                    </div>

                <div class="form-group" align="center">
                    <button type="submit" class="btn btn-success"><i class="fa fa-upload"></i> Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('footer')
@endsection
