@extends('layouts.master')

@section('header')
    @parent
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">

        <div class="panel panel-aqua">
            <div class="panel-heading" align="center">
                <div class="panel-title">{{ $cite ? 'Editar CITE' : 'Crear CITE' }}</div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-undo"></i>
                    </a>
                    <a href="{{ '/cite' }}" class="btn btn-warning" title="Volver a la tabla de CITEs">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                @if($cite)
                    <form id="delete" action="/cite/{{ $cite->id }}" method="post">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                    <form novalidate="novalidate" action="{{ '/cite/'.$cite->id }}" method="post" id="form">
                        <input type="hidden" name="_method" value="put">
                @else
                    <form novalidate="novalidate" action="{{ '/cite' }}" method="post" id="form">
                @endif
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                    @if($cite)
                                        <label>{{ $cite->code }}</label>
                                    @else
                                        <label>
                                            {{ $cod_cite->title.'-'.str_pad($cod_cite->num_cite, 3, "0", STR_PAD_LEFT).
                                                date_format($cod_cite->created_at,'-Y') }}
                                        </label>
                                    @endif
                                    {{--
                                    <select required="required" class="form-control" name="title"
                                        value="{{ $cite ? $cite->title : '' }}">
                                        <option value="">Tipo</option>
                                        <option value="GTEC">GTEC</option>
                                        <option value="GC">GC</option>
                                    </select>
                                    <!--Convertido en lista desplegable como tipo de CITE
                                    <input required="required" type="text" class="form-control" name="title"
                                        value="{{ $cite ? $cite->title : '' }}" placeholder="Título">

                                    <select required="required" class="form-control" name="area"
                                        value="{{ $cite ? $cite->area : ''}}">
                                        <option value="">Area</option>
                                        <option value="Gerencia Tecnica">Gerencia Tecnica</option>
                                        <option value="Gerencia Comercial">Gerencia Comercial</option>
                                    </select>
                                    --}}
                                    <p>

                                    @if($user->priv_level==4)
                                        <div class="input-group" style="width: 100%">
                                            <label for="cite_prefix" class="input-group-addon" style="width: 23%;text-align: left">
                                                Prefijo: <span class="pull-right">*</span>
                                            </label>

                                            <select required="required" class="form-control" name="cite_prefix" id="cite_prefix">
                                                <option value="" hidden>Seleccione un prefijo</option>
                                                @foreach($prefixes as $prefix)
                                                    <option value="{{  $prefix->title }}"
                                                            {{ $cite&&$cite->title==$prefix->title ? 'selected="selected"' : '' }}
                                                    >{{$prefix->title}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    <div class="input-group" style="width:100%">
                                        <label for="responsable" class="input-group-addon" style="width: 23%;text-align: left">
                                            Responsable: <span class="pull-right">*</span>
                                        </label>

                                        <input required="required" type="text" class="form-control" name="responsable"
                                               id="responsable" value="{{ $cite ? $cite->responsable : $user->name }}"
                                               placeholder="Responsable"
                                               {{ $user->priv_level==4 ? '' : 'readonly="readonly"' }}>
                                    </div>
                                    <br>

                                    <div class="input-group" style="width:100%">
                                        <label for="para_empresa" class="input-group-addon" style="width: 23%;text-align: left">
                                            Empresa: <span class="pull-right">*</span>
                                        </label>

                                        <input required="required" type="text" class="form-control" name="para_empresa"
                                               id="para_empresa" value="{{ $cite ? $cite->para_empresa : old('para_empresa') }}"
                                               placeholder="Empresa a la que va dirigida la carta">
                                    </div>

                                    <div class="input-group" style="width:100%">
                                        <label for="destino" class="input-group-addon" style="width: 23%;text-align: left">
                                            Destinatario: <span class="pull-right">*</span>
                                        </label>

                                        <input required="required" type="text" class="form-control" name="destino" id="destino"
                                               value="{{ $cite ? $cite->destino : old('destino') }}"
                                               placeholder="Persona a la que va dirigida la carta">
                                    </div>

                                    <div class="input-group" style="width:100%">
                                        <label for="asunto" class="input-group-addon" style="width: 23%;text-align: left">
                                            Asunto: <span class="pull-right">*</span>
                                        </label>

                                        <input required="required" type="text" class="form-control" name="asunto" id="asunto"
                                               value="{{ $cite ? $cite->asunto : old('asunto') }}"
                                               placeholder="Motivo por el que se envía la carta">
                                    </div>

                                </span>
                            </div>
                        </div>

                        @include('app.loader_gif')

                        <div class="form-group" align="center">
                            <button type="submit" class="btn btn-success"
                                    onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                                <i class="fa fa-floppy-o"></i> Guardar
                            </button>

                            @if($cite&&$user->priv_level==4)
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
