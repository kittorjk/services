@extends('layouts.info_master')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/info_tabs.css") }}">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

{{--
@section('menu_options')
  <li><a href="#">&ensp;<i class="fa fa-list-alt"></i> O.C.s <span class="caret"></span>&ensp;</a>
      <ul class="sub-menu">
          <li><a href="{{ '/oc' }}"><i class="fa fa-bars fa-fw"></i> Ver todo</a></li>
          @if($user->action->oc_add)
              <li><a href="{{ '/oc/create' }}"><i class="fa fa-plus fa-fw"></i> Agregar O.C.</a></li>
              <li>
                  <a href="{{ '/oc/create?action=cmp' }}">
                      <i class="fa fa-plus fa-fw"></i> Agregar O.C. complementaria
                  </a>
              </li>
          @endif
          <li><a href="{{ '/invoice/create' }}"><i class="fa fa-plus fa-fw"></i> Agregar factura</a></li>
      </ul>
  </li>
  @if ($user->priv_level >=1)
      <li><a href="{{ '/provider' }}">&ensp;<i class="fa fa-truck"></i> PROVEEDORES&ensp;</a></li>
  @endif
  @if ($user->priv_level>=2)
      <li><a href="{{ '/oc_certificate' }}">&ensp;<i class="fa fa-file-text-o"></i> CERTIFICADOS&ensp;</a></li>
  @endif
  <li><a href="{{ '/invoice' }}">&ensp;<i class="fa fa-money"></i> PAGOS&ensp;</a></li>
  <li>
    <a data-toggle="modal" href="#searchBox">&ensp;<i class="fa fa-search"></i> BUSCAR&ensp;</a>
  </li>
@endsection
--}}

@section('content')
    <div id="loginbox" class="mg-tp-px-50 col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1 mg-btm-px-40">

        <div class="panel panel-10gray">

            <div class="panel-heading" align="center">
                <div class="panel-title">
                    <div class="pull-left">
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#details" data-toggle="tab"> Detalles </a></li>
                            @if ($oc->status <> 'Anulado')
                                <li><a href="#obligations" data-toggle="tab"> Obligaciones</a></li>
                            @endif
                        </ul>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <!--<div class="panel-title">Detalles de OC</div>-->
            </div>

            <div class="panel-body">
              <div class="tab-content">

                <div class="tab-pane fade in active" id="details">

                    {{--<div class="col-lg-5 mg20">
                        <a href="#" onclick="history.back();" class="btn btn-warning">
                            <i class="fa fa-arrow-left"></i> Volver
                        </a>
                        <a href="{{ '/oc' }}" class="btn btn-warning" title="Ir a la tabla de OCs">
                            <i class="fa fa-arrow-up"></i> OCs
                        </a>
                    </div>--}}

                    <div class="col-lg-12" align="right">
                        @if (($oc->status == 'Creado' && $user->action->oc_apv_tech && $oc->type == 'Servicio') ||
                            ($oc->status == 'Creado' && $oc->type == 'Compra de material' && $user->action->oc_apv_gg) ||
                            ($oc->status == 'Aprobado Gerencia Tecnica' && $user->action->oc_apv_gg) ||
                            (($oc->status =='Aprobado Gerencia Tecnica' || $oc->status == 'Creado') && $user->priv_level == 4))
                            <button type="button" class="btn btn-primary" title="Aprobar Orden"
                                    data-toggle="modal" data-target="#approveBox">
                                <i class="fa fa-check"></i>
                            </button>
                        @endif
                        @if ($oc->status == 'Observado' && ($user->id == $oc->user_id || $user->priv_level == 4))
                            <a href="/oc/request_approval/{{ $oc->id }}" class="btn btn-primary request_approval">
                                <i class="fa fa-send"></i>
                            </a>
                        @endif
                        @if ((($oc->status == 'Creado' || $oc->status == 'Aprobado Gerencia Tecnica') &&
                            ($user->action->oc_apv_gg || ($user->action->oc_apv_gtec && $oc->type == 'Servicio'))) || $user->priv_level == 4)
                            <button type="button" class="btn btn-warning" title="Observar Orden"
                                    data-toggle="modal" data-target="#observeBox">
                                <i class="fa fa-eye"></i>
                            </button>
                        @endif
                        @if (($oc->status <> 'Anulado' && $oc->status != 'Aprobado Gerencia General' && $user->action->oc_nll &&
                            ($user->id == $oc->user_id || $user->action->oc_edt)) || $user->priv_level == 4)
                            <button type="button" class="btn btn-danger" title="Anular Orden"
                                    data-toggle="modal" data-target="#cancelBox">
                                <i class="fa fa-ban"></i>
                            </button>
                        @endif
                        <a href="" onclick="window.close()" class="btn btn-dark" title="Cerrar">
                            <i class="fa fa-times"></i>
                        </a>
                    </div>

                    <div class="col-sm-12 mg10">
                        @include('app.session_flashed_messages', array('opt' => 0))
                    </div>

                    @if ($oc->status == 'Anulado' || $oc->status == 'Observado')
                        <div class="col-lg-12">
                            <div class="alert alert-{{ $oc->status == 'Anulado' ? 'danger' : 'warning' }}" align="center" id="motivo_anulacion">
                                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                <i class="fa fa-{{ 'warning' }} fa-2x pull-left"></i>
                                {{ $oc->observations }}
                            </div>
                        </div>
                    @endif

                    <div class="col-sm-12 mg10 mg-tp-px-10">
                        <table class="table table-hover table-bordered">
                            <thead>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="8" rowspan="7" align="center">
                                        <h2>ORDEN DE COMPRA</h2>
                                        @if ($oc->status == 'Anulado')
                                            <h2 style="color:red">ANULADO</h2>
                                        @elseif ($oc->status == 'Observado')
                                            <h2 style="color:gold">OBSERVADO</h2>
                                        @endif
                                        <h4>ABROS Technologies srl<br>
                                        La Paz, Av. 16 de Julio N°1642 P.2 Of. 202<br>
                                        Tel/Fax: (591-2) 2112908</h4>
                                        @if ($oc->linked)
                                            <br>
                                            <h4>
                                                {{ 'OC complementaria a '.$oc->linked->code }}
                                            </h4>
                                        @endif
                                    </td>
                                    <th colspan="2">Número</th>
                                    <td colspan="2">{{ $oc->id }}</td>
                                </tr>
                                <tr>
                                    <th colspan="2">Fecha</th>
                                    <td colspan="2">{{ date_format($oc->created_at,'d-m-Y') }}</td>
                                </tr>
                                <tr>
                                    <th colspan="2">Modalidad</th>
                                    <td colspan="2">Invitación directa</td>
                                </tr>
                                <tr>
                                    <th colspan="2">Cliente</th>
                                    <td colspan="2">{{ $oc->client }}</td>
                                </tr>
                                <tr>
                                    <th colspan="2">PC Cliente</th>
                                    <td colspan="2">{{ $oc->client_oc && $oc->client_oc > 0 ? $oc->client_oc : '' }}</td>
                                </tr>
                                <tr>
                                    <th colspan="2">Doc. Asig.</th>
                                    <td colspan="2">{{ $oc->client_ad }}</td>
                                </tr>
                                <tr>
                                    <th colspan="2">Centro de costos</th>
                                    <td colspan="2">
                                        {{ $oc->assignment && $oc->assignment->cost_center && $oc->assignment->cost_center > 0 ?
                                            $oc->assignment->cost_center : '' }}
                                    </td>
                                </tr>
                                <tr><td colspan="12"></td></tr>
                                <tr>
                                    <th width="20%">Proveedor</th>
                                    <td colspan="3">
                                        {{ $oc->provider_record ? $oc->provider_record->prov_name : $oc->provider }}
                                    </td>
                                    <th>NIT</th>
                                    <td colspan="3">{{ $oc->provider_record ? $oc->provider_record->nit : '' }}</td>
                                    <th>TC</th>
                                    <td colspan="3" align="right">6.96</td>
                                </tr>
                                <tr>
                                    <th width="20%">Dirección</th>
                                    <td colspan="7">{{ $oc->provider_record ? $oc->provider_record->address : '' }}</td>
                                    <td>Moneda</td>
                                    <td colspan="3">Bolivianos</td>
                                </tr>
                                <tr>
                                    <th width="20%">Teléfono</th>
                                    <td width="20%">{{ $oc->provider_record && $oc->provider_record->phone_number != 0 ? $oc->provider_record->phone_number : '' }}</td>
                                    <td></td>
                                    <th width="20%">Fax</th>
                                    <td>{{ $oc->provider_record && $oc->provider_record->fax != 0 ? $oc->provider_record->fax : '' }}</td>
                                    <td></td>
                                    <th>Directo</th>
                                    <td>{{ $oc->provider_record && $oc->provider_record->alt_phone_number != 0 ? $oc->provider_record->alt_phone_number : '' }}</td>
                                    <td colspan="4" rowspan="2"></td>
                                </tr>
                                <tr>
                                    <th width="20%">Persona de contacto</th>
                                    <td colspan="3">{{ $oc->provider_record ? $oc->provider_record->contact_name : '' }}</td>
                                    <td>Email</td>
                                    <td colspan="3">{{ $oc->provider_record ? $oc->provider_record->email : '' }}</td>
                                </tr>
                                <tr>
                                    <th width="20%">Proyecto</th>
                                    <td colspan="11">{{ $oc->proy_name.' - '.$oc->client }}</td>
                                </tr>
                                <tr style="background-color: #4d8ccb">
                                    <th>Item</th>
                                    <th colspan="7">Descripción</th>
                                    <th>Cantidad</th>
                                    <th>Unidades</th>
                                    <th>Precio unitario bruto [Bs]</th>
                                    <th>Importe total bruto [Bs]</th>
                                    {{--
                                    <td width="8%"></td>
                                    <td width="35%">Descripción</td>
                                    <td width="15%">Cantidad</td>
                                    <td width="15%">Unidades</td>
                                    <td width="15%" title="Precio unitario [Bs]">P. Unit. [Bs]</td>
                                    <td width="12%"></td>
                                    --}}
                                </tr>
                                @if ($oc->rows->count() > 0)
                                    @foreach($oc->rows as $row)
                                        <tr>
                                            <td>{{ $row->num_order }}</td>
                                            <td colspan="7">{{ $row->description }}</td>
                                            <td align="right">{{ number_format($row->qty, 2) }}</td>
                                            <td align="center">{{ $row->units }}</td>
                                            <td align="right">{{ number_format($row->unit_cost, 2) }}</td>
                                            <td align="right">{{ number_format(($row->qty * $row->unit_cost), 2) }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr><td colspan="12">{{ 'No se registraron items para esta orden' }}</td></tr>
                                @endif
                                <tr>
                                    <th colspan="10">Total Orden de compra bruto (con impuestos)</th>
                                    <td>Bs</td>
                                    <td align="right">{{ $oc->oc_amount }}</td>
                                    {{--<td>{{ number_format($oc->oc_amount,2).' Bs' }}</td>--}}
                                </tr>
                                <tr>
                                    <th>Son</th>
                                    <td colspan="11">{{ $total_amount_literal }}</td>
                                </tr>
                                <tr><td colspan="12"></td></tr>
                                <tr>
                                    <th colspan="2">Lugar de entrega</th>
                                    <td colspan="10">{{ $oc->delivery_place ?: '' }}</td>
                                </tr>
                                <tr>
                                    <th colspan="2">Plazo de entrega</th>
                                    <td colspan="10">
                                        @if ($oc->delivery_term && $oc->delivery_term != 0)
                                            {{ ($oc->delivery_term == 1 ? '1 día' : $oc->delivery_term.' días').
                                                ' de la fecha de la presente Orden de Compra' }}
                                        @else
                                            {{ '' }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th colspan="2">Penalidades</th>
                                    <td colspan="8">Se aplicara una penalidad por dia de retraso del 0,7% del total de la OC</td>
                                    <td>Bs</td>
                                    <td align="right">{{ number_format((($oc->oc_amount * 0.7) / 100), 2) }}</td>
                                </tr>
                                <tr>
                                    <th colspan="2">Términos de pago</th>
                                    <td colspan="10">{{ $payment_terms }}</td>
                                </tr>
                                <tr>
                                    <td colspan="12"><strong>Datos cuenta bancaria proveedor</strong><br>
                                    {{ $oc->provider_record ? $oc->provider_record->bnk_name.' '.$oc->provider_record->bnk_account.' / '.$oc->provider_record->contact_name : '' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="12"><strong>Notas:</strong><br>
                                        1. Esta Orden de compra se lleva a cabo bajo Términos y Condiciones acordados y otros Documentos válidos, firmados por ABROS y el Proveedor.<br>
                                        2. Otras consideraciones necesarias para cumplir con esta Orden de Compra se encuentran al reverso.<br>
                                        3. El proveedor debe firmar como constancia de aceptacion al pie de la presente y dejar una copia o enviar por cualquier medio.<br>
                                        4. El proveedor debe registrar sus datos comerciales y cuentas bancarias en Administración de ABROS<br>
                                        5. El proveedor debe emitir factura a nombre de ABROS Technologies srl - NIT 160462020<br>
                                        &ensp;
                                    </td>
                                </tr>
                                {{--<tr><td colspan="12"></td></tr>--}}
                                <tr>
                                    <th width="20%">PM</th>
                                    <td colspan="3">
                                        {{ $oc->responsible ? $oc->responsible->name : 'No asignado' }}
                                    </td>
                                    <th>Teléfono</th>
                                    <td colspan="3">{{ $oc->responsible && $oc->responsible->phone != 0 ? $oc->responsible->phone : '' }}</td>
                                    <th>Email</th>
                                    <td colspan="3">{{ $oc->responsible ? $oc->responsible->email : '' }}</td>
                                </tr>
                                <tr><td colspan="12"></td></tr>
                                <tr>
                                    <td colspan="3">
                                        <strong>Orden creada por</strong><br>
                                        {{ $oc->user->name }}
                                    </td>
                                    <td colspan="3">
                                        <strong>Autorización de Gerencia Técnica</strong><br>
                                        {{ $gtec_content }}
                                    </td>
                                    <td colspan="3">
                                        <strong>Autorización de Gerencia General</strong><br>
                                        {{ $gg_content }}
                                    </td>
                                    <td colspan="3" align="center">
                                        <img src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->size(200)->margin(2)->generate($qr_data)) !!} ">
                                    </td>
                                </tr>
                                {{--
                                <tr>
                                    <th>Tipo de OC:</th>
                                    <td colspan="3">{{ $oc->type }}</td>
                                </tr>

                                @foreach($oc->complements as $complement)
                                    <tr>
                                        <th>OC complementaria:</th>
                                        <td colspan="3">
                                            <a href="/oc/{{ $complement->id }}">{{ $complement->code }}</a>
                                        </td>
                                    </tr>
                                @endforeach

                                @if($oc->linked)
                                    <tr>
                                        <th>Complemento a:</th>
                                        <td colspan="3">
                                            <a href="/oc/{{ $oc->linked->id }}">{{ $oc->linked->code }}</a>
                                        </td>
                                    </tr>
                                @endif

                                @if($oc->events->count() > 0)
                                    <tr>
                                        <th>Eventos:</th>
                                        <td colspan="3">
                                            <a href="/event/oc/{{ $oc->id }}">{{ 'Ver eventos' }}</a>
                                        </td>
                                    </tr>
                                @endif
                                --}}
                            </tbody>
                        </table>
                    </div>

                    @if(($oc->status <> 'Anulado' && $oc->status != 'Aprobado Gerencia General' && ($user->id == $oc->user_id || $user->action->oc_edt)) ||
                        $user->priv_level == 4)
                      <div class="col-sm-12 mg20" align="center">
                      </div>
                    @endif
                </div>

                <div class="tab-pane fade" id="obligations">
                  <div class="col-lg-12" align="right">
                    <a href="" onclick="window.close()" class="btn btn-dark" title="Cerrar">
                        <i class="fa fa-times"></i>
                    </a>
                  </div>

                  <div class="col-sm-12 mg10 mg-tp-px-10">
                    <table class="table table-bordered">
                      <thead>
                      </thead>
                      <tbody>
                        <tr>
                            <td colspan="2">
                                <strong>CONSIDERACIONES A SER TOMADAS EN CUENTA PARA LA EJECUCION DEL TRABAJO</strong><br>
                                <p style="color:navy">COORDINACIÓN CON TERCEROS</p>
                                <p>De manera enunciativa general, a continuación se detallan algunas recomendaciones:</p>
                                <ul>
                                    <li>Provisión completa al personal de la correspondiente ropa de trabajo, accesorios de seguridad necesarios e identificación como parte de su Empresa.</li>
                                    <li>Identificación de todos los vehículos asignados al proyecto con su debido registro y adecuadas condiciones de uso.</li>
                                    <li>No se podrá ejecutar ningún trabajo sin los respectivos permisos de las autoridades correspondientes.</li>
                                    <li>Se colocarán las señales de peligro de acuerdo a las características del trabajo a realizar y no se retiraran hasta culminar los mismos y retirar los desechos en ciudades. - En canalizaciones se evitarán que las zanjas y escombros perjudiquen el tráfico vehicular y peatonal o ponga en riesgo las edificaciones circundantes.</li>
                                    <li>El trabajo deberá ser organizado de tal manera que tanto en el transcurso de la obra como al finalizar la misma no ocasionen molestias a transeúntes, vehículos o inmuebles.</li>
                                    <li>Los trabajos de excavación que se requieran, no deberán afectar u obstruir ductos, cañerías, drenajes y desagües, obras de arte, zanjas de coronamiento en carreteras.</li>
                                    <li>Los escombros sobrantes deben ser retirados simultáneamente con el avance de la obra, dejando las calles o vías afectadas como estaban antes del trabajo.</li>
                                    <li>En caso de daños que se puedan ocasionar a instalaciones de gasoductos, oleoductos, alcantarillados, energía gas o agua deben ser comunicados a ABROS S.R.L. asumiendo el CONTRATISTA adjudicado los gastos de reparación, sanciones y/o multas que se generen.</li>
                                </ul>
                                <p style="color:navy">ENTREGA DE MATERIALES NO UTILIZADOS</p>
                                <p>Concluidos los trabajos de instalación, fusiones y medidas ópticas de certificación, el OFERENTE adjudicado deberá realizar la devolución del material no empleado, mismo que deberá ser entregado debidamente organizado en cajas según corresponda por tipo de elemento, debidamente etiquetadas, con las siguiente información:</p>
                                <ul>
                                    <li>Nombre de elemento.</li>
                                    <li>Código de fábrica.</li>
                                    <li>Cantidad del elemento en caja.</li>
                                    <li>Número de cajas x de xx.</li>
                                </ul>
                                <p>Estos materiales deberán ser depositados en las estaciones o almacenes que ABROS TECHNOLOGIES S.R.L. designe, bajo un acta de entrega en la cual se detalle todo el material.</p>
                                <p style="color:navy">RELEVAMIENTO</p>
                                <p>El OFERENTE adjudicado deberá realizar un relevamiento y reconocimiento preliminar de toda la ruta de instalación para determinar todas las actividades a desarrollar.<br>
                                Los principales aspectos que debe definir tras el reconocimiento "in situ", son los siguientes:</p>
                                <ul>
                                    <li>Definición de la ruta más óptima para efectuar el montaje de la infraestructura aérea.</li>
                                    <li>Disponibilidad  y cantidad de postes existentes.</li>
                                    <li>Identificación de las características de los postes existentes. (Material, altura, estado)</li>
                                    <li>Vanos para la instalación del cable de fibra óptica.</li>
                                    <li>Definición de segmentos de postación nueva.</li>
                                    <li>Definición de la ubicación de postación intermedia.</li>
                                    <li>Cruces de carretera/ Puentes existentes.</li>
                                    <li>Ubicación de cámaras existentes.</li>
                                    <li>Definición de la ruta más óptima para efectuar el montaje de la infraestructura subterránea. </li>
                                    <li>Definición de la ubicación y construcción de cámaras.</li>
                                    <li>Recorrido e identificación de la ruta subterránea con infraestructura existente.</li>
                                    <li>Puntos con criticidades.</li>
                                    <li>Método de tendido a utilizar.</li>
                                    <li>Número de empalmes a realizar.</li>
                                    <li>Limpieza de cámaras y descolmatado de ductos.</li>
                                    <li>Cantidad y tipo de materiales a ser empleados (Cable de fibra óptica, cajas de empalme, ferretería, rack y bandejas ODF, pigtails, tapones abiertos, tapones cerrados, conectores SC/APC, etc).</li>
                                    <li>Maquinaria necesaria para realizar el tendido de cable de fibra óptica.</li>
                                    <li>Equipo humano requerido para la realización de los trabajos.</li>
                                    <li>Medidas de seguridad industrial y sistemas de señalización.</li>
                                </ul>
                                <p style="color:navy">PLAN DE TENDIDO</p>
                                <p>Concluida la etapa de relevamiento, el OFERENTE adjudicado, deberá elaborar y presentar para su aprobación a la Supervisión de ABROS S.R.L., un documento en el que además del plan de tendido se incluyan lo siguientes aspectos:</p>
                                <ul>
                                    <li>Descripción de la metodología de instalación del cable de fibra óptica a emplear.</li>
                                    <li>Cronograma de Actividades actualizado y detallado por tipo de actividad.</li>
                                    <li>Descripción de la metodología para la realización de las pruebas y medidas ópticas.</li>
                                </ul>
                                <p>La verificación de estos aspectos más el plan de tendido, determinará la aprobación o no del diseño propuesto, además de autorizar o no el inicio de los trabajos de implementación.</p>
                                <p style="color:navy">FUSIONES Y MEDIDAS ÓPTICAS DE CERTIFICACIÓN</p>
                                <p>Una vez se hayan concluido los trabajos de tendido del cable de fibra óptica y previa comunicación a ABROS TECHNOLOGIES S.R.L., el OFERENTE adjudicado deberá proceder a realizar las fusiones de fibra óptica, para tal efecto se describen a continuación las actividades y especificaciones técnicas requeridas:</p>
                                <ul>
                                    <li>Fusiones de fibra óptica en  postes y/o cámaras (donde sea necesario).</li>
                                    <li>Fusiones de fibra óptica en ODFs en las estaciones de ABROS TECHNOLOGIES S.R.L.</li>
                                    <li>Medidas ópticas de Retrodifusión (OTDR) bidireccional del enlace de fibra óptica A ‒> B y B ‒> A.</li>
                                    <li>Se debe garantizar que los valores de pérdida en los empalmes de línea deben ser ≤ 0,1 dB y ≤ 0,5 dB en ODFs.</li>
                                    <li>Las mediciones se realizarán en las ventanas de 1310 y 1550 nm.</li>
                                </ul>
                                <p style="color:navy">PRESENTACIÓN DE LA PLANILLA FINAL</p>
                                <p>Concluido los trabajos el OFERENTE adjudicado deberá presentar a la supervisión regional de ABROS TECHNOLOGIES S.R.L. la planilla final, en la cual se deberá incluir todas las obras ejecutadas y en un plazo máximo de 10 días hábiles procederá a la verificación control y aprobación o rechazo. Para tal efecto convocará a realizar una inspección conjunta in-situ en la cual procederá a verificar la calidad de las obras ejecutadas y material utilizado. En caso de existir observaciones y discrepancias estas deberán ser corregidos en un plazo no mayor de 5 días calendario, pasado los cuales el OFERENTE adjudicado deberá nuevamente notificar a la supervisión regional de ABROS TECHNOLOGIES S.R.L. las correcciones realizadas y solicitar una nueva inspección conjunta in-situ en un plazo máximo de 10 días hábiles.</p>
                                <p style="color:navy">DOCUMENTACIÓN AS BUILT</p>
                                <p>El OFERENTE adjudicado concluida la verificación de la calidad de las obras ejecutadas y material utilizado sin observaciones deberá entregar al referente especialista asignado del proyecto la documentación as built en formato impreso y electrónico (CD-ROM, DVD-ROM o Memoria flash) 3 ejemplares originales con toda la información generada en el desarrollo del proyecto, debidamente organizados en bloques y básicamente deberá contener lo siguiente:</p>
                                <ul>
                                    <li>Información del relevamiento realizado.</li>
                                    <li>Plan de tendido</li>
                                    <li>Tabla de atenuaciones.</li>
                                    <li>Esquemáticos de longitudes ópticas.</li>
                                    <li>Esquemáticos de longitudes de tendido.</li>
                                    <li>Esquemáticos de conexiones.</li>
                                    <li>Planilla final de cantidades y materiales firmada por la supervisión de ABROS TECHNOLOGIES S.R.L.</li>
                                </ul>
                                <p style="color:navy">PLAZOS DE EJECUCIÓN</p>
                                <ul>
                                    <li>El tiempo para la ejecución de tendidos de cable de fibra óptica y provisión de materiales de instalación esta especificado en la hoja principal de la presente Orden de Compra</li>
                                    <li>El Contratista debera adjuntar a la presente el cronograma de trabajo el cual servira para realizar el control de los tiempos de ejecusion de la obra asignada.</li>
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <td width="50%">
                                <strong>Proveedor</strong><br>
                                {{ $oc->provider_record ? $oc->provider_record->prov_name : $oc->provider }}
                                <br>
                                <strong>Fecha</strong><br>
                                {{ date_format($oc->created_at,'d-m-Y') }}
                            </td>
                            <td width="50%">
                                <strong>Representante</strong><br>
                                {{ $oc->provider_record ? $oc->provider_record->contact_name : '' }}
                                <br>
                                <strong>Número de documento de indentidad</strong><br>
                                {{ $oc->provider_record && $oc->provider_record->contact_id != 0 ? $oc->provider_record->contact_id : '' }}
                            </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>

                  @if(($oc->status <> 'Anulado' && ($oc->executed_amount - $oc->payed_amount >= 0) &&
                      $oc->payment_status != 'Concluido' && $oc->status == 'Aprobado Gerencia General') ||
                      $user->priv_level == 4)
                    <div class="col-sm-12 mg10" align="center">
                    </div>
                  @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Aprobación Modal -->
@if (($oc->status == 'Creado' && $user->action->oc_apv_tech && $oc->type == 'Servicio') ||
    ($oc->status == 'Creado' && $oc->type == 'Compra de material' && $user->action->oc_apv_gg) ||
    ($oc->status == 'Aprobado Gerencia Tecnica' && $user->action->oc_apv_gg) ||
    (($oc->status =='Aprobado Gerencia Tecnica' || $oc->status == 'Creado') && $user->priv_level == 4))
  <div id="approveBox" class="modal fade" role="dialog">
    <link rel="stylesheet" href="{{ asset("app/css/custom_table.css") }}">
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
                <h4 class="modal-title">{{ 'Aprobar '.$oc->code }}</h4>
            </div>

            <div class="modal-body">
                {{--<div class="col-sm-12 mg10">--}}
                    <form method="post" action="{{ '/approve_oc' }}" id="approve_oc_form"
                            accept-charset="UTF-8" enctype="multipart/form-data">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        {{--
                        <div class="checkbox" style="margin-left: 20px">
                            <input type="checkbox" name="0" id="0" value="{{ $oc->id }}" class="checkbox">
                            <label for="0">Aprobar OC</label>
                        </div>
                        --}}

                        <input type="hidden" name="0" value="{{ $oc->id }}">
                        <input type="hidden" name="count" value="1">
                        
                        <label>Confirme su identidad introduciendo su contraseña por favor</label>

                        <div class="input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-lock fa-fw"></i></span>
                            <input required="required" type="password" class="form-control" name="password"
                                    id="password" placeholder="Password">
                        </div>
                        
                        <div class="checkbox col-sm-offset-1" align="left">
                            <label>
                                <input type="checkbox" name="add_comments" id="add_comments" value="1"
                                    checked=""> Agregar un comentario sobre la aprobación
                            </label>
                        </div>

                        <div class="input-group" id="comments_container">
                            <span class="input-group-addon">
                                <textarea rows="3" class="form-control" name="comments" id="comments"
                                        placeholder="Agregar comentarios" disabled="disabled"></textarea>
                            </span>
                        </div>
                    </form>
                {{--</div>--}}
            </div>

            <div class="modal-footer">
                <button type="submit" id="approve_button" class="btn btn-primary"
                    onclick="this.disabled=true; approve_oc_form.submit()" disabled="disabled">
                    <i class="fa fa-check-circle"></i> Aprobar
                </button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
  </div>
@endif

<!-- Anulación modal -->
@if(($oc->status <> 'Anulado' && $oc->status != 'Aprobado Gerencia General' && $user->action->oc_nll &&
    ($user->id == $oc->user_id || $user->action->oc_edt)) || $user->priv_level == 4)
  <div id="cancelBox" class="modal fade" role="dialog">
    <link rel="stylesheet" href="{{ asset("app/css/custom_table.css") }}">
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
                <h4 class="modal-title">{{ 'Anular '.$oc->code }}</h4>
            </div>

            <div class="modal-body">
                <label>
                    <em>Indique el motivo para anular esta orden<br>
                    Los campos con * son obligatorios</em>
                </label>

                <form novalidate="novalidate" action="{{ '/oc/cancel/'.$oc->id }}" method="post" id="cancel_oc_form">
                    <input type="hidden" name="_method" value="put">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i>
                                <input type="hidden" name="action" value="{{ 'anular' }}">

                                <textarea rows="3" required="required" class="form-control" name="observations"
                                            id="cancel_observations" placeholder="{{ 'Motivo para anular la OC *' }}"></textarea>
                            </span>
                        </div>
                    </div>
                    
                    {{--
                    <div class="input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-lock fa-fw"></i></span>
                        <input required="required" type="password" class="form-control" name="password"
                                id="password" placeholder="Password">
                    </div>
                    --}}
                    
                </form>
            </div>

            <div class="modal-footer">
                <button type="submit" id="cancel_button" class="btn btn-danger"
                    onclick="this.disabled=true; cancel_oc_form.submit()" disabled="disabled">
                    <i class="fa fa-minus-circle"></i> Anular
                    {{--<i class="fa fa-ban"></i> Anular OC}--}}
                </button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
  </div>
@endif

<!-- Observación modal -->
@if ((($oc->status == 'Creado' || $oc->status == 'Aprobado Gerencia Tecnica') &&
    ($user->action->oc_apv_gg || ($user->action->oc_apv_gtec && $oc->type == 'Servicio'))) || $user->priv_level == 4)
  <div id="observeBox" class="modal fade" role="dialog">
    <link rel="stylesheet" href="{{ asset("app/css/custom_table.css") }}">
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
                <h4 class="modal-title">{{ 'Observar '.$oc->code }}</h4>
            </div>

            <div class="modal-body">
                <label>
                    <em>Indique el motivo para observar esta orden<br>
                    Los campos con * son obligatorios</em>
                </label>

                <form novalidate="novalidate" action="{{ '/oc/reject' }}" method="post" id="observe_oc_form">
                    <input type="hidden" name="_method" value="put">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i>
                                <input type="hidden" name="action" value="{{ 'observar' }}">
                                <input type="hidden" name="id" value="{{ $oc->id }}">

                                <textarea rows="3" required="required" class="form-control" name="observations"
                                            id="obs_observations" placeholder="{{ 'Motivo para observar la OC *' }}"></textarea>
                            </span>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="submit" id="observe_button" class="btn btn-warning"
                    onclick="this.disabled=true; observe_oc_form.submit()" disabled="disabled">
                    <i class="fa fa-eye"></i> Observar
                </button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
  </div>
@endif

<!-- Certification Modal -->
{{--
@if($oc->certificates->count() > 0)
  <div id="certificationBox" class="modal fade" role="dialog">
    @include('app.oc_certification_modal', array('user'=>$user,'service'=>$service,'oc'=>$oc))
  </div>
@endif
--}}

@endsection

@section('footer')
@endsection

@section('javascript')
  <script src="{{ asset('app/js/set_current_url.js') }}"></script>{{-- For recording current url --}}
  <script>
    $('#alert').delay(2000).fadeOut('slow');

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    /*
    var data = '', oc_id = '';
    function replace(element,id){
        data = $.trim($(element).text());
        oc_id = id;
        var arr = data.split(' ');
        arr[0] = arr[0].replace(/,/g, '');
        $(element).html("<input type=\"text\" value=\"" + arr[0] + "\" id=\"editable\" />");
        $(element).find('input').focus();
        $(element).off();
    }
    */

    $(document).ready(function() {
        //var c = $('td.edit').html();
        /*
        $('td.edit').on("click",function(){
            $(this).html("<input type=\"text\" value=\"" + $.trim($(this).text()) + "\" id=\"editable\" />");
            $(this).find('input').focus();
            $(this).off();
        });
        */
        $(document).on("focusout","td.edit input",function() {
            var c = $("#editable").val();

            if (c.length >0) {
                $.post('/set_oc_executed_amount', { amount: c, id: oc_id }, function(result) {
                    $('td.edit').html(result.executed_amount);
                    $('td.update').html(result.balance);
                });
            } else {
                $('td.edit').html(data);
                //$('td.edit').html(c+' Bs');
                //$('td.update').html('hola');
            }
        });
    });

    /*
    $(document).on("click", ".open-rowBox", function () {
        var rowId = $(this).data('id');
        var rowNumOrder = $(this).data('numorder');
        var rowDescription = $(this).data('description');
        var rowQty = $(this).data('qty');
        var rowUnits = $(this).data('units');
        var rowUnitCost = $(this).data('unitcost');

        $('#rowBox .modal-body #rowForm').attr('action', rowId > 0 ? '/oc_row/'+rowId : '/oc_row');
        $("#rowBox .modal-body #_method").val( rowId > 0 ? 'put' : 'post' );
        $("#rowBox .modal-body #num_order").val( rowNumOrder );
        $("#rowBox .modal-body #description").val( rowDescription );
        $("#rowBox .modal-body #qty").val( rowQty );
        $("#rowBox .modal-body #units").val( rowUnits );
        $("#rowBox .modal-body #unit_cost").val( rowUnitCost );
    });

    $(document).on("click", ".removeRow", function () {
        var rowId = $(this).data('id');
        if (rowId > 0) {
          $('#removeRow').attr('action', '/oc_row/'+rowId);
          $('#removeRow').submit();
        }
    });

    $("#wait").hide();
    */

    /*
    var $password = $('#password'), $submit_button = $('#submit_button'), $container = $('#container');
    $("#approve_oc").change(function() {
      var checked = $("#approve_oc input:checked").length > 0;
      if (checked) {
        $container.show();
        $password.removeAttr('disabled').show();
        $submit_button.removeAttr('disabled').show();
      } else {
        $container.hide();
        $password.attr('disabled', 'disabled').hide();
        $submit_button.attr('disabled', 'disabled').hide();
      }
    }).trigger('change');
    */

    var $password = $('#password'), $approve_button = $('#approve_button');
    if ($password.length > 0) {
        $approve_button.removeAttr('disabled');
    } else {
        $approve_button.attr('disabled', 'disabled');
    }

    var $cancel_observations = $('#cancel_observations'), $cancel_button = $('#cancel_button');
    if ($cancel_observations.length > 0) {
        $cancel_button.removeAttr('disabled');
    } else {
        $cancel_button.attr('disabled', 'disabled');
    }

    var $obs_observations = $('#obs_observations'), $observe_button = $('#observe_button');
    if ($obs_observations.length > 0) {
        $observe_button.removeAttr('disabled');
    } else {
        $observe_button.attr('disabled', 'disabled');
    }

    var $add_comments = $('#add_comments'), $comments = $('#comments'), $comments_container = $('#comments_container');
    $add_comments.click(function () {
      if ($add_comments.prop('checked')) {
        $comments_container.show();
        $comments.removeAttr('disabled').show();
      } else {
        $comments_container.hide();
        $comments.attr('disabled', 'disabled').hide();
      }
    }).trigger('click');

    $('.request_approval').on('click', function () {
        return confirm('Está seguro de que desea solicitar la aprobación de esta orden? ' +
            'Asegúrese de haber corregido todas las observaciones.');
    });
  </script>
@endsection
