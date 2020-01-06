@extends('layouts.master')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
        <div class="panel panel-info" >
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    @if($model=='order-to-site') {{ 'Asociar orden a un sitio o proyecto' }}
                    @elseif($model=='order-to-bill') {{ 'Asociar orden a una factura' }}
                    @elseif($model=='site-to-order') {{ 'Asociar sitio a una orden' }}
                    @elseif($model=='bill-to-order') {{ 'Asociar factura a una orden' }}
                    @endif
                </div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-undo"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <div class="col-sm-6">
                    <table class="table table-striped table-hover table-bordered">
                        <tbody>
                            <tr>
                                <th>
                                    @if($model=='order-to-site'||$model=='order-to-bill'){{ 'Código de orden:' }}
                                    @elseif($model=='bill-to-order'){{ 'Número de factura:' }}
                                    @elseif($model=='site-to-order'){{ 'Sitio:' }}
                                    @endif
                                </th>
                            </tr>
                            <tr>
                                <td align="center">
                                    @if($model=='order-to-site'||$model=='order-to-bill'||$model=='bill-to-order')
                                        {{ $to_join->code }}
                                    @elseif($model=='site-to-order'){{ $to_join->name }}
                                    @endif
                                </td>
                            </tr>
                            <tr><td></td></tr>
                            <tr>
                                <th>
                                    @if($model=='order-to-site'||$model=='order-to-bill'||$model=='bill-to-order')
                                        {{ 'Fecha de emisión:' }}
                                    @elseif($model=='site-to-order'){{ 'Proyecto:' }}
                                    @endif
                                </th>
                            </tr>
                            <tr>
                                <td align="center">
                                    @if($model=='order-to-site'||$model=='order-to-bill'||$model=='bill-to-order')
                                        {{ date_format(new \DateTime($to_join->date_issued), 'd-m-Y') }}
                                    @elseif($model=='site-to-order')
                                        {{ $to_join->assignment->name.' - '. $to_join->assignment->client }}
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-sm-6" align="right">
                    <form method="post" action="/join/{{ $model }}/{{ $id }}" accept-charset="UTF-8" enctype="multipart/form-data">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-link"></i></span>
                                @if($model=='order-to-site')

                                    <div>
                                        <div class="site_changer">
                                            <select required="required" class="form-control dynamic" name="site[0][assignment_id]"
                                                    id="0" onchange="changer(this)">
                                                <option value="" hidden>Seleccione un proyecto</option>
                                                @foreach($options as $option)
                                                    <option value="{{ $option->id }}">{{ $option->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="replaceable">
                                            <select required="required" class="form-control dynamic" name="site[0][site_id]" id="site0">
                                                <option value="" hidden>Seleccione un sitio</option>
                                            </select>
                                        </div>
                                        <input required="required" type="number" class="form-control dynamic" name="site[0][amount]"
                                               id="amount0" step="any" min="0" max="{{ $total_available }}"
                                               placeholder="{{ 'Asignar Monto ('.$total_available.' Bs disp.)' }}">
                                    </div>
                                    {{--<!--
                                        <select required="required" class="form-control" name="assignment_id1" id="assignment_id1">
                                            <option value="" hidden>Seleccione un proyecto</option>
                                            @foreach($options as $option)
                                                <option value="{{ $option->id }}">{{ $option->name }}</option>
                                            @endforeach
                                        </select>
                                        <select required="required" class="form-control" name="site_id1" id="site_id1">
                                            <option value="" hidden>Seleccione un sitio</option>
                                        </select>
                                    <div id="input1" style="margin-bottom:4px;" class="clonedInput">
                                        <input required="required" type="number" class="form-control" name="amount1" id="amount1"
                                                step="any" min="0" max="{{ $total_available }}"
                                                placeholder="{{ 'Asignar Monto ('.$total_available.' Bs disp.)' }}">

                                        Name: <input type="text" name="name1" id="name1">
                                    </div>
                                    <fieldset>
                                        <label>Need more fields?</label>
                                        <input type="button" id="btnAdd" value="+" >
                                        <input type="button" id="btnDel" value="-" >
                                    </fieldset>
                                    -->--}}

                                    <div id="questions">
                                        <!--This will hold the dynamic fields -->
                                    </div>

                                    <p>
                                        <a href="#" id="addFields" class="form-control"><i class="fa fa-plus"></i> Agregar sitio</a>
                                    </p>

                                @elseif($model=='order-to-bill')

                                    <select required="required" class="form-control" name="id">
                                        <option value="" hidden>Seleccione una factura</option>
                                        @foreach($options as $option)
                                            <option value="{{ $option->id }}">
                                                {{ $option->code.' - '.date_format(new \DateTime($option->date_issued), 'd-m-Y') }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input required="required" type="number" class="form-control" name="amount" step="any" min="0"
                                           placeholder="{{ 'Asignar monto en Bs.' }}">

                                @elseif($model=='site-to-order')

                                    <select required="required" class="form-control" name="id">
                                        <option value="" hidden>Seleccione una orden</option>
                                        @foreach($options as $option)
                                            <option value="{{ $option->id }}">
                                                {{ $option->type.' '.$option->code.' --> '.
                                                    date_format(new \DateTime($option->date_issued), 'd-m-Y') }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input required="required" type="number" class="form-control" name="amount" step="any" min="0"
                                           placeholder="{{ 'Asignar monto en Bs.' }}">

                                @elseif($model=='bill-to-order')

                                    <div>
                                        <div class="order_changer">
                                            <select required="required" class="form-control dynamic" name="order[0][order_id]" id="0">
                                                <option value="" hidden>Seleccione una orden</option>
                                                @foreach($options as $option)
                                                    <option value="{{ $option->id }}">
                                                        {{ $option->type.' '.$option->code.' -> '.
                                                            date_format(new \DateTime($option->date_issued), 'd-m-Y') }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <input required="required" type="number" class="form-control dynamic" name="order[0][amount]"
                                               id="amount0" step="any" min="0" placeholder="{{ 'Asignar monto en Bs.' }}">
                                    </div>

                                    <div id="orders">
                                        <!--This will hold the dynamic fields -->
                                    </div>

                                    <p>
                                        <a href="#" id="addOrderFields" class="form-control">
                                            <i class="fa fa-plus"></i> Agregar orden
                                        </a>
                                    </p>

                                @endif
                            </div>
                        </div>

                        @include('app.loader_gif')

                        <div class="form-group" align="center">
                            <button type="submit" class="btn btn-success"
                                    onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                                <i class="fa fa-paperclip"></i> Asociar
                            </button>
                        </div>
                    </form>

                    {{--<!--
                    <div id="masterQuestion" style="display: none">
                        This is the hidden master content used for cloning
                        <div class="questionChanger">
                            <select name="question[*][type]" class="dynamic">
                                <option value="type1">Type 1</option>
                                <option value="type2">Type 2</option>
                                <option value="type3">Type 3</option>
                            </select>
                        </div>

                        <div id="type1" class="questionSet">
                            <h3>Type 1</h3>
                            <p><label>Question: </label><input type="text" class="dynamic" name="question[*][question]" /></p>
                            <p><label>Answer: </label><input type="text" class="dynamic" name="question[*][answer]" /></p>
                        </div>

                        <div id="type2" class="questionSet">
                            <h3>Type 2</h3>
                            <p><label>Question</label><input type="text" class="dynamic" name="question[*][question]" /></p>
                            <p><label>Answer</label><input type="text" class="dynamic" name="question[*][answer]" /></p>
                        </div>
                    </div>
                    -->--}}

                    <div id="clonable_fields" style="display: none">
                        <div class="site_changer">
                            <select required="required" class="form-control dynamic" name="site[*][assignment_id]" id="*"
                                    onchange="changer(this)">
                                <option value="" hidden>Seleccione un proyecto</option>
                                @foreach($options as $option)
                                    <option value="{{ $option->id }}">{{ $option->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="replaceable">
                            <select required="required" class="form-control dynamic" name="site[*][site_id]" id="site*">
                                <option value="" hidden>Seleccione un sitio</option>
                            </select>
                        </div>
                        <input required="required" type="number" class="form-control dynamic" name="site[*][amount]"
                               id="amount*" step="any" min="0" max="{{ $total_available }}"
                               placeholder="{{ 'Asignar Monto ('.$total_available.' Bs disp.)' }}">
                    </div>

                    <div id="clonable_order_fields" style="display: none">
                        <div class="order_changer">
                            <select required="required" class="form-control dynamic" name="order[*][order_id]" id="*">
                                <option value="" hidden>Seleccione una orden</option>
                                @foreach($options as $option)
                                    <option value="{{ $option->id }}">
                                        {{ $option->type.' '.$option->code.' -> '.
                                            date_format(new \DateTime($option->date_issued), 'd-m-Y') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <input required="required" type="number" class="form-control dynamic" name="order[*][amount]"
                               id="amount*" step="any" min="0" placeholder="{{ 'Asignar monto en Bs.' }}">
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection

@section('footer')
@endsection

@section('javascript')
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("#wait").hide();
        /*
        //Cargar sitios mediante select dependiente, no funciona con campos dinámicos
        $(document).ready(function(){
            $("#assignment_id").change(function () {
                $("#assignment_id option:selected").each(function () {
                    $.post('/dynamic_sites', { assignment_id: $(this).val() }, function(data){
                        $("#site_id").html(data);
                    });
                });
            });
        });

        //Test 1 de agregado de campos dinamicamente
        $('#btnAdd').click(function() {
            var num     = $('.clonedInput').length;
            var newNum  = new Number(num + 1);

            var newElem = $('#input' + num).clone().attr('id', 'input' + newNum);

            var c = newElem.children[1];
            alert(c);

            //i[0].attr('id', 'assignment_id' + newNum).attr('name', 'assignment_id' + newNum);
            //i[1].attr('id', 'site_id' + newNum).attr('name', 'site_id' + newNum);
            newElem.children[0].attr('id', 'amount' + newNum).attr('name', 'amount' + newNum);
            newElem.children[1].attr('id', 'name' + newNum).attr('name', 'name' + newNum);

            $('#input' + num).after(newElem);
        });

        $('#btnDel').click(function() {
            var num = $('.clonedInput').length;
            $('#input' + num).remove();
        });
        */

        //Agregar campos de sitio dinámicamente
        $('#addFields').click(function(e){
            //prevent the button from submitting the form
            e.preventDefault();

            //get the new question number
            var questionNumber = $('.question').length + 1;

            //clone the master questionChanger and 'type1' question (remove the ID from the questionType - we don't need it)
            var clonable_fields = $('#clonable_fields').clone(true).removeAttr('id').removeAttr('style');
            //var questionChanger = $('#masterQuestion .questionChanger').clone(true);
            //var questionType = $('#type3').clone().removeAttr('id');

            //create a new wrapper for the new question, set the question number, add a class, and add the new content to it
            var newfields = $('<div>').data('qNum', questionNumber).addClass('question').append(clonable_fields);

            //loop through the '.dynamic' elements so we can change the name
            $('.dynamic', newfields).each(function() {
                //get the old dummy name
                var oldName = $(this).attr('name');
                var oldId = $(this).attr('id');

                //replace the dummy text (*) with the question number
                $(this).attr('name', oldName.replace('*', questionNumber));
                $(this).attr('id', oldId.replace('*', questionNumber));
            })

            //add the new question to the #questions DIV
            newfields.appendTo('#questions');
        });

        function changer(c){
            var site_field = $(c).attr('id');
            $.post('/dynamic_sites', { assignment_id: $(c).val() }, function(data){
                $('#site'+site_field).html(data);
            });
        }

        //Agregar campos de orden dinámicamente
        $('#addOrderFields').click(function(e){

            e.preventDefault();

            //get the new order number
            var orderNumber = $('.order').length + 1;

            var clonable_order_fields = $('#clonable_order_fields').clone(true).removeAttr('id').removeAttr('style');

            //create a new wrapper for the new order, set the order number, add a class, and add the new content to it
            var newOrderfields = $('<div>').data('orderNum', orderNumber).addClass('order').append(clonable_order_fields);

            //loop through the '.dynamic' elements so we can change the name
            $('.dynamic', newOrderfields).each(function() {
                //get the old dummy name
                var oldOrderName = $(this).attr('name');
                var oldOrderId = $(this).attr('id');

                //replace the dummy text (*) with the order number
                $(this).attr('name', oldOrderName.replace('*', orderNumber));
                $(this).attr('id', oldOrderId.replace('*', orderNumber));
            });

            //add the new order to the #orders DIV
            newOrderfields.appendTo('#orders');
        });
    </script>
@endsection
