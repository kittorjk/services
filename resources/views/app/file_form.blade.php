@extends('layouts.master')

@section('header')
    @parent
    <style>
        .progress
        {
            display:none;
            position:relative;
            background-color: lightgray;
            width:400px;
            border: 1px solid #ddd;
            padding: 1px;
            border-radius: 3px;
        }
        .bar
        {
            width:0;
            height:20px;
            border-radius: 3px;
        }
        .percent
        {
            position:absolute;
            display:inline-block;
            vertical-align: middle;
            left:48%;
        }
    </style>

    <script type="text/javascript" src="{{ asset('/app/js/jQuery-File-Upload-9.18.0/js/vendor/jquery.ui.widget.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/app/js/jQuery-File-Upload-9.18.0/js/jquery.iframe-transport.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/app/js/jQuery-File-Upload-9.18.0/js/jquery.fileupload.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/app/js/jQuery-File-Upload-9.18.0/js/jquery.fileupload-process.js') }}"></script>

    {{--<script src="http://malsup.github.com/jquery.form.js"></script>
        <script type="text/javascript" src="/app/js/jquery.form.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/3.51/jquery.form.min.js"></script>
        <meta name="csrf-token" content="{{ csrf_token() }}" />--}}
@endsection

@section('content')

<div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
    <div class="panel panel-info" >
        <div class="panel-heading" align="center">
            <div class="panel-title">{{ $delete_flag ? 'Borrar archivo' : 'Subir archivo' }}</div>
        </div>
        <div class="panel-body">
            <div class="mg20">
                <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                    <i class="fa fa-undo"></i>
                </a>
            </div>

            @include('app.session_flashed_messages', array('opt' => 1))

            @if($delete_flag==1)
                <form method="post" action="/delete/{{ $type }}" accept-charset="UTF-8" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-trash"></i></span>
                            <input type="text" class="form-control" name="file_name" placeholder="Nombre del archivo"
                                value="{{ old('file_name') }}">
                                {{--
                                @if($type=='project')
                                    <select required="required" class="form-control" name="phase_of_file">
                                        <option value="1">Documento de asignación</option>
                                        <option value="2">Cotización</option>
                                        <option value="3">Pedido de compra original</option>
                                        <option value="4">Pedido de compra firmado</option>
                                        <option value="5">Planilla de cantidades original</option>
                                        <option value="6">Planilla de cantidades firmada</option>
                                        <option value="7">Planilla económica original</option>
                                        <option value="8">Planilla económica firmada</option>
                                        <option value="9">Certificado de control de calidad</option>
                                    </select>
                                @endif
                                --}}
                        </div>
                    </div>
                    <div class="form-group" align="center">
                        <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> Borrar archivo</button>
                    </div>
                </form>
            @else
                <form method="post" action="/files/{{ $type }}/{{ $id }}" accept-charset="UTF-8"
                      enctype="multipart/form-data" id="myForm">

                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-cloud-upload"></i></span>
                            <input type="file" class="form-control" name="file" id="fileupload" value="{{ old('file') }}">

                            @if($type=='assignment'||$type=='site'||$type=='order')
                                <div class="input-group" style="width: 100%">
                                    <label for="name_of_file" class="input-group-addon" style="width: 26%;text-align: left">
                                        Tipo de documento
                                    </label>

                                    <select required="required" class="form-control" name="name_of_file" id="name_of_file">
                                        <option value="" hidden>Seleccione el tipo de archivo</option>

                                        @if($type=='assignment'||$type=='site')
                                            {!! $options !!}
                                        @else
                                            <option value="{{ 'org' }}">Orden original</option>
                                            <option value="{{ 'sgn' }}">Orden firmada</option>
                                        @endif
                                            {{--
                                                <option value="qcc">Certificado de control de calidad</option>
                                                <option value="sch">Cronograma</option>
                                                <option value="qty_org">Planilla de cantidades original</option>
                                                <option value="qty_sgn">Planilla de cantidades firmada</option>
                                                <option value="cst_org">Planilla económica original</option>
                                                <option value="cst_sgn">Planilla económica firmada</option>
                                            --}}
                                            {{--
                                                @if($user->area=='Gerencia General'||$user->area=='Gerencia Administrativa'
                                                    ||$user->priv_level==4)
                                                    <option value="wty">Poliza de garantia</option>
                                                @endif
                                            --}}
                                    </select>
                                </div>
                                {{--
                            @elseif($type=='site')
                                <select required="required" class="form-control" name="name_of_file" id="name_of_file">
                                    <option value="" hidden>Seleccione el tipo de archivo</option>
                                    {!! $options !!}
                                            {{--
                                            <option value="qcc">Certificado de control de calidad</option>
                                            <option value="asig' }}">Documento de asignación</option>
                                            <option value="ctz">Cotización</option>
                                            <option value="sch">Cronograma</option>
                                            <option value="qty_org">Planilla de cantidades original</option>
                                            <option value="qty_sgn">Planilla de cantidades firmada</option>
                                            <option value="cst_org">Planilla económica original</option>
                                            <option value="cst_sgn">Planilla económica firmada</option>
                                </select>
                                --}}

                                <input type="hidden" name="description" id="description" value="">
                                <input type="text" class="form-control" name="other_description" id="other_description"
                                       value="{{ old('other_description') }}" placeholder="Título o descripción" disabled="disabled">
                            @else
                                <input type="text" class="form-control" name="description" value="{{ old('description') }}"
                                       placeholder="Título o descripción">
                            @endif

                            {{--
                            @if($type=='activity'||$type=='vehicle_img'||$type=='device_img'||$type=='operator'||
                                $type=='driver'||$type=='vehicle_file'||$type=='device_file'||$type=='event'||
                                $type=='contract'||$type=='guarantee'||$type=='oc_certification_backup'||
                                $type=='project'||$type=='warehouse_img'||$type=='warehouse_file'||
                                $type=='material_img'||$type=='wh_entry_receipt'||$type=='wh_entry_img'||
                                $type=='wh_outlet_receipt'||$type=='wh_outlet_img'||$type=='dead_interval'||
                                $type=='corp_line'||$type=='line_assignation'||$type=='vhc_gas_inspection'||
                                $type=='vhc_failure_report'||$type=='dvc_failure_report')
                                <input type="text" class="form-control" name="description" value="{{ old('description') }}"
                                       placeholder="Título o descripción">
                            @else
                                <input type="hidden" name="description" id="description" value="">
                            @endif
                            --}}

                            @if($type=='vhc_gas_inspection'||$type=='replace')
                                <div class="input-group" id="exp_date_container" style="width: 100%;">
                                <span class="input-group-addon" style="text-align: right">
                                    <label for="exp_date" style="font-weight: normal; margin-bottom: 0">
                                        Expira en:
                                    </label>

                                    <input type="date" name="exp_date" id="exp_date" step="1" min="{{ date('Y-m-d') }}"
                                        value="{{ old('exp_date') }}">
                                </span>
                                </div>
                            @endif
                        </div>

                        @if($type=='vehicle_img'||$type=='device_img'||$type=='material_img')
                            <div class="checkbox" style="padding-left: 40px;">
                                <label>
                                    <input type="checkbox" name="main_pic"
                                           value="1"> Marque la casilla si ésta es la foto principal
                                </label>
                            </div>
                        @endif

                        {{--
                        <div class="checkbox" style="padding-left: 40px;">
                            <label>
                                <input type="checkbox" name="exp" id="exp" value="1"> Este documento tiene fecha de expiración
                            </label>
                        </div>
                        --}}

                        {{--
                        Guarantee implemented as a separated module
                            <div class="input-group" style="width: 100%;" id="expiration_container">
                                <br>
                                <span class="input-group-addon">
                                    <label>Fecha de vencimiento: </label>
                                    <input type="date" name="expiration_date" id="expiration_date" step="1"
                                        min="2014-01-01" disabled="disabled">
                                </span>
                            </div>
                        --}}

                    </div>

                    <div id="wait" align="center" style="margin-top: 10px;margin-bottom: 10px">
                        {{--<img src="/imagenes/pre_loader.gif"/>--}}
                        <div id="progress" class="progress w3-light-grey w3-round" align="left">
                            <div class="bar w3-green w3-round w3-container w3-center" style="width: 0;">
                                <div class="percent w3-round">0%</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" align="center">
                        <button type="submit" class="btn btn-success" id="start"
                                onclick="this.disabled=true; $('#wait').show(); this.form.submit();">
                            <i class="fa fa-upload"></i> Subir archivo
                        </button>
                    </div>

                </form>
            @endif
        </div>
    </div>
</div>

@endsection

@section('footer')
@endsection

@section('javascript')
    <script>
        /* expiration date separated to another page
        var $name_of_file = $('#name_of_file'), $expiration_date = $('#expiration_date'),
                $expiration_container = $('#expiration_container');
        $expiration_container.hide();

        $name_of_file.change(function () {
            if ($name_of_file.val() == 'wty') {
                $expiration_date.removeAttr('disabled').show();
                $expiration_container.show();
            } else {
                $expiration_date.attr('disabled', 'disabled').val('').hide();
                $expiration_container.hide();
            }
        }).trigger('change');
        */

        //$("#wait").hide();

        var $name_of_file = $('#name_of_file'), other_description = $('#other_description');

        $name_of_file.change(function() {
            if($name_of_file.val()==='Otro'){
                other_description.removeAttr('disabled').show();
            }
            else{
                $('#description').val($(this).find("option:selected").text());
                other_description.attr('disabled', 'disabled').val('').hide();
            }
        }).trigger('change');

        /* For progress bar */
        $('#myForm').fileupload({
            dataType: 'json',
            replaceFileInput: false,

            add: function (e, data) {

                $('#start').click(function () {
                    data.submit();
                });

            },
            progress: function (e, data) {

                var progress = parseInt(data.loaded / data.total * 100, 10);
                $('.progress').css('display','block');
                $('#progress').find('.bar').css('width', progress + '%')
                        .find('.percent').html(progress + '%');

            }
        });

            /* Code for jquery form plugin
            var bar = $('#bar');
            var percent = $('#percent');
            var status = $('#status');

            $('#myForm').ajaxForm({
                beforeSend: function() {
                    status.empty();
                    var percentVal = '0%';
                    bar.width(percentVal);
                    percent.html(percentVal);
                },
                uploadProgress: function(event, position, total, percentComplete) {
                    var percentVal = percentComplete + '%';
                    bar.width(percentVal);
                    percent.html(percentVal);
                },
                success: function(data, statusText, xhr) {
                    var percentVal = '100%';
                    bar.width(percentVal);
                    percent.html(percentVal);
                    status.html(xhr.responseText);
                },
                error: function(xhr, statusText, err) {
                    status.html(err || statusText);
                },
                complete: function(xhr) {
                    bar.width("100%");
                    percent.html("100%");
                    status.html(xhr.responseText);
                }
            });
            */

        /*
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                async: false
            }
        });
        */

        /*
        $(document).ready(function(){
            $('#exp').prop('checked', false);
            $("#exp_date_container").hide();
            $("#exp_date").hide();
        });

        var exp = $('#exp'), exp_date = $('#exp_date'), exp_date_container = $('#exp_date_container');
        exp.click(function () {
            if (exp.prop('checked')) {
                exp_date_container.show();
                exp_date.removeAttr('disabled').show();
            } else {
                exp_date_container.hide();
                exp_date.attr('disabled', 'disabled').hide();
            }
        }).trigger('click');
        */
    </script>
@endsection
