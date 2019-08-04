<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 29/11/2017
 * Time: 06:29 PM
 */
?>

@extends('layouts.master')

@section('header')
  @parent
@endsection

@section('content')
  <div id="loginbox" class="mg-tp-px-50 mg-btm-px-40 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
    <div class="panel panel-10gray">
      <div class="panel-heading" align="center">
        <div class="panel-title">
          {{ $employee ? 'Actualizar información de empleado' : 'Crear registro de empleado' }}
        </div>
      </div>
      <div class="panel-body">
        <div class="mg20">
          <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
            <i class="fa fa-arrow-left"></i>
          </a>
          <a href="{{ '/employee' }}" class="btn btn-warning" title="Ir a la tabla de empleados">
            <i class="fa fa-arrow-up"></i>
          </a>
        </div>

        @include('app.session_flashed_messages', array('opt' => 1))

        @if($employee)
          <form id="delete" action="/employee/{{ $employee->id }}" method="post">
            <input type="hidden" name="_method" value="delete">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
          </form>
          <form novalidate="novalidate" action="{{ '/employee/'.$employee->id }}" method="post">
            <input type="hidden" name="_method" value="put">
        @else
          <form novalidate="novalidate" action="{{ '/employee' }}" method="post">
        @endif
            <input type="hidden" name="_token" value="{{ csrf_token() }}">

            <div class="form-group">
              <div class="input-group">
                <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                  <div class="input-group" style="width: 100%">
                    <label for="first_name" class="input-group-addon" style="width: 23%;text-align: left"
                            title="Nombres">
                        Nombre(s): <span class="pull-right">*</span>
                    </label>

                    <input required="required" type="text" class="form-control" name="first_name"
                            id="first_name"
                            value="{{ $employee ? $employee->first_name : old('first_name') }}"
                            placeholder="Nombre(s)">
                  </div>

                  <div class="input-group" style="width: 100%">
                    <label for="last_name" class="input-group-addon" style="width: 23%;text-align: left"
                            title="Apellidos">
                        Apellidos: <span class="pull-right">*</span>
                    </label>

                    <input required="required" type="text" class="form-control" name="last_name"
                            id="last_name"
                            value="{{ $employee ? $employee->last_name : old('last_name') }}"
                            placeholder="Apellidos">
                  </div>
                                            
                  <div class="input-group" style="width: 75%;text-align: center">
                    <label for="birthday" class="input-group-addon" style="width: 31%; text-align: left">
                        Fecha nacimiento:
                    </label>

                    <span class="input-group-addon">
                        <input type="date" name="birthday" id="birthday" step="1" min="1900-01-01"
                            value="{{ $employee ? $employee->birthday : old('birthday') }}">
                    </span>
                  </div>

                  <div class="input-group" style="width: 75%">
                    <label for="id_extension" class="input-group-addon" style="width: 31%;text-align: left">
                      C.I.: <span class="pull-right">*</span>
                    </label>

                    <input required="required" type="number" class="form-control" name="id_card"
                          id="id_card" step="1" min="1"
                          value="{{ $employee&&$employee->id_card!=0 ? $employee->id_card : old('id_card') }}"
                          placeholder="Número de C.I.">

                    <div class="input-group-btn" style="width:70px;">
                      <select required="required" class="form-control" name="id_extension"
                              id="id_extension">
                        {{-- LP=La Paz OR=Oruro PT=Potosi CB=Cochabamba SC=Santa Cruz
                            BN=Beni PA=Pando TJ=Tarija CH=Chuquisaca E=Extranjero --}}
                        <option value="LP"
                          {{ ($employee && $employee->id_extension == 'LP') ||
                              old('id_extension') == 'LP' ? 'selected="selected"' : '' }}>LP</option>
                        <option value="OR"
                          {{ ($employee && $employee->id_extension == 'OR') ||
                              old('id_extension') == 'OR' ? 'selected="selected"' : '' }}>OR</option>
                        <option value="PT"
                          {{ ($employee && $employee->id_extension == 'PT') ||
                              old('id_extension') == 'PT' ? 'selected="selected"' : '' }}>PT</option>
                        <option value="CB"
                          {{ ($employee && $employee->id_extension == 'CB') ||
                              old('id_extension') == 'CB' ? 'selected="selected"' : '' }}>CB</option>
                        <option value="SC"
                          {{ ($employee && $employee->id_extension == 'SC') ||
                              old('id_extension') == 'SC' ? 'selected="selected"' : '' }}>SC</option>
                        <option value="BN"
                          {{ ($employee && $employee->id_extension == 'BN') ||
                              old('id_extension') == 'BN' ? 'selected="selected"' : '' }}>BN</option>
                        <option value="PA"
                          {{ ($employee && $employee->id_extension == 'PA') ||
                              old('id_extension') == 'PA' ? 'selected="selected"' : '' }}>PA</option>
                        <option value="TJ"
                          {{ ($employee && $employee->id_extension == 'TJ') ||
                              old('id_extension') == 'TJ' ? 'selected="selected"' : '' }}>TJ</option>
                        <option value="CH"
                          {{ ($employee && $employee->id_extension == 'CH') ||
                              old('id_extension') == 'CH' ? 'selected="selected"' : '' }}>CH</option>
                        <option value="E"
                          {{ ($employee && $employee->id_extension == 'E') ||
                              old('id_extension') == 'E' ? 'selected="selected"' : '' }}>E</option>
                      </select>
                    </div>
                  </div>

                  <div class="input-group" style="width: 100%">
                    <label for="bnk_account" class="input-group-addon" style="width: 23%;text-align: left">
                      Nro cuenta:
                    </label>

                    <input required="required" type="text" class="form-control" name="bnk_account"
                          id="bnk_account" value="{{ $employee ? $employee->bnk_account : old('bnk_account') }}"
                          placeholder="Número de cuenta">
                  </div>

                  <div class="input-group" style="width: 100%">
                    <label for="bnk" class="input-group-addon" style="width: 23%;text-align: left">
                      Banco:
                    </label>

                    <select required="required" class="form-control" name="bnk" id="bnk">
                      <option value="" hidden>Seleccione un banco o agregue uno a la lista</option>
                      @foreach($bnk_options as $bnk_option)
                        <option value="{{ $bnk_option->bnk }}"
                            {{ ($employee && $employee->bnk == $bnk_option->bnk) ||
                                old('bnk') == $bnk_option->bnk ? 'selected="selected"' : '' }}
                        >{{ $bnk_option->bnk }}</option>
                      @endforeach
                      <option value="Otro">Otro</option>
                    </select>
                  </div>
                  <input required="required" type="text" class="form-control" name="other_bnk" id="other_bnk"
                          placeholder="Indique un banco (*)" disabled="disabled">

                  <div class="input-group" style="width: 100%">
                    <label for="role" class="input-group-addon" style="width: 23%;text-align: left">
                      Cargo:
                    </label>

                    <input required="required" type="text" class="form-control" name="role"
                          id="role" value="{{ $employee ? $employee->role : old('role') }}"
                          placeholder="Cargo">
                  </div>

                  <div class="input-group" style="width: 100%">
                    <label for="category" class="input-group-addon" style="width: 23%;text-align: left">
                      Categoría:
                    </label>

                    <input required="required" type="text" class="form-control" name="category"
                          id="category" value="{{ $employee ? $employee->category : old('category') }}"
                          placeholder="Categoría">
                  </div>

                  <div class="input-group" style="width: 100%">
                    <label for="area" class="input-group-addon" style="width: 23%;text-align: left">
                      Área: <span class="pull-right">*</span>
                    </label>

                    <select required="required" class="form-control" name="area" id="area">
                      <option value="" hidden>Área de trabajo</option>
                      <option value="Gerencia Administrativa"
                        {{ ($employee && $employee->area == 'Gerencia Administrativa') ||
                          old('area') == 'Gerencia Administrativa' ?
                          'selected="selected"' : '' }}>Gerencia Administrativa</option>
                      <option value="Gerencia General"
                        {{ ($employee && $employee->area == 'Gerencia General') ||
                          old('area') == 'Gerencia General' ?
                          'selected="selected"' : '' }}>Gerencia General</option>
                      <option value="Gerencia Tecnica"
                        {{ ($employee && $employee->area == 'Gerencia Tecnica') ||
                          old('area') == 'Gerencia Tecnica' ?
                          'selected="selected"' : '' }}>Gerencia Tecnica</option>
                      <option value="Subcontratista"
                        {{ ($employee && $employee->area == 'Subcontratista') ||
                          old('area') == 'Subcontratista' ?
                          'selected="selected"' : '' }}>Subcontratista</option>
                    </select>
                  </div>

                  <div class="input-group" style="width: 100%">
                    <label for="branch_id" class="input-group-addon" style="width: 23%;text-align: left">
                      Oficina: <span class="pull-right">*</span>
                    </label>

                    <select required="required" class="form-control" name="branch_id" id="branch_id">
                      <option value="" hidden>Seleccione una oficina</option>
                      @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" title="{{ $branch->name }}"
                          {{ ($employee && $employee->branch_id == $branch->id) ||
                            old('branch_id') == $branch->id ? 'selected="selected"' :
                            '' }}>{{ $branch->city }}</option>
                      @endforeach
                      {{--
                      <option value="La Paz"
                        {{ ($employee && $employee->branch == 'La Paz') ||
                          old('branch') == 'La Paz' ? 'selected="selected"' : '' }}>La Paz</option>
                      <option value="Santa Cruz"
                        {{ ($employee && $employee->branch == 'Santa Cruz') ||
                          old('branch') == 'Santa Cruz' ? 'selected="selected"' : '' }}>Santa Cruz</option>
                      <option value="Cochabamba"
                        {{ ($employee && $employee->branch == 'Cochabamba') ||
                          old('branch') == 'Cochabamba' ? 'selected="selected"' : '' }}>Cochabamba</option>
                      --}}
                    </select>
                  </div>

                  {{--<div class="input-group" style="width: 75%">
                    <span class="input-group-addon" style="width: 31%;text-align: left">
                      Ingresos:
                    </span>

                    <input required="required" type="number" class="form-control" name="income"
                          id="income" step="any" min="0"
                          value="{{ $employee&&$employee->income!=0 ?
                              $employee->income : old('income') }}"
                          placeholder="Ingreso mensual">

                    <span class="input-group-addon">Bs.</span>
                  </div>--}}

                  <div class="input-group" style="width: 75%">
                    <span class="input-group-addon" style="width: 31%;text-align: left">
                      Sueldo básico:
                    </span>

                    <input required="required" type="number" class="form-control" name="basic_income"
                          id="basic_income" step="any" min="0"
                          value="{{ $employee && $employee->basic_income != 0 ?
                              $employee->basic_income : old('basic_income') }}"
                          placeholder="0.00">

                    <span class="input-group-addon">Bs.</span>
                  </div>

                  <div class="input-group" style="width: 75%">
                    <span class="input-group-addon" style="width: 31%;text-align: left">
                      Bono producción:
                    </span>

                    <input required="required" type="number" class="form-control" name="production_bonus"
                          id="production_bonus" step="any" min="0"
                          value="{{ $employee && $employee->production_bonus != 0 ?
                              $employee->production_bonus : old('production_bonus') }}"
                          placeholder="0.00">

                    <span class="input-group-addon">Bs.</span>
                  </div>

                  <div class="input-group" style="width: 75%">
                    <span class="input-group-addon" style="width: 31%;text-align: left">
                      Líquido pagable:
                    </span>

                    <input required="required" type="number" class="form-control" name="payable_amount"
                          id="payable_amount" step="any" min="0"
                          value="{{ $employee && $employee->payable_amount != 0 ?
                              $employee->payable_amount : old('payable_amount') }}"
                          placeholder="0.00">

                    <span class="input-group-addon">Bs.</span>
                  </div>

                  <div class="input-group" style="width: 100%">
                    <label for="corp_email" class="input-group-addon" style="width: 23%;text-align: left">
                      Correo corp.:
                    </label>

                    <input required="required" type="text" class="form-control" name="corp_email"
                          id="corp_email"
                          value="{{ $employee ? $employee->corp_email : old('corp_email') }}"
                          placeholder="Correo corporativo">
                  </div>

                  <div class="input-group" style="width: 100%">
                    <label for="ext_email" class="input-group-addon" style="width: 23%;text-align: left">
                      Correo externo:
                    </label>

                    <input required="required" type="text" class="form-control" name="ext_email"
                          id="ext_email"
                          value="{{ $employee ? $employee->ext_email : old('ext_email') }}"
                          placeholder="Correo externo">
                  </div>

                  <div class="input-group" style="width: 100%">
                    <label for="phone" class="input-group-addon" style="width: 23%;text-align: left">
                      Teléfono:
                    </label>

                    <input required="required" type="number" class="form-control" name="phone"
                          id="phone" step="1" min="1"
                          value="{{ $employee&&$employee->phone!=0 ? $employee->phone :
                              old('phone') }}"
                          placeholder="Número de teléfono fijo o celular">
                  </div>

                  <div class="input-group" style="width: 75%;text-align: center">
                    <label for="date_in" class="input-group-addon" style="width: 31%; text-align: left">
                      Fecha ingreso:
                    </label>

                    <span class="input-group-addon">
                      <input type="date" name="date_in" id="date_in" step="1" min="2014-01-01"
                            value="{{ $employee ? $employee->date_in : old('date_in') }}">
                    </span>
                  </div>

                  <div class="input-group" style="width: 75%;text-align: center">
                    <label for="date_in_employee" class="input-group-addon" style="width: 31%; text-align: left">
                      Fecha ingreso planilla:
                    </label>

                    <span class="input-group-addon">
                      <input type="date" name="date_in_employee" id="date_in_employee" step="1" min="2014-01-01"
                            value="{{ $employee ? $employee->date_in_employee : old('date_in_employee') }}">
                    </span>
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

              {{--@if($employee && $user->priv_level == 4)
                <button type="submit" form="delete" class="btn btn-danger"
                        title="Se inhabilitará el acceso y uso del registro de este empleado">
                  <i class="fa fa-user-times"></i> Retirar
                </button>
              @endif--}}
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
    $(document).ready(function() {
      $("#wait").hide();
    });

    var bnk = $('#bnk'), other_bnk = $('#other_bnk');
    bnk.change(function () {
      if (bnk.val() === 'Otro') {
        other_bnk.removeAttr('disabled').show();
      } else {
        other_bnk.attr('disabled', 'disabled').val('').hide();
      }
    }).trigger('change');
  </script>
@endsection
