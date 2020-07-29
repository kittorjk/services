<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 19/12/2017
 * Time: 05:12 PM
 */
?>

@extends('layouts.info_master')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/custom_table.css") }}">
    <link rel="stylesheet" href="{{ asset("app/css/info_tabs.css") }}">
    <style>
        .submit_button, .submit_button:hover {
            background:none!important;
            border:none;
            padding:0!important;
            font: inherit;
            cursor: pointer;
            color: #0645AD;
        }
        .submit_button:hover {
            text-decoration: underline;
        }
    </style>
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1 mg-btm-px-40">

        <div class="panel panel-sky">
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    <div class="pull-left">
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#main" data-toggle="tab"> Reporte general de gastos por asignación</a></li>
                        </ul>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
            <div class="panel-body">

                <div class="tab-content">

                    <div class="tab-pane fade in active" id="main">

                        <div class="col-lg-5 mg20">
                            <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                                <i class="fa fa-arrow-left"></i>
                            </a>
                            <a href="{{ '/assignment' }}" class="btn btn-warning" title="Volver a la tabla de asignaciones">
                                <i class="fa fa-arrow-up"></i>
                            </a>
                        </div>

                        @if($user->action->prj_vtc_exp /*$user->priv_level>=3*/)
                            <div class="col-lg-7" align="right">
                                <a href="{{ '/assignment/generate/'.$type.'?from='.$from.'&to='.$to.
                                    (array_key_exists('id',$form_data) ? '&id='.$form_data['id'] : '').
                                    (array_key_exists('client',$form_data) ? '&client='.$form_data['client'] : '').
                                    (array_key_exists('type',$form_data) ? '&area='.$form_data['type'] : '').
                                    (array_key_exists('project_id',$form_data) ? '&project_id='.$form_data['project_id'] : '') }}"
                                   class="btn btn-primary" title="Exportar en excel">
                                    <i class="fa fa-file-excel-o"></i> Exportar
                                </a>
                            </div>
                        @endif

                        <div class="col-sm-12 mg10">
                            @include('app.session_flashed_messages', array('opt' => 0))
                        </div>

                        @if($parent)
                            <div class="col-sm-12 mg10" align="center">
                                <h4 style="margin-top: 0">{{ 'Proyecto: '.$parent->name }}</h4>
                            </div>
                        @endif

                        <p class="col-sm-12">
                            <em>Nota.- Solo se toman en cuenta las solicitudes de viáticos marcadas como "Completadas"</em>
                        </p>

                        <div class="col-sm-12 mg10 mg-tp-px-10">
                            <table class="table table-striped table-hover table-bordered table_blue formal_table"
                                   id="fixable_table">
                                <thead>
                                <tr>
                                    <th width="40%">Asignación</th>
                                    <th>Acciones</th>
                                    <th># Solicitudes</th>
                                    <th>Viáticos [Bs]</th>
                                    <th>Adicionales [Bs]</th>
                                    <th>Total [Bs]</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($assignments as $assignment)
                                    <tr>
                                        <td>{{ $assignment->name }}</td>
                                        <td align="center">
                                            @if($assignment->requests_number>0)
                                                <div class="row">
                                                    <div class="col-sm-4 col-sm-offset-1">
                                                        <form novalidate="novalidate"
                                                              action="{{ '/site/expense_report/'.$type.'/'.$assignment->id }}"
                                                              method="post" style="margin: 0">
                                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                            <input type="hidden" name="from" value="{{ date_format($from,'Y-m-d') }}">
                                                            <input type="hidden" name="to" value="{{ date_format($to,'Y-m-d') }}">
                                                            <input type="hidden" name="assignment_id" value="{{ $assignment->id }}">

                                                            <button type="submit" class="btn btn-primary submit_button"
                                                                    title="Ver gastos por sitio de esta asignación">
                                                                <i class="fa fa-picture-o"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                    <div class="col-sm-4 col-sm-offset-1">
                                                        <form novalidate="novalidate"
                                                              action="{{ '/assignment/expense_report/'.$type.'_per_tech' }}"
                                                              method="post" style="margin: 0">
                                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                            <input type="hidden" name="from" value="{{ date_format($from,'Y-m-d') }}">
                                                            <input type="hidden" name="to" value="{{ date_format($to,'Y-m-d') }}">
                                                            <input type="hidden" name="id" value="{{ $assignment->id }}">

                                                            <button type="submit" class="submit_button"
                                                                    title="Ver gastos por personal para esta asignación">
                                                                <i class="fa fa-user"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                        <td align="center">{{ $assignment->requests_number }}</td>
                                        <td align="right">{{ number_format($assignment->viatic_total,2) }}</td>
                                        <td align="right">{{ number_format($assignment->additionals_total,2) }}</td>
                                        <td align="right">
                                            {{ number_format($assignment->viatic_total+$assignment->additionals_total,2) }}
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1" id="fixed">
        <div class="col-sm-12">
            <div class="col-sm-12 mg10">
                <table class="table table-striped table-hover table-bordered table_blue formal_table" id="cloned"></table>
            </div>
        </div>
    </div>

@endsection

@section('footer')
@endsection

@section('javascript')
    <script src="{{ asset('app/js/fix_table_header.js') }}"></script> {{-- For fixed header --}}
    <script>
        $('#alert').delay(2000).fadeOut('slow');
    </script>
@endsection
