@extends('layouts.master')

@section('header')
    @parent
@endsection

@section('content')

<div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
    <div class="panel panel-info" >
        <div class="panel-heading" align="center">
            <div class="panel-title">
                @if($table=='cites'){{ 'Buscar CITE' }}
                @elseif($table=='ocs'){{ 'Buscar Orden de Compra' }}
                @elseif($table=='projects'){{ 'Buscar Proyecto' }}
                @elseif($table=='assignments'){{ 'Buscar Proyecto' }}
                @elseif($table=='users'){{ 'Buscar Usuario' }}
                @elseif($table=='providers'){{ 'Buscar Proveedor' }}
                @elseif($table=='sites'){{ 'Buscar Sitio' }}
                @elseif($table=='contacts'){{ 'Buscar Contacto' }}
                @elseif($table=='orders'){{ 'Buscar Orden' }}
                @elseif($table=='bills'){{ 'Buscar Factura' }}
                @elseif($table=='tasks'){{ 'Buscar Items' }}
                @elseif($table=='invoices'){{ 'Buscar información de pagos' }}
                @elseif($table=='vehicles'){{ 'Buscar vehículo' }}
                @elseif($table=='devices'){{ 'Buscar equipo' }}
                @elseif($table=='drivers'||$table=='operators'){{ 'Buscar registro' }}
                @elseif($table=='maintenances'){{ 'Buscar registro de mantenimiento' }}
                @endif
            </div>
        </div>
        <div class="panel-body">
            <div class="mg20">
                <a href="#" onclick="history.back();" class="btn btn-warning">
                    <i class="fa fa-arrow-circle-left"></i> Volver
                </a>
            </div>

            @include('app.session_flashed_messages', array('opt' => 1))

            <form novalidate="novalidate" action="/search/{{$table}}/{{$id}}" method="post">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">

            <div class="panel-group" id="accordion">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title" data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
                                1. Buscar por fecha de registro<i class="indicator glyphicon glyphicon-minus pull-right"></i>
                            </a>
                            {{--
                            <input type="radio" name="group1" value="1" data-toggle="collapse" data-parent="#accordion"
                                href="#collapseOne">
                                <i class="indicator glyphicon glyphicon-chevron-right pull-right"></i> 1. Buscar por fecha
                            --}}
                        </h4>
                    </div>
                    <div id="collapseOne" class="panel-collapse collapse in">
                        <div class="panel-body" align="center">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <label for="fecha_desde" style="font-weight: normal; margin-bottom: 0">Desde:</label>
                                    <input type="date" name="fecha_desde" id="fecha_desde" step="1" min="2014-01-01" value="">
                                </span>
                                <span class="input-group-addon">
                                    <label for="fecha_hasta" style="font-weight: normal; margin-bottom: 0">Desde:</label>
                                    <input type="date" name="fecha_hasta" id="fecha_hasta" step="1" min="2014-01-01"
                                           value="{{ date("Y-m-d") }}">
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo">
                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo">
                                2. Buscar por contenido<i class="indicator glyphicon glyphicon-plus pull-right"></i>
                            </a>
                            {{--
                            <!--<input type="radio" name="group1" value="2" data-toggle="collapse" data-parent="#accordion"
                            href="#collapseTwo">
                            <i class="indicator glyphicon glyphicon-chevron-right pull-right"></i> 2. Buscar por contenido-->
                            --}}
                        </h4>
                    </div>
                    <div id="collapseTwo" class="panel-collapse collapse">
                        <div class="panel-body" align="center">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-search"></i></span>

                                <label for="parametro"></label>
                                <select required="required" class="form-control" name="parametro" id="parametro">
                                    @if($table=='bills')
                                        <option value="code">Número de Factura</option>
                                    @elseif($table=='cites')
                                        <option value="codigo_cite">Codigo de CITE</option>
                                        <option value="responsable">Responsable</option>
                                        <option value="para_empresa">Para Empresa</option>
                                        <option value="destino">Destinatario</option>
                                        <option value="asunto">Asunto</option>
                                    @elseif($table=='ocs')
                                        <option value="provider">Proveedor</option>
                                        <option value="proy_name">Proyecto</option>
                                        <option value="client">Cliente</option>
                                        <option value="client_oc">OC de Cliente</option>
                                        <option value="status">Estado</option>
                                        <option value="pm_name">Project Manager</option>
                                    @elseif($table=='orders')
                                        <option value="code">Código de Orden</option>
                                        <option value="type">Tipo de Orden</option>
                                        <option value="client">Cliente</option>
                                        <option value="status">Estado</option>
                                    @elseif($table=='projects')
                                        <option value="name">Nombre de proyecto</option>
                                        <option value="client">Cliente</option>
                                        <option value="bill_number">Número de factura</option>
                                    @elseif($table=='tasks')
                                        <option value="name">Nombre de item</option>
                                        <option value="units">Unidades</option>
                                        <option value="status">Estado</option>
                                        <option value="responsible">Responsable</option>
                                    @elseif($table=='users')
                                        <option value="name">Nombre de usuario</option>
                                        <option value="area">Area</option>
                                    @elseif($table=='providers')
                                        <option value="prov_name">Nombre o razón social</option>
                                        <option value="nit">Número de NIT</option>
                                        <option value="contact_name">Persona de contacto</option>
                                    @elseif($table=='sites')
                                        <option value="name">Nombre</option>
                                        <option value="status">Estado</option>
                                        <option value="resp_name">Responsable de ABROS</option>
                                        <option value="contact_name">Responsable del cliente</option>
                                    @elseif($table=='assignments')
                                        <option value="name">Nombre de proyecto</option>
                                        <option value="client">Cliente</option>
                                        <option value="type">Tipo de trabajo</option>
                                        <option value="status">Estado</option>
                                        <option value="resp_name">Responsable de ABROS</option>
                                        <option value="contact_name">Responsable del cliente</option>
                                    @elseif($table=='invoices')
                                        <option value="oc_code">Código de OC</option>
                                        <option value="number">Número de factura</option>
                                        <option value="provider">Proveedor</option>
                                        <option value="transaction_code">Código de transacción</option>
                                    @elseif($table=='maintenances')
                                        <option value="active">Número de placa o número de serie del activo</option>
                                        <option value="user_name">Nombre de responsable</option>
                                    @elseif($table=='vehicles'||$table=='drivers')
                                        <option value="license_plate">Número de placa</option>
                                        <option value="type">Tipo de vehículo</option>
                                        <option value="model">Modelo</option>
                                        <option value="status">Estado actual</option>
                                        <option value="destination">Destino actual</option>
                                    @elseif($table=='devices'||$table=='operators')
                                        <option value="serial">Número de serie</option>
                                        <option value="type">Tipo de equipo</option>
                                        <option value="model">Modelo</option>
                                        <option value="status">Estado actual</option>
                                        <option value="destination">Destino actual</option>
                                    @endif
                                    @if($table=='drivers'||$table=='operators')
                                        <option value="who_receives">Quién recibe</option>
                                        <option value="who_delivers">Quién entrega</option>
                                    @endif
                                </select>
                                <input required="required" type="text" class="form-control" name="buscar" placeholder="Buscar">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

                @include('app.loader_gif')

                <div class="form-group" align="center">
                    <button type="submit" class="btn btn-success"
                            onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                        <i class="fa fa-search"></i> Buscar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('footer')
@endsection

@section('javascript')
<script>
    /*
    function toggleChevron(e) {
        $(e.target)
                .prev('.panel-heading')
                .find("i.indicator")
                .toggleClass('glyphicon-chevron-down glyphicon-chevron-right');
    }
    $('#accordion').on('hide.bs.collapse', toggleChevron)
    .on('show.bs.collapse', toggleChevron);
    */

    $("#wait").hide();

    $('.collapse').on('show.bs.collapse', function () {
        $(this).prev(".panel-heading").find('.indicator').toggleClass("glyphicon-minus glyphicon-plus");
    }).on('hide.bs.collapse', function () {
        $(this).prev(".panel-heading").find('.indicator').toggleClass("glyphicon-plus glyphicon-minus");
    });
</script>
@endsection
