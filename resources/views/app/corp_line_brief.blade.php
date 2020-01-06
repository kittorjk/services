<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 19/09/2017
 * Time: 05:46 PM
 */
?>

@extends('layouts.actives_structure')

@section('header')
  @parent
  <style>
    .dropdown-menu-prim > li > a {
        width: 200px;
    }
  </style>
  <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
  <div class="btn-group">
    <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
      <i class="fa fa-phone"></i> Líneas corporativas <span class="caret"></span>
    </button>
    <ul class="dropdown-menu dropdown-menu-prim">
      <li><a href="{{ '/corporate_line' }}"><i class="fa fa-refresh fa-fw"></i> Recargar página</a></li>
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
      @if($user->action->acv_ln_exp /*$user->priv_level>=1*/)
        <li class="divider"></li>
        <li><a href="{{ '/excel/corp_lines' }}"><i class="fa fa-file-excel-o fa-fw"></i> Exportar a Excel</a></li>
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
  <div class="col-sm-12 mg10 mg-tp-px-10">
    @include('app.session_flashed_messages', array('opt' => 0))
  </div>

  <div class="col-sm-12 mg10 mg-tp-px-10">
    <p>Líneas encontradas: {{ $lines->total() }}</p>

    <table class="fancy_table table_brown tablesorter" id="fixable_table">
      <thead>
        <tr>
          <th>Número de línea</th>
          <th>Área de servicio</th>
          <th>Código responsable</th>
          <th>Responsable actual</th>
          <th>Estado</th>
          <th>Fecha de registro</th>
          <th>Última asignación</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($lines as $line)
          <tr>
            <td>
              <a href="/corporate_line/{{ $line->id }}" title="Información detallada de línea">{{ $line->number }}</a>
              @if(($user->action->acv_ln_edt && $line->status !== 'Baja') || $user->priv_level == 4)
                <a href="/corporate_line/{{ $line->id }}/edit" title="Modificar información de línea">
                  <i class="fa fa-pencil-square"></i>
                </a>
              @endif
            </td>
            <td>{{ $line->service_area }}</td>
            <td>{{ $line->responsible && $line->responsible->employee ? $line->responsible->employee->code : 'N/E' }}</td>
            <td>{{ $line->responsible ? $line->responsible->name : 'N/E' }}</td>
            <td>
              {{ $line->status }}

              @if ($line->flags == '0010' && ($user->action->acv_ln_asg
                /*($user->area=='Gerencia General'&&$user->priv_level>=2)*/|| $user->priv_level == 4))
                <a href="{{ '/line_assignation/devolution?ln_id='.$line->id }}">
                  <i class="fa fa-flag pull-right" title="Registrar línea como 'Disponible'"></i>
                </a>
              @endif
            </td>
            <td>{{ date_format($line->created_at, 'd-m-Y') }}</td>
            <td>
              {{ $line->last_assignation ? date_format($line->last_assignation->created_at, 'd-m-Y') : 'N/E' }}
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="col-sm-12 mg10" align="center">
    {!! $lines->appends(request()->except('page'))->render() !!}
  </div>

  <div class="col-sm-12 mg10" id="fixed">
    <table class="fancy_table table_brown" id="cloned"></table>
  </div>

  <!-- Search Modal -->
  <div id="searchBox" class="modal fade" role="dialog">
    @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'corp_lines','id'=>0))
  </div>

@endsection

@section('footer')
  @parent
@endsection

@section('javascript')
  <script src="{{ asset('app/js/fix_table_header.js') }}"></script> {{-- For fixed header --}}
  <script src="{{ asset('app/js/set_current_url.js') }}"></script> {{-- For recording current url --}}
  <script>
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    $(function() {
      $('#fixable_table').tablesorter({
        cssAsc: 'headerSortUp',
        cssDesc: 'headerSortDown',
        cssNone: '',
        dateFormat: 'uk'
      });
    });

    $('#alert').delay(2000).fadeOut('slow');

    /*
    function flag_change(element, flag, id) {
      var text = "Confirmar";
      var flag_color = "";

      if (flag == 'maintenance') {
        text = "Marcar equipo en mantenimiento?";
        flag_color = "red";
      } else if (flag == 'req_maintenance') {
        text = "Solicitar mantenimiento para este equipo?";
        flag_color = "orange";
      } else if (flag == 'available') {
        text = "Marcar equipo como disponible?";
        flag_color = "green";
      }

      var r = confirm(text);
      if (r == true) {
        $.post('/flag/device', { flag: flag, id: id }, function(data) {
          //alert(data);
          $(element).parent().find('.status').html(data);
          element.style.color = flag_color;
        });
      }
    }
    */
  </script>
@endsection
