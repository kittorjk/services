@extends('layouts.ocs_structure')

@section('header')
  @parent
  <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
  <li><a href="#">&ensp;<i class="fa fa-money"></i> PAGOS <span class="caret"></span>&ensp;</a>
    <ul class="sub-menu">
      <li><a href="{{ '/invoice' }}"><i class="fa fa-bars"></i> Ver todo </a></li>
      <li><a href="{{ '/invoice/create' }}"><i class="fa fa-plus"></i> Agregar factura </a></li>
      @if ($user->action->oc_inv_exp /*$user->priv_level>=3*/)
        <li><a href="{{ '/excel/invoices' }}"><i class="fa fa-file-excel-o"></i> Exportar a Excel</a></li>
      @endif
    </ul>
  </li>
  <li>
    <!--<a href="/search/invoices/0"><i class="fa fa-search"></i> BUSCAR </a>-->
    <a data-toggle="modal" href="#searchBox">&ensp;<i class="fa fa-search"></i> BUSCAR&ensp;</a>
  </li>
@endsection

@section('content')
  @if ($inv_waiting_approval != 0 && $user->action->oc_inv_pmt)
    <div class="col-sm-12 mg10">
      <div class="alert alert-warning" align="center">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        <i class="fa fa-warning fa-2x pull-left"></i>
        {{--@if($user->area=='Gerencia Administrativa')--}}
            {{ $inv_waiting_approval == 1 ? 'Existe 1 factura de proveedor pendiente de pago' :
                'Existen '.$inv_waiting_approval.' facturas de proveedor pendientes de pago' }}
        {{--
        @else
            <a href="{{ '/invoice/approve' }}" style="color: inherit">
                {{ $inv_waiting_approval==1 ? 'Existe 1 factura de proveedor pendiente de aprobación' :
                  'Existen '.$inv_waiting_approval.' facturas de proveedor pendientes de aprobación' }}
            </a>
        @endif
        --}}
      </div>
    </div>
  @endif

  <div class="col-sm-12 mg10">
      @include('app.session_flashed_messages', array('opt' => 0))
  </div>

  <div class="col-sm-12 mg10 mg-tp-px-5">
    <p>Facturas encontradas: {{ $invoices->total() }}</p>

    <table class="fancy_table table_ground tablesorter" id="fixable_table">
      <thead>
        <tr>
          <th width="12%">Fecha emisión</th>
          <th>Nº factura</th>
          <th>OC</th>
          <th class="{sorter: 'thousands'}">Monto</th>
          <th>Proveedor</th>
          <th>Concepto</th>
          <th>Estado</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($invoices as $invoice)
          <tr>
            <td>{{ date_format($invoice->date_issued,'d-m-Y') }}</td>
            <td><a href="/invoice/{{ $invoice->id }}">{{ $invoice->number }}</a></td>
            <td>
              <a href="/oc/{{ $invoice->oc_id }}">{{ $invoice->oc->code }}</a>
            </td>
            <td align="right">{{ number_format($invoice->amount,2).' Bs' }}</td>
            <td>{{ $invoice->oc->provider }}</td>
            <td>
              {{ $invoice->concept == 'Adelanto' ? 'Adelanto' :
                ($invoice->concept == 'Avance' ? 'Pago contra avance' :
                ($invoice->concept == 'Entrega' ? 'Pago contra entrega' : '' )) }}
            </td>
            {{-- <td>
              {{ substr($invoice->flags, -3)=='100' ? 'Adelanto' :
                (substr($invoice->flags, -3)=='010' ? 'Pago contra avance' :
                (substr($invoice->flags, -3)=='001' ? 'Pago contra entrega' : '' )) }}
            </td> --}}
            <td>
              @if ($invoice->status == 'Pagado')
                {{ 'Pagado' }}
              @else
                @if ($user->action->oc_inv_pmt &&
                  ($user->area=='Gerencia Administrativa' && $user->priv_level >= 2) || $user->priv_level == 4)
                  <a href="/invoice/payment/{{ $invoice->id }}">{{ 'Autorizado, pago pendiente' }}</a>
                @else
                  {{ 'Autorizado, pago pendiente' }}
                @endif
              @endif
            </td>
            {{-- <td>
              @if ($invoice->flags[0] == 1)
                {{ 'Pagado' }}
              @elseif ($invoice->flags[2] == 0)
                @if (($user->priv_level == 3 && $user->area == 'Gerencia Tecnica') || $user->priv_level == 4)
                  <a href="{{ '/invoice/approve' }}">{{ 'Autorización de G. Tecnica pendiente' }}</a>
                @else
                  {{ 'Autorización de G. Tecnica pendiente' }}
                @endif
              @elseif ($invoice->flags[1] == 0)
                @if (($user->priv_level == 3 && $user->area == 'Gerencia General') || $user->priv_level == 4)
                  <a href="{{ '/invoice/approve' }}">{{ 'Autorización de G. General pendiente' }}</a>
                @else
                  {{ 'Autorización de G. General pendiente' }}
                @endif
              @else
                @if ($user->action->oc_inv_pmt
                  ($user->area=='Gerencia Administrativa'&&$user->priv_level>=2)||$user->priv_level==4)
                  <a href="/invoice/payment/{{ $invoice->id }}">{{ 'Autorizado, pago pendiente' }}</a>
                @else
                  {{ 'Autorizado, pago pendiente' }}
                @endif
              @endif
            </td> --}}
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="col-sm-12 mg10" align="center">
    {!! $invoices->appends(request()->except('page'))->render() !!}
  </div>

  <div class="col-sm-12 mg10" id="fixed">
    <table class="fancy_table table_ground" id="cloned"></table>
  </div>

  <!-- Search Modal -->
  <div id="searchBox" class="modal fade" role="dialog">
    @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'invoices','id'=>0))
  </div>
@endsection

@section('footer')
  @parent
@endsection

@section('javascript')
  <script src="{{ asset('app/js/fix_table_header.js') }}"></script> {{-- For fixed header --}}
  <script src="{{ asset('app/js/set_current_url.js') }}"></script> {{-- For recording current url --}}
  <script>
    $('#alert').delay(2000).fadeOut('slow');

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    // add parser through the tablesorter addParser method
    $.tablesorter.addParser({
      id: 'thousands', // unique id
      is: function(s) {
        // return false so this parser is not auto detected
        return false;
      },
      format: function(s) {
        // data format for normalization
        return s.replace(' Bs','').replace(/,/g,'');
      },
      type: 'numeric' // set type, either numeric or text
    });

    $(function() {
      $('#fixable_table').tablesorter({
        cssAsc: 'headerSortUp',
        cssDesc: 'headerSortDown',
        cssNone: '',
        dateFormat: 'uk'
      });
    });
  </script>
@endsection
