@extends('layouts.ocs_structure')

@section('header')
  @parent
  <link rel="stylesheet" href="{{ asset("app/css/info_tabs.css") }}">
  <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
  <li><a href="#">&ensp;<i class="fa fa-truck"></i> PROVEEDORES <span class="caret"></span>&ensp;</a>
    <ul class="sub-menu">
      <li><a href="{{ '/provider' }}"><i class="fa fa-list fa-fw"></i> Ver todo </a></li>
      <li><a href="{{ '/provider/create' }}"><i class="fa fa-plus fa-fw"></i> Agregar proveedor </a></li>
      <li><a href="{{ '/provider/incomplete' }}"><i class="fa fa-list fa-fw"></i> Lista de registros incompletos </a></li>
    </ul>
  </li>
@endsection

@section('content')

<div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

  <div class="panel panel-skin" >
    <div class="panel-heading" align="center">
      <div class="panel-title">
        <div class="pull-left">
          <ul class="nav nav-tabs">
            <li class="active"><a href="#details" data-toggle="tab"> Información de proveedor</a></li>
            @if(($user->area == 'Gerencia Tecnica' && $user->priv_level == 2) || $user->priv_level >= 4)
              <li><a href="#payments" data-toggle="tab"> Estado de pagos</a></li>
            @endif
          </ul>
        </div>
        <div class="clearfix"></div>
      </div>
      <!--<div class="panel-title">Información de proveedor</div>-->
    </div>
    <div class="panel-body">

      <div class="tab-content">

        <div class="tab-pane fade in active" id="details">

          <div class="col-lg-6 mg20">
            <a href="#" onclick="history.back();" class="btn btn-warning">
              <i class="fa fa-arrow-circle-left"></i> Volver
            </a>
            <a href="{{ '/provider' }}" class="btn btn-warning" title="Ir a la tabla de proveedores">
              <i class="fa fa-arrow-circle-up"></i> Proveedores
            </a>
          </div>

          @include('app.session_flashed_messages', array('opt' => 1))

          <div class="col-sm-12 mg10 mg-tp-px-10">
            <table class="table table-striped table-hover table-bordered">
              <thead>
                <tr>
                  <th width="40%">Nombre o razón social:</th>
                  <td>{{ $provider->prov_name }}</td>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <th>NIT</th>
                  <td>{{ $provider->nit ? $provider->nit : '' }}</td>
                </tr>
                <tr>
                  <th>Área de especialidad</th>
                  <td>{{ $provider->specialty ? $provider->specialty : '' }}</td>
                </tr>
                <tr>
                  <th>Dirección:</th>
                  <td>{{ $provider->address }}</td>
                </tr>
                <tr><td colspan="2"> </td></tr>
                <tr>
                  <th colspan="2">Teléfono(s)</th>
                </tr>
                <tr>
                  <td>Principal</td>
                  <td>{{ $provider->phone_number ? $provider->phone_number : '' }}</td>
                </tr>
                @if($provider->alt_phone_number)
                  <tr>
                    <td>Alternativo</td>
                    <td>{{ $provider->alt_phone_number }}</td>
                  </tr>
                @endif
                @if($provider->fax)
                  <tr>
                    <td>Fax</td>
                    <td>{{ $provider->fax }}</td>
                  </tr>
                @endif
                <tr><td colspan="2"> </td></tr>
                <tr>
                  <th colspan="2">Información de cuenta:</th>
                </tr>
                <tr>
                  <td>Número de cuenta:</td>
                  <td>{{ $provider->bnk_account }}</td>
                </tr>
                <tr>
                  <td>Banco:</td>
                  <td>{{ $provider->bnk_name }}</td>
                </tr>
                <tr><td colspan="2"> </td></tr>
                <tr>
                  <th colspan="2">Persona autorizada para cobro:</th>
                </tr>
                <tr>
                  <td>Nombre</td>
                  <td>{{ $provider->contact_name }}</td>
                </tr>
                <tr>
                  <td>Documento de identificación</td>
                  <td>
                    {{ $provider->contact_id ? $provider->contact_id.' '.$provider->contact_id_place : '' }}
                  </td>
                </tr>
                <tr>
                  <td>Teléfono</td>
                  <td>{{ $provider->contact_phone ? $provider->contact_phone : '' }}</td>
                </tr>
                @if($provider->email)
                  <tr>
                    <td>Correo electrónico</td>
                    <td><a href="mailto:{{ $provider->email }}">{{ $provider->email }}</a></td>
                  </tr>
                @endif
              </tbody>
            </table>
          </div>

          @if($user->action->oc_prv_edt /*(($user->area=='Gerencia Tecnica'||
                $user->area=='Gerencia General')&&$user->priv_level==2)||$user->priv_level>=3*/)
            <div class="col-sm-12 mg10" align="center">
              <a href="{{ '/provider/'.$provider->id.'/edit' }}" class="btn btn-success">
                <i class="fa fa-pencil-square-o"></i> Modificar / Actualizar datos
              </a>
            </div>
          @endif

        </div>

        @if(($user->area == 'Gerencia Tecnica' && $user->priv_level == 2) || $user->priv_level >= 4)
          <div class="tab-pane fade" id="payments">

            <div class="col-lg-6 mg20">
              <a href="#" onclick="history.back();" class="btn btn-warning">
                <i class="fa fa-arrow-circle-left"></i> Volver
              </a>
              <a href="{{ '/provider' }}" class="btn btn-warning" title="Ir a la tabla de proveedores">
                <i class="fa fa-arrow-circle-up"></i> Proveedores
              </a>
            </div>

            <div class="col-sm-12 mg10 mg-tp-px-10">
              <table class="table table-striped table-hover table-bordered">
                <thead>
                  <tr>
                    <th width="40%">Nombre o razón social:</th>
                    <td>{{ $provider->prov_name }}</td>
                  </tr>
                </thead>
                <tbody>
                  <tr><th colspan="4"></th></tr>
                  <tr>
                    <th colspan="4">Pagos pendientes:</th>
                  </tr>
                  <tr>
                    <th colspan="4">
                      <table class="table table-bordered table-striped">
                        <tr>
                          <td width="20%">OC</td>
                          <td width="25%">Saldo [Bs]</td>
                          <td>Última actividad</td>
                        </tr>
                        @if($ocs->count() == 0)
                          <tr>
                            <td colspan="3" align="center">
                              No existen pagos pendientes a este proveedor
                            </td>
                          </tr>
                        @else
                          @foreach($ocs as $oc)
                            <tr>
                              <td>
                                <a href="/oc/{{ $oc->id }}" title="Ver orden de compra">
                                  {{ $oc->code }}
                                </a>
                              </td>
                              <td align="right" style="padding-right: 15px">
                                {{ number_format(
                                    ($oc->executed_amount!=0 ? $oc->executed_amount : $oc->oc_amount)
                                    -$oc->payed_amount,2).' Bs' }}
                              </td>
                              <td>
                                @foreach($oc->invoices as $invoice)
                                  @if($invoice == $oc->invoices->sortBy('updated_at')->last())
                                    {{ date_format($invoice->updated_at,'d-m-Y') }}
                                    &ensp;
                                    {{ $invoice->concept == 'Adelanto' ? 'Adelanto' :
                                      ($invoice->concept == 'Avance' ? 'Pago contra avance' :
                                      ($invoice->concept == 'Entrega' ? 'Pago contra entrega' : '' )) }}
                                    {{ $invoice->status == 'Pagado' ? ' (Pendiente)' : ' (Completado)' }}
                                    {{-- substr($invoice->flags,-3) == '100' ? 'Adelanto' :
                                        (substr($invoice->flags,-3) == '010' ? 'Pago contra avance' :
                                        (substr($invoice->flags,-3) == '001' ? 'Pago contra entrega' :
                                        '' )) --}}
                                    {{-- $invoice->flags[0] == 0 ? ' (Pendiente)' : ' (Completado)' --}}
                                  @endif
                                @endforeach
                              </td>
                            </tr>
                          @endforeach
                        @endif
                      </table>
                    </th>
                  </tr>
                </tbody>
              </table>
            </div>

            @if($ocs->count() != 0)
              <div class="col-sm-12 mg10" align="center">
                <a href="{{ '/invoice/create' }}" class="btn btn-success">
                  <i class="fa fa-plus"></i> Agregar factura
                </a>
              </div>
            @endif

          </div>
        @endif

      </div>
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
  </script>
@endsection
