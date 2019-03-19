@extends('layouts.ocs_structure')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    <li><a href="#">&ensp;<i class="fa fa-list-alt"></i> O.C.s <span class="caret"></span>&ensp;</a>
        <ul class="sub-menu">
            <li><a href="{{ '/oc' }}"><i class="fa fa-bars fa-fw"></i> Ver todo</a></li>
            @if($user->action->oc_add)
                <li><a href="{{ '/oc/create' }}"><i class="fa fa-plus fa-fw"></i> Agregar O.C.</a></li>
                <li>
                    <a href="{{ '/oc/create?action=cmp' }}">
                        <i class="fa fa-plus fa-fw"></i> Agregar O.C. complementaria
                    </a>
                </li>
            @endif

            <li><a href="{{ '/invoice/create' }}"><i class="fa fa-plus fa-fw"></i> Agregar factura</a></li>
            {{--
            @if($user->priv_level==4)
                <li><a href="/delete/oc"><i class="fa fa-trash-o"></i> Borrar un archivo </a></li>
            @endif
            --}}
            @if($user->action->oc_exp /*$user->priv_level>=3*/)
                <li><a href="{{ '/excel/ocs' }}"><i class="fa fa-file-excel-o fa-fw"></i> Exportar a Excel</a></li>
            @endif
        </ul>
    </li>
    @if($user->priv_level>=1/*($user->area=='Gerencia Tecnica'&&$user->priv_level==2)||$user->priv_level>=3*/)
        <li><a href="{{ '/provider' }}">&ensp;<i class="fa fa-truck"></i> PROVEEDORES&ensp;</a></li>
    @endif
    @if($user->priv_level>=2)
        <li><a href="{{ '/oc_certificate' }}">&ensp;<i class="fa fa-file-text-o"></i> CERTIFICADOS&ensp;</a></li>
    @endif
    <li><a href="{{ '/invoice' }}">&ensp;<i class="fa fa-money"></i> PAGOS&ensp;</a></li>
    <li>
        <!--<a href="/search/ocs/0"><i class="fa fa-search"></i> BUSCAR </a>-->
        <a data-toggle="modal" href="#searchBox">&ensp;<i class="fa fa-search"></i> BUSCAR&ensp;</a>
    </li>
@endsection

@section('content')

    @if($ocs_waiting_approval!=0)
        <div class="col-sm-12 mg10">
            <div class="alert alert-info" align="center">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <i class="fa fa-info-circle fa-2x pull-left"></i>
                <a href="{{ '/approve_oc' }}" style="color: inherit;">
                    {{ $ocs_waiting_approval==1 ? 'Existe 1 orden pendiente de aprobación' :
                        'Existen '.$ocs_waiting_approval.' ordenes pendientes de aprobación' }}
                </a>
            </div>
        </div>
    @endif

    @if($rejected_ocs!=0)
        <div class="col-sm-12 mg10">
            <div class="alert alert-warning" align="center">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <i class="fa fa-warning fa-2x pull-left"></i>
                <a href="{{ '/rejected_ocs' }}" style="color: inherit;">
                    {{ $rejected_ocs==1 ? 'Existe 1 orden rechazada' : 'Existen '.$rejected_ocs.' ordenes rechazadas' }}
                </a>
            </div>
        </div>
    @endif

    {{--
    @if($inv_waiting_approval!=0)
        <div class="col-sm-12 mg10">
            <div class="alert alert-info" align="center">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <i class="fa fa-info-circle fa-2x pull-left"></i>
                <a href="{{ '/invoice/approve' }}" style="color: inherit;">
                    {{ $inv_waiting_approval==1 ? 'Existe 1 factura de proveedor pendiente de aprobación' :
                         'Existen '.$inv_waiting_approval.' facturas de proveedor pendientes de aprobación' }}
                </a>
            </div>
        </div>
    @endif
    --}}

    @if($incomplete_providers!=0)
        <div class="col-sm-12 mg10">
            <div class="alert alert-warning" align="center">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <i class="fa fa-warning fa-2x pull-left"></i>
                <a href="{{ '/provider/incomplete' }}" style="color: inherit; /*darkgoldenrod*/">
                    {{ $incomplete_providers==1 ?
                        '1 registro de proveedor está incompleto, complete el registro para poder asignarle una OC' :
                         $incomplete_providers.' registros de proveedor están incompletos, complete estos registros para
                         poder asignarles una OC' }}
                </a>
            </div>
        </div>
    @endif

    <div class="col-sm-12 mg10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10 mg-tp-px-5">
        <p>Registros encontrados: {{ $ocs->total() }}</p>

        <table class="fancy_table table_orange" id="fixable_table">
            <thead>
            <tr>
                <th width="10%">Nº OC</th>
                <th width="10%">Fecha</th>
                <th width="30%">Concepto</th>
                <th width="20%">Proveedor</th>
                <th width="15%">Cliente</th>
                <th>Estado</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($ocs as $oc)
                <tr>
                    <td>
                        <a href="/oc/{{ $oc->id }}">
                            {{ $oc->code }}
                            {{-- 'OC-'.str_pad($oc->id, 5, "0", STR_PAD_LEFT) --}}
                        </a>
                        @if($user->action->oc_edt /*$user->priv_level==4*/)
                            <a href="{{ '/oc/'.$oc->id.'/edit' }}"><i class="fa fa-pencil-square"></i></a>
                        @endif
                    </td>
                    <td>{{ date_format($oc->created_at,'d-m-Y') }}</td>
                    <td>
                        {{ $oc->proy_concept }}

                        @if($oc->linked)
                            <a href="/oc/{{ $oc->linked->id }}" class="pull-right"
                               title="{{ 'Orden complemento a '.$oc->linked->code }}">
                                <i class="fa fa-chain"></i>
                            </a>
                        @endif
                    </td>
                    <td>{{ $oc->provider }}</td>
                    <td>{{ $oc->client }}</td>
                    <td>
                        @if($oc->status=='Anulada')
                            {{ 'Anulada' }}
                        @elseif($oc->status=='Rechazada')
                            @if($oc->user_id==$user->id||$user->action->oc_edt /*$user->priv_level>=3*/)
                                <a href="{{ '/rejected_ocs' }}">{{ 'Rechazada' }}</a>
                            @else
                                {{ 'Rechazada' }}
                            @endif
                        @elseif($oc->flags[7]==1)
                            {{ 'Concluída' }}
                        @elseif($oc->flags[1]==1)
                            {{ 'Aprobada' }}
                        @elseif($oc->flags[1]==0&&$oc->flags[2]==1)
                            @if($user->action->oc_apv_gg /*($user->priv_level==3&&$user->area=='Gerencia General')*/||
                                $user->priv_level==4)
                                <a href="{{ '/approve_oc?code='.$oc->code }}">{{ 'Pendiente aprobación de G. General' }}</a>
                            @else
                                {{ 'Pendiente aprobación de G. General' }}
                            @endif
                        @elseif($oc->flags[1]==0&&$oc->flags[2]==0)
                            @if($user->action->oc_apv_tech /*($user->priv_level==3&&$user->area=='Gerencia Tecnica')*/||
                                $user->priv_level==4)
                                <a href="{{ '/approve_oc?code='.$oc->code }}">{{ 'Pendiente aprobación de G. Tecnica' }}</a>
                            @else
                                {{ 'Pendiente aprobación de G. Tecnica' }}
                            @endif
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-sm-12 mg10" align="center">
        {!! $ocs->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="fancy_table table_orange" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'ocs','id'=>0))
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
    </script>
@endsection
