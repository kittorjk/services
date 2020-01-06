<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 20/02/2017
 * Time: 03:49 PM
 */
?>

@extends('layouts.projects_structure')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    @include('app.project_navigation_button', array('user'=>$user))
    <div class="btn-group">
        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
            <i class="fa fa-file-text"></i> Polizas <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li><a href="{{ '/guarantee' }}"><i class="fa fa-bars fa-fw"></i> Ver todo</a></li>
            <li><a href="{{ '/guarantee/create' }}"><i class="fa fa-plus fa-fw"></i> Agregar poliza</a></li>
            <li><a href="{{ '/guarantee?arch=0' }}"><i class="fa fa-list-ul fa-fw"></i> Ver polizas vigentes</a></li>
            <li><a href="{{ '/guarantee?arch=1' }}"><i class="fa fa-list-ul fa-fw"></i> Ver polizas archivadas</a></li>
            {{--@if($user->priv_level==4)--}}
                <li class="divider"></li>
                <li><a href="{{ '/excel/guarantees' }}"><i class="fa fa-file-excel-o fa-fw"></i> Exportar a Excel </a></li>
            {{--@endif--}}
        </ul>
    </div>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchBox">
        <i class="fa fa-search"></i> Buscar
    </button>
@endsection

@section('content')
    {{--
    @foreach($guarantees as $guarantee)
        @if(Carbon\Carbon::now()->diffInDays($guarantee->expiration_date,false)<=5&&$guarantee->closed==0)
            {{--
            ($guarantee->guaranteeable_type=='App\Assignment'&&
            (!in_array($guarantee->guaranteeable->status,array('Concluído','No asignado'))))

            <div class="col-sm-12 mg10">
                <div class="alert alert-danger" align="center">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    <i class="fa fa-exclamation-circle fa-2x pull-left"></i>
                    <a href="guarantee/{{ $guarantee->id }}" style="color: darkred">
                        {{ 'La Poliza de garantía '.$guarantee->code.
                            (Carbon\Carbon::now()->diffInDays($guarantee->expiration_date,false)<0 ?
                            ' ha vencido!' : ' vence pronto!') }}
                    </a>
                </div>
            </div>
        @endif
    @endforeach
    --}}

    <div class="col-sm-12 mg10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10">
        <p>Polizas encontradas: {{ $guarantees->total() }}</p>

        <table class="fancy_table table_ground tablesorter" id="fixable_table">
            <thead>
            <tr>
                <th>Poliza</th>
                <th width="20%">Empresa</th>
                <th>Tipo</th>
                <th width="30%">Objeto</th>
                <th>Fecha vencimiento</th>
                <th width="10%">Acciones</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($guarantees as $guarantee)
                <tr>
                    <td>
                        <a href="/guarantee/{{ $guarantee->id }}">
                            {{ $guarantee->code }}
                        </a>
                    </td>
                    <td>{{ $guarantee->company }}</td>
                    <td>{{ $guarantee->type }}</td>
                    <td>
                        {{ $guarantee->applied_to }}
                        {{--
                        {{ $guarantee->guaranteeable&&$guarantee->guaranteeable_type=='App\Assignment' ?
                            str_limit($guarantee->guaranteeable->name,100) : 'Sin asignar' }}
                        --}}
                    </td>
                    <td>
                        <span style="{{Carbon\Carbon::now()->diffInDays($guarantee->expiration_date,false)<=5&&
                            $guarantee->closed==0 ? 'color:red' : ($guarantee->closed==1 ? 'color:grey' : '') }}">

                            {{ date_format($guarantee->expiration_date,'d-m-Y') }}
                            @if(Carbon\Carbon::now()->diffInDays($guarantee->expiration_date,false)<0&&$guarantee->closed==0)
                                {{ ' Vencida' }}
                            @elseif($guarantee->closed==1)
                                {{ ' Archivada' }}
                            @endif
                        </span>

                        @if(Carbon\Carbon::now()->diffInDays($guarantee->expiration_date,false)<=5&&
                            $guarantee->closed==0)
                            <a href="guarantee/{{ $guarantee->id }}" style="color: darkred">
                                <i class="fa fa-exclamation-circle pull-right"
                                   title="{{ Carbon\Carbon::now()->diffInDays($guarantee->expiration_date,false)<0 ?
                                        'Vencido' : 'Vence pronto' }}">
                                </i>
                            </a>
                        @endif
                    </td>
                    <td align="center">
                        @if($guarantee->closed==0)
                            <a href="/files/guarantee/{{ $guarantee->id }}" title="Subir archivo" style="text-decoration: none">
                                <i class="fa fa-upload"></i>
                            </a>
                            &emsp;
                            <a href="/guarantee/close/{{ $guarantee->id }}" class="confirmation" style="text-decoration: none"
                               title="Archivar poliza (una vez archivada no podrá ser modificada)">
                                <i class="fa fa-archive"></i>
                            </a>
                            {{--@if(($user->area=='Gerencia General'&&$user->priv_level>=2)||$user->priv_level==4)--}}
                                &emsp;
                                <a href="/guarantee/{{ $guarantee->id }}/edit" title="Editar / Modificar poliza">
                                    <i class="fa fa-pencil-square-o"></i>
                                </a>
                            {{--@endif--}}
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-sm-12 mg10" align="center">
        {!! $guarantees->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="fancy_table table_ground" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'guarantees','id'=>0))
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

        $('.confirmation').on('click', function () {
            return confirm('Está seguro de que desea marcar esta Poliza como "No renovable"? ' +
                    'Una vez marcada no podrá ser modificada');
        });

        $(function(){
            $('#fixable_table').tablesorter({
                cssAsc: 'headerSortUp',
                cssDesc: 'headerSortDown',
                cssNone: '',
                dateFormat: "uk"
            });
        });
    </script>
@endsection
