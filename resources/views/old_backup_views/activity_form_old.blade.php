@extends('layouts.master')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">

        <div class="panel panel-sky" >
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    @if($activity)
                        @if($selector[0]=='tk'){{ 'Modificar datos de actividad' }}
                        @elseif($selector[0]=='st'){{ 'Modificar datos de evento' }}
                        @endif
                    @else
                        @if($selector[0]=='tk'){{ 'Agregar nueva actividad' }}
                        @elseif($selector[0]=='st'){{ 'Agregar nuevo evento' }}
                        @endif
                    @endif
                </div>
            </div>
            <div class="panel-body" >
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning"><i class="fa fa-arrow-circle-left"></i> Volver</a>
                </div>
                @if (Session::has('message'))
                    <div class="alert alert-danger" align="center">{{ Session::get('message') }}</div>
                @endif
                @if($activity)
                    <form id="delete" action="/activity/{{ $activity->id }}" method="post">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                    <form novalidate="novalidate" action="{{ '/activity/'.$activity->id }}" method="post">
                        <input type="hidden" name="_method" value="put">
                        @else
                            <form novalidate="novalidate" action="{{ '/activity' }}" method="post">
                                @endif
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="form-group">
                                    <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                    <select required="required" class="form-control" name="site_id">
                                        <option value="" hidden>Seleccione un sitio</option>
                                        <option value="{{ $site->id }}" selected="selected">{{ 'Sitio: '.$site->name.' - Proyecto: '.str_limit($site->assignment->name,50) }}</option>
                                    </select>
<!--
                                    <select required="required" class="form-control" name="type" id="type">
                                        <option value="" hidden>Seleccione el tipo de actividad / evento</option>
                                        @foreach($types as $type)
                                            <option value="{{ $type->type }}"
                                            @if($activity&&$type->type==$activity->type){{ 'selected="selected"' }}@endif
                                            >{{ $type->type }}</option>
                                        @endforeach
                                        <option value="Otro">Otro</option>
                                    </select>
                                    <input required="required" type="text" class="form-control" name="other_type" id="other_type" value="{{ $activity ? $activity->type : '' }}" placeholder="Tipo de actividad" disabled="disabled">
-->
                                    <input required="required" type="text" class="form-control" name="type" value="{{ $activity ? $activity->type : '' }}" placeholder="{{ $selector[0]=='tk' ? 'Tipo de actividad (resumen)' : 'Tipo de evento (resumen)' }}">

                                    @if($user&&$user->priv_level==4)
                                    <div class="input-group" style="width: 100%;">
                                        <span class="input-group-addon">
                                            <label>Fecha: </label>
                                            <input type="date" name="start_date" step="1" min="2014-01-01" value="{{ $activity ? $activity->start_date : date("Y-m-d") }}">
                                        </span>
                                    </div>
                                    @endif

                                    <textarea rows="3" required="required" class="form-control" name="description" placeholder="{{ $selector[0]=='tk' ? 'Detalle (información adicional)' : 'Detalle de evento' }}">{{ $activity ? $activity->description : '' }}</textarea>

                                </span>
                                    </div>

                                        @if($selector[0]=='tk')
                                            <!--
                                            <input class="checkbox-inline" type="checkbox" name="add_task" id="add_task" value="1" {{ $activity&&$activity->task_id!=0 ? 'checked' : 'checked=""' }}> Agregar avance
                                            -->
                                        @elseif($selector[0]=='st'&&$user->priv_level>=1)
                                            <div class="input-group col-sm-offset-1">
                                                <br><label>Información adicional: </label><br>
                                                <input class="checkbox-inline" type="checkbox" name="add_oc" id="add_oc" value="1" {{ $activity&&$activity->oc_id!=0 ? 'checked' : 'checked=""' }}> Agregar Orden de compra
                                                <input class="checkbox-inline" type="checkbox" name="add_cite" id="add_cite" value="1" {{ $activity&&$activity->cite_id!=0 ? 'checked' : 'checked=""' }}> Agregar CITE
                                            </div>
                                        @endif

                                    <br>

                                    @if($selector[0]=='tk')
                                    <div class="input-group" id="task_container">
                                        <span class="input-group-addon">
                                        <label>Información de avance</label>

                                        <div class="input-group" style="width: 100%" id="task_values" align="center"></div>

                                        <select required="required" class="form-control" name="task_id" id="task_id">
                                            <!--<option value="" hidden>Seleccione un item</option>-->
                                            <option value="{{ $task->id }}">{{ 'Item: '.$task->name }}</option>
                                        </select>
                                        <input required="required" type="number" class="form-control" name="progress" id="progress" step="1" min="1" value="{{ $activity&&$activity->task_id!=0 ? $activity->progress : '' }}" placeholder="Cantidad avanzada">
                                        </span>
                                    </div>
                                    @elseif($selector[0]=='st'&&$user->priv_level>=1)
                                    <div class="input-group" id="oc_container">
                                        <span class="input-group-addon">
                                        <label>Información de OC</label>
                                        <input required="required" type="text" class="form-control" name="oc_id" id="oc_id" value="{{ $activity&&$activity->oc_id!=0 ? $activity->oc_id : '' }}" placeholder="Código de OC" disabled="disabled">
                                        </span>
                                    </div>

                                    <div class="input-group" id="cite_container">
                                        <span class="input-group-addon">
                                        <label>Información de CITE</label>
                                        <input required="required" type="text" class="form-control" name="cite_id" id="cite_id" value="{{ $activity&&$activity->cite_id!=0 ? $activity->cite_id : '' }}" placeholder="Código de CITE" disabled="disabled">
                                        </span>
                                    </div>
                                    @endif

                                </div>
                                <div class="form-group" align="center">
                                    <button type="submit" class="btn btn-success" onclick="this.disabled=true; this.form.submit()"><i class="fa fa-floppy-o"></i> Guardar </button>
                                    @if($user&&$user->priv_level==4)
                                        <button type="submit" form="delete" class="btn btn-danger"><i class="fa fa-trash-o"></i> Eliminar </button>
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
        /*
        var $type = $('#type'), $other_type = $('#other_type');
        $type.change(function () {
            if ($type.val() == 'Otro') {
                $other_type.removeAttr('disabled').show();
            } else {
                $other_type.attr('disabled', 'disabled').val('').hide();
            }
        }).trigger('change');

        var $add_task = $('#add_task'), $task_id = $('#task_id'), $progress = $('#progress'), $task_container = $('#task_container');
        $add_task.click(function () {
            if ($add_task.prop('checked')) {
                $task_container.show();
                $task_id.removeAttr('disabled').show();
                $progress.removeAttr('disabled').show();
            } else {
                $task_container.hide();
                $task_id.attr('disabled', 'disabled').hide();
                $progress.attr('disabled', 'disabled').hide();
            }
        }).trigger('click');
        */

        var $add_oc = $('#add_oc'), $oc_id = $('#oc_id'), $oc_container = $('#oc_container');
        $add_oc.click(function () {
            if ($add_oc.prop('checked')) {
                $oc_container.show();
                $oc_id.removeAttr('disabled').show();
            } else {
                $oc_container.hide();
                $oc_id.attr('disabled', 'disabled').hide();
            }
        }).trigger('click');

        var $add_cite = $('#add_cite'), $cite_id = $('#cite_id'), $cite_container = $('#cite_container');
        $add_cite.click(function () {
            if ($add_cite.prop('checked')) {
                $cite_container.show();
                $cite_id.removeAttr('disabled').show();
            } else {
                $cite_container.hide();
                $cite_id.attr('disabled', 'disabled').hide();
            }
        }).trigger('click');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function(){
            $("input").not($(":button")).keypress(function (evt) {
                if (evt.keyCode == 13) {
                    itype = $(this).attr('type');
                    if (itype !== 'submit'){
                        var fields = $(this).parents('form:eq(0),body').find('button, input, textarea, select');
                        var index = fields.index(this);
                        if (index > -1 && (index + 1) < fields.length) {
                            fields.eq(index + 1).focus();
                        }
                        return false;
                    }
                }
            });

            $("#task_values").hide();

            $("#progress").keyup(function(){
                $.post('/load_task_values', { task_id: $("#task_id").val(), progress: $("#progress").val() }, function(data){
                    $("#task_values").html(data).show();
                });
            });

        });
    </script>
@endsection
