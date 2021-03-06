@extends('layouts.master')

@section('header')
  @parent
  <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')
  <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">
    <div class="panel panel-ground">
      <div class="panel-heading" align="center">
        <div class="panel-title">
          {{ $invoice ? 'Actualizar datos - Factura '.$invoice->number : 'Agregar factura de proveedor' }}
        </div>
      </div>
      <div class="panel-body">
        <div class="mg20">
          <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
            <i class="fa fa-arrow-left"></i>
          </a>
          <a href="{{ '/invoice' }}" class="btn btn-warning" title="Ir a la lista de facturas">
            <i class="fa fa-arrow-up"></i>
          </a>
        </div>

        @include('app.session_flashed_messages', array('opt' => 1))

        <p><em>Nota.- Los campos con * son obligatorios</em></p>

        @if($invoice)
          <form novalidate="novalidate" action="{{ '/invoice/'.$invoice->id }}" method="post">
            <input type="hidden" name="_method" value="put">
        @else
          <form novalidate="novalidate" action="{{ '/invoice' }}" method="post">
        @endif
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                  {{-- @if(!$invoice||($invoice&&$user->id==$invoice->user_id)||$user->priv_level==4) --}}
                  <div class="input-group" style="width: 100%">
                    <label for="oc_id" class="input-group-addon" style="width: 23%;text-align: left">
                      Orden: <span class="pull-right">*</span>
                    </label>

                    <select required="required" class="form-control" name="oc_id" id="oc_id"
                            {{ $invoice ? 'disabled="disabled"' : '' }}>
                      <option value="" hidden>Seleccione una orden de compra</option>
                      @foreach($ocs as $oc)
                        <option value="{{ $oc->id }}"
                          {{ ($invoice && $invoice->oc_id == $oc->id) || ($ps_id && $ps_id == $oc->id) ||
                              old('oc_id') == $oc->id ? 'selected="selected"' : '' }}>{{ $oc->code
                            /*'OC-'.str_pad($oc->id, 5, "0", STR_PAD_LEFT)*/ }}</option>
                      @endforeach
                    </select>
                  </div>

                  <div class="input-group" style="width: 100%" id="oc_values" align="center"></div>

                  <div class="input-group" style="width: 100%">
                    <label for="number" class="input-group-addon" style="width: 23%;text-align: left">
                      Factura: <span class="pull-right">*</span>
                    </label>

                    <input required="required" type="text" class="form-control" name="number"
                          id="number" value="{{ $invoice ? $invoice->number : old('number') }}"
                          placeholder="Número de factura">
                  </div>

                  <div class="input-group" style="width: 75%">
                    <span class="input-group-addon" style="width:31%;text-align: left">
                      Monto: <span class="pull-right">*</span>
                    </span>

                    <input required="required" type="number" class="form-control" name="amount"
                            id="amount" step="any" min="0" placeholder="Monto facturado"
                            value="{{ $invoice ? $invoice->amount : old('amount') }}">

                    <span class="input-group-addon">Bs.</span>
                  </div>

                  <div class="input-group" style="width:50%;">
                    <label for="date_issued" class="input-group-addon" style="text-align: left">
                      Fecha de emisión: *
                    </label>

                    <span class="input-group-addon" style="text-align: right">
                        <input type="date" name="date_issued" id="date_issued" step="1"
                            min="2014-01-01" max="{{ date("Y-m-d") }}"
                            value="{{ $invoice ? $invoice->date_issued : (old('date_issued') ?: date("Y-m-d")) }}">
                    </span>
                  </div>

                  {{-- @if(!$invoice||($invoice&&$user->id==$invoice->user_id)||$user->priv_level==4) --}}
                  <div class="input-group" style="width: 100%">
                    <label for="concept" class="input-group-addon" style="width: 23%;text-align: left">
                      Motivo: <span class="pull-right">*</span>
                    </label>

                    <select required="required" class="form-control" name="concept" id="concept">
                      <option value="" hidden>Seleccione el motivo de la factura</option>
                      <option value="{{ 'Adelanto' }}"
                          {{ ($invoice && $invoice->concept == "Adelanto") ||
                              old('concept') == 'Adelanto' ?
                                'selected="selected"' : '' }}>{{ 'Pago por adelanto' }}</option>
                      <option value="{{ 'Avance' }}"
                          {{ ($invoice && $invoice->concept == "Avance") ||
                              old('concept') == 'Avance' ?
                                'selected="selected"' : '' }}>{{ 'Pago contra avance' }}</option>
                      <option value="{{ 'Entrega' }}"
                          {{ ($invoice && $invoice->concept == "Entrega") ||
                              old('concept') == 'Entrega' ?
                                'selected="selected"' : '' }}>{{ 'Pago contra entrega' }}</option>
                    </select>
                  </div>

                  <div class="input-group" style="width: 100%" id="certification_select">
                    <label for="oc_certification_id" class="input-group-addon" style="width: 23%;text-align: left">
                      Certificado: <span class="pull-right">*</span>
                    </label>

                    <select required="required" class="form-control" name="oc_certification_id" id="oc_certification_id">
                      <option value="" hidden>Seleccione un certificado</option>
                      {{-- @foreach($oc_certifications as $oc_certification)
                        <option value="{{ $oc_certificaction->id }}"
                          {{ ($invoice && $invoice->oc_certification_id == $oc_certification->id) ||
                              old('oc_certification_id') == $oc_certification->id ? 'selected="selected"' : '' }}>{{ $oc_certification->code }}</option>
                      @endforeach --}}
                    </select>
                  </div>

                  <textarea rows="3" required="required" class="form-control" name="detail"
                            id="detail" placeholder="Información adicional">{{ $invoice ?
                              $invoice->detail : old('detail') }}</textarea>

                </span>
              </div>
            </div>

            @include('app.loader_gif')

            <div class="form-group" align="center">
              <button type="submit" class="btn btn-success"
                      onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                <i class="fa fa-floppy-o"></i> Guardar
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
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    function show_oc_values() {
      if ($("#oc_id").val() && $("#amount").val() && $("#concept").val() && $("#oc_certification_id").val()) {
        $.post('/load_oc_values', { oc_id: $("#oc_id").val(), amount: $("#amount").val(), concept: $("#concept").val(), cert: $("#oc_certification_id").val() },
        function(data) {
          $("#oc_values").html(data).show();
        });
      }
    }

    $(document).ready(function() {
      $("#wait").hide();
      $("#oc_values").hide();
      // $("#certification_select").hide();

      show_oc_values();

      // Cargar certificados
      var ps_id = {!! json_encode($ps_id) !!};
      var oc_stored = {!! json_encode($invoice) !!};
      var oc_id_stored = oc_stored ? oc_stored.oc_id : null;

      $.post('/load_oc_certificates', { oc_id: $("oc_id").val() || ps_id || oc_id_stored }, function(data) {
        $("#oc_certification_id").html(data);
      });

      /*
      if ($("#concept").val() === 'Avance' || $("#concept").val() === 'Entrega') {
        $("#certification_select").show();
      } else {
        $("#certification_select").hide();
      }
      */
    });

    $("#oc_id").change(function () {
      $("#oc_id option:selected").each(function () {
        /*
        if ($("#oc_id").val() && $("#amount").val() && $("#concept").val()) {
          $.post('/load_oc_values', { oc_id: $(this).val(), amount: $("#amount").val(),
            concept: $("#concept").val() }, function(data) {
            $("#oc_values").html(data).show();
          });
        }
        */
        show_oc_values();

        // Cargar certificados
        $.post('/load_oc_certificates', { oc_id: $(this).val() }, function(data) {
          $("#oc_certification_id").html(data);
        });
      });
    });

    $("#concept").change(function () {
      $("#concept option:selected").each(function () {
        /*
        if ($("#oc_id").val() && $("#amount").val() && $("#concept").val()) {
          $.post('/load_oc_values', { oc_id: $("#oc_id").val(), amount: $("#amount").val(),
            concept: $("#concept").val() }, function(data) {
            $("#oc_values").html(data).show();
          });
        }
        */
        show_oc_values();

        /*
        if ($("#concept").val() === 'Avance' || $("#concept").val() === 'Entrega') {
          $("#certification_select").show();
        } else {
          $("#certification_select").hide();
        }
        */
      });
    });

    $("#amount").keyup(function() {
      /*
      if ($("#oc_id").val() && $("#amount").val() && $("#concept").val()) {
        $.post('/load_oc_values', { oc_id: $("#oc_id").val(), amount: $("#amount").val(),
          concept: $("#concept").val() }, function(data) {
          $("#oc_values").html(data);
        });
      }
      */
      show_oc_values();
    });

    $("#oc_certification_id").change(function () {
      $("#oc_certification_id option:selected").each(function () {
        show_oc_values();
      });
    });
  </script>
@endsection
