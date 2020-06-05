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

        <div class="panel panel-sky">
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    {{ $activity ? ($selector[0]=='tk' ? 'Modificar actividad' : '') :
                        ($selector[0]=='tk' ? 'Agregar nueva actividad' : '') }}
                </div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="javascript:history.back()" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                    <a href="/activity/{{ $selector[1] }}" class="btn btn-warning" title="Volver a resumen de actividades">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <p><em>Nota.- Los campos con * son obligatorios</em></p>

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

                                    <div class="input-group" style="width: 100%">
                                        <label for="task_id" class="input-group-addon" style="width: 23%;text-align: left">
                                            Item: <span class="pull-right">*</span>
                                        </label>

                                        <select required="required" class="form-control" name="task_id" id="task_id">
                                            <option value="" hidden>Seleccione un item</option>
                                            <option value="{{ $task->id }}" selected="selected"
                                                title="{{ $task->name.' - Sitio: '.$task->site->name }}">
                                                {{ str_limit($task->name,50).' - Sitio: '.str_limit($task->site->name,50) }}
                                            </option>
                                        </select>
                                    </div>

                                    <div id="responsible_container" class="form-group has-feedback">
                                        <div class="input-group" style="width: 100%">
                                            <span class="input-group-addon" style="width: 23%;text-align: left">
                                                Responsable: <span class="pull-right">*</span>
                                            </span>
                                            <input required="required" type="text" class="form-control" name="responsible_name"
                                               id="responsible_name" value="{{ $activity&&$activity->responsible ?
                                               $activity->responsible->name :
                                               ($task->person_responsible ? $task->person_responsible->name :
                                                old('responsible_name') ) }}"
                                               placeholder="Responsable de ABROS en sitio">
                                        </div>
                                        <div class="input-group" style="width: 100%;" id="result"></div>
                                    </div>

                                    {{--
                                    @if(!$activity)
                                        <div class="input-group" style="width: 100%;">
                                            <span class="input-group-addon">
                                                <label>Fecha: </label>
                                                &emsp;
                                                <input type="radio" name="day" value="today" checked="checked"> Hoy
                                                &emsp;
                                                <input type="radio" name="day" value="yesterday"> Ayer
                                            </span>
                                        </div>
                                    @endif

                                    @if($activity&&$user->priv_level==4)
                                    --}}
                                        <div class="input-group" style="width: 100%;">
                                            <span class="input-group-addon">
                                                <label for="date">* Fecha: </label>
                                                <input type="date" name="date" id="date" step="1" min="2014-01-01"
                                                       value="{{ $activity ? $activity->date : (old('date') ?: date("Y-m-d")) }}">
                                            </span>
                                        </div>
                                    {{--
                                    @endif
                                    --}}

                                </span>
                                    </div>

                                    @if($selector[0]=='tk')
                                        <p></p>
                                        <div class="input-group" id="task_container" style="width: 100%">
                                            <div class="input-group-addon">
                                            {{--
                                            <span class="input-group-addon">
                                            --}}
                                                <label>Avance</label>
                                                <div class="input-group" style="width: 100%" id="task_values" align="center">
                                                </div>

                                                <div class="input-group" style="width: 100%">
                                                    <label for="progress" class="input-group-addon"
                                                           style="width: 23%;text-align: left">
                                                        Cantidad: <span class="pull-right">*</span>
                                                    </label>

                                                    <input required="required" type="number" class="form-control" name="progress"
                                                           id="progress" step="1" min="1"
                                                           value="{{ $activity&&$activity->progress!=0 ?
                                                                $activity->progress : old('progress') }}"
                                                           placeholder="Cantidad avanzada">
                                                </div>

                                                <textarea rows="3" required="required" class="form-control" name="observations"
                                                    placeholder="Información adicional">{{ $activity ?
                                                     $activity->observations : old('observations') }}</textarea>
                                            {{--
                                            </span>
                                            --}}
                                            </div>
                                        </div>
                                    @endif

                                </div>

                                @include('app.loader_gif')

                                <div class="form-group" align="center">
                                    <button type="submit" class="btn btn-success"
                                            onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                                        <i class="fa fa-floppy-o"></i> Guardar
                                    </button>

                                    @if($activity&&$user->action->prj_act_del /*$activity&&$user->priv_level==4*/)
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

        function check_existence() {
            var resp_name = $('#responsible_name').val();
            if (resp_name.length > 0) {
                $.post('/check_existence', { value: resp_name }, function(data) {
                    $("#result").html(data.message).show();
                    if (data.status === "warning") {
                        $('#responsible_container').addClass("has-warning").removeClass("has-success");
                    } else if (data.status === "success") {
                        $('#responsible_container').addClass("has-success").removeClass("has-warning");
                    }
                });
            } else {
                $("#result").hide();
                $('#responsible_container').removeClass("has-warning").removeClass("has-success");
            }
        }

        $(document).ready(function() {
            $("#wait").hide();
            $("#result").hide();
            $('#responsible_name').focusout(check_existence);

            $("#task_values").hide();

            $("#progress").keyup(function() {
                $.post('/load_task_values', { task_id: $("#task_id").val(), progress: $("#progress").val() }, function(data) {
                    $("#task_values").html(data).show();
                });
            });
        });

        $('#responsible_name').autocomplete({
            type: 'post',
            serviceUrl:'/autocomplete/users',
            dataType: 'JSON',
            onSelect: check_existence
        });

    </script>
@endsection
