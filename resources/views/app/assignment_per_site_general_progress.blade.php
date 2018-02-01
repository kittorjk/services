<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 11/08/2017
 * Time: 05:21 PM
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
                            <li class="active"><a href="#main" data-toggle="tab"> Avance general por sitio</a></li>
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
                                <i class="fa fa-undo"></i>
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
                                    <th>Asignación</th>
                                    <th colspan="2">{{ $assignment->name }}</th>
                                </tr>
                                <tr>
                                    <td width="40%">
                                        Cable tendido
                                        <i class="fa fa-question-circle pull-right"
                                           title="{{ 'Debe agregar items a esta categoría para'.
                                                               ' reflejar su avance' }}">
                                           {{-- 'Cuentan los items cuyo nombre contenga'.
                                                                ' las palabras "tendido" y "cable"' --}}
                                        </i>
                                    </td>
                                    <td align="center">
                                        {!! '<strong>'.$assignment->cable_executed.'</strong> de <strong>'.
                                            $assignment->cable_projected.'</strong>' !!}
                                    </td>
                                    <td align="center">
                                        <strong>{{ $assignment->cable_percentage.'%' }}</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Empalmes ejecutados
                                        <i class="fa fa-question-circle pull-right"
                                           title="{{ 'Debe agregar items a esta categoría para'.
                                                               ' reflejar su avance' }}">
                                           {{-- 'Cuentan los items cuyo nombre contenga'.
                                                                ' las palabras "ejecución" y "empalme"' --}}
                                        </i>
                                    </td>
                                    <td align="center">
                                        {!! '<strong>'.$assignment->splice_executed.'</strong> de <strong>'.
                                            $assignment->splice_projected.'</strong>' !!}
                                    </td>
                                    <td align="center">
                                        <strong>{{ $assignment->splice_percentage.'%' }}</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Postes plantados
                                        <i class="fa fa-question-circle pull-right"
                                           title="{{ 'Debe agregar items a esta categoría para'.
                                                               ' reflejar su avance' }}">
                                           {{-- 'Cuentan los items cuyo nombre contenga'.
                                                                ' las palabras "poste" y "madera" o "prfv" o "hormigón"' --}}
                                        </i>
                                    </td>
                                    <td align="center">
                                        {!! '<strong>'.$assignment->posts_executed.'</strong> de <strong>'.
                                            $assignment->posts_projected.'</strong>' !!}
                                    </td>
                                    <td align="center">
                                        <strong>{{ $assignment->posts_percentage.'%' }}</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Medidas ópticas
                                        <i class="fa fa-question-circle pull-right"
                                           title="{{ 'Debe agregar items a esta categoría para'.
                                                               ' reflejar su avance' }}">
                                           {{-- 'Cuentan los items cuyo nombre contenga'.
                                                                ' la palabra "medida"' --}}</i>
                                    </td>
                                    <td align="center">
                                        {!! '<strong>'.$assignment->meassures_executed.'</strong> de <strong>'.
                                            $assignment->meassures_projected.'</strong>' !!}
                                    </td>
                                    <td align="center">
                                        <strong>{{ $assignment->meassures_percentage.'%' }}</strong>
                                    </td>
                                </tr>

                                <tr><td colspan="3"></td></tr>

                                <tr>
                                    <th>Número de sitios</th>
                                    <td colspan="2" style="text-align: center">{{ $assignment->sites->count() }}</td>
                                </tr>
                                <tr><td colspan="3"></td></tr>

                                @foreach($assignment->sites as $site)
                                    @if($site->status!=0 /*'No asignado'*/)
                                        <tr>
                                            <th colspan="3">{{ $site->name }}</th>
                                        </tr>
                                        <tr>
                                            <td width="40%">
                                                Cable tendido
                                                <i class="fa fa-question-circle pull-right"
                                                   title="{{ 'Debe agregar items a esta categoría para'.
                                                               ' reflejar su avance' }}">
                                                   {{-- 'Cuentan los items cuyo nombre contenga'.
                                                                ' las palabras "tendido" y "cable"' --}}
                                                </i>
                                            </td>
                                            <td align="center">
                                                {!! '<strong>'.$site->cable_executed.'</strong> de <strong>'.
                                                    $site->cable_projected.'</strong>' !!}
                                            </td>
                                            <td align="center">
                                                <strong>{{ $site->cable_percentage.'%' }}</strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                Empalmes ejecutados
                                                <i class="fa fa-question-circle pull-right"
                                                   title="{{ 'Debe agregar items a esta categoría para'.
                                                               ' reflejar su avance' }}">
                                                   {{-- 'Cuentan los items cuyo nombre contenga'.
                                                                ' las palabras "ejecución" y "empalme"' --}}
                                                </i>
                                            </td>
                                            <td align="center">
                                                {!! '<strong>'.$site->splice_executed.'</strong> de <strong>'.
                                                    $site->splice_projected.'</strong>' !!}
                                            </td>
                                            <td align="center">
                                                <strong>{{ $site->splice_percentage.'%' }}</strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                Postes plantados
                                                <i class="fa fa-question-circle pull-right"
                                                   title="{{ 'Debe agregar items a esta categoría para'.
                                                               ' reflejar su avance' }}">
                                                   {{-- 'Cuentan los items cuyo nombre contenga'.
                                                                ' las palabras "poste" y "madera" o "prfv" o "hormigón"' --}}
                                                </i>
                                            </td>
                                            <td align="center">
                                                {!! '<strong>'.$site->posts_executed.'</strong> de <strong>'.
                                                    $site->posts_projected.'</strong>' !!}
                                            </td>
                                            <td align="center">
                                                <strong>{{ $site->posts_percentage.'%' }}</strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                Medidas ópticas
                                                <i class="fa fa-question-circle pull-right"
                                                   title="{{ 'Debe agregar items a esta categoría para'.
                                                               ' reflejar su avance' }}">
                                                   {{-- 'Cuentan los items cuyo nombre contenga'.
                                                                ' la palabra "medida"' --}}
                                                </i>
                                            </td>
                                            <td align="center">
                                                {!! '<strong>'.$site->meassures_executed.'</strong> de <strong>'.
                                                    $site->meassures_projected.'</strong>' !!}
                                            </td>
                                            <td align="center">
                                                <strong>{{ $site->meassures_percentage.'%' }}</strong>
                                            </td>
                                        </tr>
                                    @else
                                        <tr>
                                            <th colspan="3">{{ $site->name }}</th>
                                        </tr>
                                        <tr>
                                            <td colspan="3" align="center">Sitio no asignado</td>
                                        </tr>
                                    @endif
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
