<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 13/12/2017
 * Time: 05:33 PM
 */
?>

@extends('layouts.master')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/custom_autocomplete.css") }}">
    <script src="{{ asset('https://cdnjs.cloudflare.com/ajax/libs/jquery.devbridge-autocomplete/1.2.27/jquery.autocomplete.js') }}">
    </script>

    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 mg-btm-px-40 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
        <div class="panel panel-10gray">
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    {{ $branch ? 'Actualizar información de sucursal' : 'Agregar una nueva sucursal' }}
                </div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                    <a href="{{ '/branch' }}" class="btn btn-warning" title="Volver a la tabla de sucursales">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                @if($branch)
                    <form id="delete" action="/branch/{{ $branch->id }}" method="post">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                    <form novalidate="novalidate" action="{{ '/branch/'.$branch->id }}" method="post">
                        <input type="hidden" name="_method" value="put">
                        @else
                            <form novalidate="novalidate" action="{{ '/branch' }}" method="post">
                                @endif
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                            <div class="input-group" style="width: 100%">
                                                <label for="name" class="input-group-addon" style="width: 23%;text-align: left"
                                                       title="Nombre de la sucursal">
                                                    Nombre: <span class="pull-right">*</span>
                                                </label>

                                                <input required="required" type="text" class="form-control" name="name"
                                                       id="name"
                                                       value="{{ $branch ? $branch->name : old('name') }}"
                                                       placeholder="Nombre de la sucursal">
                                            </div>

                                            <div class="input-group" style="width: 100%">
                                                <label for="city" class="input-group-addon" style="width: 23%;text-align: left"
                                                       title="Ciudad">
                                                    Ciudad: <span class="pull-right">*</span>
                                                </label>

                                                <input required="required" type="text" class="form-control" name="city"
                                                       id="city"
                                                       value="{{ $branch ? $branch->name : old('city') }}"
                                                       placeholder="Ciudad">
                                            </div>

                                            <div class="input-group" style="width: 100%">
                                                <label for="address" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Dirección:
                                                </label>

                                                <textarea rows="3" required="required" class="form-control" name="address"
                                                          placeholder="Dirección de la sucursal">{{ $branch ?
                                                     $branch->address : old('branch') }}</textarea>
                                            </div>

                                            <div class="input-group" style="width: 100%">
                                                <label for="phone" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Teléfono:
                                                </label>

                                                <input required="required" type="number" class="form-control" name="phone"
                                                       id="phone" step="1" min="1"
                                                       value="{{ $branch&&$branch->phone!=0 ? $branch->phone : old('phone') }}"
                                                       placeholder="Número de teléfono fijo o celular">
                                            </div>

                                            <div class="input-group" style="width: 100%">
                                                <label for="alt_phone" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Telf. alternativo:
                                                </label>

                                                <input required="required" type="number" class="form-control" name="alt_phone"
                                                       id="alt_phone" step="1" min="1"
                                                       value="{{ $branch&&$branch->alt_phone!=0 ? $branch->alt_phone :
                                                            old('alt_phone') }}"
                                                       placeholder="Número de teléfono fijo o celular">
                                            </div>

                                            <div class="input-group" style="width: 100%">
                                                <label for="head_name" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Encargado:
                                                </label>

                                                <input required="required" type="text" class="form-control" name="head_name"
                                                       id="head_name" value="{{ $branch&&$branch->head_person ?
                                                            $branch->head_person->first_name.' '.$branch->head_person->last_name :
                                                             old('head_name') }}"
                                                       placeholder="Responsable a cargo de la oficina">
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

                                    @if($branch&&$user->priv_level==4)
                                        <button type="submit" form="delete" class="btn btn-danger"
                                                title="Se inhabilitará el registro de ésta oficina">
                                            <i class="fa fa-user-times"></i> Deshabilitar
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

        $(document).ready(function(){
            $("#wait").hide();
        });

        $('#head_name').autocomplete({
            type: 'post',
            serviceUrl:'/autocomplete/employees',
            dataType: 'JSON'
        });
    </script>
@endsection
