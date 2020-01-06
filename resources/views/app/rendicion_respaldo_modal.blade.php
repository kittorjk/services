<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 01/02/2017
 * Time: 04:13 PM
 */
?>

<style>
  input[type=date]:before {  right: 10px;  }
  .modal-footer {
      background-color: #f4f4f4;
  }
</style>

<div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content" style="overflow:hidden;">

        <div class="modal-header alert-info">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">{{ 'Agregar o modificar respaldo - '.$rendicion->codigo }}</h4>
        </div>

        <div class="modal-body">

          <form novalidate="novalidate" id="respaldoForm" action="{{ '/rendicion_respaldo' }}" method="post" class="form-horizontal">
            <input type="hidden" id="_method" name="_method" value="post">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">

            {{-- @include('app.session_flashed_messages', array('opt' => 1)) --}}

            <div class="panel-body" align="center">
              <div class="row">
                <div class="col-md-12 col-sm-12">

                  <div class="form-group{{ $errors->has('rendicion_id') ? ' has-error' : '' }}">
                    <label for="rendicion_id" class="col-md-4 control-label">(*) Rendición</label>

                    <div class="col-md-6">
                      <select id="rendicion_id" name="rendicion_id" class="form-control">
                        <option value="{{ $rendicion->id }}" selected="selected" readonly="readonly">
                          {{ $rendicion->codigo }}
                        </option>
                      </select>

                      @if ($errors->has('rendicion_id'))
                        <span class="help-block">
                          <strong>{{ $errors->first('rendicion_id') }}</strong>
                        </span>
                      @endif
                    </div>
                  </div>

                  <div class="form-group{{ $errors->has('fecha_respaldo') ? ' has-error' : '' }}">
                    <label for="fecha_respaldo" class="col-md-4 control-label">(*) Fecha emisión</label>

                    <div class="col-md-6">
                      <input id="fecha_respaldo" type="date" class="form-control" name="fecha_respaldo"
                        step="1" max="{{ date('Y-m-d') }}"
                        value="{{ old('fecha_respaldo') }}">

                      @if($errors->has('fecha_respaldo'))
                        <span class="help-block">
                          <strong>{{ $errors->first('fecha_respaldo') }}</strong>
                        </span>
                      @endif
                    </div>
                  </div>

                  <div class="form-group{{ $errors->has('tipo_respaldo') ? ' has-error' : '' }}">
                    <label for="tipo_respaldo" class="col-md-4 control-label">(*) Tipo de respaldo</label>

                    <div class="col-md-6">
                      <select id="tipo_respaldo" name="tipo_respaldo" class="form-control">
                        <option value="" hidden="hidden">Seleccione un tipo</option>
                        <option value="Factura">{{ 'Factura' }}</option>
                        <option value="Recibo">{{ 'Recibo' }}</option>
                      </select>

                      @if ($errors->has('tipo_respaldo'))
                        <span class="help-block">
                          <strong>{{ $errors->first('tipo_respaldo') }}</strong>
                        </span>
                      @endif
                    </div>
                  </div>

                  <div class="form-group{{ $errors->has('nit') ? ' has-error' : '' }}">
                    <label for="nit" class="col-md-4 control-label">(*) NIT</label>

                    <div class="col-md-6">
                      <input id="nit" type="text"
                        class="form-control"
                        name="nit" placeholder="NIT"
                        value="{{ old('nit') }}"
                        required>

                      @if ($errors->has('nit'))
                        <span class="help-block">
                          <strong>{{ $errors->first('nit') }}</strong>
                        </span>
                      @endif
                    </div>
                  </div>

                  <div class="form-group{{ $errors->has('nro_respaldo') ? ' has-error' : '' }}">
                    <label for="nro_respaldo" class="col-md-4 control-label">(*) Número</label>

                    <div class="col-md-6">
                      <input id="nro_respaldo" type="text"
                        class="form-control"
                        name="nro_respaldo" placeholder="Número de rendición"
                        value="{{ old('nro_respaldo') }}" 
                        required>

                      @if ($errors->has('nro_respaldo'))
                        <span class="help-block">
                          <strong>{{ $errors->first('nro_respaldo') }}</strong>
                        </span>
                      @endif
                    </div>
                  </div>

                  <div class="form-group{{ $errors->has('codigo_autorizacion') ? ' has-error' : '' }}">
                    <label for="codigo_autorizacion" class="col-md-4 control-label">(*) Código de autorización</label>

                    <div class="col-md-6">
                      <input id="codigo_autorizacion" type="text"
                        class="form-control"
                        name="codigo_autorizacion" placeholder="Código de autorización"
                        value="{{ old('codigo_autorizacion') }}" 
                        required>

                      @if ($errors->has('codigo_autorizacion'))
                        <span class="help-block">
                          <strong>{{ $errors->first('codigo_autorizacion') }}</strong>
                        </span>
                      @endif
                    </div>
                  </div>

                  <div class="form-group{{ $errors->has('codigo_control') ? ' has-error' : '' }}">
                    <label for="codigo_control" class="col-md-4 control-label">(*) Código de control</label>

                    <div class="col-md-6">
                      <input id="codigo_control" type="text"
                        class="form-control"
                        name="codigo_control" placeholder="Código de control"
                        value="{{ old('codigo_control') }}" 
                        required>

                      @if ($errors->has('codigo_control'))
                        <span class="help-block">
                          <strong>{{ $errors->first('codigo_control') }}</strong>
                        </span>
                      @endif
                    </div>
                  </div>

                  <div class="form-group{{ $errors->has('razon_social') ? ' has-error' : '' }}">
                    <label for="razon_social" class="col-md-4 control-label">(*) Razón social</label>

                    <div class="col-md-6">
                      <input id="razon_social" type="text"
                        class="form-control"
                        name="razon_social" placeholder="Razón social"
                        value="{{ old('razon_social') }}" 
                        required>

                      @if ($errors->has('razon_social'))
                        <span class="help-block">
                          <strong>{{ $errors->first('razon_social') }}</strong>
                        </span>
                      @endif
                    </div>
                  </div>

                  <div class="form-group{{ $errors->has('detalle') ? ' has-error' : '' }}">
                    <label for="detalle" class="col-md-4 control-label">(*) Detalle</label>

                    <div class="col-md-6">
                      <input id="detalle" type="text"
                        class="form-control"
                        name="detalle" placeholder="Detalle"
                        value="{{ old('detalle') }}" 
                        required>

                      @if ($errors->has('detalle'))
                        <span class="help-block">
                          <strong>{{ $errors->first('detalle') }}</strong>
                        </span>
                      @endif
                    </div>
                  </div>

                  <div class="form-group{{ $errors->has('corresponde_a') ? ' has-error' : '' }}">
                    <label for="corresponde_a" class="col-md-4 control-label">(*) Corresponde a</label>

                    <div class="col-md-6">
                      <select id="corresponde_a" name="corresponde_a" class="form-control">
                        <option value="" hidden="hidden">Seleccione una opción</option>
                        @foreach ($tipos_gasto as $opcion)
                          <option value="{{ $opcion }}" 
                            {{ old('corresponde_a') === $opcion ? 'selected="selected"' : '' }}>
                            {{ $opcion }}
                          </option>
                        @endforeach
                      </select>

                      @if ($errors->has('corresponde_a'))
                        <span class="help-block">
                          <strong>{{ $errors->first('corresponde_a') }}</strong>
                        </span>
                      @endif
                    </div>
                  </div>
  
                  <div class="form-group{{ $errors->has('monto') ? ' has-error' : '' }}">
                    <label for="monto" class="col-md-4 control-label">(*) Monto [Bs]</label>

                    <div class="col-md-6">
                      <input required="required" type="number" class="form-control" name="monto"
                        id="monto" step="any" min="0" placeholder="0.00"
                        value="{{ old('monto') }}">

                      @if ($errors->has('monto'))
                        <span class="help-block">
                          <strong>{{ $errors->first('monto') }}</strong>
                        </span>
                      @endif
                    </div>
                  </div>
                  
                </div>
              </div>
            </div>
        </div>
        
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success" onclick="this.disabled=true; this.form.submit()">
                <i class="fa fa-save"></i> Guardar
            </button>
          </form>
        </div>
    </div>
</div>

<script>
</script>
