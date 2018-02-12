@extends('layouts.master')

@section('header')
    @parent
    {{--
    <link rel="stylesheet" href="//codeorigin.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css" />
    <script src="//codeorigin.jquery.com/ui/1.10.2/jquery-ui.min.js"></script>
    --}}

    <link rel="stylesheet" href="{{ asset("app/css/custom_autocomplete.css") }}">
    <script src="{{ asset('https://cdnjs.cloudflare.com/ajax/libs/jquery.devbridge-autocomplete/1.2.27/jquery.autocomplete.js') }}">
    </script>

    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 mg-btm-px-40 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">

        <div class="panel panel-sky" >
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    {{ $assignment ? 'Actualizar información de asignación' : 'Agregar nueva asignación' }}
                </div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="javascript:history.back()" {{-- onclick="history.back();" --}} class="btn btn-warning" title="Atrás">
                        <i class="fa fa-undo"></i>
                    </a>
                    <a href="{{ '/assignment' }}" class="btn btn-warning" title="Volver a asignaciones">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <p><em>Nota.- Los campos con * son obligatorios</em></p>

                @if($assignment)
                    <form id="delete" action="/assignment/{{ $assignment->id }}" method="post">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                    <form novalidate="novalidate" action="{{ '/assignment/'.$assignment->id }}" method="post">
                        <input type="hidden" name="_method" value="put">
                        @else
                            <form novalidate="novalidate" action="{{ '/assignment' }}" method="post" enctype="multipart/form-data">
                                @endif
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="form-group">
                                    <div class="input-group">

                                <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                    <div class="input-group" style="width: 100%">
                                        <label for="name" class="input-group-addon" style="width: 23%;text-align: left"
                                            title="Nombre o título de la asignación">
                                            Asignación <span class="pull-right">*</span>
                                        </label>

                                        <input required="required" type="text" class="form-control" name="name" id="name"
                                               value="{{ $assignment ? $assignment->name : old('name') }}"
                                               placeholder="Nombre de asignación">
                                    </div>

                                    <div class="input-group" style="width: 100%">
                                        <label for="literal_code" class="input-group-addon" style="width: 23%;text-align: left">
                                            Identificador <span class="pull-right">*</span>
                                        </label>

                                        <input required="required" type="text" class="form-control" name="literal_code"
                                               id="literal_code"
                                               value="{{ $assignment ? $assignment->literal_code : old('literal_code') }}"
                                               placeholder="Nombre abreviado">
                                    </div>

                                    <div class="input-group" style="width: 100%">
                                        <label for="client_code" class="input-group-addon" style="width: 23%;text-align: left">
                                            Código cliente <span class="pull-right">*</span>
                                        </label>

                                        <input required="required" type="text" class="form-control" name="client_code"
                                               id="client_code"
                                               value="{{ $assignment ? $assignment->client_code : old('client_code') }}"
                                               placeholder="Código de asignación según cliente">
                                    </div>

                                    <div class="input-group" style="width: 100%">
                                        <label for="project_id" class="input-group-addon" style="width: 23%;text-align: left">
                                            Contrato <span class="pull-right">*</span>
                                        </label>

                                        <select required="required" class="form-control" name="project_id" id="project_id"
                                                onchange="{{--dynamic_project_change($(this));--}} dynamic_select(this)">
                                            <option value="" hidden>Seleccione el contrato al que pertenece esta asignación</option>
                                            @foreach($projects as $project)
                                                <option value="{{ $project->id }}"
                                                    {{ ($assignment&&$assignment->project_id==$project->id)||
                                                        old('project_id')==$project->id ?
                                                     'selected="selected"' : '' }}
                                                        title="{{ $project->name }}">{{ str_limit($project->name, 75) }}</option>
                                            @endforeach
                                            {{--
                                            <option value="0"
                                                    {{ ($assignment&&$assignment->project_id==0)||
                                                        old('project_id')==0&&old('project_id')!='' ? 'selected="selected"' :
                                                         '' }}>Omitir selección de proyecto por ahora</option>
                                             --}}
                                            {{-- $assignment&&$assignment->project_id==0 ? 'selected="selected"' : '' --}}
                                        </select>
                                    </div>

                                    {{--
                                    <div class="input-group" style="width: 100%" id="type_container">
                                        <label for="type" class="input-group-addon" style="width: 23%;text-align: left">
                                            Área: <span class="pull-right">*</span>
                                        </label>

                                        <select required="required" class="form-control" name="type" id="type"
                                                onchange="dynamic_select(this)" disabled="disabled">
                                            <option value="" hidden>Seleccione el área de trabajo</option>
                                            <option value="Fibra óptica"
                                                {{ ($assignment&&$assignment->type=='Fibra óptica')||
                                                    old('type')=='Fibra óptica' ? 'selected="selected"' :
                                                     '' }}>Fibra óptica</option>
                                            <option value="Radiobases"
                                                {{ ($assignment&&$assignment->type=='Radiobases')||
                                                    old('type')=='Radiobases' ? 'selected="selected"' :
                                                     '' }}>Radiobases</option>
                                            <option value="Instalación de energía"
                                                {{ ($assignment&&$assignment->type=='Instalación de energía')||
                                                    old('type')=='Instalación de energía' ? 'selected="selected"' :
                                                     '' }}>Instalación de energía</option>
                                            <option value="Obras Civiles"
                                                {{ ($assignment&&$assignment->type=='Obras Civiles')||
                                                    old('type')=='Obras Civiles' ? 'selected="selected"' :
                                                     '' }}>Obras Civiles</option>
                                            <option value="Venta de material"
                                                {{ ($assignment&&$assignment->type=='Venta de material')||
                                                    old('type')=='Venta de material' ? 'selected="selected"' :
                                                     '' }}>Venta de material</option>
                                        </select>
                                    </div>
                                    --}}

                                    <div class="input-group" style="width: 100%">
                                        <label for="sub_type" class="input-group-addon" style="width: 23%;text-align: left">
                                            Tipo trabajo <span class="pull-right">*</span>
                                        </label>

                                        <select required="required" class="form-control" name="sub_type" id="sub_type"
                                                data-sub_type="{{ $assignment ? $assignment->sub_type : '' }}">
                                            <option value="" hidden>Seleccione el tipo de trabajo</option>
                                        </select>
                                    </div>

                                    {{--
                                    <div class="input-group" style="width: 100%" id="client_container">
                                        <label for="client" class="input-group-addon" style="width: 23%;text-align: left">
                                            Cliente
                                        </label>

                                        <select required="required" class="form-control" name="client" id="client"
                                                disabled="disabled">
                                            <option value="" hidden>Seleccione un cliente</option>
                                            @foreach($clients as $client)
                                                <option value="{{ $client->client }}"
                                                        {{ ($assignment&&$assignment->client==$client->client)||
                                                            old('client')==$client->client ?
                                                         'selected="selected"' : '' }}>{{ $client->client }}</option>
                                            @endforeach
                                            <option value="Otro">Otro</option>
                                        </select>
                                    </div>
                                    <input required="required" type="text" class="form-control" name="other_client"
                                           id="other_client" placeholder="Cliente" disabled="disabled">
                                    --}}

                                    <div class="input-group" style="width: 100%">
                                        <label for="type_award" class="input-group-addon" style="width: 23%;text-align: left">
                                            Adjudicación <span class="pull-right">*</span>
                                        </label>

                                        <select required="required" class="form-control" name="type_award" id="type_award">
                                            <option value="" hidden>Seleccione el tipo de adjudicación</option>
                                            <option value="Licitación"
                                                {{ ($assignment&&$assignment->type_award=='Licitación')||
                                                    old('type_award')=='Licitación' ? 'selected="selected"' :
                                                     '' }}>Licitación</option>
                                            @foreach($types_award as $type_award)
                                                <option value="{{ $type_award->type_award }}"
                                                        {{ ($assignment&&$assignment->type_award==$type_award->type_award)||
                                                            old('type_award')==$type_award->type_award ?
                                                            'selected="selected"' : '' }}>{{ $type_award->type_award }}</option>
                                            @endforeach
                                            <option value="Otro">Otro</option>
                                        </select>
                                    </div>
                                    <input required="required" type="text" class="form-control" name="other_type_award"
                                           id="other_type_award" placeholder="Tipo de adjudicación" disabled="disabled">

                                    <div class="input-group" style="width: 100%">
                                        <label for="status" class="input-group-addon" style="width: 23%;text-align: left">
                                            Estado <span class="pull-right">*</span>
                                        </label>

                                        <select required="required" class="form-control" name="status" id="status"
                                                onchange="dynamic_status_change($(this))">
                                            <option value="" hidden>Seleccione un estado</option>

                                            @for($i=0;$i<=$last_stat;$i++)
                                                <option value="{{ $i }}" data-content="{{ App\Assignment::$status_names[$i] }}"
                                                        {{ ($assignment&&$assignment->status==$i)||
                                                            old('status')==$i&&old('status')!='' ? 'selected="selected"' :
                                                             '' }}>{{ App\Assignment::$status_names[$i]
                                                          /*App\Assignment::statuses($i)*/ }}</option>
                                            @endfor

                                        </select>

                                        {{--
                                        <select required="required" class="form-control" name="status" id="status"
                                                onchange="dynamic_status_change($(this))">
                                            <option value="" hidden>Seleccione un estado</option>
                                            <option value="Relevamiento"
                                                {{ $assignment&&$assignment->statuses($assignment->status)=='Relevamiento' ?
                                                 'selected="selected"' : '' }}
                                            >Relevamiento</option>
                                            <option value="Cotizado"
                                                {{ $assignment&&$assignment->statuses($assignment->status)=='Cotizado' ?
                                                 'selected="selected"' : '' }}
                                            >Cotizado</option>
                                            <option value="Ejecución"
                                                {{ $assignment&&$assignment->statuses($assignment->status)=='Ejecución' ?
                                                 'selected="selected"' : '' }}
                                            >Ejecución</option>
                                            <option value="Revisión"
                                                {{ $assignment&&$assignment->statuses($assignment->status)=='Revisión' ?
                                                 'selected="selected"' : '' }}
                                            >Revisión</option>
                                            <option value="Cobro"
                                                {{ $assignment&&$assignment->statuses($assignment->status)=='Cobro' ?
                                                 'selected="selected"' : '' }}
                                            >Cobro</option>
                                            <option value="Concluído"
                                                {{ $assignment&&$assignment->statuses($assignment->status)=='Concluído' ?
                                                 'selected="selected"' : '' }}
                                            >Concluído</option>
                                            <option value="No asignado"
                                                {{ $assignment&&$assignment->statuses($assignment->status)=='No asignado' ?
                                                 'selected="selected"' : '' }}
                                            >No asignado</option>
                                        </select>
                                        --}}
                                    </div>

                                    <div class="input-group" style="width: 100%">
                                        <label for="branch_id" class="input-group-addon" style="width: 23%;text-align: left">
                                            Sucursal <span class="pull-right">*</span>
                                        </label>

                                        <select required="required" class="form-control" name="branch_id" id="branch_id">
                                            <option value="" hidden>Seleccione la oficina que ejecutará el trabajo</option>
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}"
                                                        {{ ($assignment&&$assignment->branch_id==$branch->id)||
                                                            old('branch_id')==$branch->id ? 'selected="selected"' :
                                                             '' }}>{{ $branch->city }}</option>
                                            @endforeach
                                            {{--
                                            <option value="La Paz"
                                                {{ ($assignment&&$assignment->branch=='La Paz')||old('branch')=='La Paz' ?
                                                    'selected="selected"' : '' }}>La Paz</option>
                                            <option value="Cochabamba"
                                                    {{ ($assignment&&$assignment->branch=='Cochabamba')||old('branch')=='Cochabamba' ?
                                                        'selected="selected"' : '' }}>Cochabamba</option>
                                            <option value="Santa Cruz"
                                                    {{ ($assignment&&$assignment->branch=='Santa Cruz')||old('branch')=='Santa Cruz' ?
                                                        'selected="selected"' : '' }}>Santa Cruz</option>
                                            --}}
                                        </select>
                                    </div>

                                    <div id="resp_container" class="form-group has-feedback">
                                        <div class="input-group" style="width: 100%">
                                            <label for="resp_name" class="input-group-addon" style="width: 23%;text-align: left">
                                                Responsable
                                            </label>

                                            <input required="required" type="text" class="form-control" name="resp_name"
                                                   id="resp_name"
                                                   value="{{ $assignment&&$assignment->responsible ?
                                                    $assignment->responsible->name : old('resp_name') }}"
                                                   placeholder="Project Manager de ABROS">
                                        </div>

                                        <div class="input-group" style="width: 100%;text-align: center" id="resultado" align="center"></div>
                                    </div>

                                    <div class="input-group" style="width: 100%">
                                        <label for="contact_name" class="input-group-addon" style="width: 23%;text-align: left">
                                            Contacto <span class="pull-right">*</span>
                                        </label>

                                        <input required="required" type="text" class="form-control" name="contact_name"
                                               id="contact_name"
                                               value="{{ $assignment&&$assignment->contact ?
                                                $assignment->contact->name : old('contact_name') }}"
                                               placeholder="Responsable por parte del cliente">
                                    </div>

                                    {{--@if(!$assignment||$assignment->status=='Relevamiento'||$user->priv_level==4)--}}
                                    <div class="input-group" style="width: 100%;text-align: center" id="quote_dates">
                                        <span class="input-group-addon" style="width: 23%/*133px*/;text-align: left">
                                            Plazo cotización:
                                        </span>
                                        <span class="input-group-addon">
                                            <label for="quote_from" style="font-weight: normal; margin-bottom: 0">Desde:</label>
                                            <input type="date" name="quote_from" id="quote_from" step="1" min="2014-01-01"
                                                   value="{{ $assignment ? $assignment->quote_from :
                                                        (old('quote_from') ?: $current_date) }}">

                                            <label for="quote_to" style="font-weight: normal; margin-bottom: 0">Hasta:</label>
                                            <input type="date" name="quote_to" id="quote_to" step="1" min="2014-01-01"
                                                   value="{{ $assignment ? $assignment->quote_to : old('quote_to') }}">
                                        </span>
                                        <input required="required" type="number" class="form-control" name="quote_days"
                                               step="1" min="0" placeholder="Días" value="{{ old('quote_days') }}">
                                    </div>

                                    <div class="input-group" style="width: 100%;text-align: center" id="execution_dates_assigned">
                                        <span class="input-group-addon" style="width: 23%;text-align: left">
                                            Plazo asignado:
                                        </span>
                                        <span class="input-group-addon">
                                            <label for="start_line" style="font-weight: normal; margin-bottom: 0">Desde:</label>
                                            <input type="date" name="start_line" id="start_line" step="1" min="2014-01-01"
                                                   value="{{ $assignment ? $assignment->start_line :
                                                        (old('start_line') ?: $current_date) }}">

                                            <label for="deadline" style="font-weight: normal; margin-bottom: 0">Hasta:</label>
                                            <input type="date" name="deadline" id="deadline" step="1" min="2014-01-01"
                                                   value="{{ $assignment ? $assignment->deadline : old('deadline') }}">
                                        </span>
                                        <input required="required" type="number" class="form-control" name="exec_days_assigned"
                                               step="1" min="0" placeholder="Días" value="{{ old('quote_days') }}">
                                    </div>

                                    {{--@if(($assignment&&$assignment->status=='Ejecución')||$user->priv_level==4)--}}
                                    <div class="input-group" style="width: 100%;text-align: center" id="execution_dates">
                                        <span class="input-group-addon" style="width: 23%/*133px*/;text-align: left">
                                            Ejecución:
                                        </span>
                                        <span class="input-group-addon">
                                            <label for="start_date" style="font-weight: normal; margin-bottom: 0">Desde:</label>
                                            <input type="date" name="start_date" id="start_date" step="1" min="2014-01-01"
                                                   value="{{ $assignment ? $assignment->start_date :
                                                        (old('start_date') ?: $current_date) }}">

                                            <label for="end_date" style="font-weight: normal; margin-bottom: 0">Hasta:</label>
                                            <input type="date" name="end_date" id="end_date" step="1" min="2014-01-01"
                                                   value="{{ $assignment ? $assignment->end_date : old('end_date') }}">
                                        </span>
                                        <input required="required" type="number" class="form-control" name="exec_days"
                                               step="1" min="0" placeholder="Días" value="{{ old('quote_days') }}">
                                    </div>

                                    @if($assignment&&$user->priv_level==4)
                                        <div class="input-group" style="width: 100%;text-align: center">
                                            <span class="input-group-addon" style="width: 23%/*133px*/;text-align: left">
                                                Tiempo cobro:
                                            </span>
                                            <span class="input-group-addon">
                                                <label for="billing_from" style="font-weight: normal; margin-bottom: 0">Desde:</label>
                                                <input type="date" name="billing_from" id="billing_from" step="1" min="2014-01-01"
                                                       value="{{ $assignment ? $assignment->billing_from :
                                                            old('billing_from') }}">

                                                <label for="billing_to" style="font-weight: normal; margin-bottom: 0">Hasta:</label>
                                                <input type="date" name="billing_to" id="billing_to" step="1" min="2014-01-01"
                                                       value="{{ $assignment ? $assignment->billing_to : old('billing_to') }}">
                                            </span>
                                            <input required="required" type="number" class="form-control" name="billing_days"
                                                   step="1" min="0" placeholder="Días">
                                        </div>
                                    @endif

                                    {{--
                                    @if($user->priv_level==4)
                                        <div class="input-group" style="width: 75%">
                                            <span class="input-group-addon" style="width: 31%;text-align: left">
                                                Monto cotizado:
                                            </span>
                                            <input required="required" type="number" class="form-control" name="quote_price"
                                                   step="any" min="0"
                                                   value="{{ $assignment ? $assignment->quote_price : old('quote_price') }}"
                                                   placeholder="Monto cotizado (sin impuestos)">
                                            <span class="input-group-addon">Bs.</span>
                                        </div>

                                        <div class="input-group" style="width: 75%">
                                            <span class="input-group-addon" style="width: 31%;text-align: left">
                                                Monto ejecutado:
                                            </span>
                                            <input required="required" type="number" class="form-control" name="executed_price"
                                                   step="any" min="0"
                                                   value="{{ $assignment ? $assignment->executed_price : old('executed_price') }}"
                                                   placeholder="Monto ejecutado (sin impuestos)">
                                            <span class="input-group-addon">Bs.</span>
                                        </div>

                                        <div class="input-group" style="width: 75%">
                                            <span class="input-group-addon" style="width: 31%;text-align: left">
                                                Monto asignado:
                                            </span>
                                            <input required="required" type="number" class="form-control" name="assigned_price"
                                                   step="any" min="0"
                                                   value="{{ $assignment ? $assignment->assigned_price : old('assigned_price') }}"
                                                   placeholder="Monto asignado (sin impuestos)">
                                            <span class="input-group-addon">Bs.</span>
                                        </div>

                                        <div class="input-group" style="width: 75%">
                                            <span class="input-group-addon" style="width: 31%;text-align: left">
                                                Monto cobrado:
                                            </span>
                                            <input required="required" type="number" class="form-control" name="charged_price"
                                                   step="any" min="0"
                                                   value="{{ $assignment ? $assignment->charged_price : old('charged_price') }}"
                                                   placeholder="Monto cobrado (sin impuestos)">
                                            <span class="input-group-addon">Bs.</span>
                                        </div>
                                    @endif
                                    --}}

                                    <textarea rows="3" class="form-control" name="observations"
                                        placeholder="Información adicional">{{ $assignment ?
                                         $assignment->observations : old('observations') }}</textarea>

                                </span>
                                    </div>

                                    @if(!$assignment)
                                        <br>
                                        <div id="file_container" class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-cloud-upload"></i>
                                                <label>Documento de asignación</label>
                                                <input type="file" class="form-control" name="file" id="file">
                                            </span>
                                        </div>
                                    @endif

                                </div>

                                @include('app.loader_gif')

                                <div class="form-group" align="center">
                                    <button type="submit" class="btn btn-success"
                                            onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                                        <i class="fa fa-floppy-o"></i> Guardar
                                    </button>

                                    @if($assignment&&$user->priv_level==4)
                                        <button type="submit" form="delete" class="btn btn-danger">
                                            <i class="fa fa-trash-o"></i> Quitar
                                        </button>
                                    @endif
                                </div>

                                {{ csrf_field() }}
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

        /*
        var $client = $('#client'), $other_client = $('#other_client');
        $client.change(function () {
            if ($client.val()==='Otro') {
                $other_client.removeAttr('disabled').show();
            } else {
                $other_client.attr('disabled', 'disabled').val('').hide();
            }
        }).trigger('change');
        */

        var $type_award = $('#type_award'), $other_type_award = $('#other_type_award');
        $type_award.change(function () {
            if ($type_award.val()==='Otro') {
                $other_type_award.removeAttr('disabled').show();
                $('#file_container').show();
                $('#file').removeAttr('disabled').show();
            }
            else if($type_award.val()==='Licitación') {
                $('#file_container').hide();
                $('#file').attr('disabled', 'disabled').hide();
            }
            else {
                $other_type_award.attr('disabled', 'disabled').val('').hide();
                $('#file_container').show();
                $('#file').removeAttr('disabled').show();
            }
        }).trigger('change');

        /*
        var $consulta;
        $("#resp_name").change(function(e){
            $consulta = $("#resp_name").val();
            $("#resultado").delay(1000).queue(function(n) {
                $("#resultado").html('comprobando');
                $.ajax({
                    type: "POST",
                    url: "verifying",
                    data: "b="+$consulta,
                    dataType: "html",
                    error: function(){
                        alert("error petición ajax");
                    },
                    success: function(data){
                        $("#resultado").html(data);
                        n();
                    }
                });
            });
        });
        */

        function check_existence(){
            /* $.get('/ajax', function(data){ alert(data); }); */
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
            var project_id_field = $("#project_id")/*, type = $("#type"), type_container = $('#type_container')*/;

            $("#wait").hide();
            //$('#client').hide();
            //$('#client_container').hide();
            $('#quote_dates').hide();
            $('#execution_dates').hide();
            $('#execution_dates_assigned').hide();
            //type.hide();
            //type_container.hide();

            $("#resultado").hide();
            $('#resp_name').focusout(check_existence);

            //dynamic_project_change(project_id_field);
            dynamic_select(project_id_field);
            //dynamic_select(type);
            dynamic_status_change($("#status"));

            /*
            $('#inputform').on('keydown', 'input', function (event) {
                if (event.which == 13) {
                    event.preventDefault();
                    var $this = $(event.target);
                    var index = parseFloat($this.attr('data-index'));
                    $('[data-index="' + (index + 1).toString() + '"]').focus();
                }
            });
            */
        });

        $('#resp_name').autocomplete({
            type: 'post',
            serviceUrl:'/autocomplete/users',
            dataType: 'JSON',
            onSelect: check_existence
        });

        $('#contact_name').autocomplete({
            type: 'post',
            serviceUrl:'/autocomplete/contacts',
            dataType: 'JSON'
        });

        /* code for use with jquery.ui autocomplete
        $( "#resp_name" ).autocomplete({
            source: function (request, response) {
                $.ajax({
                    type: "POST",
                    url:"/autocomplete/users",
                    data: request,
                    success: response,
                    dataType: 'json'
                });
            }
        }, {minLength: 1 });
        */

        function dynamic_select(c){
            if($(c).val()!==0){
                $.post('/dynamic_assignment/sub_type', { type: $(c).attr('id'), option: $(c).val(),
                    sub_type: $("#sub_type").data('sub_type') }, function(data){
                    $("#sub_type").html(data).show();
                });
            }
        }

        /*
        function dynamic_project_change(c){

            var $type = $('#type'), $type_container = $('#type_container'),
                    $client = $('#client'), $client_container = $('#client_container');

            if (c.val() === 0) {
                $type_container.show();
                $type.removeAttr('disabled').show();
                $client_container.show();
                $client.removeAttr('disabled').show();
            } else {
                $type_container.hide();
                $type.attr('disabled', 'disabled').hide();
                $client_container.hide();
                $client.attr('disabled', 'disabled').hide();
            }
        }
        */

        function dynamic_status_change(c){

            var /*$status = $('#status'),*/ $quote_dates = $('#quote_dates'), $execution_dates = $('#execution_dates'),
                    $execution_dates_assigned = $('#execution_dates_assigned');

            if (c.find(':selected').data("content")==='Relevamiento') {
                $quote_dates.show();
                $execution_dates.hide();
                $execution_dates_assigned.hide();
            }
            else if (c.find(':selected').data("content")==='Ejecución') {
                $quote_dates.hide();
                $execution_dates.show();
                $execution_dates_assigned.show();
            }
            else {
                $quote_dates.hide();
                $execution_dates.hide();
                $execution_dates_assigned.hide();
            }
        }
    </script>
@endsection
