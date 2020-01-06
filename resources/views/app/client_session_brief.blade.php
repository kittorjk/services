<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 21/08/2017
 * Time: 02:28 PM
 */
?>

@extends('layouts.adm_structure')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    <li><a href="#">&ensp;<i class="fa fa-users"></i> SESIONES <span class="caret"></span>&ensp;</a>
        <ul class="sub-menu">
            <li><a href="{{ '/client_session' }}"><i class="fa fa-bars fa-fw"></i> Ver todos </a></li>
            @if($user->priv_level==4)
                <li><a href="{{ '/excel/client_sessions' }}"><i class="fa fa-file-excel-o"></i> Exportar a Excel </a></li>
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
        <p>Historial de sessiones encontradas: {{ $records->total() }}</p>

        <table class="fancy_table table_gray" id="fixable_table">
            <thead>
            <tr>
                <th width="20%">Usuario</th>
                <th>Servicio</th>
                <th>Dirección IP</th>
                <th width="15%">Estado</th>
                <th>De</th>
                <th>A</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($records as $record)
                <tr>
                    <td>
                        @if($user->priv_level==4)
                            <a href="/user/{{ $record->user_id }}" title="Ver información de usuario">
                                {{ $record->user ? $record->user->name : '' }}
                            </a>
                        @else
                            {{ $record->user ? $record->user->name : '' }}
                        @endif
                    </td>
                    <td>{{ $record->service_accessed }}</td>
                    <td>{{ $record->ip_address }}</td>
                    <td>{{ $record->status==1 ? 'Cerrado' : 'Abierto' }}</td>
                    <td>{{ $record->created_at }}</td>
                    <td>{{ $record->status==0 ? 'Vigente' : $record->updated_at }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="col-sm-12 mg10" align="center">
        {!! $records->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="fancy_table table_gray" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'client_sessions','id'=>0))
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
