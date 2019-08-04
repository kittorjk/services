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
          {{ 'Retirar empleado' }}
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

        <form novalidate="novalidate" action="{{ '/employee/'.$employee->id.'/retire' }}" method="post">
          <input type="hidden" name="_method" value="put">
          <input type="hidden" name="_token" value="{{ csrf_token() }}">

          <div class="form-group">
            <div class="input-group">
              <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                <div class="input-group" style="width: 100%">
                  <label for="first_name" class="input-group-addon" style="width: 23%;text-align: left"
                          title="Nombres">
                      Nombre(s):
                  </label>

                  <input type="text" class="form-control" name="first_name"
                        id="first_name" value="{{ $employee ? $employee->first_name : '' }}"
                        placeholder="Nombre(s)" disabled="disabled">
                </div>

                <div class="input-group" style="width: 100%">
                  <label for="last_name" class="input-group-addon" style="width: 23%;text-align: left"
                          title="Apellidos">
                      Apellidos:
                  </label>

                  <input type="text" class="form-control" name="last_name"
                        id="last_name" value="{{ $employee ? $employee->last_name : '' }}"
                        placeholder="Apellidos" disabled="disabled">
                </div>

                <div class="input-group" style="width: 100%">
                  <label for="address" class="input-group-addon" style="width: 23%;text-align: left">
                      Motivo de retiro:
                  </label>

                  <textarea rows="5" required="required" class="form-control" name="reason_out"
                            placeholder="Indique el motivo de retiro del empleado">{{ $employee ?
                       $employee->reason_out : old('reason_out') }}</textarea>
                </div>
                
              </div>
            </div>
          </div>

          @include('app.loader_gif')

          <div class="form-group" align="center">
            <button type="submit" class="btn btn-danger"
                    onclick="this.disabled=true; $('#wait').show(); this.form.submit()"
                    title="Se inhabilitará el acceso y uso del registro de este empleado">
              <i class="fa fa-user-times"></i> Retirar
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
    $(document).ready(function() {
      $("#wait").hide();
    });
  </script>
@endsection
