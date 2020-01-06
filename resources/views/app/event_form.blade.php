@extends('layouts.master')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/custom_autocomplete.css") }}">
    <script src="{{ asset('https://cdnjs.cloudflare.com/ajax/libs/jquery.devbridge-autocomplete/1.2.27/jquery.autocomplete.js') }}">
    </script>

    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">

        <div class="panel panel-sky" >
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    {{ $event ? 'Modificar información de evento' : 'Agregar nuevo evento' }}
                </div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-undo"></i>
                    </a>
                    <a href="/event/{{ $type }}/{{ $id }}" class="btn btn-warning" title="Volver al resumen de eventos">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <p><em>Nota.- Los campos con * son obligatorios</em></p>

                @if($event)
                    <form id="delete" action="/event/{{ $type }}/{{ $event->id }}" method="post">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                    <form novalidate="novalidate" action="{{ '/event/'.$type.'/'.$event->id }}" method="post">
                        <input type="hidden" name="_method" value="put">
                        @else
                            <form novalidate="novalidate" action="{{ '/event/'.$type.'/'.$id }}" method="post">
                                @endif
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                            <div class="input-group" style="width: 100%">
                                                <label for="to_associate_id" class="input-group-addon"
                                                       style="width: 23%;text-align: left">
                                                    Pertenece a: <span class="pull-right">*</span>
                                                </label>

                                                <select required="required" class="form-control" name="to_associate_id"
                                                    id="to_associate_id">
                                                    <option value="{{ $id }}" selected="selected">
                                                        @if($type=='site')
                                                            {{ 'Sitio: '.$type_info->name.' - Proyecto: '.
                                                                str_limit($type_info->assignment->name,50) }}
                                                        @elseif($type=='assignment')
                                                            {{ 'Asignación: '.str_limit($type_info->name,100) }}
                                                        @elseif($type=='task')
                                                            {{ 'Item: '.str_limit($type_info->name,100) }}
                                                        @elseif($type=='oc')
                                                            {{ 'Orden de compra: '.$type_info->code }}
                                                        @elseif($type=='invoice')
                                                            {{ 'Factura de proveedor: '.$type_info->number }}
                                                        @endif
                                                    </option>
                                                </select>
                                            </div>

                                            <div class="input-group" style="width: 100%">
                                                <label for="description" class="input-group-addon"
                                                       style="width: 23%;text-align: left">
                                                    Tipo: <span class="pull-right">*</span>
                                                </label>

                                                <select required="required" class="form-control" name="description"
                                                        id="description">
                                                    <option value="" hidden>Seleccione el tipo de evento</option>
                                                    @foreach($event_types as $type)
                                                        <option value="{{ $type->description }}"
                                                                {{ ($event&&$event->description==$type->description)||
                                                                    old('description')==$type->description ?
                                                                    'selected="selected"' : '' }}
                                                        >{{ $type->description }}</option>
                                                    @endforeach
                                                    <option value="Otro" {{ old('description')=='Otro' ?
                                                        'selected="selected"' : '' }}>Otro</option>
                                                </select>
                                            </div>
                                            <input required="required" type="text" class="form-control" name="other_description"
                                                    id="other_description" placeholder="Tipo de evento *" disabled="disabled"
                                                    value="{{ old('other_description') }}">

                                            <div class="input-group" style="width: 100%">
                                                <label for="detail" class="input-group-addon"
                                                       style="width: 23%;text-align: left">
                                                    Detalle: <span class="pull-right">*</span>
                                                </label>

                                                <textarea rows="3" required="required" class="form-control" name="detail"
                                                    id="detail" placeholder="Detalle de evento">{{ $event ?
                                                     $event->detail : old('detail') }}</textarea>
                                            </div>

                                            <div id="responsible_container" class="form-group has-feedback">
                                                <div class="input-group" style="width: 100%">
                                                    <label for="responsible_name" class="input-group-addon"
                                                           style="width: 23%;text-align: left">
                                                        Responsable:
                                                    </label>

                                                    <input required="required" type="text" class="form-control"
                                                           name="responsible_name" id="responsible_name"
                                                           value="{{ $event&&$event->responsible ?
                                                                $event->responsible->name : old('responsible_name') }}"
                                                           placeholder="Persona que reporta el evento">
                                                </div>

                                                <div class="input-group" style="width: 100%;" id="result"></div>
                                            </div>

                                            <div class="input-group" style="width: 100%;text-align: center">
                                                <span class="input-group-addon">
                                                    <label for="date" style="font-weight: normal; margin-bottom: 0;">
                                                        Desde:
                                                    </label>
                                                    <input type="date" name="date" id="date" step="1" min="2014-01-01"
                                                           value="{{ old('date') ?: ($event ? $event->date : date('Y-m-d')) }}">
                                                    &emsp;
                                                    <label for="date_to" style="font-weight: normal; margin-bottom: 0;">
                                                        Hasta:
                                                    </label>
                                                    <input type="date" name="date_to" id="date_to" step="1" min="2014-01-01"
                                                           value="{{ old('date_to') ?: ($event ? $event->date_to : date('Y-m-d')) }}">
                                                </span>
                                                <input required="required" type="number" class="form-control" name="total_days"
                                                       step="1" min="0" placeholder="Días"
                                                       title="Indique la fecha de fin o la cantidad de días desde la fecha de inicio">
                                            </div>

                                            {{--
                                            <div class="input-group" style="width: 100%">
                                                <span class="input-group-addon">
                                                    <label for="date" style="font-weight: normal">Fecha: </label>

                                                    <input type="date" name="date" id="date" step="1" min="2014-01-01"
                                                           max="{{ date('Y-m-d') }}"
                                                           value="{{ $event ? $event->date : (old('date') ?: date("Y-m-d")) }}">
                                                </span>
                                            </div>
                                            --}}

                                        </div>
                                    </div>
                                </div>

                                @include('app.loader_gif')

                                <div class="form-group" align="center">
                                    <button type="submit" class="btn btn-success"
                                            onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                                        <i class="fa fa-floppy-o"></i> Guardar
                                    </button>

                                    @if($event&&$user->prj_evt_edt /*$user->priv_level==4*/)
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
        var $description = $('#description'), $other_description = $('#other_description');
        $description.change(function () {
            if ($description.val()==='Otro') {
                $other_description.removeAttr('disabled').show();
            } else {
                $other_description.attr('disabled', 'disabled').val('').hide();
            }
        }).trigger('change');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function check_existence(){

            var resp_name=$('#responsible_name').val();
            if(resp_name.length >0){
                $.post('/check_existence', { resp_name: resp_name }, function(data){
                    $("#result").html(data.message).show();
                    if(data.status==="warning"){
                        $('#responsible_container').addClass("has-warning").removeClass("has-success");
                    }
                    else if(data.status==="success"){
                        $('#responsible_container').addClass("has-success").removeClass("has-warning");
                    }
                });
            }
            else{
                $("#result").hide();
                $('#responsible_container').removeClass("has-warning").removeClass("has-success");
            }
        }

        $(document).ready(function(){
            $("#wait").hide();
            $("#result").hide();
            $('#responsible_name').focusout(check_existence);
        });

        $('#responsible_name').autocomplete({
            type: 'post',
            serviceUrl:'/autocomplete/users',
            dataType: 'JSON',
            onSelect: check_existence
        });
    </script>
@endsection
