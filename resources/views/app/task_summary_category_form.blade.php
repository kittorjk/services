<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 25/09/2017
 * Time: 02:58 PM
 */
?>

@extends('layouts.master')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1">

        <div class="panel panel-sky">
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    {{ $category ? 'Cambiar de categoría este item' : 'Seleccionar una categoría para este item' }}
                </div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                    <a href="{{ '/task/'.$task->site_id }}" class="btn btn-warning"
                        title="{{ 'Volver a la lista de items del sitio '.($task->site ? $task->site->name : '') }}">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                @if($category)
                    <form id="delete" action="/task_category/{{ $category->id }}" method="post">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                    <form novalidate="novalidate" action="{{ '/task_category/'.$category->id }}" method="post" class="form-horizontal">
                        <input type="hidden" name="_method" value="put">
                        @else
                            <form novalidate="novalidate" action="{{ '/task_category' }}" method="post" class="form-horizontal">
                                @endif
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                                <div class="row">
                                    <div class="col-md-12 col-sm-12">

                                        <div class="form-group">
                                            <label for="task_id" class="col-md-4 control-label">Item:</label>

                                            <div class="col-md-6">
                                                <select required="required" class="form-control" name="task_id" id="task_id">
                                                    <option value="" hidden>Seleccione un item (*)</option>
                                                    <option value="{{ $task->id }}" selected="selected" title="{{ $task->name }}"
                                                        >{{ str_limit($task->name,50) }}</option>
                                                </select>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <fieldset>
                                    <legend class="col-md-10">Categorías de F.O.</legend>

                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">

                                            <div class="form-group">
                                                <label for="cat_options" class="col-md-4 control-label">Categoría</label>

                                                <div class="col-md-6" id="cat_options">
                                                    <input id="fo_cable" type="radio" name="cat_name" value="fo_cable"
                                                            {{ ($category&&$category->cat_name=='fo_cable')||
                                                                old('cat_name')=='fo_cable' ? 'checked="checked"' : '' }}>
                                                    <label for="fo_cable" class="control-label">Cable de F.O. tendido</label>
                                                    &emsp;
                                                    <input id="fo_splice" type="radio" name="cat_name" value="fo_splice"
                                                            {{ ($category&&$category->cat_name=='fo_splice')||
                                                                old('cat_name')=='fo_splice' ? 'checked="checked"' : '' }}>
                                                    <label for="fo_splice" class="control-label">Empalme de F.O.</label>
                                                    <br>
                                                    <input id="fo_post" type="radio" name="cat_name" value="fo_post"
                                                            {{ ($category&&$category->cat_name=='fo_post')||
                                                                old('cat_name')=='fo_post' ? 'checked="checked"' : '' }}>
                                                    <label for="fo_post" class="control-label">Plantado de poste</label>
                                                    &emsp;
                                                    <input id="fo_measure" type="radio" name="cat_name" value="fo_measure"
                                                            {{ ($category&&$category->cat_name=='fo_measure')||
                                                                old('cat_name')=='fo_measure' ? 'checked="checked"' : '' }}>
                                                    <label for="fo_measure" class="control-label">Medidas ópticas</label>
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                </fieldset>

                                @include('app.loader_gif')

                                <div class="form-group" align="center">
                                    <button type="submit" class="btn btn-success"
                                            onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                                        <i class="fa fa-arrow-right"></i> Guardar
                                    </button>

                                    @if($category&&(($user->priv_level>=1&&$user->area=='Gerencia Tecnica')||$user->priv_level>=3))
                                        <button type="submit" form="delete" class="btn btn-danger">
                                            <i class="fa fa-trash-o"></i> Eliminar
                                        </button>
                                    @endif
                                </div>
                                {{ csrf_field() }}
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
