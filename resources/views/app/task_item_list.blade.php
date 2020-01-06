@extends('layouts.master')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 mg-btm-px-40 col-md-10 col-md-offset-1 col-sm-8 col-sm-offset-2">
        <div class="panel panel-sky">
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    {{ 'Listado de items por categoría' }}
                </div>
            </div>
            <div class="panel-body">
                <div class="col-lg-6 mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning">
                        <i class="fa fa-arrow-circle-left"></i> Volver
                    </a>
                </div>
                <div class="col-lg-6" align="right">
                    <div class="input-group" style="width: 100%">
                        <label for="category" class="input-group-addon" style="width: 23%;text-align: left">
                            Categoría:
                        </label>

                        <select required="required" class="form-control" name="category" id="category" onchange="changer(this)">
                            <option value="" hidden>Seleccione una categoría</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->name }}" title="{{ $category->name }}">
                                    {{ str_limit($category->name,150) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <div class="col-md-12">
                    <p><em>Nota.- Use la combinación de teclas Ctrl+F para buscar un item dentro de la lista</em></p>
                </div>

                <form method="post" action="/task/{{ $site_id }}/add" id="item_list" accept-charset="UTF-8"
                      enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="col-sm-12">
                        <table class="table table-striped table-hover table-bordered">
                            <thead>
                            <tr>
                                <th>
                                    <label for="select_all"></label>
                                    <input type="checkbox" name="select_all" id="select_all">
                                </th>
                                <th>Nº</th>
                                <th>Código</th>
                                <th width="15%">Subcategoría</th>
                                <th width="30%">Descripción</th>
                                <th>Unidades</th>
                                {{--<th>Costo unitario</th>--}}
                                <th>Cant. proyectada</th>
                            </tr>
                            </thead>
                            <tbody id="container">
                            <tr>
                                <td colspan="7" align="center">
                                    No hay resultados para mostrar, seleccione una categoría
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="col-sm-10 col-sm-offset-1" id="container" align="center">
                        @include('app.loader_gif')

                        <div class="form-group" align="center">
                            <button type="submit" id="submit_button" class="btn btn-primary"
                                    onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                                <i class="fa fa-plus"></i> Agregar item(s)
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>

    {{-- Code to present list of items, moved to AjaxController
    $i=0
    @foreach($categories as $category)
        @foreach($items as $item)
            @if($item->category == $category->category)
                <tr>
                    <td align="center">
                        <input type="checkbox" name="{{ 'item_'.$i }}" value="{{ $item->id }}"
                            class="checkbox" onclick="enable_field(this,i='{{ $i }}')">
                    </td>
                    <td>{{ $item->number }}</td>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->units }}</td>
                    <td>{{ $item->cost_unit_central }}</td>
                    <td>{{ $item->cost_unit_remote }}</td>
                    <td>
                        <input required="required" type="number" class="form-control quantity"
                            name="{{ 'quantity_'.$i }}" id="{{ 'quantity_'.$i }}" step="1" min="1"
                            placeholder="Cantidad contratada" disabled="disabled">
                    </td>
                </tr>
                $i++
            @endif
        @endforeach
    @endforeach
    <input type="hidden" name="listed_items" value="{{ $i }}">
    --}}

@endsection

@section('footer')
@endsection

@section('javascript')
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("#wait").hide();

        $("#select_all").change(function(){
            var status = this.checked;
            $('.checkbox').each(function(){
                this.checked = status;
            });
            if(status){
                $('.quantity').removeAttr('disabled');
            }
            else{
                $('.quantity').attr('disabled', 'disabled');
            }
        });

        /*
        $('.checkbox').change(function(){
            if(this.checked == false){
                $("#select_all")[0].checked = false;
            }

            if ($('.checkbox:checked').length == $('.checkbox').length ){
                $("#select_all")[0].checked = true;
                $('.quantity').each(function(){
                    this.removeAttr('disabled');
                });
            }
        });
        */

        function update_select_all(c){
            if(c){
                if ($('.checkbox:checked').length == $('.checkbox').length ){
                    $("#select_all")[0].checked = true;
                    $('.quantity').each(function(){
                        this.removeAttr('disabled');
                    });
                }
            }
            else{
                $("#select_all")[0].checked = false;
            }
        }

        function enable_field(element,i){
            var c = element.checked;
            if (c){
                $("#quantity_"+i).removeAttr('disabled');
            }
            else{
                //alert("Please check at least one checkbox");
                $("#quantity_"+i).attr('disabled', 'disabled');
            }

            update_select_all(c);
        }

        function changer(c){
            //var site_field = $(c).attr('id');
            $.post('/dynamic_items', { category: $(c).val() }, function(data){
                $("#container").html(data);
            });
        }
    </script>
@endsection
