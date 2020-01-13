@extends('layouts.ocs_structure')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/info_tabs.css") }}">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

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
    @if($user->priv_level>=1/*($user->area=='Gerencia Tecnica'&&$user->priv_level==2)||$user->priv_level>=3*/)
        <li><a href="{{ '/provider' }}">&ensp;<i class="fa fa-truck"></i> PROVEEDORES&ensp;</a></li>
    @endif
    @if($user->priv_level>=2)
        <li><a href="{{ '/oc_certificate' }}">&ensp;<i class="fa fa-file-text-o"></i> CERTIFICADOS&ensp;</a></li>
    @endif
    <li><a href="{{ '/invoice' }}">&ensp;<i class="fa fa-money"></i> PAGOS&ensp;</a></li>
@endsection

@section('content')

<div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

    <div class="panel panel-orange">
        <div class="panel-heading" align="center">
            <div class="panel-title">
                <div class="pull-left">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#details" data-toggle="tab"> Detalle de OC</a></li>
                        @if ($oc->status<>'Anulado' || $user->priv_level == 4)
                            <li><a href="#payments" data-toggle="tab"> Estado de pagos</a></li>
                        @endif
                    </ul>
                </div>
                <div class="clearfix"></div>
            </div>
            <!--<div class="panel-title">Detalles de OC</div>-->
        </div>
        <div class="panel-body" >

            <div class="tab-content">

                <div class="tab-pane fade in active" id="details">

                    <div class="col-lg-5 mg20">
                        <a href="#" onclick="history.back();" class="btn btn-warning">
                            <i class="fa fa-arrow-circle-left"></i> Volver
                        </a>
                        <a href="{{ '/oc' }}" class="btn btn-warning" title="Ir a la tabla de OCs">
                            <i class="fa fa-arrow-circle-up"></i> OCs
                        </a>
                    </div>

                    <div class="col-lg-7" align="right">
                        @if ($oc->status <> 'Anulado')
                            @if($user->action->oc_ctf_add /*$user->priv_level>=2*/)
                                <a href="{{ '/oc_certificate/create?id='.$oc->id }}" class="btn btn-success"
                                   title="Agregar certificado de aceptación para ésta OC">
                                    <i class="fa fa-file-text-o"></i> Emitir certificado
                                </a>
                            @endif
                            @if(empty($file_sgn)||$user->priv_level==4)
                                <a href="/excel/oc/{{ $oc->id }}" class="btn btn-success" title="Descargar OC">
                                    <i class="fa fa-file-excel-o"></i> Descargar orden
                                </a>
                            @endif
                        @endif
                    </div>

                    <div class="col-sm-12 mg10">
                        @include('app.session_flashed_messages', array('opt' => 0))
                    </div>

                    <div class="col-sm-12 mg10 mg-tp-px-10">

                        <table class="table table-striped table-hover table-bordered">
                            <thead>
                            <tr>
                                <th width="25%">Código:</th>
                                <td width="25%">{{ $oc->code }}</td>
                                <th width="25%">Estado:</th>
                                <td>
                                    {{ $oc->status }}
                                    @if($oc->certificates->count()>0)
                                        <button type="button" class="btn btn-success pull-right" title="Certificado"
                                                data-toggle="modal" data-target="#certificationBox">
                                            <i class="fa fa-check"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td colspan="2" rowspan="3"></td>
                                <th>Monto:</th>
                                <td>{{ number_format($oc->oc_amount,2).' Bs' }}</td>
                            </tr>
                            <tr>
                                <th>Fecha:</th>
                                <td>{{ date_format($oc->created_at,'d-m-Y') }}</td>
                            </tr>
                            <tr>
                                <th>Creado por:</th>
                                <td>{{ $oc->user->name }}</td>
                            </tr>
                            <tr>
                                <th>Concepto:</th>
                                <td colspan="3">{{ $oc->proy_concept }}</td>
                            </tr>
                            <tr>
                                <th>Tipo de OC:</th>
                                <td colspan="3">{{ $oc->type }}</td>
                            </tr>
                            <tr>
                                <th>Proveedor:</th>
                                <td colspan="3">
                                    @if($oc->provider_record)
                                        <a href="/provider/{{ $oc->provider_record->id }}">
                                            {{ $oc->provider_record->prov_name }}
                                        </a>
                                    @else
                                        {{ $oc->provider }}
                                    @endif
                                </td>
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

                            @if ($oc->status <> 'Anulado')
                                <tr><td colspan="4"></td></tr>
                                <tr>
                                    <th colspan="4">Datos de proyecto:</th>
                                </tr>
                                <tr>
                                    <td>Proyecto:</td>
                                    <td colspan="3">{{ $oc->proy_name }}</td>
                                </tr>
                                @if($oc->assignment && $oc->assignment->cost_center && $oc->assignment->cost_center > 0)
                                <tr>
                                  <td>
                                    <span title="Centro de costos">C.C.:</span>
                                  </td>
                                  <td colspan="3">{{ $oc->assignment->cost_center }}</td>
                                </tr>
                                @endif
                                @if($oc->proy_description)
                                <tr>
                                    <td>Descripción:</td>
                                    <td colspan="3">
                                      {{ $oc->proy_description }}
                                      <button type="button" 
                                              class="btn btn-success pull-right open-rowBox" 
                                              title="Agregar item"
                                              data-toggle="modal" 
                                              data-target="#rowBox"
                                              data-id="0"
                                              data-numorder=""
                                              data-description=""
                                              data-qty=""
                                              data-units=""
                                              data-unitcost="">
                                          <i class="fa fa-plus"></i>
                                      </button>
                                    </td>
                                </tr>
                                @endif
                                @if ($oc->rows->count() > 0)
                                <tr>
                                  <td colspan="4">Items</td>
                                </tr>
                                <tr>
                                  <th colspan="4">
                                    <table class="table table-bordered">
                                        <tr>
                                            <td width="8%"></td>
                                            <td width="35%">Descripción</td>
                                            <td width="15%">Cantidad</td>
                                            <td width="15%">Unidades</td>
                                            <td width="15%" title="Precio unitario [Bs]">P. Unit. [Bs]</td>
                                            <td width="12%"></td>
                                        </tr>
                                        @foreach($oc->rows as $row)
                                            <tr>
                                                <td>{{ $row->num_order }}</td>
                                                <td>{{ $row->description }}</td>
                                                <td align="right">{{ number_format($row->qty, 2) }}</td>
                                                <td align="center">{{ $row->units }}</td>
                                                <td align="right">{{ number_format($row->unit_cost, 2) }}</td>
                                                <td align="center">
                                                  <a data-toggle="modal" 
                                                    data-id="{{ $row->id }}"
                                                    data-numorder="{{ $row->num_order }}"
                                                    data-description="{{ $row->description }}"
                                                    data-qty="{{ $row->qty }}"
                                                    data-units="{{ $row->units }}"
                                                    data-unitcost="{{ $row->unit_cost }}"
                                                    title="Modificar item"
                                                    class="open-rowBox"
                                                    href="#rowBox"
                                                    style="text-decoration: none">
                                                    <i class="fa fa-pencil-square"></i>
                                                  </a>
                                                  &ensp;
                                                  <a href="javascript:;" class="removeRow" data-id="{{ $row->id }}" title="Eliminar item" style="text-decoration: none">
                                                    <i class="fa fa-trash"></i>
                                                  </a>
                                                  {{--
                                                  <form action="{{ '/oc_row/'.$row->id }}" method="post">
                                                      <input type="hidden" name="_method" value="delete">
                                                      <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                      <a href="javascript:;" onclick="parentNode.submit();" title="Eliminar item">
                                                        <i class="fa fa-trash"></i>
                                                      </a>
                                                  </form>
                                                  --}}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>
                                  </th>
                                </tr>
                                @endif
                                <tr><td colspan="4"></td></tr>

                                <tr>
                                    <th colspan="4">Datos de cliente:</th>
                                </tr>
                                <tr>
                                    <td>Cliente:</td>
                                    <td colspan="3">{{ $oc->client }}</td>
                                </tr>
                                @if($oc->client_ad)
                                <tr>
                                    <td>Código de asignación:</td>
                                    <td colspan="3">{{ $oc->client_ad }}</td>
                                </tr>
                                @endif
                                @if($oc->client_oc)
                                <tr>
                                    <td>OC de cliente:</td>
                                    <td colspan="3">{{ $oc->client_oc ? $oc->client_oc : '' }}</td>
                                </tr>
                                @endif
                                <tr><td colspan="4"></td></tr>

                                <tr>
                                    <th colspan="4">Información de entrega:</th>
                                </tr>
                                @if($oc->delivery_place)
                                    <tr>
                                        <td>Lugar:</td>
                                        <td colspan="3">{{ $oc->delivery_place }}</td>
                                    </tr>
                                @endif
                                @if($oc->delivery_term&&$oc->delivery_term!=0)
                                    <tr>
                                        <td>Plazo:</td>
                                        <td colspan="3">{{ $oc->delivery_term==1 ? '1 día' : $oc->delivery_term.' días' }}</td>
                                    </tr>
                                @endif

                                <tr><td colspan="4"></td></tr>
                                <tr>
                                    <th>Responsable:</th>
                                    <td colspan="3">{{ $oc->responsible ? $oc->responsible->name : 'No asignado' }}</td>
                                </tr>

                                @if($oc->events->count()>0)
                                    <tr>
                                        <th>Eventos:</th>
                                        <td colspan="3">
                                            <a href="/event/oc/{{ $oc->id }}">{{ 'Ver eventos' }}</a>
                                        </td>
                                    </tr>
                                @endif

                                <tr><td colspan="4"></td></tr>
                                <tr>
                                    <th colspan="4">Archivos:</th>
                                </tr>
                                <tr>
                                    <td colspan="2">Documento original:</td>
                                    <td colspan="2">
                                        <?php $original_exists = false; ?>
                                        @foreach($oc->files as $file)
                                            @if(substr(explode('_',$file->name)[2],0,3)=='org')
                                                @include('app.info_document_options', array('file'=>$file))

                                                {{--
                                                <a href="/download/{{ $file->id }}" style="text-decoration: none">
                                                    @if($file->type=='pdf')
                                                        <img src="/imagenes/pdf-icon.png" alt="PDF" />
                                                    @else
                                                        <img src="/imagenes/excel-icon.png" alt="EXCEL" />
                                                    @endif
                                                </a>
                                                <a href="/file/{{ $file->id }}">Detalles</a>
                                                @if($file->type=='pdf')
                                                    &emsp;
                                                    <a href="/display_file/{{ $file->id }}">Ver</a>
                                                @endif
                                                --}}
                                                <?php $original_exists = true; ?>
                                            @endif
                                        @endforeach
                                        @if(!$original_exists)
                                            <a href="/files/oc_org/{{ $oc->id }}"><i class="fa fa-upload"></i> Subir archivo</a>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">Documento firmado:</td>
                                    <td colspan="2">
                                        <?php $signed_exists = false; ?>
                                        @foreach($oc->files as $file)
                                            @if(substr(explode('_',$file->name)[2],0,3)=='sgn')
                                                @include('app.info_document_options', array('file'=>$file))
                                                
                                                @if($file->status === 0 || $user->priv_level === 4)
                                                    &emsp;
                                                    <a href="/files/replace/{{ $file->id }}" title="Reemplazar este archivo"><i class="fa fa-refresh"></i> Reemplazar</a>
                                                @endif

                                                {{--
                                                <a href="/download/{{ $file->id }}" style="text-decoration: none">
                                                    <img src="/imagenes/pdf-icon.png" alt="PDF"/>
                                                </a>
                                                <a href="/file/{{ $file->id }}">Detalles</a>
                                                &emsp;
                                                <a href="/display_file/{{ $file->id }}">Ver</a>
                                                --}}
                                                <?php $signed_exists = true; ?>
                                            @endif
                                        @endforeach
                                        @if(!$signed_exists)
                                            <a href="/files/oc_sgn/{{ $oc->id }}"><i class="fa fa-upload"></i> Subir archivo</a>
                                        @endif
                                    </td>
                                </tr>
                            @else
                                <tr><td colspan="4"></td></tr>
                                <tr>
                                    <th colspan="4">Motivo de anulación</th>
                                </tr>
                                <tr>
                                    <td colspan="4">
                                        {{ $oc->observations }}
                                    </td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- @if((substr($oc->flags,0,4)=='0001' && $user->area == 'Gerencia Tecnica' && $user->priv_level == 3) ||
                        (substr($oc->flags,0,4)=='0011' && $user->area == 'Gerencia General' && $user->priv_level == 3) ||
                        ((substr($oc->flags,0,4)=='0011' || substr($oc->flags,0,4)=='0001') && $user->priv_level == 4)) --}}
                    @if (($oc->status == 'Creado' && $user->area == 'Gerencia Tecnica' && $user->priv_level == 3) ||
                        ($oc->status == 'Aprobado Gerencia Tecnica' && $user->area == 'Gerencia General' && $user->priv_level == 3) ||
                        (($oc->status =='Aprobado Gerencia Tecnica' || $oc->status == 'Creado') && $user->priv_level == 4))
                        
                      <div class="col-sm-12 mg10">
                        <form method="post" action="{{ '/approve_oc' }}" id="approve_oc"
                              accept-charset="UTF-8" enctype="multipart/form-data">
                          <input type="hidden" name="_token" value="{{ csrf_token() }}">

                          <div class="checkbox" style="margin-left: 20px">
                            <input type="checkbox" name="0" id="0" value="{{ $oc->id }}" class="checkbox">
                            <label for="0">Aprobar OC</label>
                          </div>

                          <input type="hidden" name="count" value="1">

                          <div class="col-sm-8 col-sm-offset-2" id="container" align="center">
                            <label>Introduzca su contraseña por favor</label>

                            <div class="input-group">
                              <span class="input-group-addon"><i class="glyphicon glyphicon-lock fa-fw"></i></span>
                              <input required="required" type="password" class="form-control" name="password"
                                      id="password" placeholder="Password" disabled="disabled">
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
                            <br>

                            @include('app.loader_gif')

                            <div class="form-group" align="center">
                              <button type="submit" id="submit_button" class="btn btn-primary"
                                onclick="this.disabled=true; $('#wait').show(); this.form.submit()" disabled="disabled">
                                <i class="fa fa-check-circle"></i> Aprobar
                              </button>
                            </div>
                          </div>
                        </form>
                      </div>
                    @endif

                    @if(($oc->status <> 'Anulado' && $oc->status != 'Aprobado Gerencia General' && ($user->id == $oc->user_id || $user->action->oc_edt
                        /*$user->priv_level==3*/)) || $user->priv_level == 4)
                      <div class="col-sm-12 mg20" align="center">
                        <a href="/oc/{{ $oc->id }}/edit" class="btn btn-success">
                          <i class="fa fa-pencil-square-o"></i> Modificar OC
                        </a>

                        @if($user->action->oc_nll)
                          <a href="/oc/cancel/{{ $oc->id }}" class="btn btn-danger">
                            <i class="fa fa-ban"></i> Anular OC
                          </a>
                        @endif
                      </div>
                    @endif
                </div>

                <div class="tab-pane fade" id="payments">
                  <div class="col-lg-4 mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning">
                      <i class="fa fa-arrow-left"></i> Volver
                    </a>
                  </div>

                  <div class="col-sm-12 mg10 mg-tp-px-10">
                    <table class="table table-striped table-hover table-bordered">
                      <thead>
                        <tr>
                          <th width="25%">Código:</th>
                          <td width="25%">{{ $oc->code }}</td>
                          <th width="25%">Estado:</th>
                          <td>
                            {{ $oc->status }}
                            @if ($oc->certification)
                              <button type="button" class="btn btn-success pull-right" title="Certificado"
                                      data-toggle="modal" data-target="#certificationBox">
                                <i class="fa fa-check"></i>
                              </button>
                            @endif
                          </td>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td colspan="2" rowspan="5"></td>
                          <th>Asignado:</th>
                          <td>{{ number_format($oc->oc_amount,2).' Bs' }}</td>
                        </tr>
                        <tr>
                          <th>
                            Ejecutado:
                            @if(/*((($oc->user_id==$user->id||$oc->pm_id==$user->id||$user->priv_level==3)&&
                                $oc->flags[0]==0&&$oc->flags[7]==0&&($oc->flags[1]==1||$oc->flags[2]==1))&&
                                $oc->executed_amount==0)||*/$user->priv_level == 4)
                              <i class="fa fa-pencil-square-o pull-right"></i>
                            @endif
                          </th>
                          <td class='edit'
                            @if(/*((($oc->user_id==$user->id||$oc->pm_id==$user->id||$user->priv_level==3)&&
                                $oc->flags[0]==0&&$oc->flags[7]==0&&($oc->flags[1]==1||$oc->flags[2]==1))&&
                                $oc->executed_amount==0)||*/$user->priv_level == 4)
                                onclick="replace(this,id='{{ $oc->id }}')"
                            @endif
                          >
                            {{ number_format($oc->executed_amount,2).' Bs' }}
                          </td>
                        </tr>
                        <tr>
                          <th>Pagado:</th>
                          <td>{{ number_format($oc->payed_amount,2).' Bs' }}</td>
                        </tr>
                        <tr>
                          <th>Pendiente de pago:</th>
                          <td class="update">
                            {{ $oc->executed_amount != 0 ? number_format(($oc->executed_amount - $oc->payed_amount), 2).' Bs' : '0.00 Bs' }}
                          </td>
                        </tr>
                        <tr>
                          <th>Pendiente de certificación:</th>
                          <td>
                            {{ number_format(($oc->oc_amount - $oc->executed_amount),2).' Bs' }}
                          </td>
                        </tr>
                        <tr><th colspan="4"></th></tr>

                        <tr>
                          <th colspan="2">Porcentajes de pago:</th>
                          <td colspan="2">
                            {{ $exploded_percentages[0].' adelanto' }}
                            <br>
                            {{ $exploded_percentages[1].' avance' }}
                            <br>
                            {{ $exploded_percentages[2].' entrega' }}
                          </td>
                        </tr>
                        <tr><th colspan="4"></th></tr>

                        <tr>
                          <th colspan="4">Pagos al proveedor:</th>
                        </tr>
                        <tr>
                          <th colspan="4">
                            <table class="table table-bordered">
                              <tr>
                                <td width="18%">Fecha</td>
                                <td width="18%"># Factura</td>
                                <td width="18%">Monto [Bs]</td>
                                <td>Concepto</td>
                                <td>Estado</td>
                              </tr>
                              @foreach ($oc->invoices as $invoice)
                                <tr>
                                  <td>
                                    {{ ($invoice->transaction_date != '0000-00-00 00:00:00' ?
                                      \Carbon\Carbon::parse($invoice->transaction_date)->format('d-m-Y') :
                                      date_format($invoice->updated_at,'d-m-Y')) }}
                                  </td>
                                  <td>
                                    <a href="/invoice/{{ $invoice->id }}">
                                        {{ $invoice->number }}
                                    </a>
                                  </td>
                                  <td align="right">{{ number_format($invoice->amount,2) }}</td>
                                  <td>
                                    {{ $invoice->concept == 'Adelanto' ? 'Adelanto' :
                                      ($invoice->concept == 'Avance' ? 'Pago contra avance' :
                                      ($invoice->concept == 'Entrega' ? 'Pago contra entrega' : '' )) }}
                                    {{-- substr($invoice->flags,-3)=='100' ? 'Adelanto' :
                                      (substr($invoice->flags,-3)=='010' ? 'Pago contra avance' :
                                      (substr($invoice->flags,-3)=='001' ? 'Pago contra entrega' : '' )) --}}
                                  </td>
                                  <td>
                                    @if ($invoice->status == 'Pagado')
                                      {{ 'Pagado' }}
                                      {{--
                                      @elseif($invoice->flags[2]==0)
                                          @if(($user->priv_level==3&&$user->area=='Gerencia Tecnica')||
                                                  $user->priv_level==4)
                                              <a href="{{ '/invoice/approve' }}">
                                                  {{ 'Autorización de G. Tecnica pendiente' }}
                                              </a>
                                          @else
                                              {{ 'Autorización de G. Tecnica pendiente' }}
                                          @endif
                                      @elseif($invoice->flags[1]==0)
                                          @if(($user->priv_level==3&&$user->area=='Gerencia General')||
                                                  $user->priv_level==4)
                                              <a href="{{ '/invoice/approve' }}">
                                                  {{ 'Autorización de G. General pendiente' }}
                                              </a>
                                          @else
                                              {{ 'Autorización de G. General pendiente' }}
                                          @endif
                                      --}}
                                    @else
                                      @if ($user->action->oc_inv_pmt
                                          /*$user->area=='Gerencia Administrativa'||$user->priv_level==4*/)
                                        <a href="/invoice/payment/{{ $invoice->id }}">
                                            {{ 'Pago pendiente' }}
                                        </a>
                                      @else
                                        {{ 'Autorizado, pago pendiente' }}
                                      @endif
                                    @endif
                                  </td>
                                </tr>
                              @endforeach
                              @if ($oc->invoices->count() == 0)
                                <tr>
                                  <td colspan="5" align="center">
                                    No se ha registrado ninguna factura para esta OC
                                  </td>
                                </tr>
                              @endif
                            </table>
                          </th>
                        </tr>
                      </tbody>
                    </table>
                  </div>

                  @if(($oc->status <> 'Anulado' && ($oc->executed_amount - $oc->payed_amount >= 0) &&
                      $oc->payment_status != 'Concluido' && $oc->status == 'Aprobado Gerencia General') ||
                      $user->priv_level == 4)
                    <div class="col-sm-12 mg10" align="center">
                      <a href="{{ '/invoice/create?oc='.$oc->id }}" class="btn btn-success">
                        <i class="fa fa-plus"></i> Agregar factura
                      </a>
                    </div>
                  @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Certification Modal -->
@if($oc->certificates->count()>0)
    <div id="certificationBox" class="modal fade" role="dialog">
        @include('app.oc_certification_modal', array('user'=>$user,'service'=>$service,'oc'=>$oc))
    </div>
@endif

<!-- Row Modal -->
<div id="rowBox" class="modal fade" role="dialog">
    @include('app.oc_row_modal', array('user'=>$user,'service'=>$service,'oc'=>$oc))
</div>

<form id="removeRow" action="{{ '/oc_row' }}" method="post">
  <input type="hidden" name="_method" value="delete">
  <input type="hidden" name="_token" value="{{ csrf_token() }}">
</form>

@endsection

@section('footer')
@endsection

@section('javascript')
  <script src="{{ asset('app/js/set_current_url.js') }}"></script> {{-- For recording current url --}}
  <script>
    $('#alert').delay(2000).fadeOut('slow');

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

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
  </script>
@endsection
