<?php
/**
 * User: Admininstrador
 * Date: 26/08/2018
 * Time: 02:05 PM
 */
?>

@extends('layouts.projects_structure')

@section('header')
  @parent
  <link rel="stylesheet" href="{{ asset("app/css/custom_table.css") }}">
  <style>
    .dropdown-menu-prim > li > a {
      width: 190px;
    }
  </style>
  <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
  @if ($user->priv_level > 0)
    @include('app.project_navigation_button', array('user'=>$user))
  @endif
  <div class="btn-group">
    <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
      <i class="fa fa-money"></i> Rendiciones de viáticos <span class="caret"></span>
    </button>
    <ul class="dropdown-menu dropdown-menu-prim">
      <li><a href="{{ '/rendicion_viatico' }}"><i class="fa fa-refresh fa-fw"></i> Recargar página </a></li>
      <li><a href="{{ '/rendicion_viatico/create' }}"><i class="fa fa-plus fa-fw"></i> Registrar rendición </a></li>
      {{--@if($user->action->aprobar_rendicion)--}}
      @if ($user->priv_level > 1)
        <li>
          <a href="{{ '/rendicion_viatico/pendientes' }}">
            <i class="fa fa-check fa-fw"></i> Pendientes de aprobación
          </a>
        </li>
        <li>
          <a href="{{ '/rendicion_viatico/observados' }}">
            <i class="fa fa-eye fa-fw"></i> Observadas
          </a>
        </li>
      @endif
      {{-- @endif --}}
      @if(/*$user->action->prj_vtc_exp*/ $user->priv_level == 4)
        <li class="divider"></li>
        <li class="dropdown-submenu">
          <a href="#" data-toggle="dropdown"><i class="fa fa-file-excel-o"></i> Exportar a Excel</a>
          <ul class="dropdown-menu dropdown-menu-prim">
            <li>
              <a href="{{ '/excel/rendicion_viatico' }}">
                <i class="fa fa-file-excel-o fa-fw"></i> Tabla de rendiciones
              </a>
            </li>
          </ul>
        </li>
      @endif
    </ul>
  </div>
  <a href="{{ '/stipend_request' }}" class="btn btn-primary" title="Ver solicitudes de viáticos">
    <i class="fa fa-file"></i> Solicitudes
  </a>
  @if($user->priv_level >= 2)
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchBox">
      <i class="fa fa-search"></i> Buscar
    </button>
  @endif
@endsection

@section('content')

  @if($pendiente_aprobacion > 0)
    <div class="col-sm-12 mg10">
      <div class="alert alert-info" align="center">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        <i class="fa fa-info-circle fa-2x pull-left"></i>
        <a href="{{ '/rendicion_viatico/pendientes' }}" style="color: inherit;">
          {{ $pendiente_aprobacion === 1 ? 'Existe 1 rendición pendiente de aprobación' :
                'Existen '.$pendiente_aprobacion.' rendiciones pendientes de aprobación' }}
        </a>
      </div>
    </div>
  @endif

  @if($observados > 0)
    <div class="col-sm-12 mg10">
      <div class="alert alert-warning" align="center">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        <i class="fa fa-warning fa-2x pull-left"></i>
        <a href="{{ '/rendicion_viatico/observados' }}" style="color: inherit;">
            {{ $observados === 1 ? 'Existe 1 rendición observada' :
                'Existen '.$observados.' rendiciones observadas' }}
        </a>
      </div>
    </div>
  @endif

  <div class="col-sm-12 mg10">
    @include('app.session_flashed_messages', array('opt' => 0))
  </div>

  <div class="col-sm-12 mg10">

    <p>Registros encontrados: {{ $rendiciones->total() }}</p>

    <table class="formal_table table_blue tablesorter" id="fixable_table">
      <thead>
        <tr>
          <th width="12%">Codigo</th>
          <th width="12%">Corresponde a</th>
          <th width="15%">Elaborado por</th>
          <th width="12%">Total rendicion [Bs]</th>
          <th>Observaciones</th>
          <th>Estado</th>
          <th width="10%">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @foreach($rendiciones as $rendicion)
          <tr style="{{ $rendicion->estado === 'Observado' ? 'background-color: lightgrey' : '' }}">
            <td>
              <a href="/rendicion_viatico/{{ $rendicion->id }}" title="Ver información de rendición">
                {{ $rendicion->codigo }}
              </a>

              @if($user->id === $rendicion->usuario_creacion && $rendicion->editable /*$user->priv_level==4*/)
                <a href="/rendicion_viatico/{{ $rendicion->id }}/edit" title="Modificar rendición">
                  <i class="fa fa-pencil-square-o pull-right"></i>
                </a>
              @endif
            </td>
            <td>{{ $rendicion->solicitud ? $rendicion->solicitud->code : 'N/E' }}</td>
            <td>{{ $rendicion->creadoPor ? $rendicion->creadoPor->name : 'N/E' }}</td>
            <td>{{ $rendicion->total_rendicion }}</td>
            <td>{{ $rendicion->observaciones }}</td>
            <td>{{ $rendicion->estado }}</td>
            <td align="center">
              @if($rendicion->estado != 'Cancelado' && $rendicion->estado != 'Aprobado')
                @if($user->id === $rendicion->usuario_creacion || $user->priv_level == 4)
                  @if ($rendicion->estado === 'Pendiente' || $rendicion->estado === 'Observado')
                    <a href="/rendicion_viatico/{{ $rendicion->id }}/edit" title="Modificar rendición">
                      <i class="fa fa-pencil-square-o"></i>
                    </a>
                    &ensp;
                    <a href="{{ '/rendicion_viatico/estado?mode=cancelar&id='.$rendicion->id }}"
                      title="Cancelar registro de rendición de viáticos">
                      <i class="fa fa-times"></i>
                    </a>
                    <a href="{{ '/rendicion_viatico/estado?mode=presentar&id='.$rendicion->id }}"
                      title="Presentar rendición para su aprobación">
                      <i class="fa fa-send"></i>
                    </a>
                  @endif
                @endif
                @if($rendicion->estado === 'Presentado' && ($user->action->aprobar_rendicion || ($user->priv_level >= 2 && $user->area === 'Gerencia Administrativa') || $user->priv_level == 4))
                  <a href="{{ '/rendicion_viatico/estado?mode=aprobar&id='.$rendicion->id }}"
                    title="Aprobar rendición" class="confirm_close">
                    <i class="fa fa-check"></i>
                  </a>
                  &ensp;
                  <a href="{{ '/rendicion_viatico/estado?mode=observar&id='.$rendicion->id }}"
                    title="Observar rendición">
                    <i class="fa fa-eye"></i>
                  </a>
                @endif
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>

  </div>

  <div class="col-sm-12 mg10" align="center">
    {!! $rendiciones->appends(request()->except('page'))->render() !!}
  </div>

  <div class="col-sm-12 mg10" id="fixed">
    <table class="formal_table table_blue" id="cloned"></table>
  </div>

  <!-- Search Modal -->
  <div id="searchBox" class="modal fade" role="dialog">
    @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'rendicion_viaticos','id'=>0))
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

    $(document).ready(function() {
      $.post('/set_current_url', { url: window.location.href }, function(){});
    });

    $(function() {
      $('#fixable_table').tablesorter({
        cssAsc: 'headerSortUp',
        cssDesc: 'headerSortDown',
        cssNone: ''
      });
    });

    /*
    $('.confirm_close').on('click', function () {
        return confirm('Está seguro de que desea marcar este registro como "Completado"? ' +
            'Una vez hecho este cambio no podrá modificar el contenido del registro. '+
            'Las solicitudes agrupadas se registrarán como "Completadas" en conjunto');
    });
    */
    /*
    $('.confirm_applied').on('click', function () {
        return confirm('Está seguro de que desea registrar el envío de documentación para aplicar a la ' +
            'licitación indicada?');
    });

    $('.confirm_assignment').on('click', function () {
        return confirm('Está seguro de que desea crear una asignación de trabajo de este proyecto?');
    });
    */
  </script>
@endsection
