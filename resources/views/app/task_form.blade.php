@extends('layouts.master')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/custom_autocomplete.css") }}">
    <script src="{{ asset('https://cdnjs.cloudflare.com/ajax/libs/jquery.devbridge-autocomplete/1.2.27/jquery.autocomplete.js') }}">
    </script>

    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 mg-btm-px-40 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">

        <div class="panel panel-sky">
            <div class="panel-heading" align="center">
                <div class="panel-title">{{ $task ? 'Modificar item' : 'Crear un item adicional' }}</div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-undo"></i>
                    </a>
                    <a href="/task/{{ $site->id }}" class="btn btn-warning" title="Volver a lista de items">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <p><em>Nota.- Los campos con * son obligatorios</em></p>

                @if($task)
                    <form id="delete" action="/task/{{ $task->id }}" method="post">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                    <form novalidate="novalidate" action="{{ '/task/'.$task->id }}" method="post">
                        <input type="hidden" name="_method" value="put">
                        @else
                            <form novalidate="novalidate" action="{{ '/task' }}" method="post">
                                @endif
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="form-group">
                                    <div class="input-group">

                                <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                    <div class="input-group" style="width: 100%">
                                        <label for="name" class="input-group-addon" style="width: 23%;text-align: left">
                                            Item: <span class="pull-right">*</span>
                                        </label>

                                        <input required="required" type="text" class="form-control" name="name" id="name"
                                               value="{{ $task ? $task->name : old('name') }}"
                                               placeholder="Nombre o descripción de item">
                                    </div>

                                    <div class="input-group" style="width: 100%">
                                        <label for="number" class="input-group-addon" style="width: 23%;text-align: left">
                                            Número:
                                        </label>

                                        <input required="required" type="number" class="form-control" name="number" id="number"
                                               step="any" min="0"
                                               value="{{ $task&&$task->item ? $task->item->number : old('number') }}"
                                               {{ $task&&$user->priv_level<2 ? 'readonly="readonly"' : '' }}
                                               placeholder="Número de item">
                                    </div>

                                    <div class="input-group" style="width: 100%">
                                        <label for="client_code" class="input-group-addon" style="width: 23%;text-align: left">
                                            Código:
                                        </label>

                                        <input required="required" type="text" class="form-control" name="client_code"
                                               id="client_code"
                                               value="{{ $task&&$task->item ? $task->item->client_code : old('client_code') }}"
                                               {{ $task&&$user->priv_level<2 ? 'readonly="readonly"' : '' }}
                                               placeholder="Código de item según cliente">
                                    </div>

                                    <div class="input-group" style="width: 100%">
                                        <label for="category" class="input-group-addon" style="width: 23%;text-align: left">
                                            Categoría: <span class="pull-right">*</span>
                                        </label>

                                        <select required="required" class="form-control" name="category" id="category"
                                                {{ $task&&$user->priv_level<2 ? 'readonly="readonly"' : '' }}>
                                            <option value="" hidden>Seleccione una categoría</option>
                                            {{--@foreach($categories as $category)--}}
                                            @if($task&&$task->item)
                                                <option value="{{ $task->item->category }}"
                                                        selected="selected">{{ $task->item->category }}</option>
                                            @else
                                                <option value="{{ 'Adicionales '.$user->work_type.' '.date('Y') }}"
                                                        selected="selected">{{ 'Adicionales '.$user->work_type.' '.
                                                        date('Y') }}</option>
                                            @endif
                                            {{--@endforeach--}}
                                            {{--<option value="Otro">Otro</option>--}}
                                        </select>
                                    </div>
                                    {{--
                                    <input required="required" type="text" class="form-control" name="other_category"
                                           id="other_category" placeholder="Nueva categoría" disabled="disabled">
                                           --}}

                                    <div class="input-group" style="width: 100%">
                                        <label for="subcategory" class="input-group-addon" style="width: 23%;text-align: left">
                                            Subcategoría:
                                        </label>

                                        <input required="required" type="text" class="form-control" name="subcategory"
                                               id="subcategory"
                                               value="{{ $task&&$task->item ? $task->item->subcategory : old('subcategory') }}"
                                               {{ $task&&$user->priv_level<2 ? 'readonly="readonly"' : '' }}
                                               placeholder="Subcategoría o subdivisión de ítem">
                                    </div>

                                    <div class="input-group" style="width: 75%">
                                        <span class="input-group-addon" style="width: 31%/*170px*/;text-align: left">
                                            Peso ponderado:
                                        </span>

                                        <input required="required" type="number" class="form-control" name="pondered_weight"
                                               step="1" min="1" max="10" placeholder="Peso o importancia del item (1 a 10)"
                                               value="{{ $task ? $task->pondered_weight : old('pondered_weight') }}">
                                    </div>

                                    <div class="input-group" style="width: 100%">
                                        <label for="site_id" class="input-group-addon" style="width: 23%;text-align: left">
                                            Sitio: <span class="pull-right">*</span>
                                        </label>

                                        <select required="required" class="form-control" name="site_id" id="site_id"
                                                {{ $user->priv_level==1 ? 'readonly="readonly"' : '' }}>
                                            <option value="" hidden>Seleccione un sitio</option>
                                            <option value="{{ $site->id }}"
                                                    {{ ($site&&$site->id==$site_id)||old('site_id')==$site_id ?
                                                        'selected="selected"' : '' }}>{{ $site->name.
                                                        ' - '.$site->assignment->name }}</option>
                                        </select>
                                    </div>

                                    @if($task||$user->priv_level==4)
                                        <div class="input-group" style="width: 100%">
                                            <label for="status" class="input-group-addon" style="width: 23%;text-align: left">
                                                Estado:
                                            </label>

                                            <select required="required" class="form-control" name="status" id="status">
                                                <option value="" hidden>Seleccione un estado</option>
                                                @for($i=0; $i<=$last_stat; $i++)
                                                    <option value="{{ $i }}"
                                                            {{ ($task&&$task->status==$i)||old('status')==$i ?
                                                                'selected="selected"' : '' }}>
                                                            {{ App\Task::$status_options[$i] /*first()->statuses($i)*/ }}</option>
                                                @endfor
                                                {{--
                                                <option value="En espera"
                                                        {{ $task&&$task->status=='En espera' ? 'selected="selected"' : '' }}>
                                                    En espera</option>
                                                <option value="Ejecución"
                                                        {{ $task&&$task->status=='Ejecución' ? 'selected="selected"' : '' }}>
                                                    Ejecución</option>
                                                <option value="Revisión"
                                                        {{ $task&&$task->status=='Revisión' ? 'selected="selected"' : '' }}>
                                                    Revisión</option>
                                                <option value="Concluído"
                                                        {{ $task&&$task->status=='Concluído' ? 'selected="selected"' : '' }}>
                                                    Concluído</option>
                                                <option value="No asignado"
                                                        {{ $task&&$task->status=='No asignado' ? 'selected="selected"' : '' }}>
                                                    No asignado</option>
                                                --}}
                                            </select>
                                        </div>
                                    @endif

                                    <div id="resp_container" class="form-group has-feedback">
                                        <div class="input-group" style="width: 100%">
                                            <label for="resp_name" class="input-group-addon" style="width: 23%;text-align: left">
                                                Responsable: <span class="pull-right">*</span>
                                            </label>

                                            <input required="required" type="text" class="form-control" name="resp_name"
                                                   id="resp_name"
                                                   value="{{ $task&&$task->responsible ? $task->person_responsible->name :
                                                    old('resp_name') }}" placeholder="Responsable en sitio (ABROS)">
                                        </div>

                                        <div class="input-group" style="width: 100%;text-align: center" id="resultado" align="center"></div>
                                    </div>

                                    <div class="input-group" style="width: 100%">
                                        <label for="total_expected" class="input-group-addon" style="width: 23%;text-align: left">
                                            Proyectado: <span class="pull-right">*</span>
                                        </label>

                                        <input required="required" type="number" class="form-control" name="total_expected"
                                               id="total_expected" step="1" min="1"
                                               value="{{ $task ? $task->total_expected : old('total_expected') }}"
                                               placeholder="Cantidad proyectada">
                                    </div>

                                    <div class="input-group" style="width: 100%">
                                        <label for="units" class="input-group-addon" style="width: 23%;text-align: left">
                                            Unidades: <span class="pull-right">*</span>
                                        </label>

                                        <input required="required" type="text" class="form-control" name="units" id="units"
                                               value="{{ $task ? $task->units : old('units') }}" placeholder="Unidades de medida">
                                    </div>

                                    {{--
                                    @if($user->priv_level==4)
                                        <div class="input-group" style="width: 100%">
                                            <label for="progress" class="input-group-addon" style="width: 23%;text-align: left">
                                                Ejecutado:
                                            </label>

                                            <input required="required" type="number" class="form-control" name="progress"
                                                   id="progress" step="1" min="1"
                                                   value="{{ $task&&$task->progress!=0 ? $task->progress : old('progress') }}"
                                                   placeholder="Total avanzado">
                                        </div>
                                    @endif
                                    --}}

                                    @if($user->priv_level>=3)
                                        <div class="input-group" style="width: 75%">
                                            {{--
                                            <span class="input-group-addon">
                                                <input type="radio" name="use_this" value="central_cost"
                                                        {{ $task&&$task->quote_price!=0 ? 'checked="checked""' : '' }}>
                                            </span>
                                            --}}
                                            <span class="input-group-addon" style="width: 31%/*170px*/;text-align: left">
                                                Precio unitario:
                                            </span>
                                            <input required="required" type="number" class="form-control" name="cost_unit_central"
                                                   step="any" min="0" placeholder="Precio por unidad"
                                                   value="{{ $task&&$task->quote_price!=0 ? $task->quote_price :
                                                        ($task&&$task->item ? $task->item->cost_unit_central :
                                                         old('cost_unit_central')) }}">
                                            <span class="input-group-addon">Bs.</span>
                                        </div>
                                    @endif

                                    {{-- Separated to only one cost cloumn per item
                                    <div class="input-group" style="width: 75%">
                                        <span class="input-group-addon">
                                            <input type="radio" name="use_this" value="remote_cost"
                                                    {{ $task&&$task->item&&$task->quote_price==$task->item->cost_unit_remote ?
                                                        'checked' : '' }}>
                                        </span>
                                        <span class="input-group-addon" style="width: 170px;text-align: left">
                                            Precio (localidad):</span>
                                        <input required="required" type="number" class="form-control" name="cost_unit_remote"
                                               step="any" min="0" placeholder="Precio por unidad"
                                               value="{{ $task&&$task->item ? $task->item->cost_unit_remote : '' }}">
                                        <span class="input-group-addon">Bs.</span>
                                    </div>
                                    --}}

                                    <div class="input-group" style="width: 100%;text-align: center">
                                        <span class="input-group-addon">
                                            <label for="start_date" style="font-weight: normal">
                                                Fecha de inicio: <span class="pull-right">*</span>
                                            </label>
                                            <input type="date" name="start_date" id="start_date" step="1"
                                                   min="{{ $site->start_date }}" max="{{ $site->end_date }}"
                                                   value="{{ $task ? $task->start_date : (old('start_date') ?: $current_date) }}">
                                        </span>

                                        <span class="input-group-addon">
                                            <label for="end_date" style="font-weight: normal">
                                                Fecha de fin: <span class="pull-right">*</span>
                                            </label>
                                            <input type="date" name="end_date" id="end_date" step="1"
                                                   min="{{ $site->start_date }}" max="{{ $site->end_date }}"
                                                   value="{{ $task ? $task->end_date : old('end_date') }}">
                                        </span>
                                    </div>
                                    {{--
                                    <div class="input-group" style="width: 75%">
                                      <span class="input-group-addon" style="width: 31%;text-align: left">Monto ejecutado:</span>
                                      <input required="required" type="number" class="form-control" name="executed_price"
                                        step="any" min="0"
                                        value="{{ $task ? $task->executed_price : '' }}" placeholder="Monto ejecutado (s/impuestos)">
                                      <span class="input-group-addon">Bs.</span>
                                    </div>
                                    <div class="input-group" style="width: 75%">
                                      <span class="input-group-addon" style="width: 31%;text-align: left">Monto asignado:</span>
                                      <input required="required" type="number" class="form-control" name="assigned_price"
                                        step="any" min="0"
                                        value="{{ $task ? $task->assigned_price : '' }}" placeholder="Monto asignado (s/impuestos)">
                                      <span class="input-group-addon">Bs.</span>
                                    </div>
                                    <div class="input-group" style="width: 75%">
                                      <span class="input-group-addon" style="width: 31%;text-align: left">Monto cobrado:</span>
                                      <input required="required" type="number" class="form-control" name="charged_price"
                                        step="any" min="0"
                                        value="{{ $task ? $task->charged_price : '' }}" placeholder="Monto cobrado (s/impuestos)">
                                      <span class="input-group-addon">Bs.</span>
                                    </div>
                                    --}}
                                    <textarea rows="3" class="form-control" name="description"
                                        placeholder="Detalle de trabajos del item / Observaciones">{{ $task ?
                                         $task->description : old('description') }}</textarea>

                                </span>

                                    </div>
                                </div>

                                @include('app.loader_gif')

                                <div class="form-group" align="center">
                                    <button type="submit" class="btn btn-success"
                                            onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                                        <i class="fa fa-floppy-o"></i> Guardar
                                    </button>

                                    @if($task&&$user->action->prj_tk_del /*$user&&$user->priv_level==4*/)
                                        <button type="submit" form="delete" class="btn btn-danger">
                                            <i class="fa fa-trash-o"></i> Eliminar
                                        </button>
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
    <script src="{{ asset('app/js/prevent_enter_form_submit.js') }}"></script> {{-- Avoid submitting form on enter press --}}
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function check_existence(){
            var resp_name=$('#resp_name').val();
            if(resp_name.length >0){
                $.post('/check_existence', { resp_name: resp_name }, function(data){
                    $("#resultado").html(data.message).show();
                    if(data.status==="warning"){
                        $('#resp_container').addClass("has-warning").removeClass("has-success");
                    }
                    else if(data.status==="success"){
                        $('#resp_container').addClass("has-success").removeClass("has-warning");
                    }
                });
            }
            else{
                $("#resultado").hide();
                $('#resp_container').removeClass("has-warning").removeClass("has-success");
            }
        }

        $(document).ready(function(){
            $("#wait").hide();
            $("#resultado").hide();
            $('#resp_name').focusout(check_existence);
        });

        $('#resp_name').autocomplete({
            type: 'post',
            serviceUrl:'/autocomplete/users',
            dataType: 'JSON',
            onSelect: check_existence
        });

        /*
        var $category = $('#category'), $other_category = $('#other_category');
        $category.change(function () {
            if ($category.val() == 'Otro') {
                $other_category.removeAttr('disabled').show();
            } else {
                $other_category.attr('disabled', 'disabled').val('').hide();
            }
        }).trigger('change');
        */
    </script>
@endsection
