@extends('layouts.master')

@section('header')
  @parent
@endsection

@section('content')

<div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">
  <div class="panel panel-skin">
    <div class="panel-heading" align="center">
      <div class="panel-title">{{ $provider ? 'Actualizar información de proveedor' : 'Agregar un proveedor' }}</div>
    </div>

    <div class="panel-body">
      <div class="mg20">
        <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
          <i class="fa fa-undo"></i>
        </a>
        <a href="{{ '/provider' }}" class="btn btn-warning" title="Volver a lista de proveedores">
          <i class="fa fa-arrow-up"></i>
        </a>
      </div>

      @include('app.session_flashed_messages', array('opt' => 1))

      <p><em>Nota.- Los campos con * son obligatorios</em></p>

      @if($provider)
        <form id="delete" action="/provider/{{ $provider->id }}" method="post">
          <input type="hidden" name="_method" value="delete">
          <input type="hidden" name="_token" value="{{ csrf_token() }}">
        </form>
        <form novalidate="novalidate" action="{{ '/provider/'.$provider->id }}" method="post">
          <input type="hidden" name="_method" value="put">
      @else
        <form novalidate="novalidate" action="{{ '/provider' }}" method="post">
      @endif
          <input type="hidden" name="_token" value="{{ csrf_token() }}">
          <div class="form-group">
            <div class="input-group">
              <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                <div class="input-group" style="width: 100%">
                  <label for="prov_name" class="input-group-addon" style="width: 23%;text-align: left">
                    Nombre: <span class="pull-right">*</span>
                  </label>

                  <input required="required" type="text" class="form-control" name="prov_name" id="prov_name"
                        value="{{ $provider ? $provider->prov_name : old('prov_name') }}"
                        placeholder="Nombre o razón social">
                </div>

                <div class="input-group" style="width: 100%">
                  <label for="nit" class="input-group-addon" style="width: 23%;text-align: left">
                    NIT: <span class="pull-right">*</span>
                  </label>

                  <input required="required" type="number" class="form-control" name="nit" id="nit" step="1" min="1"
                        value="{{ $provider ? $provider->nit : old('nit') }}" placeholder="Número de NIT">
                </div>

                <div class="input-group" style="width: 100%">
                  <label for="specialty" class="input-group-addon" style="width: 23%; text-align: left">
                        Especialidad: <span class="pull-right">*</span>
                  </label>
  
                  <select required="required" class="form-control" name="specialty" id="specialty">
                    <option value="" hidden>Indique el área de especialidad de la empresa / proveedor</option>
                    @foreach($specialtyOptions as $specialtyOption)
                    <option value="{{ $specialtyOption->specialty }}"
                            {{ ($provider && $provider->specialty == $specialtyOption->specialty) ||
                                old('specialty') == $specialtyOption->specialty ? 'selected="selected"' :
                                '' }}>{{ $specialtyOption->specialty }}</option>
                    @endforeach
                    <option value="Otro">Otro (Especificar)</option>
                  </select>
                </div>
  
                <input required="required" type="text" class="form-control" name="other_specialty" id="other_specialty"
                        placeholder="Indique el área de especialidad (*)" disabled="disabled">

                <div class="input-group" style="width: 100%">
                  <label for="phone_number" class="input-group-addon" style="width: 23%;text-align: left">
                        Teléfono 1: <span class="pull-right">*</span>
                  </label>

                  <input required="required" type="number" class="form-control" name="phone_number"
                        id="phone_number" step="1" min="1"
                        value="{{ $provider&&$provider->phone_number!=0 ? $provider->phone_number :
                                old('phone_number') }}"
                        placeholder="Número de teléfono principal">
                </div>

                <div class="input-group" style="width: 100%">
                  <label for="alt_phone_number" class="input-group-addon" style="width: 23%;text-align: left">
                        Teléfono 2:
                  </label>

                  <input required="required" type="number" class="form-control" name="alt_phone_number"
                        id="alt_phone_number" step="1" min="1"
                        value="{{ $provider&&$provider->alt_phone_number!=0 ? $provider->alt_phone_number :
                                old('alt_phone_number') }}"
                        placeholder="Número de teléfono alternativo">
                </div>

                <div class="input-group" style="width: 100%">
                  <label for="fax" class="input-group-addon" style="width: 23%;text-align: left">
                        FAX:
                  </label>

                  <input required="required" type="number" class="form-control" name="fax" id="fax" step="1" min="1"
                        value="{{ $provider&&$provider->fax!=0 ? $provider->fax : old('fax') }}" placeholder="Fax">
                </div>

                <div class="input-group" style="width: 100%">
                  <label for="address" class="input-group-addon" style="width: 23%;text-align: left">
                        Dirección: <span class="pull-right">*</span>
                  </label>

                  <textarea rows="3" required="required" class="form-control" name="address" id="address"
                        placeholder="Dirección">{{ $provider ? $provider->address : old('address') }}</textarea>
                </div>

                <div class="input-group" style="width: 100%">
                  <label for="bnk_account" class="input-group-addon" style="width: 23%;text-align: left">
                        Nro cuenta:
                  </label>

                  <input required="required" type="text" class="form-control" name="bnk_account" id="bnk_account"
                        value="{{ $provider ? $provider->bnk_account : old('bnk_account') }}"
                        placeholder="Número de cuenta">
                </div>

                <div class="input-group" style="width: 100%">
                  <label for="bnk_name" class="input-group-addon" style="width: 23%;text-align: left">
                        Banco: <span class="pull-right">*</span>
                  </label>

                  <select required="required" class="form-control" name="bnk_name" id="bnk_name">
                    <option value="" hidden>Seleccione un banco o agregue uno a la lista</option>
                    @foreach($bank_options as $bank_option)
                      <option value="{{ $bank_option->bnk_name }}"
                            {{ ($provider&&$provider->bnk_name==$bank_option->bnk_name)||
                                old('bnk_name')==$bank_option->bnk_name ? 'selected="selected"' :
                                '' }}>{{ $bank_option->bnk_name }}</option>
                    @endforeach
                    <option value="Otro">Otro</option>
                  </select>

                  {{--
                    <input required="required" type="text" class="form-control" name="bnk_name"
                        value="{{ $provider ? $provider->bnk_name : '' }}" placeholder="Banco">
                  --}}
                </div>

                <input required="required" type="text" class="form-control" name="other_bnk_name" id="other_bnk_name"
                        placeholder="Indique un banco (*)" disabled="disabled">

                <div class="input-group" style="width: 100%">
                  <label for="contact_name" class="input-group-addon" style="width: 23%;text-align: left">
                        Contacto: <span class="pull-right">*</span>
                  </label>

                  <input required="required" type="text" class="form-control" name="contact_name"
                        id="contact_name"
                        value="{{ $provider ? $provider->contact_name : old('contact_name') }}"
                        placeholder="Nombre de persona autorizada para cobro">
                </div>

                <div class="input-group" style="width: 75%">
                  <label for="contact_id_place" class="input-group-addon" style="width: 31%;text-align: left">
                        C.I. de contacto: <span class="pull-right">*</span>
                  </label>

                  <input required="required" type="number" class="form-control" name="contact_id"
                        id="contact_id" step="1" min="1"
                        value="{{ $provider&&$provider->contact_id!=0 ? $provider->contact_id : old('contact_id') }}"
                        placeholder="Número de C.I.">

                  <div class="input-group-btn" style="width:70px;">

                    <select required="required" class="form-control" name="contact_id_place" id="contact_id_place">
                      {{--
                        LP=La Paz OR=Oruro PT=Potosi CB=Cochabamba SC=Santa Cruz BN=Beni
                        PA=Pando TJ=Tarija CH=Chuquisaca
                      --}}
                      <option value="LP"
                            {{ ($provider&&$provider->contact_id_place=='LP')||old('contact_id_place')=='LP' ?
                                'selected="selected"' : '' }}>LP</option>
                      <option value="OR"
                            {{ ($provider&&$provider->contact_id_place=='OR')||old('contact_id_place')=='OR' ?
                                'selected="selected"' : '' }}>OR</option>
                      <option value="PT"
                            {{ ($provider&&$provider->contact_id_place=='PT')||old('contact_id_place')=='PT' ?
                                'selected="selected"' : '' }}>PT</option>
                      <option value="CB"
                            {{ ($provider&&$provider->contact_id_place=='CB')||old('contact_id_place')=='CB' ?
                                'selected="selected"' : '' }}>CB</option>
                      <option value="SC"
                            {{ ($provider&&$provider->contact_id_place=='SC')||old('contact_id_place')=='SC' ?
                                'selected="selected"' : '' }}>SC</option>
                      <option value="BN"
                            {{ ($provider&&$provider->contact_id_place=='BN')||old('contact_id_place')=='BN' ?
                                'selected="selected"' : '' }}>BN</option>
                      <option value="PA"
                            {{ ($provider&&$provider->contact_id_place=='PA')||old('contact_id_place')=='PA' ?
                                'selected="selected"' : '' }}>PA</option>
                      <option value="TJ"
                            {{ ($provider&&$provider->contact_id_place=='TJ')||old('contact_id_place')=='TJ' ?
                                'selected="selected"' : '' }}>TJ</option>
                      <option value="CH"
                            {{ ($provider&&$provider->contact_id_place=='CH')||old('contact_id_place')=='CH' ?
                                'selected="selected"' : '' }}>CH</option>
                    </select>

                  </div>
                </div>

                <div class="input-group" style="width: 100%">
                  <label for="contact_phone" class="input-group-addon" style="width: 23%;text-align: left">
                    Telf. contacto: <span class="pull-right">*</span>
                  </label>

                  <input required="required" type="number" class="form-control" name="contact_phone" step="1" min="1"
                        value="{{ $provider&&$provider->contact_phone!=0 ? $provider->contact_phone :
                            old('contact_phone') }}"
                        placeholder="Número de teléfono de persona de contacto">
                </div>

                <div class="input-group" style="width: 100%">
                  <label for="email" class="input-group-addon" style="width: 23%;text-align: left">
                        Email:
                  </label>

                  <input required="required" type="text" class="form-control" name="email" id="email"
                        value="{{ $provider ? $provider->email : old('email') }}" placeholder="Correo electronico">
                </div>

              </div>
            </div>
          </div>

          @include('app.loader_gif')

          <div class="form-group" align="center">
            <button type="button" {{-- type="submit" --}} class="btn btn-success"
                    onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
              <i class="fa fa-floppy-o"></i> Guardar
            </button>

            {{--
            @if($provider)
              @if($session_user->priv_level==4)
                <button type="submit" form="delete" class="btn btn-danger">
                  <i class="fa fa-trash-o"></i> Quitar
                </button>
              @endif
            @endif
            --}}
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
    var $bnk_name = $('#bnk_name'), $other_bnk_name = $('#other_bnk_name');

    $bnk_name.change(function () {
      if ($bnk_name.val()==='Otro') {
        $other_bnk_name.removeAttr('disabled').show();
      } else {
        $other_bnk_name.attr('disabled', 'disabled').val('').hide();
      }
    }).trigger('change');

    var $specialty = $('#specialty'), $other_specialty = $('#other_specialty');

    $specialty.change(function () {
      if ($specialty.val() === 'Otro') {
          $other_specialty.removeAttr('disabled').show();
      } else {
          $other_specialty.attr('disabled', 'disabled').val('').hide();
      }
    }).trigger('change');

    $(document).ready(function(){
      $("#wait").hide();
    });
  </script>
@endsection
