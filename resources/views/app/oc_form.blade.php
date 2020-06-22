@extends('layouts.master')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/custom_autocomplete.css") }}">
    <script src="{{ asset('https://cdnjs.cloudflare.com/ajax/libs/jquery.devbridge-autocomplete/1.2.27/jquery.autocomplete.js') }}"></script>

    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

<div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

    <div class="panel panel-orange">
        <div class="panel-heading" align="center">
            <div class="panel-title">
                {{ $oc ? ($action ? ($action=='reject' ? 'Rechazar Orden de Compra ' : 'Anular Orden de Compra ') :
                    'Modificar Orden de Compra ').$oc->code : 'Agregar Order de Compra' }}
            </div>
        </div>
        <div class="panel-body">
            <div class="mg20">
                <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                    <i class="fa fa-arrow-left"></i>
                </a>
                <a href="{{ '/oc' }}" class="btn btn-warning" title="Volver a lista de OCs">
                    <i class="fa fa-arrow-up"></i>
                </a>
            </div>

            @include('app.session_flashed_messages', array('opt' => 1))

            <p><em>Nota.- Los campos con * son obligatorios</em></p>

            @if($oc&&(!$action||$action=='reject_disable'))
                {{--
                <form id="delete" action="/oc/{{ $oc->id }}" method="post">
                    <input type="hidden" name="_method" value="delete">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                </form>
                --}}
                <form novalidate="novalidate" action="{{ '/oc/'.$oc->id }}" method="post">
                    <input type="hidden" name="_method" value="put">
                @elseif($action&&$action=='anular')
                    <form novalidate="novalidate" action="{{ '/oc/cancel/'.$oc->id }}" method="post">
                        <input type="hidden" name="_method" value="put">
                @elseif($action&&$action=='reject')
                    <form novalidate="novalidate" action="{{ '/oc/reject' }}" method="post">
                        <input type="hidden" name="_method" value="put">
                @else
                    <form novalidate="novalidate" action="{{ '/oc' }}" method="post">
                @endif
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        {{-- Security token to avoid repeated submissions --}}
                        <?php
                            $secret = md5(uniqid(rand(), true));
                            Session::put('oc_token', $secret); //$_SESSION['oc_token'] = $secret;
                        ?>
                        <input type="hidden" name="oc_token" value="{{ $secret }}">

                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                    <input type="hidden" name="action" value="{{ $action }}">

                                    @if (!$action || $action == 'reject_disable')
                                        <div class="input-group" style="width: 100%">
                                            <label for="assignment_id" class="input-group-addon" style="width: 23%;text-align: left"
                                                title="Enlazado al módulo de proyectos">
                                                Asignación <span class="pull-right">*</span>
                                            </label>

                                            <select required="required" class="form-control" name="assignment_id" id="assignment_id"
                                                    {{ $oc && $oc->status == 'Anulado' ? 'disabled' : '' }}>
                                                <option value="" hidden>Seleccione una asignación</option>
                                                @foreach($assignments as $assignment)
                                                    <option value="{{ $assignment->id }}"
                                                            title="{{ $assignment->name.' - '.$assignment->literal_code }}"
                                                            {{ (($oc && $oc->assignment_id == $assignment->id) ||
                                                                ($asg_id && $asg_id == $assignment->id) ||
                                                                old('assignment_id') == $assignment->id) ? 'selected="selected"' :
                                                                 '' }}>{{ str_limit($assignment->name, 70) }}</option>
                                                @endforeach
                                                {{--<option value="Otro">Otro</option>--}}
                                            </select>
                                        </div>
                                        {{--
                                        <input required="required" type="text" class="form-control" name="other_proy_name"
                                               id="other_proy_name" value="{{ old('proy_name') }}"
                                               placeholder="Nuevo proyecto"
                                               disabled="disabled">
                                               --}}

                                        <div class="input-group" style="width: 100%">
                                            <label for="proy_concept" class="input-group-addon" style="width: 23%;text-align: left">
                                                Concepto <span class="pull-right">*</span>
                                            </label>

                                            <input required="required" type="text" class="form-control" name="proy_concept"
                                                   id="proy_concept"
                                                   value="{{ $oc ? $oc->proy_concept : old('proy_concept') }}"
                                                   placeholder="Concepto"
                                                    {{ $oc && $oc->status == 'Anulado' ? 'disabled' : '' }}>
                                        </div>

                                        {{--<!--
                                        <input required="required" type="text" class="form-control" name="proy_description"
                                            value="{{ $oc ? $oc->proy_description : '' }}" placeholder="Descripción"
                                            @if($oc && $oc->status == 'Anulado'){{'disabled'}}@endif>
                                        -->--}}

                                        <textarea rows="2" required="required" class="form-control" name="proy_description"
                                                  placeholder="Descripción del proyecto (info adicional)"
                                                {{ $oc && $oc->status == 'Anulado' ? 'disabled' : '' }}
                                        >{{ $oc ? $oc->proy_description : old('proy_description') }}</textarea>

                                        {{--<!--
                                        <input required="required" type="text" class="form-control" name="provider"
                                            value="{{ $oc ? $oc->provider : '' }}" placeholder="Proveedor"
                                            @if($oc && $oc->status == 'Anulado'){{'disabled'}}@endif>
                                        -->--}}

                                        <div class="input-group" style="width: 100%">
                                            <label for="provider_name" class="input-group-addon" style="width: 23%;text-align: left">
                                                Proveedor <span class="pull-right">*</span>
                                            </label>

                                            {{--
                                            <select required="required" class="form-control" name="provider_id" id="provider_id"
                                                    {{$oc && $oc->status == 'Anulado' ? 'disabled' : ''}}>
                                                <option value="" hidden>Seleccione un proveedor de la lista</option>
                                                @foreach($providers as $provider)
                                                    <option value="{{$provider->id}}"
                                                            {{ ($oc&&$oc->provider_id==$provider->id)||
                                                                old('provider_id')==$provider->id ? 'selected="selected"' :
                                                                '' }}>{{$provider->prov_name}}</option>
                                                @endforeach
                                            </select>
                                            --}}

                                            <input required="required" type="text" class="form-control" name="provider_name"
                                                    id="provider_name" {{$oc && $oc->status == 'Anulado' ? 'disabled' : ''}}
                                                    value="{{ $oc && $oc->provider_record ? $oc->provider_record->prov_name : old('provider_name') }}"
                                                    placeholder="Proveedor">
                                        </div>

                                        <div class="input-group" style="width: 100%">
                                          <label for="type" class="input-group-addon" style="width: 23%;text-align: left">
                                              Tipo de OC <span class="pull-right">*</span>
                                          </label>

                                          <select required="required" class="form-control" name="type" id="type"
                                                  {{$oc && $oc->status === 'Anulado' ? 'disabled' : ''}}>
                                              <option value="" hidden>Seleccione el tipo de OC</option>
                                              <option value="Servicio" 
                                                {{ ($oc && $oc->type == 'Servicio') || old('type') == 'Servicio' ? 'selected="selected"' : '' }}>
                                                Servicio
                                              </option>
                                              <option value="Compra de material" 
                                                {{ ($oc && $oc->type == 'Compra de material') || old('type') == 'Compra de material' ? 'selected="selected"' : '' }}>
                                                Compra de material
                                              </option>
                                          </select>
                                        </div>

                                        <div class="input-group" style="width: 100%">
                                            <label for="delivery_place" class="input-group-addon" style="width: 23%;text-align: left">
                                                Entregar en
                                            </label>

                                            <input required="required" type="text" class="form-control" name="delivery_place"
                                                   id="delivery_place"
                                                   value="{{ $oc ? $oc->delivery_place : old('delivery_place') }}"
                                                   placeholder="Lugar de entrega"
                                                    {{ $oc && $oc->status == 'Anulado' ? 'disabled' : '' }}>
                                        </div>

                                        <div class="input-group" style="width: 75%">
                                            <span class="input-group-addon" style="width:31%;text-align: left">Plazo de entrega</span>
                                            <input required="required" type="number" class="form-control" name="delivery_term"
                                                   step="1" min="1" placeholder="1"
                                                   value="{{ $oc && $oc->delivery_term != 0 ? $oc->delivery_term :
                                                        old('delivery_term') }}"
                                                   {{ $oc && $oc->status == 'Anulado' ? 'disabled' : '' }}>
                                            <span class="input-group-addon" style="width:55px">días</span>
                                        </div>
                                    @endif

                                    @if ($action && $action == 'cmp')
                                        <div class="input-group" style="width:75%">
                                            <label for="link_id" class="input-group-addon" style="width:31%; text-align: left">
                                                Número OC <span class="pull-right">*</span>
                                            </label>
                                            <input required="required" type="number" class="form-control" name="link_id"
                                               id="link_id" step="1" min="1" placeholder="00000" value="{{ old('link_id') }}">
                                        </div>

                                        <input type="hidden" name="action" value="cmp">
                                    @endif

                                    @if(!$action||($action!='anular'&&$action!='reject'))
                                        <div class="input-group" style="width: 75%">
                                            <span class="input-group-addon" style="width:31%;text-align: left"
                                                title="Monto asignado a esta OC">
                                                Monto <span class="pull-right">*</span>
                                            </span>

                                            <input required="required" type="number" class="form-control" name="oc_amount"
                                                   step="any" min="0" placeholder="0.00"
                                                   value="{{ $oc ? $oc->oc_amount : old('oc_amount') }}"
                                                   {{ $oc && $oc->status == 'Anulado' ? 'disabled' : '' }}>
                                            <span class="input-group-addon" style="width:55px">Bs</span>
                                        </div>
                                    @endif

                                    @if (!$action || $action == 'reject_disable')
                                        @if ($oc && $user->priv_level == 4)
                                            <div class="input-group" style="width: 75%">
                                                <span class="input-group-addon" style="width:31%;text-align: left"
                                                    title="Monto ejecutado hasta la fecha">
                                                    M. ejecutado
                                                </span>
                                                <input required="required" type="number" class="form-control" name="executed_amount"
                                                       step="any" min="0" placeholder="0.00"
                                                       value="{{ $oc->executed_amount!=0 ? $oc->executed_amount :
                                                            old('executed_amount') }}"
                                                       {{ $oc && $oc->status == 'Anulado' ? 'disabled' : '' }}>
                                                <span class="input-group-addon" style="width:55px">Bs</span>
                                            </div>
                                        @endif

                                        <div class="input-group" style="width: 100%">
                                            <label for="percentages" class="input-group-addon" style="width: 23%;text-align: left"
                                                title="Términos de pago (porcentajes)">
                                                % de pago <span class="pull-right">*</span>
                                            </label>

                                            <select required="required" class="form-control" name="percentages" id="percentages"
                                                    {{ $oc && $oc->status == 'Anulado' ? 'disabled' : '' }}>
                                                <option value="" hidden>Seleccione los porcentajes de pago</option>
                                                @foreach ($percentages as $percentage)
                                                    <option value="{{ $percentage->percentages }}"
                                                            {{ ($oc && $oc->percentages == $percentage->percentages) ||
                                                                old('percentages') == $percentage->percentages ?
                                                                'selected="selected"' : '' }}
                                                    >{{ str_replace('-','% - ',$percentage->percentages).'%' }}</option>
                                                @endforeach
                                                <option value="Otro">Otro</option>
                                            </select>
                                        </div>
                                        <input required="required" type="text" class="form-control" name="other_percentages"
                                               id="other_percentages" value="{{ old('percentages') }}"
                                               placeholder="Especifique los porcentajes de pago sin espacios ni símbolos (xx-xx-xx)"
                                               disabled="disabled">

                                        <div class="input-group" style="width: 100%">
                                            <label for="client" class="input-group-addon" style="width: 23%;text-align: left">
                                                Cliente <span class="pull-right">*</span>
                                            </label>

                                            <select required="required" class="form-control" name="client" id="client"
                                                    {{ $oc && $oc->status == 'Anulado' ? 'disabled' : '' }}>
                                                <option value="" hidden>Seleccione un cliente</option>
                                                @foreach ($clients as $client)
                                                    <option value="{{ $client->client }}"
                                                            {{ ($oc && $oc->client == $client->client) ||
                                                                old('client') == $client->client ? 'selected="selected"' :
                                                                '' }}>{{ $client->client }}</option>
                                                @endforeach
                                                <option value="Otro">Otro</option>
                                            </select>
                                        </div>
                                        <input required="required" type="text" class="form-control" name="other_client"
                                               id="other_client" value="{{ old('client') }}" placeholder="Nuevo cliente"
                                               disabled="disabled">

                                        <div class="input-group" style="width: 100%">
                                            <label for="client_oc" class="input-group-addon" style="width: 23%;text-align: left">
                                                Orden de cliente
                                            </label>

                                            <input required="required" type="text" class="form-control" name="client_oc"
                                                   id="client_oc" placeholder="Código de orden de compra de cliente"
                                                   value="{{ $oc && $oc->client_oc ? $oc->client_oc : old('client_oc') }}"
                                                    {{ $oc && $oc->status == 'Anulado' ? 'disabled' : '' }}>
                                        </div>

                                        <div class="input-group" style="width: 100%">
                                            <label for="client_ad" class="input-group-addon" style="width: 23%;text-align: left">
                                                Asignación
                                            </label>

                                            <input required="required" type="text" class="form-control" name="client_ad"
                                                   id="client_ad" placeholder="Código de documento de asignación de cliente"
                                                   value="{{ $oc ? $oc->client_ad : old('client_ad') }}"
                                                   {{ $oc && $oc->status == 'Anulado' ? 'disabled' : '' }}>
                                        </div>

                                        <div class="input-group" style="width: 100%">
                                            <label for="pm_id" class="input-group-addon" style="width: 23%;text-align: left">
                                                Responsable
                                            </label>

                                            <select required="required" class="form-control" name="pm_id" id="pm_id"
                                                    {{ $oc && $oc->status == 'Anulado' ? 'disabled' : '' }}>
                                                <option value="">Seleccione un responsable por parte de ABROS</option>
                                                @foreach ($pm_candidates as $pm_candidate)
                                                    <option value="{{ $pm_candidate->id }}"
                                                            {{ ($oc && $oc->pm_id == $pm_candidate->id) ||
                                                                old('pm_id') == $pm_candidate->id ? 'selected="selected"' :
                                                                '' }}>{{ $pm_candidate->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    @if ($action && ($action == 'anular' || $action == 'reject'))
                                        <textarea rows="3" required="required" class="form-control" name="observations"
                                                id="observations" placeholder="{{ 'Motivo para '.($action=='reject' ?
                                                 'rechazar' : 'anular').' la OC *' }}">{{ $oc ?
                                                 $oc->observations : old('observations') }}</textarea>
                                    @endif

                                    @if ($action && $action == 'reject')
                                        <input type="hidden" name="id" value="{{ $oc->id }}">
                                    @endif

                                    @if (!$action && $oc && $user->action->oc_nll /*$user->priv_level==4*/)
                                        <div class="input-group" style="width: 100%">
                                            <label for="status" class="input-group-addon" style="width: 23%;text-align: left">
                                                Estado
                                            </label>

                                            <select required="required" class="form-control" name="status" id="status">
                                              <option value="Creado"
                                                  {{ ($oc && $oc->status == 'Creado') || old('status') == 'Creado' ?
                                                      'selected="selected"' : '' }}>Activa</option>
                                              <option value="Anulado"
                                                  {{ ($oc && $oc->status == 'Anulado') || old('status') == 'Anulado' ?
                                                      'selected="selected"' : '' }}>Anulada</option>
                                            </select>
                                        </div>
                                    @endif
                                </span>
                            </div>
                        </div>

                        @include('app.loader_gif')

                        <div class="form-group" align="center">
                            @if ($action && ($action == 'anular' || $action == 'reject'))
                                <button type="button" class="btn btn-danger" {{-- previously type="submit" --}}
                                        onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                                    <i class="fa fa-minus-circle"></i>
                                    {{ $action && $action == 'anular' ? 'Anular' :
                                        ($action && $action == 'reject' ? 'Rechazar' : 'Guardar') }}
                                </button>
                            @else
                                <button type="button" class="btn btn-success" {{-- previously type="submit" --}}
                                        onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                                    <i class="fa fa-floppy-o"></i> Guardar
                                </button>
                            @endif
                            {{--
                                @if($oc&&!$action&&$user->priv_level==4)
                                    <button type="submit" form="delete" class="btn btn-danger">
                                        <i class="fa fa-trash-o"></i> Quitar
                                    </button>
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
    <script src="{{ asset('app/js/prevent_multiple_submissions.js') }}"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        /*
        var $proy_name = $('#proy_name'), $other_proy_name = $('#other_proy_name');
        $proy_name.change(function () {
            if ($proy_name.val() === 'Otro') {
                $other_proy_name.removeAttr('disabled').show();
            } else {
                $other_proy_name.attr('disabled', 'disabled').val('').hide();
            }
        }).trigger('change');
        */

        var $client = $('#client'), $other_client = $('#other_client');
        $client.change(function () {
            if ($client.val() === 'Otro') {
                $other_client.removeAttr('disabled').show();
            } else {
                $other_client.attr('disabled', 'disabled').val('').hide();
            }
        }).trigger('change');

        var $percentages = $('#percentages'), $other_percentages = $('#other_percentages');
        $percentages.change(function () {
            if ($percentages.val() === 'Otro') {
                $other_percentages.removeAttr('disabled').show();
            } else {
                $other_percentages.attr('disabled', 'disabled').val('').hide();
            }
        }).trigger('change');

        $(document).ready(function(){
            $("#wait").hide();
        });

        var $link = $('#link'), //$new_pass = $('#new_pass'), $confirm_pass = $('#confirm_pass'),
            /*$link_container = $('.link_container'),*/ $input_group = $('.input-group');

        $link.click(function () {
            if ($link.prop('checked')) {
                $input_group.hide();
                //$to_link.show();
                //$new_pass.removeAttr('disabled').show();
                //$confirm_pass.removeAttr('disabled').show();
            } else {
                $input_group.show();
                //$new_pass.attr('disabled', 'disabled').hide();
                //$confirm_pass.attr('disabled', 'disabled').hide();
            }
        }).trigger('click');

        $('#provider_name').autocomplete({
            type: 'post',
            serviceUrl: '/autocomplete/providers',
            dataType: 'JSON'
        });

        //onSelect: check_existence
    </script>
@endsection
