<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 30/10/2017
 * Time: 03:28 PM
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
            <i class="fa fa-list"></i> Items <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li><a href="{{ '/item/?cat='.$category->id }}"><i class="fa fa-refresh"></i> Recargar página </a></li>
            <li>
                <a href="/import/items/{{ 0 }}" title="Cargar una nueva categoría de items de proyecto">
                    <i class="fa fa-upload"></i> Cargar items
                </a>
            </li>
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

    <div class="col-sm-12 mg10">
        <p><a href="{{ '/item_category' }}">Categoría</a> > Items</p>
        <p>Categoría: {{ $category->name }}</p>
        <p>Items en ésta categoría: {{ $items->total() }}</p>

        <table class="formal_table table_blue tablesorter" id="fixable_table">
            <thead>
            <tr>
                <th width="8%">Número</th>
                <th width="8%" title="Código de item según cliente">Código</th>
                <th width="30%">Nombre</th>
                <th>Descripción</th>
                <th>Subcategoría</th>
                <th width="10%">Unidades</th>
                <th title="{{ $category->status==1 ? 'Haga click en los montos para modificarlos' : '' }}">
                    Costo unitario [Bs]
                </th>
            </tr>
            </thead>
            <tbody>
            @foreach($items as $item)
                <tr>
                    <td>
                        {{ $item->number }}
                    </td>
                    <td>{{ $item->client_code }}</td>
                    <td width="20%">
                        {{ $item->description }}

                        @if($category->status==1)
                            <a href="/item/{{ $item->id }}/edit">
                                <i class="fa fa-pencil-square-o"></i>
                            </a>
                        @endif
                    </td>
                    <td>{{ $item->detail }}</td>
                    <td>{{ $item->subcategory }}</td>
                    <td>{{ $item->units }}</td>
                    <td width="12%" align="right"
                        @if($category->status==1)
                            class="edit"
                            onclick="replace(this, id='{{ $item->id }}',val='{{ $item->cost_unit_central }}')"
                        @endif
                    >
                        {{ $item->cost_unit_central }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-sm-12 mg10" align="center">
        {!! $items->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="fancy_table table_blue" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'items','id'=>$category->id))
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

        $(function(){
            $('#fixable_table').tablesorter({
                cssAsc: 'headerSortUp',
                cssDesc: 'headerSortDown',
                cssNone: ''
            });
        });

        function replace(element,id, val){
            //var data = '', item_id = '';
            var data = $.trim($(element).text());
            var item_id = id;

            var arr = data.split(' ');
            arr[0] = arr[0].replace(/,/g, '');
            $(element).html("<input type=\"text\" value=\""+arr[0]+"\" id=\"editable"+item_id+"\" data-id=\""+item_id+"\"" +
                    " data-value=\""+val+"\" />");
            $(element).find('input').focus();
            $(element).off();
        }

        $(document).ready(function() {
            $(document).on("focusout","td.edit input",function() {
                var c = $(this); //$('#editable'+item_id);

                if(c.val()>0 && c.val().length >0){

                    $.post('/set_item_unit_cost', { amount: c.val(), id: c.data('id') }, function(result) {
                        c.parent().html(result.unit_cost);
                        //$('td.update').html(result.balance);
                    });

                }
                else{
                    c.parent().html(c.data('value'));
                    //$('td.edit').html(c+' Bs');
                    //$('td.update').html('hola');
                }

            });
        });
    </script>
@endsection
