<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 29/11/2017
 * Time: 08:17 PM
 */
?>

@extends('layouts.adm_structure')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/image_modal.css") }}">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    <li><a href="#">&ensp;<i class="fa fa-users"></i> EMPLEADOS <span class="caret"></span>&ensp;</a>
        <ul class="sub-menu">
            <li><a href="{{ '/employee' }}"><i class="fa fa-bars fa-fw"></i> Ver todo </a></li>
            <li><a href="{{ '/employee?stat=active' }}"><i class="fa fa-bars fa-fw"></i> Ver empleados activos </a></li>
            <li><a href="{{ '/employee?stat=retired' }}"><i class="fa fa-bars fa-fw"></i> Ver empleados retirados </a></li>
            <li><a href="{{ '/employee/create' }}"><i class="fa fa-user-plus fa-fw"></i> Agregar empleado </a></li>
        </ul>
    </li>
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
                        <i class="fa fa-arrow-left"></i>
                    </a>
                    <a href="{{ '/employee' }}" class="btn btn-warning" title="Volver a la tabla de empleados">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                <div class="col-sm-12 mg10">
                  @include('app.session_flashed_messages', array('opt' => 0))
                </div>

                <div class="col-sm-12 mg10 mg-tp-px-10">

                    <table class="table table-striped table-hover table-bordered">
                        <thead>
                        <tr>
                            <th width="40%">Código</th>
                            <td>{{ $employee->code }}</td>
                        </tr>
                        <tr>
                            <th width="40%">Estado</th>
                            <td>{{ $employee->active==1 ? 'Activo' : 'Retirado' }}</td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <th>Nombres</th>
                            <td>{{ $employee->first_name }}</td>
                        </tr>
                        <tr>
                            <th>Apellidos</th>
                            <td>{{ $employee->last_name }}</td>
                        </tr>
                        <tr>
                            <th>Fecha de nacimiento</th>
                            <td>
                                {{ date_format(new \DateTime($employee->birthday), 'd-m-Y') != '30-11--0001' ?
                                            date_format(new \DateTime($employee->birthday), 'd-m-Y') : 'N/E' }}
                            </td>
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

                        {{--@if($employee->income > 0)
                            <tr>
                                <th>Ingreso mensual</th>
                                <td>{{ number_format($employee->income,2).' Bs' }}</td>
                            </tr>
                        @endif--}}
                        @if($employee->basic_income > 0)
                            <tr>
                                <th>Sueldo básico</th>
                                <td>{{ number_format($employee->basic_income,2).' Bs' }}</td>
                            </tr>
                        @endif
                        @if($employee->production_bonus > 0)
                            <tr>
                                <th>Bonus de producción</th>
                                <td>{{ number_format($employee->production_bonus,2).' Bs' }}</td>
                            </tr>
                        @endif
                        @if($employee->payable_amount > 0)
                            <tr>
                                <th>Líquido pagable</th>
                                <td>{{ number_format($employee->payable_amount,2).' Bs' }}</td>
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
                        @if($employee->category)
                            <tr>
                                <th>Categoría</th>
                                <td>{{ $employee->category }}</td>
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

                        @if ($employee->date_in->year > -1 || $employee->date_in_employee->year > -1 || $employee->date_out->year > -1)
                            <tr><td colspan="2"></td></tr>
                            @if ($employee->date_in->year > -1)
                                <tr>
                                    <th>Fecha de ingreso</th>
                                    <td>{{ date_format($employee->date_in, 'd-m-Y') }}</td>
                                </tr>
                            @endif
                            @if ($employee->date_in_employee->year > -1)
                                <tr>
                                    <th>Fecha de ingreso en planilla</th>
                                    <td>{{ date_format($employee->date_in_employee, 'd-m-Y') }}</td>
                                </tr>
                            @endif
                            @if ($employee->date_out->year > -1)
                                <tr>
                                    <th>Fecha de retiro</th>
                                    <td>{{ date_format($employee->date_out, 'd-m-Y') }}</td>
                                </tr>
                            @endif
                            @if ($employee->reason_out)
                                <tr>
                                    <th>Motivo de retiro</th>
                                    <td>{{ $employee->reason_out }}</td>
                                </tr>
                            @endif
                        @endif
                        
                        <tr><td colspan="2"></td></tr>
                        <tr>
                          <th colspan="2">
                            Imágenes
                            <div class="pull-right">
                              @if (!$exists_picture && ($user->action->adm_emp_edt || $user->priv_level == 4))
                                <a href="/files/employee_img/{{ $employee->id }}" title="Subir una imagen del empleado">
                                  <i class="fa fa-upload"></i> Subir
                                </a>
                              @endif
                              @foreach ($employee->files as $file)
                                @if ($file->status === 0 || $user->priv_level === 4)
                                  <a href="/files/replace/{{ $file->id }}" title="Reemplazar imagen">
                                    <i class="fa fa-refresh"></i> Reemplazar
                                  </a>
                                @endif
                              @endforeach
                            </div>
                          </th>
                        </tr>
                        <tr>
                            <td colspan="2" align="center">
                                @foreach ($employee->files as $file)
                                    @if ($file->type == 'jpg' || $file->type == 'jpeg' || $file->type == 'png')
                                        <img src="/files/thumbnails/{{ 'thumb_'.$file->name }}" style="height: 60px;" class="pop"
                                             alt="{{ $file->description }}">
                                    @endif
                                @endforeach

                                {{ !$exists_picture ? 'No se subió una imágen de este empleado' : '' }}

                                <div class="modal fade" id="imagemodal" tabindex="-1" role="dialog"
                                     aria-labelledby="myModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-body">
                                                <button type="button" class="close" data-dismiss="modal">
                                                    <span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
                                                </button>
                                                <img src="" class="imagepreview" style="max-width: 100%">
                                            </div>
                                            <div class="modal-footer captioned">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                {{--@if($user->priv_level==4)--}}
                  <div class="col-sm-12 mg10" align="center">
                    <a href="/employee/{{ $employee->id }}/edit" class="btn btn-success">
                      <i class="fa fa-pencil-square-o"></i> Actualizar datos
                    </a>
                  
                    @if ($employee->active != 0)
                      <a href="/employee/{{ $employee->id }}/retire" class="btn btn-danger">
                        <i class="fa fa-user-times"></i> Retirar empleado
                      </a>
                    @endif
                </div>
                {{--@endif--}}
            </div>
        </div>
    </div>

@endsection

@section('footer')
@endsection

@section('javascript')
    <script src="{{ asset('app/js/set_current_url.js') }}"></script> {{-- For recording current url --}}
    <script>
        $('#alert').delay(2000).fadeOut('slow');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        $(function() {
            $('.pop').on('click', function() {
                var fullSizedSource = $(this).attr('src').replace('thumbnails/thumb_', '');

                $('.imagepreview').attr('src', fullSizedSource /*$(this).find('img').attr('src')*/);
                $('.captioned').html($(this).find('img').attr('alt'));
                $('#imagemodal').modal('show');
            });
        });
    </script>
@endsection
