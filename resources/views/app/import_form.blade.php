@extends('layouts.master')

@section('header')
    @parent
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
        <div class="panel panel-info" >
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    @if($type=='tasks'||$type=='tasks-from-oc'){{ 'Importar items - Sitio: '.$place->name }}
                    @elseif($type=='sites'){{ 'Importar sitios >>> '.$place->name }}
                    @elseif($type=='items'){{ 'Importar nuevos items' }}
                    @elseif($type=='client_listed_materials'){{ 'Importar lista de materiales' }}
                    @elseif($type=='stipend_requests'){{ 'Importar solicitudes de viáticos >>> '.$place->name }}
                    @elseif($type=='rendicion_respaldos'){{ 'Importar respaldos para esta rendición' }}
                    @endif
                </div>
            </div>
            <div class="panel-body" >
                <div class="col-lg-4 mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                </div>
                <div class="col-lg-8" align="right">
                    @if($type=='tasks')
                        <a href="{{ '/file_layouts/item_import_model.xlsx' }}" class="btn btn-success">
                            <i class="fa fa-file-excel-o"></i> Descargar modelo
                        </a>
                        {{-- More than one file available to choose
                        <div class="btn-group">
                            <button type="button" data-toggle="dropdown" class="btn btn-success dropdown-toggle">
                                <i class="fa fa-file-excel-o"></i> Descargar modelo <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-success">
                                <li><a href="{{ '/file_layouts/item_import_model_rural.xlsx' }}"> Rural</a></li>
                                <li><a href="{{ '/file_layouts/item_import_model_urbano.xlsx' }}"> Urbano</a></li>
                            </ul>
                        </div>
                        --}}
                    @elseif($type=='items')
                        <a href="{{ '/file_layouts/load_items_format.xlsx' }}" class="btn btn-success"
                            title="Descargar formato de referencia para importar items">
                          <i class="fa fa-download"></i> Formato de importación
                        </a>
                    @elseif($type=='sites')
                        <a href="{{ '/file_layouts/site_import_model.xlsx' }}" class="btn btn-success">
                          <i class="fa fa-file-excel-o"></i> Descargar modelo
                        </a>
                    @elseif($type=='client_listed_materials')
                        <a href="{{ '/file_layouts/client_listed_material_model.xlsx' }}" class="btn btn-success">
                          <i class="fa fa-file-excel-o"></i> Descargar modelo
                        </a>
                    @elseif($type=='stipend_requests')
                        <a href="{{ '/file_layouts/stipend_request_import_model.xlsx' }}" class="btn btn-success">
                          <i class="fa fa-file-excel-o"></i> Descargar modelo
                        </a>
                    @elseif($type == 'rendicion_respaldos')
                        <a href="{{ '/file_layouts/load_viatico_respaldos_format.xlsx' }}" class="btn btn-success">
                          <i class="fa fa-file-excel-o"></i> Descargar modelo
                        </a>
                    @endif
                </div>

                <div class="col-sm-12">
                    @include('app.session_flashed_messages', array('opt' => 0))
                </div>

                <div class="col-sm-12 mg10 mg-tp-px-10">
                    <form method="post" action="/import/{{ $type }}/{{ $id }}" accept-charset="UTF-8" enctype="multipart/form-data">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="form-group">
                            <div class="input-group">

                                <span class="input-group-addon"><i class="fa fa-cloud-upload"></i></span>

                                @if($type!='tasks-from-oc')
                                    <input type="file" class="form-control" name="import_file">
                                @endif
                                @if($type=='items'||$type=='tasks')
                                    <label for="category"></label>
                                    <select required="required" class="form-control" name="category" id="category">
                                        <option value="" hidden>Seleccione una categoría</option>
                                        @foreach($options as $option)
                                            <option value="{{ $option->name }}">{{ $option->name }}</option>
                                        @endforeach
                                        @if($type=='items')
                                            <option value="Otro">Otra categoría</option>
                                        @endif
                                    </select>
                                    <input required="required" type="text" class="form-control" name="other_category"
                                           id="other_category" placeholder="Crear una nueva categoría" disabled="disabled">

                                    @if($type=='items')
                                        <label for="area"></label>
                                        <select required="required" class="form-control" name="area" id="area">
                                            <option value="" hidden>Seleccione un área</option>
                                            <option value="Fibra óptica">Fibra óptica</option>
                                            <option value="Radiobases">Radiobases</option>
                                            <option value="Instalación de energía">Instalación de energía</option>
                                            <option value="Obras Civiles">Obras Civiles</option>
                                        </select>

                                        <label for="project_id"></label>
                                        <select required="required" class="form-control" name="project_id" id="project_id">
                                            <option value="" hidden>Seleccione un proyecto</option>
                                            @foreach($complements as $project)
                                                <option value="{{ $project->id }}"
                                                     title="{{ $project->name }}">{{ str_limit($project->name, 100) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                @endif
                                @if($type=='tasks-from-oc')
                                    <div class="input-group" style="width:100%;">
                                        <span class="input-group-addon" style="text-align: left">Código: OC-</span>
                                        <input required="required" type="number" class="form-control" name="oc_id"
                                               step="1" min="1" placeholder="00000">
                                    </div>
                                @endif
                                @if($type=='client_listed_materials')
                                    <label for="client"></label>
                                    <select required="required" class="form-control" name="client" id="client">
                                        <option value="" hidden>Seleccione un cliente</option>
                                        @foreach($options as $option)
                                            <option value="{{ $option->client }}">{{ $option->client }}</option>
                                        @endforeach
                                        <option value="Otro">Otro</option>
                                    </select>
                                    <input required="required" type="text" class="form-control" name="other_client"
                                           id="other_client" placeholder="Nombre de cliente" disabled="disabled">
                                @endif
                            </div>
                        </div>

                        @include('app.loader_gif')

                        <div class="form-group" align="center">
                            <button type="submit" class="btn btn-primary"
                                    onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                                <i class="fa fa-upload"></i> Importar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('footer')
@endsection

@section('javascript')
<script>
    $("#wait").hide();

    var $category = $('#category'), $other_category = $('#other_category');
    $category.change(function () {
        if ($category.val()==='Otro') {
            $other_category.removeAttr('disabled').show();
        } else {
            $other_category.attr('disabled', 'disabled').val('').hide();
        }
    }).trigger('change');

    var $client = $('#client'), $other_client = $('#other_client');
    $client.change(function () {
        if ($client.val()==='Otro') {
            $other_client.removeAttr('disabled').show();
        } else {
            $other_client.attr('disabled', 'disabled').val('').hide();
        }
    }).trigger('change');
</script>
@endsection
