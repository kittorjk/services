<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 16/02/2017
 * Time: 03:35 PM
 */
?>

@extends('layouts.projects_structure')

@section('header')
    @parent
@endsection

@section('menu_options')
    @include('app.project_navigation_button', array('user'=>$user))
    <div class="btn-group">
        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
            <i class="fa fa-file-text"></i> Contratos <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li><a href="{{ '/contract' }}"><i class="fa fa-bars fa-fw"></i> Ver todos</a></li>
            <li><a href="{{ '/contract/create' }}"><i class="fa fa-plus fa-fw"></i> Agregar contrato</a></li>
            <li><a href="{{ '/contract?arch=0' }}"><i class="fa fa-list-ul fa-fw"></i> Ver contratos vigentes</a></li>
            <li><a href="{{ '/contract?arch=1' }}"><i class="fa fa-archive fa-fw"></i> Ver contratos archivados</a></li>
            @if($user->priv_level==4)
                <li class="divider"></li>
                <li><a href="{{ '/excel/contracts' }}"><i class="fa fa-file-excel-o fa-fw"></i> Exportar a Excel </a></li>
            @endif
        </ul>
    </div>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchBox">
        <i class="fa fa-search"></i> Buscar
    </button>
@endsection

@section('content')
    @foreach($contracts as $contract)
        @if(Carbon\Carbon::now()->diffInDays($contract->expiration_date,false)<=5&&$contract->closed==0)
            <div class="col-sm-12 mg10">
                <div class="alert alert-danger" align="center">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    <a href="contract/{{ $contract->id }}" style="color: darkred">
                        {{ 'El contrato '.$contract->code.
                            (Carbon\Carbon::now()->diffInDays($contract->expiration_date,false)<0 ?
                            ' ha vencido!' : ' vence pronto!') }}
                    </a>
                </div>
            </div>
        @endif
    @endforeach

    <div class="col-sm-12 mg10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10">
        <p>Contratos vigentes encontrados: {{ $contracts->total() }}</p>

        <table class="fancy_table table_gray" id="fixable_table">
            <thead>
            <tr>
                <th>Código interno</th>
                <th>Código de cliente</th>
                <th>Cliente</th>
                <th width="30%">Objeto</th>
                <th>Fecha de vencimiento</th>
                <th>Archivos</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($contracts as $contract)
                <tr>
                    <td>
                        <a href="/contract/{{ $contract->id }}">
                            {{ $contract->code }}
                            {{-- 'CTO-'.date_format($contract->created_at,'y').str_pad($contract->id, 3, "0", STR_PAD_LEFT) --}}
                        </a>
                    </td>
                    <td>{{ $contract->client_code }}</td>
                    <td>{{ $contract->client }}</td>
                    <td>{{ $contract->objective }}</td>
                    <td>
                        @if(Carbon\Carbon::now()->diffInDays($contract->expiration_date,false)<=5&&$contract->closed==0)
                            <span style="color:red">
                        @elseif($contract->closed==1)
                            <span style="color:slategrey">
                        @else
                            <span>
                        @endif
                            {{ date_format($contract->expiration_date,'d-m-Y') }}
                            @if(Carbon\Carbon::now()->diffInDays($contract->expiration_date,false)<0&&$contract->closed==0)
                                {{ ' Vencido' }}
                                <a href="/contract/close/{{ $contract->id }}" class="confirmation"
                                   title="Archivar contrato (No renovable / una vez archivado no podrá ser modificado)">
                                    <i class="fa fa-archive pull-right"></i>
                                </a>
                            @endif
                                @if($contract->closed==1)
                                    {{ ' Archivado' }}
                                @endif
                            </span>
                    </td>
                    <td align="center">
                        @if($contract->closed==0||$user->priv_level==4)
                            <a href="/files/contract/{{ $contract->id }}">
                                <i class="fa fa-upload"></i> Subir archivo
                            </a>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-sm-12 mg10" align="center">
        {!! $contracts->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="fancy_table table_gray" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'contracts','id'=>0))
    </div>

@endsection

@section('footer')
    @parent
@endsection

@section('javascript')
    <script src="{{ asset('app/js/fix_table_header.js') }}"></script> {{-- For fixed header --}}
    <script>
        $('#alert').delay(2000).fadeOut('slow');

        $('.confirmation').on('click', function () {
            return confirm('Está seguro de que desea archivar este contrato? Una vez archivado no podrá ser modificado');
        });
    </script>
@endsection
