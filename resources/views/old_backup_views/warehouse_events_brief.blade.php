<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 04/05/2017
 * Time: 11:40 AM
 */
?>

@extends('layouts.wh_structure')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/custom_table.css") }}">
@endsection

@section('menu_options')
    <li><a href="/warehouse/events/{{ 0 }}">&ensp;<i class="fa fa-refresh"></i> RECARGAR&ensp;</a></li>
    <li><a data-toggle="modal" href="#searchBox">&ensp;<i class="fa fa-search"></i> BUSCAR&ensp;</a></li>
@endsection

@section('content')
    <div class="col-sm-12 mg10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10">

        <p>{{ $events->total()==1 ? 'Se encontró 1 evento' : 'Se encontraron '.$events->total().' eventos' }}</p>

        <table class="formal_table table_gray tablesorter" id="fixable_table">
            <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Evento</th>
                <th width="20%"></th>
            </tr>
            </thead>
            <tbody>
            @foreach ($events as $event)
                <tr class="accordion-toggle" data-toggle="collapse" data-parent="#accordion"
                    data-target="{{ '#collapse'.$event->id }}">

                    <td>{{ $event->number }}</td>
                    <td>{{ date_format(new \DateTime($event->date), 'd-m-Y') }}</td>
                    <td>
                        {{ $event->description }}
                        {{--
                        @if($user->priv_level==4)
                        &emsp;
                        <a href="/event/{{ $type }}/{{ $event->id }}/edit" title="Modificar">
                            <i class="fa fa-pencil-square-o"></i>
                        </a>
                        @endif
                        --}}
                    </td>
                    <td>
                        <a data-toggle="collapse" data-parent="#accordion" href="{{ '#collapse'.$event->id }}">
                            <i class="indicator glyphicon glyphicon-chevron-right pull-right"></i>
                        </a>
                    </td>
                </tr>
                <tr style="background-color: transparent" class="tablesorter-childRow expand-child">
                    <td colspan="4" style="padding: 0">
                        <div id="{{ 'collapse'.$event->id }}" class="panel-collapse collapse mg-tp-px-10 col-sm-10 col-sm-offset-1">

                            <table class="table table_lilac">
                                <tr>
                                    <th>Detalle del evento:</th>
                                    <th width="35%">Responsable:</th>
                                </tr>
                                <tr>
                                    <td rowspan="3" style="background-color: white">
                                        <p>{{ $event->detail }}</p>
                                    </td>
                                    <td style="background-color: white" align="right">
                                        {{ $event->responsible ? $event->responsible->name : 'N/A' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Evento agregado por:</th>
                                </tr>
                                <tr>
                                    <td style="background-color: white" align="right">{{ $event->user->name }}</td>
                                </tr>
                            </table>

                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-sm-12 mg10" align="center">
        {!! $events->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="formal_table table_gray" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'wh_events','id'=>0))
    </div>

@endsection

@section('footer')
    @parent
@endsection

@section('javascript')
    <script src="{{ asset('app/js/fix_table_header.js') }}"></script> {{-- For fixed header --}}
    <script>
        $('#alert').delay(2000).fadeOut('slow');

        $(function(){
            $('#fixable_table').tablesorter({
                cssAsc: 'headerSortUp',
                cssDesc: 'headerSortDown',
                cssNone: ''
            });
        });

        $('.collapse').on('show.bs.collapse', function () {
            $('.collapse.in').collapse('hide');
            $(this).closest('tr').prev(".accordion-toggle").find('.indicator')
                    .toggleClass("glyphicon-chevron-down glyphicon-chevron-right");

        }).on('hide.bs.collapse', function () {
            $(this).closest('tr').prev(".accordion-toggle").find('.indicator')
                    .toggleClass("glyphicon-chevron-right glyphicon-chevron-down");

        });
    </script>
@endsection
