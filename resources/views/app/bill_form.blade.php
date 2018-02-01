@extends('layouts.master')

@section('header')
    @parent
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">

        <div class="panel panel-sky" >
            <div class="panel-heading" align="center">
                <div class="panel-title">{{ $bill ? 'Modificar datos de factura' : 'Agregar nueva factura' }}</div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="javascript:history.back()" {{-- onclick="history.back();" --}} class="btn btn-warning" title="Atrás">
                        <i class="fa fa-undo"></i>
                    </a>
                    <a href="{{ '/bill' }}" class="btn btn-warning" title="Volver a facturas">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                @if($bill)
                    <form id="delete" action="/bill/{{ $bill->id }}" method="post">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                    <form novalidate="novalidate" action="{{ '/bill/'.$bill->id }}" method="post">
                        <input type="hidden" name="_method" value="put">
                        @else
                            <form novalidate="novalidate" action="{{ '/bill' }}" method="post">
                                @endif
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="form-group">
                                    <div class="input-group">

                                        <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                            <div class="input-group" style="width: 100%">
                                                <label for="code" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Número: <span class="pull-right">*</span>
                                                </label>

                                                <input required="required" type="text" class="form-control" name="code" id="code"
                                                       value="{{ $bill ? $bill->code : '' }}" placeholder="Número de factura">
                                            </div>

                                            <div class="input-group">
                                                <label for="date_issued" class="input-group-addon">
                                                    Fecha de emisión: *
                                                </label>
                                                <span class="input-group-addon">
                                                    <input type="date" name="date_issued" id="date_issued" step="1" min="2014-01-01"
                                                           value="{{ $bill ? $bill->date_issued : date("Y-m-d") }}">
                                                </span>
                                            </div>

                                            <div class="input-group" style="width: 75%">
                                                <span class="input-group-addon" style="width:31%;text-align: left">
                                                    Monto: <span class="pull-right">*</span>
                                                </span>
                                                <input required="required" type="number" class="form-control" name="billed_price"
                                                       step="any" min="0" value="{{ $bill ? $bill->billed_price : '' }}"
                                                       placeholder="Monto facturado">
                                                <span class="input-group-addon">Bs.</span>
                                            </div>

                                            <textarea rows="3" required="required" class="form-control" name="detail"
                                                placeholder="Información adicional de la factura">{{ $bill ?
                                                 $bill->detail : '' }}</textarea>

                                        </div>

                                    </div>
                                </div>

                                @include('app.loader_gif')

                                <div class="form-group" align="center">
                                    <button type="submit" class="btn btn-success"
                                            onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                                        <i class="fa fa-floppy-o"></i> Guardar
                                    </button>

                                    @if($bill&&$user->priv_level==4)
                                        <button type="submit" form="delete" class="btn btn-danger">
                                            <i class="fa fa-trash-o"></i> Quitar
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
        $(document).ready(function(){
            $("#wait").hide();
        });
    </script>
@endsection
