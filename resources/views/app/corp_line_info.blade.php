<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 20/09/2017
 * Time: 12:18 PM
 */
?>

@extends('layouts.actives_structure')

@section('header')
  @parent
  <link rel="stylesheet" href="{{ asset("app/css/info_tabs.css") }}">
  <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
  <div class="btn-group">
    <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
      <i class="fa fa-phone"></i> Líneas corporativas <span class="caret"></span>
    </button>
    <ul class="dropdown-menu dropdown-menu-prim">
      <li><a href="{{ '/corporate_line' }}"><i class="fa fa-refresh fa-fw"></i> Ver líneas</a></li>
      <li><a href="{{ '/line_assignation' }}"><i class="fa fa-arrow-right fa-fw"></i> Ver asignaciones</a></li>
      <li><a href="{{ '/line_requirement' }}"><i class="fa fa-arrow-right fa-fw"></i> Ver requerimientos</a></li>
      @if($user->action->acv_ln_req)
        <li>
          <a href="{{ '/line_requirement/create' }}"><i class="fa fa-exchange fa-fw"></i> Nuevo requerimiento</a>
        </li>
      @endif
      @if($user->action->acv_ln_asg)
        <li><a href="{{ '/line_assignation/create' }}"><i class="fa fa-exchange fa-fw"></i> Asignar línea</a></li>
      @endif
      @if($user->action->acv_ln_add/*($user->area=='Gerencia General'&&$user->priv_level>=2)*/|| $user->priv_level == 4)
        <li><a href="{{ '/corporate_line/create' }}"><i class="fa fa-plus fa-fw"></i> Agregar nueva línea</a></li>
      @endif
    </ul>
  </div>
  @if($user->priv_level >= 1)
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchBox">
      <i class="fa fa-search"></i> Buscar
    </button>
  @endif
@endsection

@section('content')

  <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">
    <div class="panel panel-brown">
      <div class="panel-heading" align="center">
        <div class="panel-title">
          <div class="pull-left">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#general" data-toggle="tab"> Información general</a></li>
              @if($line->technology || $line->pin || $line->puk || $line->data_plan)
                <li><a href="#technical" data-toggle="tab"> Información técnica</a></li>
              @endif
            </ul>
          </div>
          <div class="clearfix"></div>
        </div>
      </div>
      <div class="panel-body">
        <div class="tab-content">

          <div class="tab-pane fade in active" id="general">

            <div class="col-lg-6 mg20">
              <a href="#" onclick="history.back();" class="btn btn-warning">
                <i class="fa fa-arrow-circle-left"></i> Volver
              </a>
              <a href="{{ '/corporate_line' }}" class="btn btn-warning" title="Ir a la tabla de líneas corporativas">
                <i class="fa fa-arrow-circle-up"></i> Líneas
              </a>
            </div>

            <div class="col-lg-6" align="right">
              <a href="{{ '/line_assignation?ln='.$line->id }}" class="btn btn-primary">
                <i class="fa fa-list-ol"></i> Ver asignaciones
              </a>
            </div>

            <div class="col-sm-12 mg10">
              @include('app.session_flashed_messages', array('opt' => 0))
            </div>

            <div class="col-sm-12 mg10 mg-tp-px-10">
              <table class="table table-striped table-hover table-bordered">
                <thead>
                  <tr>
                    <th width="40%">Número:</th>
                    <td>{{ $line->number }}</td>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <th>Estado actual</th>
                    <td>{{ $line->status }}</td>
                  </tr>
                  <tr>
                    <th>Área de servicio</th>
                    <td>{{ $line->service_area }}</td>
                  </tr>
                  <tr>
                    <th>Responsable actual</th>
                    <td>{{ $line->responsible ? $line->responsible->name : 'N/E' }}</td>
                  </tr>

                  @if($line->observations)
                    <tr><td colspan="2"> </td></tr>
                    <tr><th colspan="2">Observaciones</th></tr>
                    <tr><td colspan="2">{{ $line->observations }}</td></tr>
                  @endif

                  <tr><td colspan="2"></td></tr>
                  <tr>
                    <th colspan="2">Documentos relacionados a esta línea</th>
                  </tr>
                  @foreach($line->files as $file)
                    <tr>
                      <td>{{ $file->description }}</td>
                      <td>
                        @include('app.info_document_options', array('file'=>$file))
                      </td>
                    </tr>
                  @endforeach
                  <tr>
                    <td colspan="2" align="center">
                      <a href="/files/corp_line/{{ $line->id }}"><i class="fa fa-upload"></i> Subir documento</a>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            @if(($user->action->acv_ln_edt && $line->status !== 'Baja') || $user->priv_level == 4)
              <div class="col-sm-12 mg10" align="center">
                <a href="/corporate_line/{{ $line->id }}/edit" class="btn btn-success">
                  <i class="fa fa-pencil-square-o"></i> Modificar registro
                </a>

                @if(($user->action->acv_ln_asg || $user->priv_level == 4) && $line->status === 'Disponible')
                  <a href="{{ '/corporate_line/disable?cl_id='.$line->id }}" class="btn btn-danger"
                        onclick="return confirm('Está seguro de que desea dar de baja esta línea corporativa? ' +
                            'Una vez dada de baja la línea ya no podrá modificarla')"
                        title="Dar de baja esta línea corporativa (Bloquear modificaciones en este registro)">
                    <i class="fa fa-ban"></i> Dar de baja
                  </a>
                @endif
              </div>
            @endif

          </div>

          <div class="tab-pane fade" id="technical">
                
            <div class="col-lg-6 mg20">
              <a href="#" onclick="history.back();" class="btn btn-warning">
                <i class="fa fa-arrow-circle-left"></i> Volver
              </a>
              <a href="{{ '/corporate_line' }}" class="btn btn-warning" title="Ir a la tabla de líneas corporativas">
                <i class="fa fa-arrow-circle-up"></i> Líneas
              </a>
            </div>

            <div class="col-sm-12 mg10">
              @include('app.session_flashed_messages', array('opt' => 0))
            </div>

            <div class="col-sm-12 mg10 mg-tp-px-10">
              <table class="table table-striped table-hover table-bordered">
                <thead>
                  <tr>
                    <th width="40%">Número:</th>
                    <td>{{ $line->number }}</td>
                  </tr>
                </thead>
                <tbody>
                  @if($line->technology)
                    <tr>
                      <th>Tecnología habilitada</th>
                      <td>{{ $line->technology }}</td>
                    </tr>
                  @endif
                  @if($line->data_plan)
                    <tr>
                      <th>Plan de datos</th>
                      <td>{{ $line->data_plan }}</td>
                    </tr>
                  @endif
                  @if($line->pin)
                    <tr>
                      <th>Código PIN</th>
                      <td>{{ $line->pin }}</td>
                    </tr>
                  @endif
                  @if($line->puk)
                    <tr>
                      <th>Código PUK</th>
                      <td>{{ $line->puk }}</td>
                    </tr>
                  @endif
                    
                  @if($line->avg_consumption > 0 || $line->credit_assigned > 0)
                    <tr><td colspan="2"> </td></tr>
                    @if($line->avg_consumption > 0)
                      <tr>
                        <th>Consumo promedio</th>
                        <td>{{ $line->avg_consumption.' Bs' }}</td>
                      </tr>
                    @endif
                      @if($line->credit_assigned > 0)
                        <tr>
                          <th>Crédito asignado</th>
                          <td>{{ $line->credit_assigned.' Bs' }}</td>
                        </tr>
                      @endif
                  @endif
                </tbody>
              </table>
            </div>

          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- Search Modal -->
  <div id="searchBox" class="modal fade" role="dialog">
    @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'corp_lines','id'=>0))
  </div>

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
  </script>
@endsection
