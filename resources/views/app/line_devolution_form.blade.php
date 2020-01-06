<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 20/09/2017
 * Time: 06:22 PM
 */
?>

@extends('layouts.master')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">
        <div class="panel panel-brown">
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    {{ $assignation ? 'Modificar registro de devolución' : 'Registrar devolución de línea corporativa' }}
                </div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-undo"></i>
                    </a>
                    <a href="{{ '/corporate_line' }}" class="btn btn-warning" title="Volver a la lista de líneas corporativas">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                @if($assignation)
                    <form novalidate="novalidate" action="{{ '/line_assignation/devolution/'.$assignation->id }}" method="post">
                        <input type="hidden" name="_method" value="put">
                        @else
                            <form novalidate="novalidate" action="{{ '/line_assignation/devolution' }}" method="post">
                                @endif
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="form-group">
                                    <div class="input-group">

                                        <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                            <div class="input-group" style="width: 100%">
                                                <label for="corp_line_id" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Línea:
                                                </label>

                                                <select required="required" class="form-control" name="corp_line_id"
                                                        id="corp_line_id">
                                                    <option value="" hidden>Seleccione una línea corporativa</option>
                                                    <option value="{{ $line->id }}" {{ 'selected="selected"' }}
                                                        >{{ $line->number }}</option>
                                                </select>
                                            </div>

                                            <div class="input-group" style="width: 100%">
                                                <label for="observations" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Observaciones:
                                                </label>

                                                <textarea rows="3" required="required" class="form-control" name="observations"
                                                          id="observations"
                                                          placeholder="Observaciones acerca de la devolución">{{ $assignation ?
                                                     $assignation->observations : old('observations') }}</textarea>
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
