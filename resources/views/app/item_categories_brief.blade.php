<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 27/10/2017
 * Time: 04:14 PM
 */
?>

@extends('layouts.projects_structure')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/custom_table.css") }}">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    @include('app.project_navigation_button', array('user'=>$user))
    <div class="btn-group">
        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
            <i class="fa fa-list"></i> Categorías <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li><a href="{{ '/item_category' }}"><i class="fa fa-refresh"></i> Recargar página </a></li>
            <li>
                <a href="/import/items/{{ 0 }}" title="{{ 'Crear una nueva categoría de items de proyecto o agregar
                    items a una categoría existente' }}">
                    <i class="fa fa-upload"></i> Cargar items
                </a>
            </li>
            @if($user->action->prj_cat_exp /*$user->priv_level==4*/)
                <li class="divider"></li>
                <li><a href="{{ '/excel/item_categories' }}"><i class="fa fa-file-excel-o"></i> Exportar a Excel </a></li>
            @endif
        </ul>
    </div>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchBox">
        <i class="fa fa-search"></i> Buscar
    </button>
@endsection

@section('content')

    <div class="col-sm-12 mg10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10 mg-tp-px-10">
        <p>Categorías encontradas: {{ $categories->total() }}</p>

        <table class="formal_table table_blue tablesorter" id="fixable_table">
            <thead>
            <tr>
                <th width="15%">Nombre</th>
                <th width="15%">Descripción</th>
                <th>Área</th>
                <th width="12%">Cliente</th>
                <th>Proyecto</th>
                <th width="12%">Estado</th>
                <th>Contenido</th>
                <th>Fecha creación</th>
            </tr>
            </thead>
            <tbody>
            @foreach($categories as $category)
                <tr>
                    <td>
                        {{--<a href="/item_category/{{ $category->id }}">{{ $category->name }}</a>--}}
                        {{ $category->name }}

                        @if($category->status==1)
                            <a href="/item_category/{{ $category->id }}/edit">
                                <i class="fa fa-pencil-square-o"></i>
                            </a>
                        @endif
                    </td>
                    <td>{{ $category->description }}</td>
                    <td>{{ $category->area }}</td>
                    <td>{{ $category->client }}</td>
                    <td>
                        @if($category->project)
                            <a href="/project/{{ $category->project->id }}" title="Ver información de proyecto">
                                {{ $category->project->name }}
                            </a>
                        @else
                            {{ 'n/e' }}
                        @endif
                    </td>
                    <td>
                        {{ $category->status==1 ? 'Vigente' : 'Archivada' }}

                        @if($category->status==1)
                            <a href="{{ '/item_category/stat?id='.$category->id.'&action=close' }}" class="confirm_status_change"
                               title="{{ 'Archivar categoría' }}">
                                <i class="fa fa-archive pull-right"></i>
                            </a>
                        @endif
                    </td>
                    <td align="center">
                        <a href="/item/{{ '?cat='.$category->id }}">
                            {{ $category->items()->count()==1 ? '1 item' : $category->items()->count().' items' }}
                        </a>
                    </td>
                    <td>{{ date_format($category->created_at, 'd-m-Y') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-sm-12 mg10" align="center">
        {!! $categories->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="fancy_table table_blue" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'item_categories','id'=>0))
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

        $(function(){
            $('#fixable_table').tablesorter({
                cssAsc: 'headerSortUp',
                cssDesc: 'headerSortDown',
                cssNone: ''
            });
        });

        $('.confirm_status_change').on('click', function () {
            return confirm('Está seguro de que desea archivar esta categoría?'+
                    ' Una vez archivada ya no podrá ser usada en proyectos futuros');
        });
    </script>
@endsection
