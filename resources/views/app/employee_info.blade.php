<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 29/11/2017
 * Time: 08:17 PM
 */
?>

@extends('layouts.info_master')

@section('header')
    @parent
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

        <div class="panel panel-10gray">
            <div class="panel-heading" align="center">
                <div class="panel-title">Información de empleado</div>
            </div>
            <div class="panel-body">
                <div class="col-sm-12 mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-undo"></i>
                    </a>
                    <a href="{{ '/employee' }}" class="btn btn-warning" title="Volver a la tabla de empleados">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <div class="col-sm-12 mg10 mg-tp-px-10">

                    <table class="table table-striped table-hover table-bordered">
                        <thead>
                        <tr>
                            <th>Código</th>
                            <td>{{ $employee->code }}</td>
                        </tr>
                        <tr>
                            <th width="40%">Nombres</th>
                            <td>{{ $employee->first_name }}</td>
                        </tr>
                        <tr>
                            <th width="40%">Apellidos</th>
                            <td>{{ $employee->last_name }}</td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <th>Estado</th>
                            <td>{{ $employee->active==1 ? 'Activo' : 'Retirado' }}</td>
                        </tr>
                        <tr>
                            <th>Carnet de identidad</th>
                            <td>{{ $employee->id_card.' '.$employee->id_extension }}</td>
                        </tr>
                        @if($employee->access)
                            @if($employee->access->license)
                                <tr>
                                    <th>Licencia de conducir</th>
                                    <td>
                                        <a href="/license/{{ $employee->access->license->id }}">
                                            {{ $employee->access->license->number }}
                                        </a>
                                    </td>
                                </tr>
                            @endif
                        @endif
                        <tr><td colspan="2"></td></tr>

                        @if($employee->income>0)
                            <tr>
                                <th>Ingreso mensual</th>
                                <td>{{ number_format($employee->income,2).' Bs' }}</td>
                            </tr>
                        @endif
                        @if($employee->bnk!='')
                            <tr>
                                <th>Banco</th>
                                <td>{{ $employee->bnk }}</td>
                            </tr>
                        @endif
                        <tr>
                            <th>Cuenta</th>
                            <td>{{ $employee->bnk_account }}</td>
                        </tr>
                        <tr><td colspan="2"></td></tr>

                        @if($employee->role)
                            <tr>
                                <th>Cargo</th>
                                <td>{{ $employee->role }}</td>
                            </tr>
                        @endif
                        @if($employee->area)
                            <tr>
                                <th>Área de trabajo</th>
                                <td>{{ $employee->area }}</td>
                            </tr>
                        @endif
                        <tr>
                            <th>Oficina</th>
                            <td>{{ $employee->branch_record ? $employee->branch_record->name : 'N/E' }}</td>
                        </tr>

                        @if($employee->corp_email||$employee->ext_email||$employee->phone>0)
                            <tr><td colspan="2"></td></tr>
                            @if($employee->corp_email)
                                <tr>
                                    <th>Correo corporativo</th>
                                    <td><a href="mailto:{{ $employee->corp_email }}">{{ $employee->corp_email }}</a></td>
                                </tr>
                            @endif
                            @if($employee->ext_email)
                                <tr>
                                    <th>Correo externo</th>
                                    <td><a href="mailto:{{ $employee->ext_email }}">{{ $employee->ext_email }}</a></td>
                                </tr>
                            @endif
                            @if($employee->phone>0)
                                <tr>
                                    <th>Teléfono</th>
                                    <td>{{ $employee->phone }}</td>
                                </tr>
                            @endif
                        @endif

                        @if($employee->date_in->year>-1||$employee->date_out->year>-1)
                            <tr><td colspan="2"></td></tr>
                            @if($employee->date_in->year>-1)
                                <tr>
                                    <th>Fecha de ingreso</th>
                                    <td>{{ date_format($employee->date_in, 'd-m-Y') }}</td>
                                </tr>
                            @endif
                            @if($employee->date_out->year>-1)
                                <tr>
                                    <th>Fecha de retiro</th>
                                    <td>{{ date_format($employee->date_out, 'd-m-Y') }}</td>
                                </tr>
                            @endif
                        @endif
                        </tbody>
                    </table>
                </div>

                {{--@if($user->priv_level==4)--}}
                    <div class="col-sm-12 mg10" align="center">
                        <a href="/employee/{{ $employee->id }}/edit" class="btn btn-success">
                            <i class="fa fa-pencil-square-o"></i> Actualizar datos
                        </a>
                    </div>
                {{--@endif--}}
            </div>
        </div>
    </div>

@endsection

@section('footer')
@endsection
