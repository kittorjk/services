<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 21/12/2017
 * Time: 03:19 PM
 */
?>

@extends('layouts.info_master')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/custom_table.css") }}">
    <link rel="stylesheet" href="{{ asset("app/css/info_tabs.css") }}">
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1 mg-btm-px-40">

        <div class="panel panel-sky">
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    <div class="pull-left">
                        <ul class="nav nav-tabs">
                            <li class="active">
                                <a href="#main" data-toggle="tab"> Reporte general de gastos según personal</a>
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
                                <i class="fa fa-undo"></i>
                            </a>
                            <a href="{{ '/assignment' }}" class="btn btn-warning" title="Volver a la tabla de proyectos">
                                <i class="fa fa-arrow-up"></i>
                            </a>
                        </div>

                        @if($user->action->prj_vtc_exp /*$user->priv_level>=3*/)
                            <div class="col-lg-7" align="right">
                                <a href="{{ '/assignment/generate/'.$type.'?from='.$from.'&to='.$to.'&id='.$form_data['id'] }}"
                                   class="btn btn-primary" title="Exportar en excel">
                                    <i class="fa fa-file-excel-o"></i> Exportar
                                </a>
                            </div>
                        @endif

                        <div class="col-sm-12 mg10">
                            @include('app.session_flashed_messages', array('opt' => 0))
                        </div>

                        <div class="col-sm-12 mg10" align="center">
                            <h4 style="margin-top: 0">{{ 'Asignación: '.$parent->name }}</h4>
                        </div>

                        <p class="col-sm-12">
                            <em>Nota.- Solo se toman en cuenta las solicitudes de viáticos marcadas como "Completadas"</em>
                        </p>

                        <div class="col-sm-12 mg10 mg-tp-px-10">
                            <table class="table table-striped table-hover table-bordered table_blue formal_table"
                                   id="fixable_table">
                                <thead>
                                <tr>
                                    <th width="40%">Empleado</th>
                                    <th># Solicitudes</th>
                                    <th>Viáticos [Bs]</th>
                                    <th>Adicionales [Bs]</th>
                                    <th>Total [Bs]</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($employees as $employee)
                                    @if($employee->requests_number>0)
                                        <tr>
                                            <td>{{ $employee->first_name.' '.$employee->last_name }}</td>
                                            <td align="center">{{ $employee->requests_number }}</td>
                                            <td align="right">{{ number_format($employee->viatic_total,2) }}</td>
                                            <td align="right">{{ number_format($employee->additionals_total,2) }}</td>
                                            <td align="right">
                                                {{ number_format($employee->viatic_total+$employee->additionals_total,2) }}
                                            </td>
                                        </tr>
                                    @endif
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
