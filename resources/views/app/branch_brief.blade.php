<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 13/12/2017
 * Time: 05:07 PM
 */
?>

@extends('layouts.adm_structure')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    <li><a href="#">&ensp;<i class="fa fa-building-o"></i> SUCURSALES <span class="caret"></span>&ensp;</a>
        <ul class="sub-menu">
            <li><a href="{{ '/branch' }}"><i class="fa fa-bars fa-fw"></i> Ver todo </a></li>
            @if($user->action->adm_bch_mod/*$user->priv_level==4*/)
                <li><a href="{{ '/branch/create' }}"><i class="fa fa-user-plus fa-fw"></i> Nueva sucursal </a></li>
                <li><a href="{{ '/excel/branches' }}"><i class="fa fa-file-excel-o"></i> Exportar a Excel </a></li>
            @endif
        </ul>
    </li>
    <li>
        <a data-toggle="modal" href="#searchBox">&ensp;<i class="fa fa-search"></i> BUSCAR&ensp;</a>
    </li>
@endsection

@section('content')
    <div class="col-sm-12 mg10 mg-tp-px-10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10 mg-tp-px-10">
        <p>Registros encontrados: {{ $branches->total() }}</p>

        <table class="fancy_table table_gray" id="fixable_table">
            <thead>
            <tr>
                <th>Número</th>
                <th>Sucursal</th>
                <th>Ciudad</th>
                <th width="25%">Dirección</th>
                <th>Teléfono(s)</th>
                <th>Encargado</th>
            </tr>
            </thead>
            <tbody>
            <?php $i = 1; ?>
            @foreach ($branches as $branch)
                <tr @if($branch->active==0)style="background-color: #ba5e5e" title="Sucursal no disponible"@endif>
                    <td>{{ $i }}</td>
                    <td>
                        <a href="/branch/{{ $branch->id }}" title="Ver información de esta sucursal"
                           @if($branch->active==0)style="color: inherit"@endif>
                            {{ $branch->name }}
                        </a>

                        @if($user->action->adm_bch_mod /*$user->priv_level==4*/)
                            <a href="/branch/{{ $branch->id }}/edit" title="Modificar registro de sucursal"
                               @if($branch->active==0)style="color: inherit"@endif>
                                <i class="fa fa-pencil-square"></i>
                            </a>
                        @endif
                    </td>
                    <td>{{ $branch->city }}</td>
                    <td>{{ $branch->address }}</td>
                    <td>
                        {{ ($branch->phone!=0 ? $branch->phone : '').'&ensp;'.
                            ($branch->alt_phone!=0 ? $branch->alt_phone : '') }}
                    </td>
                    <td>
                        {{ $branch->head_person ? $branch->head_person->first_name.' '.$branch->head_person->last_name : 'n/e' }}
                    </td>
                </tr>
                <?php $i++; ?>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="col-sm-12 mg10" align="center">
        {!! $branches->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="fancy_table table_gray" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'branches','id'=>0))
    </div>
@endsection

@section('footer')
    @parent
@endsection

@section('javascript')
    <script src="{{ asset('app/js/fix_table_header.js') }}"></script> {{-- For fixed header --}}
    <script>
        $('#alert').delay(2000).fadeOut('slow');

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
