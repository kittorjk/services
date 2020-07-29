<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 01/09/2017
 * Time: 06:12 PM
 */
?>

@extends('layouts.master')

@section('header')
    @parent
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
        <div class="panel panel-10gray">
            <div class="panel-heading" align="center">
                <div class="panel-title">{{ $role ? 'Modificar contenido de cargo' : 'Agregar un nuevo cargo' }}</div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                    @if($user->priv_level==4)
                        <a href="{{ '/staff_role' }}" class="btn btn-warning" title="Volver a lista de cargos">
                            <i class="fa fa-arrow-up"></i>
                        </a>
                    @endif
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                @if($role)
                    <form id="delete" action="/staff_role/{{ $role->id }}" method="post">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                    <form novalidate="novalidate" action="{{ '/staff_role/'.$role->id }}" method="post">
                        <input type="hidden" name="_method" value="put">
                        @else
                            <form novalidate="novalidate" action="{{ '/staff_role' }}" method="post">
                                @endif
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                            <input required="required" type="text" class="form-control" name="name"
                                                   value="{{ $role ? $role->name : '' }}" placeholder="Cargo o posición">

                                            <div class="input-group" style="width: 100%">
                                                <label for="area" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Área:
                                                </label>

                                                <select required="required" class="form-control" name="area" id="area">
                                                    <option value="" hidden>Seleccione un área</option>
                                                    <option value="AD"
                                                            {{ $role&&strpos($role->code, '0AD0')!==false ?
                                                             'selected="selected"' : '' }}>Gerencia Administrativa</option>
                                                    <option value="GG"
                                                            {{ $role&&strpos($role->code, '0GG0')!==false ?
                                                             'selected="selected"' : '' }}>Gerencia General</option>
                                                    <option value="TC"
                                                            {{ $role&&strpos($role->code, '0TC0')!==false ?
                                                             'selected="selected"' : '' }}>Gerencia Técnica</option>
                                                </select>
                                            </div>

                                            <textarea rows="3" required="required" class="form-control" name="description"
                                                      placeholder="Descripción de responsabilidades">{{ $role ?
                                                       $role->description : '' }}</textarea>

                                        </div>
                                    </div>
                                </div>

                                @include('app.loader_gif')

                                <div class="form-group" align="center">
                                    <button type="submit" class="btn btn-success"
                                            onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                                        <i class="fa fa-floppy-o"></i> Guardar
                                    </button>

                                    @if($role&&$user->priv_level>=1)
                                        <button type="submit" form="delete" class="btn btn-danger">
                                            <i class="fa fa-ban"></i> Deshabilitar
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
