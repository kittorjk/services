<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 22/08/2017
 * Time: 02:53 PM
 */
?>

@extends('layouts.staff_structure')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    <li><a href="#">&ensp;<i class="fa fa-users"></i> GRUPOS <span class="caret"></span>&ensp;</a>
        <ul class="sub-menu">
            <li><a href="{{ '/tech_group' }}"><i class="fa fa-bars fa-fw"></i> Ver todos </a></li>
            <li><a href="{{ '/tech_group?act=true' }}"><i class="fa fa-bars fa-fw"></i> Ver grupos activos </a></li>
            <li><a href="{{ '/tech_group?arch=true' }}"><i class="fa fa-bars fa-fw"></i> Ver grupos archivados </a></li>
            <li><a href="{{ '/tech_group/create' }}"><i class="fa fa-user-plus fa-fw"></i> Crear nuevo grupo </a></li>
            @if($user->priv_level==4)
                <li><a href="{{ '/excel/tech_groups' }}"><i class="fa fa-file-excel-o"></i> Exportar a Excel </a></li>
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
        <p>Registros encontrados: {{ $groups->total() }}</p>

        <table class="fancy_table table_gray" id="fixable_table">
            <thead>
            <tr>
                <th>Grupo #</th>
                <th>Área</th>
                <th>Jefe de grupo</th>
                <th>Integrantes</th>
                <th>Observaciones</th>
                <th>Estado</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($groups as $group)
                <tr @if($group->status==1)style="background-color: palegoldenrod"@endif>
                    <td>
                        <a href="/tech_group/{{ $group->id }}" title="Ver información adicional de grupo"
                           @if($group->status==1)style="color: inherit"@endif>
                            {{ 'GR-'.$group->group_number }}
                        </a>
                        <a href="/tech_group/{{ $group->id }}/edit" title="Modificar información de grupo"
                           @if($group->status==1)style="color: inherit"@endif>
                            <i class="fa fa-pencil-square-o"></i>
                        </a>
                    </td>
                    <td>{{ $group->group_area }}</td>
                    <td>{{ $group->group_head ? $group->group_head->name : '' }}</td>
                    <td>
                        {!! $group->tech_2 ? $group->tech_2->name.'<br>' : '' !!}
                        {!! $group->tech_3 ? $group->tech_3->name.'<br>' : '' !!}
                        {!! $group->tech_4 ? $group->tech_4->name.'<br>' : '' !!}
                        {!! $group->tech_5 ? $group->tech_5->name.'<br>' : '' !!}
                    </td>
                    <td>{{ $group->observations }}</td>
                    <td>{{ $group->status==0 ? 'Activo' : 'Archivado' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="col-sm-12 mg10" align="center">
        {!! $groups->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="fancy_table table_gray" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'tech_groups','id'=>0))
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
