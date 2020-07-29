<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 15/08/2017
 * Time: 12:30 PM
 */
?>

@extends('layouts.info_master')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/custom_table.css") }}">
    <link rel="stylesheet" href="{{ asset("app/css/info_tabs.css") }}">
    <link rel="stylesheet" href="{{ asset("app/css/progress_bar.css") }}">
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1 mg-btm-px-40">

        <div class="panel panel-sky">
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    <div class="pull-left">
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#main" data-toggle="tab"> Avance general (todos los items)</a></li>
                        </ul>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <!--<div class="panel-title">Información de proyecto</div>-->
            </div>
            <div class="panel-body">

                <div class="tab-content">

                    <div class="tab-pane fade in active" id="main">

                        <div class="col-lg-5 mg20">
                            <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                                <i class="fa fa-arrow-left"></i>
                            </a>
                            <a href="{{ '/assignment' }}" class="btn btn-warning" title="Volver a lista de asignaciones">
                                <i class="fa fa-arrow-up"></i>
                            </a>
                        </div>

                        <div class="col-sm-12 mg10">
                            @include('app.session_flashed_messages', array('opt' => 0))
                        </div>

                        <div class="col-sm-12 mg10 mg-tp-px-10">
                            <table class="table table-striped table-hover table-bordered table_blue formal_table">
                                <tr>
                                    <th width="50%">Asignación</th>
                                    <th colspan="2">{{ $assignment->name }}</th>
                                </tr>
                                <tr>
                                    <th>Número de Items </th>
                                    <td colspan="2" style="text-align: center">{{ $items->count() }}</td>
                                </tr>
                                <tr><td colspan="3"></td></tr>

                                @if($items->count()>0)
                                    <tr>
                                        <th>Item</th>
                                        <th>Avance</th>
                                        <th>Porcentaje</th>
                                    </tr>
                                @endif

                                @foreach($items as $item)
                                    <tr>
                                        <td>{{ $item->name }}</td>
                                        <td align="center">
                                            {!! '<strong>'.$item->progress.'</strong> de <strong>'.
                                                $item->total_expected.' '.$item->units.'</strong>' !!}
                                        </td>
                                        <td align="right">

                                            <div class="progress">
                                                <div class="progress-bar progress-bar-success"
                                                     style="{{ 'width: '.
                                                     number_format(($item->progress/$item->total_expected)*100,2).'%' }}">
                                                    <span>
                                                        {{ number_format(($item->progress/$item->total_expected)*100,2).'%' }}
                                                    </span>
                                                </div>
                                            </div>

                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection

@section('footer')
@endsection

@section('javascript')
    <script>
        $('#alert').delay(2000).fadeOut('slow');
    </script>
@endsection
