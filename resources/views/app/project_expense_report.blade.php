<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 20/12/2017
 * Time: 03:01 PM
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
                            <li class="active">
                                <a href="#main" data-toggle="tab"> Reporte general de gastos por proyecto</a>
                            </li>
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
                            <a href="{{ '/project' }}" class="btn btn-warning" title="Volver a la tabla de proyectos">
                                <i class="fa fa-arrow-up"></i>
                            </a>
                        </div>

                        @if($user->priv_level>=3)
                            <div class="col-lg-7" align="right">
                                <a href="{{ '/project/generate/'.$type.'?from='.$from.'&to='.$to.'&id='.$form_data['id'].
                                    '&client='.$form_data['client'].'&area='.$form_data['type'] }}"
                                   class="btn btn-primary" title="Exportar en excel">
                                    <i class="fa fa-file-excel-o"></i> Exportar
                                </a>
                            </div>
                        @endif

                        <div class="col-sm-12 mg10">
                            @include('app.session_flashed_messages', array('opt' => 0))
                        </div>

                        <p class="col-sm-12">
                            <em>Nota.- Solo se toman en cuenta las solicitudes de viáticos marcadas como "Completadas"</em>
                        </p>

                        <div class="col-sm-12 mg10 mg-tp-px-10">
                            <table class="table table-striped table-hover table-bordered table_blue formal_table"
                                   id="fixable_table">
                                <thead>
                                <tr>
                                    <th width="40%">Proyecto</th>
                                    <th>Acciones</th>
                                    <th># Solicitudes</th>
                                    <th>Viáticos [Bs]</th>
                                    <th>Adicionales [Bs]</th>
                                    <th>Total [Bs]</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($projects as $project)
                                    <tr>
                                        <td>{{ $project->name }}</td>
                                        <td align="center">
                                            @if($project->requests_number>0)
                                                <div class="row">
                                                    <div class="col-sm-4 col-sm-offset-1">
                                                        <form novalidate="novalidate" action="{{ '/assignment/expense_report/'.$type }}"
                                                              method="post" style="margin: 0">
                                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                            <input type="hidden" name="from" value="{{ date_format($from,'Y-m-d') }}">
                                                            <input type="hidden" name="to" value="{{ date_format($to,'Y-m-d') }}">
                                                            <input type="hidden" name="project_id" value="{{ $project->id }}">

                                                            <button type="submit" class="submit_button"
                                                                    title="Ver gastos por asignación de este proyecto">
                                                                <i class="fa fa-picture-o"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                    <div class="col-sm-4 col-sm-offset-1">
                                                        <form novalidate="novalidate"
                                                              action="{{ '/project/expense_report/'.$type.'_per_tech' }}"
                                                              method="post" style="margin: 0">
                                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                            <input type="hidden" name="from" value="{{ date_format($from,'Y-m-d') }}">
                                                            <input type="hidden" name="to" value="{{ date_format($to,'Y-m-d') }}">
                                                            <input type="hidden" name="id" value="{{ $project->id }}">

                                                            <button type="submit" class="submit_button"
                                                                    title="Ver gastos por personal para este proyecto">
                                                                <i class="fa fa-user"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                        <td align="center">{{ $project->requests_number }}</td>
                                        <td align="right">{{ number_format($project->viatic_total,2) }}</td>
                                        <td align="right">{{ number_format($project->additionals_total,2) }}</td>
                                        <td align="right">
                                            {{ number_format($project->viatic_total+$project->additionals_total,2) }}
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
