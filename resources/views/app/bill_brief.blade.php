@extends('layouts.projects_structure')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    @include('app.project_navigation_button', array('user'=>$user))
    <div class="btn-group">
        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
            <i class="fa fa-bars"></i> Facturas <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li><a href="{{ '/bill' }}"><i class="fa fa-refresh fa-fw"></i> Recargar página</a></li>
            <li><a href="{{ '/bill/create' }}"><i class="fa fa-plus fa-fw"></i> Agregar factura</a></li>
            <li><a href="{{ '/bill?stat=0' }}"><i class="fa fa-check fa-fw"></i> Ver facturas pendientes</a></li>
            <li><a href="{{ '/bill?stat=1' }}"><i class="fa fa-check fa-fw"></i> Ver facturas cobradas</a></li>
            @if($user->action->prj_bill_exp /*$user->priv_level>=3*/)
                <li class="divider"></li>
                <li><a href="{{ '/excel/bills' }}"><i class="fa fa-file-excel-o fa-fw"></i> Exportar a Excel</a></li>
                @if($user->priv_level==4)
                    <li><a href="{{ '/excel/bill_order' }}"><i class="fa fa-file-excel-o fa-fw"></i> Exportar linked</a></li>
                @endif
            @endif
        </ul>
    </div>
    @if($user->priv_level>=2)
        <!--<a href="/search/bills/0" class="btn btn-primary"><i class="fa fa-search"></i> Buscar </a>-->
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchBox">
            <i class="fa fa-search"></i> Buscar
        </button>
    @endif
@endsection

@section('content')
    <div class="col-sm-12 mg10 mg-tp-px-10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10 mg-tp-px-10">

        <p>{{ $bills->total()==1 ? 'Se encontró 1 factura' : 'Se encontraron '.$bills->total().' facturas' }}</p>

        <table class="fancy_table table_blue tablesorter" id="fixable_table">
            <thead>
            <tr>
                <th width="14%">Número de factura</th>
                <th width="12%">Fecha de emisión</th>
                <th width="14%" class="{sorter: 'thousands'}">Monto</th>
                <th width="14%">Estado</th>
                <th width="32%">Ordenes asociadas</th>
                <th width="14%">Acciones</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($bills as $bill)
                <tr>
                    <td>
                        @if($bill->status==0)
                            <a href="/bill_upstat/{{ $bill->id }}" class="pull-left confirmation" style="color:inherit">
                                <i class="fa fa-dollar" title="Marcar como cobrado"></i>
                            </a>
                        @else
                            <i class="fa fa-dollar pull-left" title="Cobrado" style="color:limegreen;"></i>
                        @endif
                        &emsp;
                        <a href="/bill/{{ $bill->id }}">{{ $bill->code }}</a>
                    </td>
                    <td align="center">{{ date_format(new \DateTime($bill->date_issued), 'd/m/Y') }}</td>
                    <td align="right">{{ number_format($bill->billed_price,2).' Bs' }}</td>
                    <td align="center">{{ $bill->status==0 ? 'Pendiente' : 'Cobrada' }}</td>
                    <td>
                        <?php $comma_flag = 0; ?>
                        @foreach($bill->orders as $order)
                            {{ $comma_flag!=0 ? ', ' : '' }}
                            <a href="/order/{{ $order->id }}">{{ $order->type.'-'.$order->code }}</a>
                            <?php $comma_flag++ ?>
                        @endforeach
                        {{ $comma_flag==0 ? 'Ésta factura no está asociada a ninguna orden' : '' }}
                    </td>
                    <td>
                        @if($bill->status==0||$user->priv_level==4)
                            <a href="/join/bill-to-order/{{ $bill->id }}"><i class="fa fa-link"></i> Asociar orden</a>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-sm-12 mg10" align="center">
        {!! $bills->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="fancy_table table_blue" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'bills','id'=>0))
    </div>

@endsection

@section('footer')
    @parent
@endsection

@section('javascript')
    <script src="{{ asset('app/js/fix_table_header.js') }}"></script> {{-- For fixed header --}}
    <script>
        $('#alert').delay(2000).fadeOut('slow');

        // add parser through the tablesorter addParser method
        $.tablesorter.addParser({
            id: 'thousands', // unique id
            is: function() {
                // return false so this parser is not auto detected
                return false;
            },
            format: function(s) {
                // data format for normalization
                return s.replace(' Bs','').replace(/,/g,'');
            },
            type: 'numeric' // set type, either numeric or text
        });

        $(function(){
            $('#fixable_table').tablesorter({
                cssAsc: 'headerSortUp',
                cssDesc: 'headerSortDown',
                cssNone: '',
                dateFormat: "uk"
            });
        });

        $('.confirmation').on('click', function () {
            return confirm('Está seguro de que desea marcar ésta factura como cobrada?');
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function(){
            $.post('/set_current_url', { url: window.location.href }, function(){});
        });
    </script>
@endsection
