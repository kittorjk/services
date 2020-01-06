<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 22/08/2017
 * Time: 11:52 AM
 */
?>

@extends('layouts.staff_structure')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    <li><a href="#">&ensp;<i class="fa fa-users"></i> PERSONAL <span class="caret"></span>&ensp;</a>
        <ul class="sub-menu">
            <li><a href="{{ '/staff' }}"><i class="fa fa-bars fa-fw"></i> Ver todo </a></li>
            <li><a href="{{ '/user/create' }}"><i class="fa fa-user-plus fa-fw"></i> Agregar usuario </a></li>
            @if($user->priv_level==4)
                <li><a href="{{ '/excel/staff' }}"><i class="fa fa-file-excel-o"></i> Exportar a Excel </a></li>
            @endif
        </ul>
    </li>
    <li><a href="{{ '/tech_group' }}">&ensp;<i class="fa fa-wrench"></i> GRUPOS DE TRABAJO&ensp;</a></li>
    <li><a href="{{ '/staff_role' }}">&ensp;<i class="fa fa-users"></i> CARGOS&ensp;</a></li>
    <li>
        <a data-toggle="modal" href="#searchBox">&ensp;<i class="fa fa-search"></i> BUSCAR&ensp;</a>
    </li>
@endsection

@section('content')
    <div class="col-sm-12 mg10 mg-tp-px-10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10 mg-tp-px-10">
        <p>Registros encontrados: {{ $records->total() }}</p>

        <table class="fancy_table table_gray" id="fixable_table">
            <thead>
            <tr>
                <th width="20%">Nombre</th>
                <th>Area</th>
                <th>T. trabajo</th>
                <th width="15%">Cargo</th>
                <th>RK</th>
                <th>PL</th>
                <th>CITEs</th>
                <th>OCs</th>
                <th>S.S.P.</th>
                <th>Activos</th>
                <th>Almacén</th>
                <th>Personal</th>
                <th>Última actualización</th>
            </tr>
            </thead>
            <tbody>
            <?php
                $areas = array();
                $areas['Gerencia Tecnica'] = 'Tecnica';
                $areas['Gerencia General'] = 'G. General';
                $areas['Gerencia Administrativa'] = 'Administrativa';
                $areas['Cliente'] = 'Cliente';
                $areas['Subcontratista'] = 'Subcontratista';

                $string_ok = "<i class=\"glyphicon glyphicon-ok\" style=\"color:darkgreen\"></i>";
                $string_remove = "<i class=\"glyphicon glyphicon-remove\" style=\"color:darkred\"></i>";
            ?>

            @foreach ($records as $record)
                <tr @if($record->role=='Retirado')style="background-color: #ba5e5e"@endif>
                    <td>
                        @if($user->priv_level==4)
                            <a href="/user/{{ $record->id }}" title="Ver información de usuario"
                               @if($record->role=='Retirado')style="color: inherit"@endif>
                                {{ $record->name }}
                            </a>
                            <a href="/user/{{ $record->id }}/edit" title="Modificar registro de usuario"
                               @if($record->role=='Retirado')style="color: inherit"@endif>
                                <i class="fa fa-pencil-square-o"></i>
                            </a>
                        @else
                            {{ $record->name }}
                        @endif
                    </td>
                    <td>{{ $areas[$record->area] }}</td>
                    <td>{{ $record->work_type }}</td>
                    <td>{{ $record->role }}</td>
                    <td align="center">{{ $record->rank }}</td>
                    <td align="center">{{ $record->priv_level }}</td>
                    <td align="center">{!! $record->acc_cite ? $string_ok : $string_remove !!}</td>
                    <td align="center">{!! $record->acc_oc ? $string_ok : $string_remove !!}</td>
                    <td align="center">{!! $record->acc_project ? $string_ok : $string_remove !!}</td>
                    <td align="center">{!! $record->acc_active ? $string_ok : $string_remove !!}</td>
                    <td align="center">{!! $record->acc_warehouse ? $string_ok : $string_remove !!}</td>
                    <td align="center">{!! $record->acc_staff ? $string_ok : $string_remove !!}</td>
                    <td>
                        {{ date_format($record->updated_at,'d-m-Y')}}
                        @if($record->role!='Retirado')
                            <a href="/send-notice/user/{{ $record->id }}" title="Enviar correo con información de cuenta"
                               class="pull-right">
                                <i class="fa fa-mail-forward"></i>
                            </a>
                        @endif
                    </td>
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
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'staff','id'=>0))
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
