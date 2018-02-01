<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 30/01/2017
 * Time: 04:39 PM
 */
?>

@extends('layouts.master')

@section('header')
    @parent
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
        <div class="panel panel-10gray" >
            <div class="panel-heading" align="center">
                <div class="panel-title">{{ $license ? 'Actualizar datos de licencia' : 'Agregar licencia de conducir' }}</div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-undo"></i>
                    </a>
                    <a href="{{ '/driver' }}" class="btn btn-warning" title="Volver a resumen de asignaciones">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                @if($license)
                    <form id="delete" action="/license/{{ $license->id }}" method="post">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                    <form novalidate="novalidate" action="{{ '/license/'.$license->id }}" method="post">
                        <input type="hidden" name="_method" value="put">
                        @else
                            <form novalidate="novalidate" action="{{ '/license' }}" method="post">
                                @endif
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                            <div class="input-group" style="width: 100%">
                                                <label for="user_id" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Usuario: <span class="pull-right">*</span>
                                                </label>

                                                <select required="required" class="form-control" name="user_id" id="user_id">
                                                    <option value="" hidden>Seleccione un usuario</option>
                                                    @foreach($potential_drivers as $potential_driver)
                                                        <option value="{{ $potential_driver->id }}"
                                                            {{ ($license&&$license->user_id==$potential_driver->id)||
                                                                old('user_id')==$potential_driver->id ? 'selected="selected"' :
                                                                '' }}>{{ $potential_driver->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="input-group" style="width: 75%">
                                                <label for="number" class="input-group-addon" style="width: 31%;text-align: left">
                                                    # Licencia: <span class="pull-right">*</span>
                                                </label>

                                                <input required="required" type="number" class="form-control" name="number"
                                                       id="number" step="1" min="1" value="{{ $license&&$license->number!=0 ?
                                                        $license->number : old('number') }}"
                                                       placeholder="Número de licencia">

                                                <label for="category"></label>

                                                <div class="input-group-btn" style="width:80px;">
                                                    <select required="required" class="form-control" name="category" id="category">
                                                    <!-- A B C P=Particular M=Motocicleta -->
                                                        <option value="" hidden="hidden">Cat.</option>
                                                        <option value="A" {{ ($license&&$license->category=='A')||
                                                            old('category')=='A' ? 'selected="selected"' : '' }}>A</option>
                                                        <option value="B" {{ ($license&&$license->category=='B')||
                                                            old('category')=='B' ? 'selected="selected"' : '' }}>B</option>
                                                        <option value="C" {{ ($license&&$license->category=='C')||
                                                            old('category')=='C' ? 'selected="selected"' : '' }}>C</option>
                                                        <option value="M" {{ ($license&&$license->category=='M')||
                                                            old('category')=='M' ? 'selected="selected"' : '' }}>M</option>
                                                        <option value="P" {{ ($license&&$license->category=='P')||
                                                            old('category')=='P' ? 'selected="selected"' : '' }}>P</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="input-group" style="width: 75%;text-align: center">
                                                <label for="exp_date" class="input-group-addon" style="width: 31%; text-align: left">
                                                    Fecha de vencimiento:
                                                </label>

                                                <span class="input-group-addon">

                                                    <input type="date" name="exp_date" id="exp_date" step="1" min="2014-01-01"
                                                       value="{{ $license ? $license->exp_date : old('exp_date') }}">
                                                </span>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                @include('app.loader_gif')

                                <div class="form-group" align="center">
                                    <button type="submit" class="btn btn-success"
                                            onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                                        <i class="fa fa-floppy-o"></i> Guardar
                                    </button>

                                    @if($license)
                                        @if($user->priv_level==4)
                                            <button type="submit" form="delete" class="btn btn-danger">
                                                <i class="fa fa-trash-o"></i> Quitar
                                            </button>
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
    <script src="{{ asset('app/js/prevent_enter_form_submit.js') }}"></script> {{-- Avoid submitting form on enter press --}}
    <script>
        $(document).ready(function(){
            $("#wait").hide();
        });
    </script>
@endsection
