@extends('layouts.master')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/custom_autocomplete.css") }}">
    <script src="{{ asset('https://cdnjs.cloudflare.com/ajax/libs/jquery.devbridge-autocomplete/1.2.27/jquery.autocomplete.js') }}">
    </script>

    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 mg-btm-px-40 col-md-6 col-md-offset-3 col-sm-10 col-sm-offset-1">

        <div class="panel panel-sky">
            <div class="panel-heading" align="center">
                <div class="panel-title">{{ $site ? 'Actualizar información de sitio' : 'Agregar nuevo sitio' }}</div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-undo"></i>
                    </a>
                    <a href="/site{{ $site ? '/'.$site->assignment_id : ($assignment_id!=0 ? '/'.$assignment_id : '') }}"
                       class="btn btn-warning" title="Volver a resumen de sitios">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <p><em>Nota.- los campos con * son obligatorios</em></p>

                @if($site)
                    <form id="delete" action="/site/{{ $site->id }}" method="post">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                    <form novalidate="novalidate" action="{{ '/site/'.$site->id }}" method="post">
                        <input type="hidden" name="_method" value="put">
                        @else
                            <form novalidate="novalidate" action="{{ '/site' }}" method="post">
                                @endif
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                                <div class="form-group">
                                    <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                    <div class="input-group" style="width: 100%">
                                        <label for="name" class="input-group-addon" style="width: 23%;text-align: left">
                                            Sitio: <span class="pull-right">*</span>
                                        </label>

                                        <input required="required" type="text" class="form-control" name="name" id="name"
                                               value="{{ $site ? $site->name : old('name') }}" placeholder="Nombre de sitio">
                                    </div>

                                    <div class="input-group" style="width: 100%">
                                        <label for="assignment_id" class="input-group-addon" style="width: 23%;text-align: left">
                                            Asignación: <span class="pull-right">*</span>
                                        </label>

                                        <select required="required" class="form-control" name="assignment_id" id="assignment_id"
                                                {{ $user->priv_level==1 ? 'readonly="readonly"' : '' }}>
                                            <option value="" hidden>Seleccione una asignación *</option>
                                            <option value="{{ $assignment->id }}" selected="selected"
                                                >{{ $assignment->name }}</option>
                                        </select>
                                    </div>

                                    @if($site/*||$user->priv_level==4*/)
                                        <div class="input-group" style="width: 100%">
                                            <label for="status" class="input-group-addon" style="width: 23%;text-align: left">
                                                Estado: <span class="pull-right">*</span>
                                            </label>

                                            <select required="required" class="form-control" name="status" id="status">
                                                <option value="" hidden>Seleccione un estado</option>
                                                @for($i=0;$i<=$last_stat;$i++)
                                                    <option value="{{ $i }}"
                                                            {{ ($site&&$site->status==$i)||old('statud')==$i
                                                                ? 'selected="selected"' : '' }}
                                                    >{{ App\Site::$status_options[$i] /*App\Site::first()->statuses($i)*/ }}</option>
                                                @endfor
                                                {{--
                                                <option value="Relevamiento"
                                                        {{ $site&&$site->status=='Relevamiento' ? 'selected="selected"' : '' }}
                                                >Relevamiento</option>
                                                <option value="Cotizado"
                                                        {{ $site&&$site->status=='Cotizado' ? 'selected="selected"' : '' }}
                                                >Cotizado</option>
                                                <option value="Ejecución"
                                                        {{ $site&&$site->status=='Ejecución' ? 'selected="selected"' : '' }}
                                                >Ejecución</option>
                                                <option value="Revisión"
                                                        {{ $site&&$site->status=='Revisión' ? 'selected="selected"' : '' }}
                                                >Revisión</option>
                                                <option value="Cobro"
                                                        {{ $site&&$site->status=='Cobro' ? 'selected="selected"' : '' }}
                                                >Cobro</option>
                                                <option value="Concluído"
                                                        {{ $site&&$site->status=='Concluído' ? 'selected="selected"' : '' }}
                                                >Concluído</option>
                                                <option value="No asignado"
                                                        {{ $site&&$site->status=='No asignado' ? 'selected="selected"' : '' }}
                                                >No asignado</option>
                                                --}}
                                            </select>
                                        </div>
                                    @endif

                                    @if($assignment&&$assignment->type=='Radiobases')
                                        <div class="input-group" style="width: 100%">
                                            <label for="site_type" class="input-group-addon" style="width: 23%;text-align: left">
                                                Tipo de sitio:
                                            </label>

                                            <select required="required" class="form-control" name="site_type" id="site_type">
                                                <option value="" hidden>Seleccione un tipo de sitio</option>
                                                <option value="Radio enlace"
                                                        {{ ($site&&$site->site_type=='Radio enlace')||
                                                            old('site_type')=='Radio enlace' ? 'selected="selected"' :
                                                            '' }}>Radio enlace</option>
                                                <option value="Radiobase RBS - Sitio Macro"
                                                        {{ ($site&&$site->site_type=='Radiobase RBS - Sitio Macro')||
                                                            old('site_type')=='Radiobase RBS - Sitio Macro' ? 'selected="selected"' :
                                                            '' }}>Radiobase RBS - Sitio Macro</option>
                                                <option value="Micro"
                                                        {{ ($site&&$site->site_type=='Micro')||
                                                            old('site_type')=='Micro' ? 'selected="selected"' :
                                                            '' }}>Micro</option>
                                                <option value="COLT"
                                                        {{ ($site&&$site->site_type=='COLT')||
                                                            old('site_type')=='COLT' ? 'selected="selected"' :
                                                            '' }}>COLT</option>
                                            </select>
                                        </div>

                                        <div class="input-group" style="width: 100%">
                                            <label for="work_type" class="input-group-addon" style="width: 23%;text-align: left">
                                                Tipo de trabajo:
                                            </label>

                                            <select required="required" class="form-control" name="work_type" id="work_type">
                                                <option value="" hidden>Seleccione un tipo de trabajo</option>
                                                <option value="Instalación"
                                                        {{ ($site&&$site->work_type=='Instalación')||
                                                            old('work_type')=='Instalación' ? 'selected="selected"' : '' }}
                                                    >Instalación</option>
                                                <option value="Desinstalación"
                                                        {{ ($site&&$site->work_type=='Desinstalación')||
                                                            old('work_type')=='Desinstalación' ? 'selected="selected"' : '' }}
                                                    >Desinstalación</option>
                                                <option value="Swap"
                                                        {{ ($site&&$site->work_type=='Swap')||
                                                            old('work_type')=='Swap' ? 'selected="selected"' : '' }}
                                                    >Swap</option>
                                            </select>
                                        </div>
                                    @endif

                                    <div class="input-group" style="width: 100%">
                                        <label for="origin_name" class="input-group-addon" style="width: 23%;text-align: left">
                                            Orígen:
                                        </label>

                                        <input required="required" type="text" class="form-control" name="origin_name"
                                               id="origin_name" value="{{ $site ? $site->origin_name : old('origin_name') }}"
                                               placeholder="Nombre de ubicación de orígen (Tx)">
                                    </div>

                                    <div class="input-group" style="width: 100%">
                                        <label for="latitude" class="input-group-addon" style="width: 23%;text-align: left">
                                            Latitud:
                                        </label>

                                        <input required="required" type="number" class="form-control" name="latitude"
                                               id="latitude" step="any" min="0"
                                               value="{{ $site&&$site->latitude!=0 ? $site->latitude : old('latitude') }}"
                                               placeholder="Ej. -19.000000" title="Latitud de orígen en grados (º)">

                                        <label for="longitude" class="input-group-addon" style="width: 23%;text-align: left">
                                            Longitud:
                                        </label>

                                        <input required="required" type="number" class="form-control" name="longitude"
                                               id="longitude" step="any" min="0"
                                               value="{{ $site&&$site->longitude!=0 ? $site->longitude : old('longitude') }}"
                                               placeholder="Ej. -67.000000" title="Longitud de orígen en grados (º)">
                                    </div>

                                    <div class="input-group" style="width: 100%">
                                        <label for="destination_name" class="input-group-addon" style="width: 23%;text-align: left">
                                            Destino:
                                        </label>

                                        <input required="required" type="text" class="form-control" name="destination_name"
                                               id="destination_name" value="{{ $site ? $site->destination_name :
                                                old('destination_name') }}"
                                               placeholder="Nombre de ubicación de destino (Rx)">
                                    </div>

                                    <div class="input-group" style="width: 100%">
                                        <label for="lat_destination" class="input-group-addon" style="width: 23%;text-align: left">
                                            Latitud:
                                        </label>

                                        <input required="required" type="number" class="form-control" name="lat_destination"
                                               id="lat_destination" step="any" min="0"
                                               value="{{ $site&&$site->lat_destination!=0 ? $site->lat_destination :
                                                old('lat_destination') }}"
                                               placeholder="Ej. -19.000000" title="Latitud de destino en grados (º)">

                                        <label for="long_destination" class="input-group-addon" style="width: 23%;text-align: left">
                                            Longitud:
                                        </label>

                                        <input required="required" type="number" class="form-control" name="long_destination"
                                               id="long_destination" step="any" min="0"
                                               value="{{ $site&&$site->long_destination!=0 ? $site->long_destination :
                                                old('long_destination') }}"
                                               placeholder="Ej. -67.000000" title="Longitud de destino en grados (º)">

                                    </div>

                                    <div class="input-group" style="width: 100%">
                                        <label for="department" class="input-group-addon" style="width: 23%;text-align: left">
                                            Departamento:
                                        </label>

                                        <select required="required" class="form-control" name="department" id="department">
                                            <option value="" hidden>Seleccione el departamento donde se ubica el sitio</option>
                                            <option value="Beni"
                                                    {{ ($site&&$site->department=='Beni')||old('department')=='Beni' ?
                                                        'selected="selected"' : '' }}>Beni</option>
                                            <option value="Chuquisaca"
                                                    {{ ($site&&$site->department=='Chuquisaca')||old('department')=='Chuquisaca' ?
                                                        'selected="selected"' : '' }}>Chuquisaca</option>
                                            <option value="Cochabamba"
                                                    {{ ($site&&$site->department=='Cochabamba')||old('department')=='Cochabamba' ?
                                                        'selected="selected"' : '' }}>Cochabamba</option>
                                            <option value="La Paz"
                                                    {{ ($site&&$site->department=='La Paz')||old('department')=='La Paz' ?
                                                        'selected="selected"' : '' }}>La Paz</option>
                                            <option value="Oruro"
                                                    {{ ($site&&$site->department=='Oruro')||old('department')=='Oruro' ?
                                                        'selected="selected"' : '' }}>Oruro</option>
                                            <option value="Pando"
                                                    {{ ($site&&$site->department=='Pando')||old('department')=='Pando' ?
                                                        'selected="selected"' : '' }}>Pando</option>
                                            <option value="Potosí"
                                                    {{ ($site&&$site->department=='Potosí')||old('department')=='Potosí' ?
                                                        'selected="selected"' : '' }}>Potosí</option>
                                            <option value="Santa Cruz"
                                                    {{ ($site&&$site->department=='Santa Cruz')||old('department')=='Santa Cruz' ?
                                                        'selected="selected"' : '' }}>Santa Cruz</option>
                                            <option value="Tarija"
                                                    {{ ($site&&$site->department=='Tarija')||old('department')=='Tarija' ?
                                                        'selected="selected"' : '' }}>Tarija</option>
                                        </select>
                                    </div>

                                    <div class="input-group" style="width: 100%">
                                        <label for="municipality" class="input-group-addon" style="width: 23%;text-align: left">
                                            Localidad:
                                        </label>

                                        <input required="required" type="text" class="form-control" name="municipality"
                                               id="municipality" value="{{ $site ? $site->municipality : old('municipality') }}"
                                               placeholder="Ciudad / Localidad / Población / Municipio">
                                    </div>

                                    <div class="input-group" style="width: 100%">
                                        <label for="type_municipality" class="input-group-addon" style="width: 23%;text-align: left">
                                            Tipo de localidad:
                                        </label>

                                        <select required="required" class="form-control" name="type_municipality"
                                                id="type_municipality">
                                            <option value="" hidden>Seleccione el tipo de localidad</option>
                                            <option value="Urbano"
                                                {{ ($site&&$site->type_municipality=='Urbano')||
                                                    old('type_municipality')=='Urbano' ? 'selected="selected"' : '' }}
                                                >Urbano</option>
                                            <option value="Rural"
                                                {{ ($site&&$site->type_municipality=='Rural')||
                                                    old('type_municipality')=='Rural' ? 'selected="selected"' : '' }}
                                                >Rural</option>
                                        </select>
                                    </div>

                                    <div id="resp_container" class="form-group has-feedback">
                                        <div class="input-group" style="width: 100%">
                                            <label for="resp_name" class="input-group-addon" style="width: 23%;text-align: left">
                                                Responsable:
                                            </label>

                                            <input required="required" type="text" class="form-control" name="resp_name"
                                                   id="resp_name" value="{{ $site&&$site->responsible ?
                                                    $site->responsible->name : old('resp_name') }}"
                                                   placeholder="Responsable por parte de ABROS">
                                        </div>

                                        <div class="input-group" style="width: 100%;text-align: center" id="resultado" align="center"></div>
                                    </div>

                                    <div class="input-group" style="width: 100%">
                                        <label for="contact_name" class="input-group-addon" style="width: 23%;text-align: left">
                                            Contacto:
                                        </label>

                                        <input required="required" type="text" class="form-control" name="contact_name"
                                               id="contact_name" value="{{ $site&&$site->contact ? $site->contact->name :
                                                old('contact_name') }}"
                                               placeholder="Responsable por parte del cliente">
                                    </div>

                                    <div class="input-group" style="width: 100%;text-align: center">
                                        <span class="input-group-addon" style="width: 23%;text-align: left">
                                            Plazo asignado:
                                        </span>
                                        <span class="input-group-addon">
                                            <label for="start_line" style="font-weight: normal; margin-bottom: 0">Desde:</label>
                                            <input type="date" name="start_line" id="start_line" step="1" min="2014-01-01"
                                                   value="{{ $site ? $site->start_line : (old('start_line') ?: $current_date) }}">

                                            <label for="deadline" style="font-weight: normal; margin-bottom: 0">Hasta:</label>
                                            <input type="date" name="deadline" id="deadline" step="1" min="2014-01-01"
                                                   value="{{ $site ? $site->deadline : old('deadline') }}">
                                        </span>
                                        <input required="required" type="number" class="form-control" name="interval_days_assigned"
                                               step="any" min="0" placeholder="Días">
                                    </div>

                                    <div class="input-group" style="width: 100%;text-align: center">
                                        <span class="input-group-addon" style="width: 23%/*138px*/;text-align: left">
                                            Plazo propio:
                                        </span>
                                        <span class="input-group-addon">
                                            <label for="start_date" style="font-weight: normal; margin-bottom: 0">Desde:</label>
                                            <input type="date" name="start_date" id="start_date" step="1" min="2014-01-01"
                                                   value="{{ $site ? $site->start_date : (old('start_date') ?: $current_date) }}">

                                            <label for="end_date" style="font-weight: normal; margin-bottom: 0">Hasta:</label>
                                            <input type="date" name="end_date" id="end_date" step="1" min="2014-01-01"
                                                   value="{{ $site ? $site->end_date : old('end_date') }}">
                                        </span>
                                        <input required="required" type="number" class="form-control" name="interval_days"
                                               step="any" min="0" placeholder="Días">
                                    </div>

                                    {{--
                                    @if($user->priv_level==4)
                                        <div class="input-group" style="width: 75%">
                                            <span class="input-group-addon" style="width: 31%;text-align: left">Monto cotizado:</span>
                                            <input required="required" type="number" class="form-control" name="quote_price"
                                                   step="any" min="0" value="{{ $site ? $site->quote_price :
                                                        old('quote_price') }}"
                                                   placeholder="Monto cotizado (sin impuestos)">
                                            <span class="input-group-addon">Bs.</span>
                                        </div>

                                        <div class="input-group" style="width: 75%">
                                            <span class="input-group-addon" style="width: 31%;text-align: left">Monto ejecutado:</span>
                                            <input required="required" type="number" class="form-control" name="executed_price"
                                                   step="any" min="0" value="{{ $site ? $site->executed_price :
                                                        old('executed_price') }}"
                                                   placeholder="Monto ejecutado (sin impuestos)">
                                            <span class="input-group-addon">Bs.</span>
                                        </div>

                                        <div class="input-group" style="width: 75%">
                                            <span class="input-group-addon" style="width: 31%;text-align: left">Monto asignado:</span>
                                            <input required="required" type="number" class="form-control" name="assigned_price"
                                                   step="any" min="0" value="{{ $site ? $site->assigned_price :
                                                        old('assigned_price') }}"
                                                   placeholder="Monto asignado (sin impuestos)">
                                            <span class="input-group-addon">Bs.</span>
                                        </div>

                                        <div class="input-group" style="width: 75%">
                                            <span class="input-group-addon" style="width: 31%;text-align: left">Monto cobrado:</span>
                                            <input required="required" type="number" class="form-control" name="charged_price"
                                                   step="any" min="0" value="{{ $site ? $site->charged_price :
                                                        old('charged_price') }}"
                                                   placeholder="Monto cobrado (sin impuestos)">
                                            <span class="input-group-addon">Bs.</span>
                                        </div>
                                    @endif
                                    --}}

                                    <textarea rows="3" class="form-control" name="observations" placeholder="Observaciones"
                                        >{{ $site ? $site->observations : old('observations') }}</textarea>

                                </span>
                                    </div>
                                </div>

                                @include('app.loader_gif')

                                <div class="form-group" align="center">
                                    <button type="submit" class="btn btn-success"
                                            onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                                        <i class="fa fa-floppy-o"></i> Guardar
                                    </button>

                                    @if($site&&$user->action->prj_st_del /*$user->priv_level==4*/)
                                        <button type="submit" form="delete" class="btn btn-danger">
                                            <i class="fa fa-trash-o"></i> Eliminar
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

        $('#contact_name').autocomplete({
            type: 'post',
            serviceUrl:'/autocomplete/contacts',
            dataType: 'JSON'
        });
    </script>
@endsection
