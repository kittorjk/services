<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 03/04/2017
 * Time: 10:58 AM
 */
?>

@extends('layouts.master')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">

        <div class="panel panel-sky" >
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    {{ ($dead_interval ? 'Modificar registro de' : 'Agregar un').' intervalo de tiempo muerto' }}
                </div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                    <a href="{{ '/dead_interval?'.($assignment ? 'assig_id='.$assignment->id : ($site ? 'st_id='.$site->id : '')) }}"
                       class="btn btn-warning" title="Volver a resumen de tiempos muertos">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <p><em>Nota.- Los campos con * son obligatorios</em></p>

                @if($dead_interval)
                    <form id="delete" action="/dead_interval/{{ $dead_interval->id }}" method="post">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                    <form novalidate="novalidate" action="{{ '/dead_interval/'.$dead_interval->id }}" method="post">
                        <input type="hidden" name="_method" value="put">
                        @else
                            <form novalidate="novalidate" action="{{ '/dead_interval' }}" method="post"
                                  enctype="multipart/form-data">
                                @endif
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="form-group">
                                    <div class="input-group">

                                        <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                            @if($assignment)
                                                <div class="input-group" style="width: 100%">
                                                    <label for="assig_name" class="input-group-addon"
                                                           style="width: 23%;text-align: left">
                                                        Asignación: <span class="pull-right">*</span>
                                                    </label>

                                                    <select required="required" class="form-control" name="assig_name"
                                                            id="assig_name">
                                                        <option value="{{ $assignment->name }}" selected="selected"
                                                            title="{{ $assignment->name }}">
                                                            {{ str_limit($assignment->name,200) }}
                                                        </option>
                                                    </select>
                                                </div>

                                                <input required="required" type="hidden" class="form-control" name="assig_id"
                                                       value="{{ $assignment->id }}">
                                            @elseif($site)
                                                <div class="input-group" style="width: 100%">
                                                    <label for="st_name" class="input-group-addon"
                                                           style="width: 23%;text-align: left">
                                                        Sitio: <span class="pull-right">*</span>
                                                    </label>

                                                    <select required="required" class="form-control" name="st_name" id="st_name">
                                                        <option value="{{ $site->name }}" selected="selected"
                                                                title="{{ $site->name }}">
                                                            {{ str_limit($site->name,200) }}
                                                        </option>
                                                    </select>
                                                </div>

                                                <input required="required" type="hidden" class="form-control" name="st_id"
                                                       value="{{ $site->id }}">
                                            @endif

                                            <div class="input-group" style="width: 100%;text-align: center">
                                                <span class="input-group-addon">
                                                    <label for="date_from" style="font-weight: normal; margin-bottom: 0;">
                                                        Desde:
                                                    </label>
                                                    <input type="date" name="date_from" id="date_from" step="1" min="2014-01-01"
                                                           value="{{ $dead_interval ? $dead_interval->date_from : $current_date }}">
                                                    &emsp;
                                                    <label for="date_to" style="font-weight: normal; margin-bottom: 0;">
                                                        Hasta:
                                                    </label>
                                                    <input type="date" name="date_to" id="date_to" step="1" min="2014-01-01"
                                                           value="{{ $dead_interval ? $dead_interval->date_to : old('date_to') }}">
                                                </span>
                                                <input required="required" type="number" class="form-control" name="total_days"
                                                       step="any" min="0" placeholder="Total días"
                                                        title="Indique la fecha de fin o la cantidad de días desde la fecha de inicio">
                                            </div>

                                            <textarea rows="4" required="required" class="form-control" name="reason"
                                                      id="reason" placeholder="Motivo *">{{ $dead_interval ?
                                                       $dead_interval->reason : old('reason') }}</textarea>

                                        </span>

                                    </div>
                                </div>

                                @include('app.loader_gif')

                                <div class="form-group" align="center">
                                    <button type="submit" class="btn btn-success"
                                            onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                                        <i class="fa fa-floppy-o"></i> Guardar
                                    </button>

                                    @if($dead_interval&&$user->priv_level==4)
                                        <button type="submit" form="delete" class="btn btn-danger">
                                            <i class="fa fa-trash-o"></i> Eliminar
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
    </script>
@endsection
