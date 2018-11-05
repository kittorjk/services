<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 01/02/2017
 * Time: 04:13 PM
 */
?>

<style>
    .modal-footer {
        background-color: #f4f4f4;
    }
</style>

<div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content" style="overflow:hidden;">

        <div class="modal-header alert-info">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">{{ 'Agregar o modificar item - '.$oc->code }}</h4>
        </div>

        <div class="modal-body">

          <form novalidate="novalidate" id="rowForm" action="{{ '/oc_row' }}" method="post">
            <input type="hidden" id="_method" name="_method" value="post">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">

            {{-- @include('app.session_flashed_messages', array('opt' => 1)) --}}

            <div class="panel-body" align="center">

              <div class="form-group">
                  <div class="input-group">
                        <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>
                            <div class="input-group" style="width: 62%">
                                <label for="oc_id" class="input-group-addon" style="width: 37%;text-align: right">
                                    OC-
                                </label>

                                <input required="required" type="number" class="form-control" name="oc_id"
                                        id="oc_id" step="1" min="1" placeholder="00000"
                                        value="{{ $oc ? $oc->id : old('oc_id') }}"
                                        readonly="readonly">
                            </div>

                            <div class="input-group" style="width: 62%">
                                <label for="num_order" class="input-group-addon" style="width: 37%;text-align: left">
                                    Posición
                                </label>

                                <input required="required" type="number" class="form-control" name="num_order"
                                        id="num_order" step="1" min="1" placeholder="Posición"
                                        value="{{ old('num_order') }}">
                            </div>

                            <div class="input-group" style="width: 100%">
                                <label for="description" class="input-group-addon" style="width: 23%;text-align: left">
                                    Descripción <span class="pull-right">*</span>
                                </label>

                                <input required="required" type="text" class="form-control" name="description"
                                        id="description" value="{{ old('description') }}"
                                        placeholder="Descripción">
                            </div>

                            <div class="input-group" style="width: 62%">
                                <label for="qty" class="input-group-addon" style="width: 37%;text-align: left">
                                    Cantidad
                                </label>

                                <input required="required" type="number" class="form-control" name="qty"
                                        id="qty" step="any" min="0" placeholder="0.00"
                                        value="{{ old('qty') }}">
                            </div>

                            <div class="input-group" style="width: 100%">
                                <label for="units" class="input-group-addon" style="width: 23%;text-align: left">
                                    Unidades <span class="pull-right">*</span>
                                </label>

                                <input required="required" type="text" class="form-control" name="units"
                                        id="units" value="{{ old('units') }}"
                                        placeholder="Unidades">
                            </div>

                            <div class="input-group" style="width: 62%">
                                <label for="unit_cost" class="input-group-addon" style="width: 37%;text-align: left">
                                    Precio unitario
                                </label>

                                <input required="required" type="number" class="form-control" name="unit_cost"
                                        id="unit_cost" step="any" min="0" placeholder="0.00"
                                        value="{{ old('unit_cost') }}">

                                <span class="input-group-addon">Bs</span>
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
