@extends('layouts.ocs_structure')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    <li><a href="#">&ensp;<i class="fa fa-money"></i> PAGOS <span class="caret"></span>&ensp;</a>
        <ul class="sub-menu">
            <li><a href="{{ '/invoice' }}"><i class="fa fa-bars"></i> Ver todo </a></li>
            <li><a href="{{ '/invoice/create' }}"><i class="fa fa-plus"></i> Agregar factura </a></li>
        </ul>
    </li>
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

        <div class="panel panel-ground">
            <div class="panel-heading" align="center">
                <div class="panel-title">Información de factura</div>
            </div>
            <div class="panel-body">
                <div class="col-lg-5 mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning">
                        <i class="fa fa-arrow-circle-left"></i> Volver
                    </a>
                    <a href="{{ '/invoice' }}" class="btn btn-warning" title="Ir a la tabla de facturas de proveedor">
                        <i class="fa fa-arrow-circle-up"></i> Facturas
                    </a>
                </div>

                <div class="col-sm-12 mg10">
                    @include('app.session_flashed_messages', array('opt' => 0))
                </div>

                <div class="col-sm-12 mg10 mg-tp-px-10">
                    <table class="table table-striped table-hover table-bordered">
                        <tbody>
                        <tr>
                            <th width="50%">Nº OC:</th>
                            <td colspan="3">
                                <a href="/oc/{{ $invoice->oc_id }}">
                                    {{ 'OC-'.str_pad($invoice->oc_id, 5, "0", STR_PAD_LEFT) }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th width="50%">Número de factura:</th>
                            <td colspan="3">{{ $invoice->number }}</td>
                        </tr>
                        <tr>
                            <th>Fecha de emisión:</th>
                            <td colspan="3">{{ date_format(new \DateTime($invoice->date_issued), 'd-m-Y') }}</td>
                        </tr>
                        <tr>
                            <th>Monto facturado:</th>
                            <td colspan="3"><p id="important">{{ number_format($invoice->amount,2).' Bs' }}</p></td>
                        </tr>
                        <tr>
                            <th>Motivo de pago:</th>
                            <td colspan="3">
                                {{ $invoice->flags[5]==1 ? 'Pago por adelanto' : '' }}
                                {{ $invoice->flags[6]==1 ? 'Pago contra avance' : '' }}
                                {{ $invoice->flags[7]==1 ? 'Pago contra entrega' : '' }}
                            </td>
                        </tr>

                        @if($invoice->files->count()>0||$invoice->flags[0]==0)
                            <tr><td colspan="4"></td></tr>
                            <tr>
                                <th colspan="4">Archivo(s):</th>
                            </tr>
                        @endif
                        @foreach($invoice->files as $file)
                        <tr>
                            <td>{{ $file->description }}</td>
                            <td colspan="3">
                                @include('app.info_document_options', array('file'=>$file))
                                {{--
                                <a href="/download/{{ $file->id }}" style="text-decoration: none">
                                    @if($file->type=='pdf')
                                        <img src="{{ '/imagenes/pdf-icon.png' }}" alt="PDF" />
                                    @elseif($file->type=='jpeg'||$file->type=='jpg'||$file->type=='png')
                                        <img src="{{ '/imagenes/image-icon.png' }}" alt="IMAGE" />
                                    @endif
                                </a>
                                <a href="/file/{{ $file->id }}">Detalles</a>
                                @if($file->type=='pdf')
                                    {{ ' - ' }}
                                    <a href="/display_file/{{ $file->id }}">Ver</a>
                                @endif
                                --}}
                            </td>
                        </tr>
                        @endforeach
                        @if($invoice->flags[0]==0)
                            <tr>
                                <th colspan="4" style="text-align: center">
                                    <a href="/files/invoice/{{ $invoice->id }}">
                                        <i class="fa fa-upload"></i> Subir factura
                                    </a>
                                </th>
                            </tr>
                        @endif

                        @if($invoice->events->count()>0)
                            <tr>
                                <th>Eventos:</th>
                                <td colspan="3">
                                    <a href="/event/invoice/{{ $invoice->id }}">{{ 'Ver eventos' }}</a>
                                </td>
                            </tr>
                        @endif

                        @if($invoice->transaction_code)
                            <tr><td colspan="4"></td></tr>
                            <tr>
                                <th>Código de transacción</th>
                                <td colspan="3">{{ $invoice->transaction_code }}</td>
                            </tr>
                            <tr>
                                <th>Fecha de pago:</th>
                                <td colspan="3">{{ date_format(new \DateTime($invoice->transaction_date), 'd-m-Y') }}</td>
                            </tr>
                        @endif

                        @if($invoice->detail<>'')
                            <tr><td colspan="4"></td></tr>
                            <tr>
                                <th colspan="4">Información adicional:</th>
                            </tr>
                            <tr>
                                <td colspan="4">{{ $invoice->detail }}</td>
                            </tr>
                        @endif

                        <tr><td colspan="4"></td></tr>
                        <tr>
                            <th colspan="2">Registro creado por</th>
                            <td colspan="2">{{ $invoice->user ? $invoice->user->name : 'N/E' }}</td>
                        </tr>

                        {{--
                        @if((substr($invoice->flags,0,3)=='000'&&$user->area=='Gerencia Tecnica'&&$user->priv_level==3)||
                            (substr($invoice->flags,0,3)=='001'&&$user->area=='Gerencia General'&&$user->priv_level==3)||
                             $user->priv_level==4)
                            <tr><td colspan="4"></td></tr>
                            <tr>
                                <td colspan="4">
                                    <form method="post" action="{{ '/invoice/approve' }}" id="approve_invoice"
                                          accept-charset="UTF-8" enctype="multipart/form-data">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                                        <div class="checkbox" style="margin-left: 20px">
                                            <input type="checkbox" name="0" id="0" value="{{ $invoice->id }}"
                                                   class="checkbox">
                                            <label for="0">Aprobar factura</label>
                                        </div>

                                        <input type="hidden" name="count" value="1">
                                        <div class="col-sm-8 col-sm-offset-2" id="container" align="center">
                                            <label>Introduzca su contraseña por favor</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="glyphicon glyphicon-lock fa-fw"></i></span>
                                                <input required="required" type="password" class="form-control" name="password"
                                                       id="password" placeholder="Password" disabled="disabled">
                                            </div>
                                            <br>

                                            @include('app.loader_gif')

                                            <div class="form-group" align="center">
                                                <button type="submit" id="submit_button" class="btn btn-primary"
                                                    onclick="this.disabled=true; $('#wait').show(); this.form.submit()"
                                                    disabled="disabled">
                                                    <i class="fa fa-check-circle"></i> Aprobar
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @endif
                        --}}

                        </tbody>
                    </table>
                </div>

                @if((($user->id==$invoice->user_id||$user->action->oc_inv_edt)&&substr($invoice->flags,0,2)=='00')||
                    $user->priv_level==4)
                    <div class="col-sm-12 mg10" align="center">
                        <a href="/invoice/{{ $invoice->id }}/edit" class="btn btn-success">
                            <i class="fa fa-pencil-square-o"></i> Modificar
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection

@section('footer')
@endsection

@section('javascript')
    <script src="{{ asset('app/js/set_current_url.js') }}"></script> {{-- For recording current url --}}
    <script>
        $('#alert').delay(2000).fadeOut('slow');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("#wait").hide();

        var $password = $('#password'), $submit_button = $('#submit_button'), $container = $('#container');
        $("#approve_invoice").change(function(){
            var checked = $("#approve_invoice input:checked").length > 0;
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
    </script>
@endsection
