@extends('layouts.master')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-8 col-md-offset-2">
        <div class="panel panel-info" >
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    {{ $invoices->count()==1 ? 'Aprobar pago a proveedor' : 'Aprobar pagos a proveedores' }}
                </div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-undo"></i>
                    </a>
                    <a href="{{ '/invoice' }}" class="btn btn-warning" title="Volver a lista de facturas">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <form method="post" action="{{ '/invoice/approve' }}" id="approve_invoice" accept-charset="UTF-8"
                      enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="col-sm-12">
                        <table class="table table-striped table-hover table-bordered">
                            <tbody>
                            <tr>
                                <th style="text-align: center">
                                    <input type="checkbox" name="select_all" id="select_all">
                                </th>
                                <th>
                                    <label for="select_all">Factura</label>
                                </th>
                                <th>Monto</th>
                                <th>Proveedor</th>
                                <th>OC</th>
                                <th>Concepto</th>
                            </tr>
                            <?php $i=0 ?>
                            @foreach($invoices as $invoice)
                                <tr>
                                    <td align="center">
                                        <input type="checkbox" name="{{ $i }}" id="{{ $i }}" value="{{ $invoice->id }}"
                                               class="checkbox">
                                    </td>
                                    <td>
                                        <label for="{{ $i }}" style="font-weight: normal">
                                            {{ $invoice->number }}
                                        </label>
                                    </td>
                                    <td align="right">{{ number_format($invoice->amount,2).' Bs' }}</td>
                                    <td width="30%">{{ $invoice->oc->provider  }}</td>
                                    <td>
                                        <a href="/oc/{{ $invoice->oc_id }}">{{ $invoice->oc->code }}</a>
                                    </td>
                                    <td>
                                        {{ substr($invoice->flags, -3)=='100' ? 'Adelanto' :
                                          (substr($invoice->flags, -3)=='010' ? 'Pago contra avance' :
                                          (substr($invoice->flags, -3)=='001' ? 'Pago contra entrega' : '' )) }}
                                    </td>
                                </tr>
                                <?php $i++ ?>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <input type="hidden" name="count" value="{{ $i }}">
                    <div class="col-sm-8 col-sm-offset-2" id="container" align="center">
                        <label>Introduzca su contraseña por favor</label>

                        <div class="input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-lock fa-fw"></i></span>
                            <input required="required" type="password" class="form-control" name="password" id="password"
                                   placeholder="Password" disabled="disabled">
                        </div>

                        <div class="checkbox col-sm-offset-1" align="left">
                            <label>
                                <input type="checkbox" name="add_comments" id="add_comments" value="1"
                                       checked=""> Agregar un comentario sobre la aprobación
                            </label>
                        </div>

                        <div class="input-group" id="comments_container">
                            <span class="input-group-addon">
                                <textarea rows="3" class="form-control" name="comments" id="comments"
                                          placeholder="Agregar comentarios" disabled="disabled"></textarea>
                            </span>
                        </div>
                        <br>

                        @include('app.loader_gif')

                        <div class="form-group" align="center">
                            <button type="submit" id="submit_button" class="btn btn-success"
                                    onclick="this.disabled=true; $('#wait').show(); this.form.submit()" disabled="disabled">
                                <i class="fa fa-check-circle"></i> Aprobar
                            </button>
                        </div>
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
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("#wait").hide();

        var $password = $('#password'), $submit_button = $('#submit_button'), $container = $('#container');
        $("#approve_invoice").change(function(){
            //var checked = $("#approve_invoice input:checked").length > 0;
            var checked = $('.checkbox:checked').size() > 0;
            if (checked){
                $container.show();
                $password.removeAttr('disabled').show();
                $submit_button.removeAttr('disabled').show();
            }
            else{
                $container.hide();
                $password.attr('disabled', 'disabled').hide();
                $submit_button.attr('disabled', 'disabled').hide();
            }
        }).trigger('change');

        $('#select_all').click(function() {
            var status = this.checked;

            $('.checkbox').prop('checked', status);
        });

        /*
        $("#select_all").change(function(){
            var status = this.checked;
            $('.checkbox').each(function(){
                this.checked = status;
            });
        });
        */

        $('.checkbox').change(function(){
            if(this.checked===false){
                $("#select_all")[0].checked = false;
            }

            if ($('.checkbox:checked').length===($('.checkbox').length - 1) ){
                $("#select_all")[0].checked = true;
            }
        });

        var $add_comments = $('#add_comments'), $comments = $('#comments'), $comments_container = $('#comments_container');
        $add_comments.click(function () {
            if ($add_comments.prop('checked')) {
                $comments_container.show();
                $comments.removeAttr('disabled').show();
            } else {
                $comments_container.hide();
                $comments.attr('disabled', 'disabled').hide();
            }
        }).trigger('click');
    </script>
@endsection
