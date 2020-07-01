<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 10/02/2017
 * Time: 04:31 PM
 */
?>

@extends('layouts.adm_structure')

@section('header')
    @parent
    <script type="text/javascript" src="{{ asset('https://viralpatel.net/blogs/demo/jquery/jquery.shorten.1.0.js') }}"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    <li><a href="#">&ensp;<i class="fa fa-key"></i> PARAMETROS <span class="caret"></span>&ensp;</a>
        <ul class="sub-menu">
            <li><a href="{{ '/service_parameter' }}"><i class="fa fa-list-ul fa-fw"></i> Ver todos</a></li>
            <li><a href="{{ '/service_parameter/create' }}"><i class="fa fa-plus fa-fw"></i> Nuevo parámetro</a></li>
            @if($user->priv_level==4)
                <li>
                    <a href="{{ '/excel/service_parameters' }}">
                        <i class="fa fa-file-excel-o fa-fw"></i> Exportar a Excel
                    </a>
                </li>
            @endif
        </ul>
    </li>
    {{--
    <li><a data-toggle="modal" href="#searchBox"><i class="fa fa-search"></i> BUSCAR</a></li>
    --}}
@endsection

@section('content')
    <div class="col-sm-12 mg10 mg-tp-px-10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10 mg-tp-px-10">
        <p>Registros encontrados: {{ $service_parameters->total() }}</p>

        <table class="fancy_table table_gray" id="fixable_table">
            <thead>
            <tr>
                <th>Nombre</th>
                <th>Grupo</th>
                <th width="30%">Contenido</th>
                <th>Descripción</th>
                <th>Última modificación</th>
                <th>Responsable</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($service_parameters as $service_parameter)
                <tr>
                    <td>
                        <a href="/service_parameter/{{ $service_parameter->id }}" title="Ver información de parámetro">
                            {{ $service_parameter->name }}
                        </a>
                    </td>
                    <td>{{ $service_parameter->group }}</td>
                    <td>
                        <div class="comment">
                            @if($service_parameter->literal_content)
                                {!! nl2br($service_parameter->literal_content) !!}
                            @elseif($service_parameter->numeric_content)
                                {{ $service_parameter->numeric_content.' '.$service_parameter->units }}
                            @endif
                        </div>
                    </td>
                    <td>{{ $service_parameter->description }}</td>
                    <td>
                        {{ date_format($service_parameter->updated_at,'d-m-Y')}}
                        <a href="/service_parameter/{{ $service_parameter->id }}/edit" title="Modificar nombre de parámetro">
                            <i class="fa fa-pencil-square-o pull-right"></i>
                        </a>
                    </td>
                    <td>{{ $service_parameter->user->name }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-sm-12 mg10" align="center">
        {!! $service_parameters->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="fancy_table table_gray" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'service_parameters','id'=>0))
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

        $(".comment").shorten({
            "showChars" : 150,
            "moreText"	: "ver más",
            "lessText"	: "ocultar"
        });
    </script>
@endsection
