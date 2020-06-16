<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 01/02/2017
 * Time: 04:13 PM
 */
?>

<style>
    .modal-footer {
        background-color: #f4f4f4;
    }
</style>

<div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content" style="overflow:hidden;">

        <div class="modal-header alert-info">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">
                {{ 'Buscar ' }}
                @if($table == 'cites') {{ 'CITE' }}
                @elseif($table == 'ocs') {{ 'orden de compra' }}
                @elseif($table == 'assignments') {{ 'asignación' }}
                @elseif($table == 'users') {{ 'usuario' }}
                @elseif($table == 'providers') {{ 'proveedor' }}
                @elseif($table == 'sites') {{ 'sitio' }}
                @elseif($table == 'contacts') {{ 'contacto' }}
                @elseif($table == 'orders') {{ 'orden' }}
                @elseif($table == 'bills') {{ 'factura' }}
                @elseif($table == 'tasks') {{ 'items' }}
                @elseif($table == 'invoices') {{ 'información de pagos' }}
                @elseif($table == 'vehicles') {{ 'vehículo' }}
                @elseif($table == 'devices') {{ 'equipo' }}
                @elseif($table == 'maintenances') {{ 'registro de mantenimiento' }}
                @elseif($table == 'vehicle_conditions') {{ 'registro de condiciones' }}
                @elseif($table == 'contracts') {{ 'contrato' }}
                @elseif($table == 'guarantees') {{ 'poliza' }}
                @elseif($table == 'events') {{ 'evento' }}
                @elseif($table == 'oc_certificates') {{ 'certificado de aceptación' }}
                @elseif($table == 'calibrations') {{ 'registro de calibración' }}
                @elseif($table == 'projects') {{ 'proyecto' }}
                @elseif($table == 'warehouses') {{ 'almacén' }}
                @elseif($table == 'materials') {{ 'material' }}
                @elseif($table == 'files') {{ 'archivo' }}
                @elseif($table == 'emails') {{ 'correo' }}
                @elseif($table == 'rbs_viatics') {{ 'solicitud' }}
                @elseif($table == 'corp_lines') {{ 'línea' }}
                @elseif($table == 'item_categories') {{ 'categoría' }}
                @elseif($table == 'items') {{ 'items' }}
                @else {{ 'registro' }}
                @endif
            </h4>
        </div>

        <form novalidate="novalidate" action="/search_results/{{$table}}/{{$id}}" method="get">
            {{-- Form uses get method to function with laravel's pagination --}}
            <div class="modal-body">

                @include('app.session_flashed_messages', array('opt' => 1))

                {{-- Token field not needed with get form
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                --}}

                <div class="panel-group" id="accordion">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title" data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
                                <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
                                    1. Buscar por fecha de registro<i class="indicator glyphicon glyphicon-minus pull-right"></i>
                                </a>
                            </h4>
                        </div>
                        <div id="collapseOne" class="panel-collapse collapse">
                            <div class="panel-body" align="center">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <label for="fecha_desde" style="font-weight: normal; margin-bottom: 0">Desde:</label>
                                        <input type="date" name="fecha_desde" id="fecha_desde" step="1" min="2014-01-01" value="">
                                    </span>

                                    <span class="input-group-addon">
                                        <label for="fecha_hasta" style="font-weight: normal; margin-bottom: 0">Hasta:</label>
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
                            </h4>
                        </div>
                        <div id="collapseTwo" class="panel-collapse collapse in">
                            <div class="panel-body" align="center">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-search"></i></span>

                                    <label for="parametro"></label>
                                    <select required="required" class="form-control" name="parametro" id="parametro">
                                        <option value="all">Todas las columnas</option>
                                        @if ($table == 'bills')
                                            <option value="code">Número de Factura</option>
                                        @elseif ($table == 'cites')
                                            <option value="code">Código de CITE</option>
                                            <option value="responsable">Responsable</option>
                                            <option value="para_empresa">Para Empresa</option>
                                            <option value="destino">Destinatario</option>
                                            <option value="asunto">Asunto</option>
                                        @elseif ($table == 'ocs')
                                            <option value="id">Número de OC</option>
                                            <option value="code">Código de OC</option>
                                            <option value="provider">Proveedor</option>
                                            <option value="type">Tipo de OC</option>
                                            <option value="proy_name">Proyecto</option>
                                            <option value="client">Cliente</option>
                                            <option value="client_oc">OC de Cliente</option>
                                            <option value="status">Estado</option>
                                            <option value="pm_name">Responsable</option>
                                        @elseif ($table == 'orders')
                                            <option value="code">Código de Orden</option>
                                            <option value="type">Tipo de Orden</option>
                                            <option value="client">Cliente</option>
                                            <option value="status">Estado</option>
                                        @elseif ($table == 'tasks')
                                            <option value="client_code">Código de cliente</option>
                                            <option value="code">Código interno</option>
                                            <option value="name">Nombre de item</option>
                                            <option value="units">Unidades</option>
                                            <option value="status">Estado</option>
                                            <option value="responsible">Responsable</option>
                                        @elseif ($table == 'users')
                                            <option value="full_name">Nombre de usuario</option>
                                            <option value="area">Area</option>
                                        @elseif ($table == 'providers')
                                            <option value="prov_name">Nombre o razón social</option>
                                            <option value="nit">Número de NIT</option>
                                            <option value="specialty">Área de especialidad</option>
                                            <option value="contact_name">Persona de contacto</option>
                                            <option value="email">Correo electrónico</option>
                                        @elseif ($table == 'sites')
                                            <option value="code">Código de sitio</option>
                                            <option value="name">Nombre</option>
                                            <option value="status">Estado</option>
                                            <option value="resp_name">Responsable de ABROS</option>
                                            <option value="contact_name">Responsable del cliente</option>
                                            <option value="du_id">DU ID</option>
                                            <option value="isdp_account">Cuenta de ISDP</option>
                                            <option value="order_code">Orden (PO)</option>
                                        @elseif ($table == 'assignments')
                                            <option value="code">Código interno</option>
                                            <option value="client_code">Código de cliente</option>
                                            <option value="literal_code">Identificador</option>
                                            <option value="name">Nombre</option>
                                            <option value="client">Cliente</option>
                                            <option value="type">Tipo de trabajo</option>
                                            <option value="status">Estado</option>
                                            <option value="project_name">Proyecto al que pertenece</option>
                                            <option value="resp_name">Responsable de ABROS</option>
                                            <option value="contact_name">Responsable del cliente</option>
                                            <option value="site_name">Contiene el sitio...</option>
                                            <option value="du_id">DU ID de sitio</option>
                                            <option value="isdp_account">Cuenta de ISDP de sitio</option>
                                            <option value="order_code">Orden (PO) de sitio</option>
                                        @elseif ($table == 'invoices')
                                            <option value="oc_code">Código de OC</option>
                                            <option value="number">Número de factura</option>
                                            <option value="provider">Proveedor</option>
                                            <option value="transaction_code">Código de transacción</option>
                                        @elseif ($table == 'maintenances')
                                            <option value="active">Número de placa o número de serie del activo</option>
                                            <option value="user_name">Nombre de responsable</option>
                                        @elseif ($table == 'vehicles' || $table == 'drivers')
                                            <option value="license_plate">Número de placa</option>
                                            <option value="type">Tipo de vehículo</option>
                                            <option value="model">Modelo</option>
                                            <option value="status">Estado actual</option>
                                            <option value="destination">Destino actual</option>
                                        @elseif ($table == 'devices' || $table == 'operators')
                                            <option value="serial">Número de serie</option>
                                            <option value="type">Tipo de equipo</option>
                                            <option value="model">Modelo</option>
                                            <option value="status">Estado actual</option>
                                            <option value="destination">Destino actual</option>
                                        @elseif ($table == 'events')
                                            <option value="description">Tipo de evento</option>
                                            <option value="detail">Contenido / detalle</option>
                                            <option value="responsible_name">Responsable</option>
                                        @endif
                                        @if ($table == 'devices' || $table == 'vehicles')
                                            <option value="responsible_name">Responsable actual</option>
                                        @endif
                                        @if ($table == 'drivers' || $table == 'operators')
                                            <option value="who_receives">Quién recibe</option>
                                            <option value="who_delivers">Quién entrega</option>
                                        @endif
                                        @if ($table == 'contacts')
                                            <option value="name">Nombre</option>
                                            <option value="company">Empresa</option>
                                            <option value="position">Cargo</option>
                                        @endif
                                        @if ($table == 'vehicle_conditions')
                                            <option value="gas_bill">Número de factura (combustible)</option>
                                        @endif
                                        @if ($table == 'contracts')
                                            <option value="code">Código interno</option>
                                            <option value="client_code">Código de cliente</option>
                                            <option value="client">Cliente</option>
                                            <option value="objective">Objeto del contrato</option>
                                        @endif
                                        @if ($table == 'guarantees')
                                            <option value="code">Número de poliza</option>
                                            <option value="company">Empresa emisora</option>
                                            <option value="type">Tipo de poliza</option>
                                            <option value="applied_to">Objeto</option>
                                            <option value="closed">Estado (0=Activo, 1=Archivado)</option>
                                        @endif
                                        @if ($table == 'oc_certificates')
                                            <option value="code">Código de certificado</option>
                                            <option value="oc_id">Número de OC</option>
                                            <option value="type_reception">Tipo de aceptación</option>
                                        @endif
                                        @if ($table == 'calibrations')
                                            <option value="type">Tipo de equipo</option>
                                            <option value="model">Modelo de equipo</option>
                                            <option value="serial">Número de serie de equipo</option>
                                            <option value="detail">Detalle de trabajos</option>
                                            <option value="completed">Estado actual de calibración</option>
                                        @endif
                                        @if ($table == 'vehicle_histories' || $table == 'device_histories')
                                            <option value="type">Tipo de registro</option>
                                            <option value="contents">Contenido de registro</option>
                                            <option value="status">
                                                {{ 'Estado de '.($table=='vehicle_histories' ? 'vehículo' : 'equipo') }}
                                            </option>
                                        @endif
                                        @if ($table == 'projects')
                                            <option value="code">Código de proyecto</option>
                                            <option value="name">Nombre</option>
                                            <option value="description">Descripción</option>
                                            <option value="client">Cliente</option>
                                            <option value="type">Área de trabajo</option>
                                            <option value="award">Tipo de adjudicación</option>
                                        @endif
                                        @if ($table == 'dead_intervals_assig' || $table == 'dead_intervals_st')
                                            <option value="closed">Estado (0 = Activo, 1 = Cerrado)</option>
                                            <option value="reason">Motivo</option>
                                        @endif
                                        @if ($table == 'warehouses')
                                            <option value="name">Nombre</option>
                                            <option value="location">Dirección</option>
                                        @endif
                                        @if ($table == 'materials')
                                            <option value="code">Código</option>
                                            <option value="name">Nombre</option>
                                            <option value="type">Tipo</option>
                                            <option value="description">Descripción</option>
                                            <option value="units">Unidades</option>
                                            <option value="brand">Marca</option>
                                            <option value="supplier">Proveedor</option>
                                            <option value="category">Categoría</option>
                                        @endif
                                        @if ($table == 'wh_entries' || $table == 'wh_outlets')
                                            <option value="received_by">Persona que recibe</option>
                                            <option value="delivered_by">Persona que entrega</option>
                                            <option value="material_name">Material</option>
                                            <option value="warehouse_name">Almacén</option>
                                            <option value="reason">Motivo</option>
                                        @endif
                                        @if ($table == 'wh_entries')
                                            <option value="entry_type">Tipo de entrada</option>
                                        @endif
                                        @if ($table == 'wh_outlets')
                                            <option value="outlet_type">Tipo de salida</option>
                                        @endif
                                        @if ($table == 'wh_events')
                                            <option value="description">Descripción</option>
                                            <option value="detail">Detalle de evento</option>
                                            <option value="responsible_name">Responsable</option>
                                        @endif
                                        @if ($table == 'files')
                                            <option value="name">Nombre</option>
                                            <option value="description">Descripción</option>
                                            <option value="type">Extensión</option>
                                            <option value="imageable_type">Pertenece a</option>
                                            <option value="status">Estado (0=Activo, 1=Archivado)</option>
                                            <option value="user_name">Subido por</option>
                                        @endif
                                        @if ($table == 'emails')
                                            <option value="sent_to">Enviado a</option>
                                            <option value="sent_cc">cc</option>
                                            <option value="subject">Asunto</option>
                                            <option value="content">Contenido</option>
                                            <option value="success">Estado (0=Falló, 1=Enviado)</option>
                                        @endif
                                        @if ($table == 'exported_files')
                                            <option value="url">URL</option>
                                            <option value="description">Descripción</option>
                                            <option value="exportable_type">Tipo de modelo</option>
                                            <option value="exportable_id">Id de modelo</option>
                                            <option value="name">Exportado por (nombre)</option>
                                        @endif
                                        @if ($table == 'rbs_viatics')
                                            <option value="status_name">Estado</option>
                                            <option value="work_description">Trabajo</option>
                                            <option value="tech_name">Técnico</option>
                                            <option value="site_name">Sitio</option>
                                        @endif
                                        @if ($table == 'device_requirements')
                                            <option value="code">Código de requerimiento</option>
                                            <option value="type">Tipo de requerimiento</option>
                                            <option value="serial">Número de serie de equipo</option>
                                            <option value="model">Modelo de equipo</option>
                                            <option value="person_from">Responsable actual</option>
                                            <option value="person_for">Entregar a</option>
                                            <option value="reason">Motivo de requerimiento</option>
                                        @endif
                                        @if ($table == 'vehicle_requirements')
                                            <option value="code">Código de requerimiento</option>
                                            <option value="type">Tipo de requerimiento</option>
                                            <option value="license_plate">Número de placa de vehículo</option>
                                            <option value="model">Modelo de vehículo</option>
                                            <option value="person_from">Responsable actual</option>
                                            <option value="person_for">Entregar a</option>
                                            <option value="reason">Motivo de requerimiento</option>
                                        @endif
                                        @if ($table == 'corp_lines')
                                            <option value="number">Número de línea</option>
                                            <option value="service_area">Área de servicio</option>
                                            <option value="technology">Tecnología habilitada</option>
                                            <option value="pin">Código PIN</option>
                                            <option value="puk">Código PUK</option>
                                            <option value="status">Estado</option>
                                            <option value="responsible_name">Responsable actual</option>
                                        @endif
                                        @if ($table == 'corp_line_assignations')
                                            <option value="line_number">Número de línea</option>
                                            <option value="type">Tipo de asignación</option>
                                            <option value="service_area">Área de servicio</option>
                                            <option value="resp_after_name">Responsable actual</option>
                                            <option value="resp_before_name">Responsable anterior</option>
                                        @endif
                                        @if ($table == 'corp_line_requirements')
                                            <option value="code">Código de requerimiento</option>
                                            <option value="user_name">Registrado por</option>
                                            <option value="person_for">Solicitado para (a quién se dará la línea)</option>
                                            <option value="reason">Motivo de requerimiento</option>
                                        @endif
                                        @if ($table == 'item_categories')
                                            <option value="name">Nombre de categoría</option>
                                            <option value="description">Detalle o descripción</option>
                                            <option value="area">Área de trabajo</option>
                                            <option value="client">Cliente</option>
                                            <option value="status">Estado (1=Vigente, 0=Archivada)</option>
                                        @endif
                                        @if ($table == 'items')
                                            <option value="number">Número de item</option>
                                            <option value="client_code">Código de cliente</option>
                                            <option value="description">Nombre o descripción</option>
                                            <option value="detail">Detalle o información adicional</option>
                                            <option value="units">Unidades</option>
                                            <option value="subcategory">Subcategoría</option>
                                        @endif
                                        @if ($table == 'vhc_failure_reports')
                                            <option value="code">Código</option>
                                            <option value="user_name">Persona que reporta la falla</option>
                                            <option value="reason">Motivo o descripción de falla</option>
                                        @endif
                                        @if ($table == 'dvc_failure_reports')
                                            <option value="code">Código</option>
                                            <option value="user_name">Persona que reporta la falla</option>
                                            <option value="reason">Motivo o descripción de la falla</option>
                                        @endif
                                        @if ($table == 'stipend_requests')
                                            <option value="code">Código</option>
                                            <option value="employee_name">Solicitado para</option>
                                            <option value="reason">Motivo de solicitud</option>
                                            <option value="work_area">Área de trabajo</option>
                                            <option value="observations">Observaciones</option>
                                            <option value="status">Estado de la solicitud</option>
                                        @endif
                                        @if ($table == 'employees')
                                            <option value="code">Código</option>
                                            <option value="first_name">Nombres</option>
                                            <option value="last_name">Apellidos</option>
                                            <option value="id_card">Número de Carnet de identidad</option>
                                            <option value="role">Cargo</option>
                                            <option value="area">Área de trabajo</option>
                                            <option value="branch">Sucursal</option>
                                            <option value="phone">Número de teléfono</option>
                                        @endif
                                        @if ($table == 'branches')
                                            <option value="name">Nombre</option>
                                            <option value="city">Ciudad</option>
                                            <option value="address">Dirección</option>
                                            <option value="head_name">Encargado o responsable de sucursal</option>
                                        @endif
                                        @if ($table == 'tenders')
                                            <option value="name">Nombre de la licitación</option>
                                            <option value="code">Código</option>
                                            <option value="description">Descripción de la licitación</option>
                                            <option value="client">Cliente</option>
                                            <option value="area">Area de trabajo</option>
                                            <option value="status">Estado</option>
                                        @endif
                                        @if ($table === 'client_sessions')
                                            <option value="service_accessed">Servicio</option>
                                        @endif
                                    </select>
                                    <input required="required" type="text" class="form-control" name="buscar"
                                           placeholder="Parámetro de búsqueda">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success" onclick="this.disabled=true; this.form.submit()">
                    <i class="fa fa-search"></i> Buscar
                </button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>

        </form>

    </div>

</div>

<script>
    $('.collapse').on('show.bs.collapse', function () {
        $(this).prev(".panel-heading").find('.indicator').toggleClass("glyphicon-minus glyphicon-plus");
    }).on('hide.bs.collapse', function () {
        $(this).prev(".panel-heading").find('.indicator').toggleClass("glyphicon-plus glyphicon-minus");
    });
</script>
