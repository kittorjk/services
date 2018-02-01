@extends('layouts.master')

@section('header')
    @parent
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">

        <div class="panel panel-aqua" >
            <div class="panel-heading" align="center">
                <div class="panel-title">{{ $guarantee ? 'Actualizar información de poliza' : 'Agregar información de poliza' }}</div>
            </div>
            <div class="panel-body" >
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning"><i class="fa fa-arrow-circle-left"></i> Volver</a>
                </div>
                @if (Session::has('message'))
                    <div class="alert alert-danger" align="center">{{ Session::get('message') }}</div>
                @endif
                @if($guarantee)
                    <form id="delete" action="/guarantee/{{ $guarantee->id }}" method="post">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                    <form novalidate="novalidate" action="{{ '/guarantee/'.$guarantee->id }}" method="post" id="form">
                        <input type="hidden" name="_method" value="put">
                        @else
                            <form novalidate="novalidate" action="{{ '/guarantee' }}" method="post" id="form">
                                @endif
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="form-group">
                                    <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                    <div class="input-group" style="width: 100%;" id="expiration_container">
                                        <br>
                                        <label>Fecha de vencimiento: </label>
                                        <input type="date" name="expiration_date" id="expiration_date" step="1" min="2014-01-01" value="{{ $guarantee ? $guarantee->expiration_date : '' }}">
                                    </div>

                                </span>
                                    </div>
                                </div>

                                <div id="wait" align="center" style="margin-top: 10px;margin-bottom: 10px">
                                    <img src="/imagenes/loading.gif"/>
                                </div>

                                <div class="form-group" align="center">
                                    <button type="submit" class="btn btn-success" onclick="this.disabled=true; $('#wait').show(); this.form.submit()"><i class="fa fa-floppy-o"></i> Guardar </button>
                                    @if($guarantee)
                                        @if($user->priv_level == 4)
                                            <button type="submit" form="delete" class="btn btn-danger"><i class="fa fa-trash-o"></i> Quitar </button>
                                        @endif
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
    <script>
        $(document).ready(function(){
            $("input").not($(":button")).keypress(function (evt) {
                if (evt.keyCode == 13) {
                    itype = $(this).attr('type');
                    if (itype !== 'submit'){
                        var fields = $(this).parents('form:eq(0),body').find('button, input, textarea, select');
                        var index = fields.index(this);
                        if (index > -1 && (index + 1) < fields.length) {
                            fields.eq(index + 1).focus();
                        }
                        return false;
                    }
                }
            });

            $("#wait").hide();
        });
    </script>
@endsection
