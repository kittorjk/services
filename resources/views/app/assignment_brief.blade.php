@extends('layouts.projects_structure')

@section('header')
  @parent
  <link rel="stylesheet" href="{{ asset("app/css/custom_table.css") }}">
  <link rel="stylesheet" href="{{ asset("app/css/progress_bar.css") }}">
  <style>
    .dropdown-menu-prim > li > a {
      width: 190px;
      /*white-space: normal; /* Set content to a second line */
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
      <i class="fa fa-cogs"></i> Asignaciones <span class="caret"></span>
    </button>
    <ul class="dropdown-menu dropdown-menu-prim">
      <li><a href="{{ '/assignment' }}"><i class="fa fa-bars fa-fw"></i> Resumen </a></li>
      @if($user->priv_level>=1)
        <li><a href="{{ '/assignment/create' }}"><i class="fa fa-plus fa-fw"></i> Nueva asignación </a></li>
      @endif
      @if($user->priv_level>=2||$user->role=='Director regional'||($user->area=='Gerencia Tecnica'&&$user->priv_level==1))
        <li><a href="{{ '/assignment?mode=rb' }}"><i class="fa fa-bars fa-fw"></i> Asignaciones de RB </a></li>
        <li><a href="{{ '/assignment?mode=fo' }}"><i class="fa fa-bars fa-fw"></i> Asignaciones de FO </a></li>
      @endif
      @if($user->action->prj_vtc_rep /*$user->priv_level>=3*/)
        <li><a href="{{ '/assignment/expense_report/stipend' }}"><i class="fa fa-money fa-fw"></i> Reporte de gastos</a></li>
      @endif
      @if($user->action->prj_asg_exp)
        <li class="divider"></li>
        <li><a href="{{ '/excel/assignments' }}"><i class="fa fa-file-excel-o fa-fw"></i> Exportar a Excel</a></li>
      @endif
      {{--
      @if($user->priv_level==4)
        <li><a href="/delete/assignment"><i class="fa fa-trash-o"></i> Borrar archivo</a></li>
      @endif
      --}}
    </ul>
  </div>
  @if($user->priv_level>=2)
    <!--<a href="/search/assignments/0" class="btn btn-primary"><i class="fa fa-search"></i> Buscar </a>-->
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchBox">
      <i class="fa fa-search"></i> Buscar
    </button>
  @endif
@endsection

@section('content')

  @foreach($assignments as $assignment)
    @foreach($assignment->guarantees as $guarantee)
      @if(($assignment->status<>$assignment->last_stat()/*Concluído*/&&$assignment->status<>0/*'No asignado'*/)&&
        ($user->area=='Gerencia General'||$user->priv_level==4))
        @if($current_date->diffInDays($guarantee->expiration_date,false)<=5&&$guarantee->closed==0)
          <div class="col-sm-12 mg10">
            <div class="alert alert-danger" align="center">
              <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
              <a href="assignment/{{ $assignment->id }}" style="color: darkred">
                {{ 'La poliza de garantia de la asignación '.$assignment->code.
                ($current_date->diffInDays($guarantee->expiration_date,false)<0 ? ' ha expirado' : ' expira pronto') }}
              </a>
            </div>
          </div>
        @endif
      @endif
    @endforeach
  @endforeach

    <div class="col-sm-12 mg10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10">

        <p>Registros encontrados: {{ $assignments->total() }}</p>

        <table class="formal_table table_blue tablesorter" id="fixable_table">
            <thead>
                <tr>
                    <th width="8%" title="Código interno de proyecto">Código INT</th>
                    <th width="8%">Código cliente</th>
                    <th width="10%" title="Identificador rápido de asignación según ABROS">Identificador</th>
                    <th title="Centro de costos">C.C.</th>
                    <th width="16%">Asignación</th>
                    <th>Cliente</th>
                    <th>Tipo de trabajo</th>
                    <th>Regional</th>
                    <th width="10%">Estado</th>
                    {{--@if(($user->area=='Gerencia Tecnica'&&$user->priv_level==2)||$user->priv_level>=3)--}}
                        <th width="8%">Avance</th>
                        <th width="8%" class="{sorter: 'digit'}">Tiempo restante</th>
                        <th width="8%">Acciones</th>
                    {{--@endif--}}
                    <th width="8%">Sitios</th>
                </tr>
            </thead>
            <tbody>
            <?php
              $types = array();
              $types['Fibra óptica'] = 'FO';
              $types['Radiobases'] = 'RB';
              $types['Instalación de energía'] = 'IE';
              $types['Obras Civiles'] = 'CW';
              $types['Venta de material'] = 'VM';

              $acronimoCiudad = array();
              $acronimoCiudad['Beni'] = 'BN';
              $acronimoCiudad['Chuquisaca'] = 'CH';
              $acronimoCiudad['Cochabamba'] = 'CB';
              $acronimoCiudad['La Paz'] = 'LP';
              $acronimoCiudad['Oruro'] = 'OR';
              $acronimoCiudad['Pando'] = 'PD';
              $acronimoCiudad['Potosi'] = 'PT';
              $acronimoCiudad['Santa Cruz'] = 'SC';
              $acronimoCiudad['Tarija'] = 'TJ';

              /*
              $options_upgrade = array();
                  $options_upgrade['Relevamiento'] = 'Cotizado';
                  $options_upgrade['Cotizado'] = 'Ejecución';
                  $options_upgrade['Ejecución'] = 'Revisión';
                  $options_upgrade['Revisión'] = 'Cobro';
                  $options_upgrade['Cobro'] = 'Concluído';

              $options_downgrade = array();
                  $options_downgrade['Cobro'] = 'Revisión';
                  $options_downgrade['Revisión'] = 'Ejecución';
                  $options_downgrade['Ejecución'] = 'Cotizado';
                  $options_downgrade['Cotizado'] = 'Relevamiento';
              */
            ?>
            @foreach ($assignments as $assignment)
                <tr class="accordion-toggle">
                    <td>
                        {{ $assignment->code }}
                    </td>
                    <td>
                        @if($user->priv_level>=1)
                            <a href="/assignment/{{ $assignment->id }}">
                                {{ $assignment->client_code ? $assignment->client_code : $assignment->code }}
                            </a>
                        @else
                            {{ $assignment->code }}
                        @endif
                    </td>
                    <td>
                      <span title="{{ $assignment->literal_code }}">
                        {{ str_limit($assignment->literal_code, 20) }}
                      </span>
                    </td>
                    <td>{{ $assignment->cost_center > 0 ? $assignment->cost_center : '' }}</td>
                    <td>
                        {{ $assignment->name }}
                        @if((/*(($user->area=='Gerencia Tecnica'&&$user->priv_level>=1)||$user->priv_level>=3)*/
                            $user->action->prj_asg_edt &&
                            ($assignment->status != $assignment->last_stat()/*'Concluído'*/ &&
                            $assignment->status != 0/*'No asignado'*/)) || $user->priv_level == 4)
                            <a href="/assignment/{{ $assignment->id }}/edit" title="Editar">
                                <i class="fa fa-pencil-square-o"></i>
                            </a>
                        @endif
                    </td>
                    <td>{{ $assignment->client }}</td>
                    <td>{{ $types[$assignment->type].' > '.$assignment->sub_type }}</td>
                    <td>{{ $assignment->branch_record ? $acronimoCiudad[$assignment->branch_record->name] : 'N/E' }}</td>
                    <td>
                        {{ $assignment->statuses($assignment->status) }}
                        @if($assignment->statuses($assignment->status)=='Cotización' && $user->action->prj_asg_edt)
                            <a href="/assignment/stat/{{ $assignment->id.'?action=close' }}" class="confirm_close"
                               title="Marcar trabajo como: No asignado">
                                <i class="fa fa-times pull-right"></i>
                            </a>
                        @endif

                        @if(($user->action->prj_asg_edt &&
                          ($assignment->status != $assignment->last_stat()/*'Concluído'*/ &&
                          $assignment->status != 0/*'No asignado'*/)))
                            <a href="/assignment/stat/{{ $assignment->id.'?action=upgrade' }}"
                               class="confirm_status_change"
                               title="{{ 'Cambiar estado a: '.$assignment->statuses($assignment->status+1)
                                    /*options_upgrade[$assignment->status]*/ }}"
                               data-option="{{ $assignment->statuses($assignment->status+1)
                                    /*$options_upgrade[$assignment->status]*/ }}"
                               {{--
                               @if($assignment->status=='Relevamiento')
                                   {{ 'Cambiar estado a: Cotizado' }}
                               @elseif($assignment->status=='Cotizado')
                                   {{ 'Cambiar estado a: Ejecución' }}
                               @elseif($assignment->status=='Ejecución')
                                   {{ 'Cambiar estado a: Revisión' }}
                               @elseif($assignment->status=='Revisión')
                                   {{ 'Cambiar estado a: Cobro' }}
                               @elseif($assignment->status=='Cobro')
                                   {{ 'Cambiar estado a: Concluído' }}
                               @endif
                                       "
                                       --}}
                            >
                                <i class="fa fa-level-up pull-right"></i> <!-- Formerly arrow-up -->
                            </a>
                            @if($assignment->statuses($assignment->status)!='Relevamiento')
                                <a href="/assignment/stat/{{ $assignment->id.'?action=downgrade' }}"
                                   class="confirm_status_change"
                                   title="{{ 'Cambiar estado a: '.$assignment->statuses($assignment->status-1)
                                        /*$options_downgrade[$assignment->status]*/ }}"
                                   data-option="{{ $assignment->statuses($assignment->status-1)
                                        /*$options_downgrade[$assignment->status]*/ }}"
                                   {{--
                                   @if($assignment->status=='Cotizado')
                                        {{ 'Cambiar estado a: Relevamiento' }}
                                   @elseif($assignment->status=='Ejecución')
                                        {{ 'Cambiar estado a: Cotizado' }}
                                   @elseif($assignment->status=='Revisión')
                                        {{ 'Cambiar estado a: Ejecución' }}
                                   @elseif($assignment->status=='Cobro')
                                        {{ 'Cambiar estado a: Revisión' }}
                                   @endif
                                           "
                                           --}}
                                >
                                    <i class="fa fa-level-down pull-right"></i> <!-- Formerly arrow-down -->
                                </a>
                            @endif
                        @endif
                    </td>

                    {{--@if(($user->area=='Gerencia Tecnica'&&$user->priv_level==2)||$user->priv_level>=3)--}}
                        <td align="center">
                            <div class="progress" data-popover="true" data-html=true data-content="
                                 @if($assignment->type=='Fibra óptica')
                                    {{
                                        'Cable tendido:
                                            <i class="fa fa-question-circle"
                                            title="Debe agregar items a esta categoría para reflejar su avance"></i>
                                            <span class="pull-right">
                                            <strong>'.$assignment->cable_executed.'</strong> de <strong>'.
                                            $assignment->cable_projected.'</strong>
                                            </span><br>
                                        Empalmes ejecutados:
                                            <i class="fa fa-question-circle"
                                            title="Debe agregar items a esta categoría para reflejar su avance"></i>
                                            &ensp;
                                            <span class="pull-right">
                                            <strong>'.$assignment->splice_executed.'</strong> de <strong>'.
                                            $assignment->splice_projected.'</strong>
                                            </span><br>'
                                     }}
                                 @endif
                                 {{
                                    'Avance s/cantidades:
                                     <span class="pull-right"><strong>'.number_format($assignment->percentage_completed,2).
                                     ' %</strong></span><br>
                                    Avance s/costos:
                                     <span class="pull-right"><strong>'.($assignment->assigned_price==0 ? 'n/e' :
                                        number_format(($assignment->executed_price/$assignment->assigned_price)*100,2).' %').
                                    '</strong></span><br>
                                    Última actualización:
                                     <span class="pull-right"><strong>'.date_format($assignment->updated_at,'d-m-Y').'</strong>
                                     </span>'
                                 }}
                            ">
                                <div class="progress-bar progress-bar-success"
                                     style="{{ 'width: '.number_format($assignment->percentage_completed,2).'%' }}">
                                    <span>
                                        {{ number_format($assignment->percentage_completed,2).' %' }}
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td align="center">
                            {{--@if($assignment->status!='Concluído'&&$assignment->status!='No asignado')--}}
                            @if($assignment->statuses($assignment->status)=='Relevamiento')
                                @if($assignment->quote_from->year<1||$assignment->quote_to->year<1)
                                {{-- $assignment->quote_from=='0000-00-00 00:00:00' zero date comparison Carbon parsed --}}
                                    <span class="label label-gray uniform_width" style="font-size: 12px">
                                        {{ 'No especificado' }}
                                    </span>
                                @elseif($current_date->diffInDays($assignment->quote_from,false)<=0)
                                    @if($current_date->diffInDays($assignment->quote_to,false)<=1)
                                        <span class="label label-danger uniform_width" style="font-size: 12px"
                                              title="{{ 'Transcurrido: '.$current_date->diffInDays($assignment->quote_from).' de '.
                                                        $assignment->quote_from->diffInDays($assignment->quote_to) }}">
                                        @if($current_date->diffInDays($assignment->quote_to,false)==1)
                                            {{ '1 dia' }}
                                        @elseif($current_date->diffInDays($assignment->quote_to,false)==0)
                                            {{ 'Vence hoy' }}
                                        @elseif($current_date->diffInDays($assignment->quote_to,false)<0)
                                            {{ abs($current_date->diffInDays($assignment->quote_to,false)).' dia(s) vencido' }}
                                        @endif
                                        </span>
                                    @else
                                        @if($current_date->diffInDays($assignment->quote_to,false)<=3)
                                            <span class="label label-danger uniform_width" style="font-size: 12px"
                                                  title="{{ 'Transcurrido: '.$current_date->diffInDays($assignment->quote_from)
                                                  .' de '.$assignment->quote_from->diffInDays($assignment->quote_to) }}">
                                        @elseif($current_date->diffInDays($assignment->quote_to,false)<=5)
                                            <span class="label label-warning uniform_width" style="font-size: 12px"
                                                  title="{{ 'Transcurrido: '.$current_date->diffInDays($assignment->quote_from)
                                                  .' de '.$assignment->quote_from->diffInDays($assignment->quote_to) }}">
                                        @elseif($current_date->diffInDays($assignment->quote_to,false)<=10)
                                            <span class="label label-yellow uniform_width" style="font-size: 12px"
                                                  title="{{ 'Transcurrido: '.$current_date->diffInDays($assignment->quote_from)
                                                  .' de '.$assignment->quote_from->diffInDays($assignment->quote_to) }}">
                                        @else
                                            <span class="label label-apple uniform_width" style="font-size: 12px"
                                                  title="{{ 'Transcurrido: '.$current_date->diffInDays($assignment->quote_from)
                                                  .' de '.$assignment->quote_from->diffInDays($assignment->quote_to) }}">
                                        @endif
                                            {{ $current_date->diffInDays($assignment->quote_to,false).' dias' }}
                                        </span>
                                    @endif
                                @else
                                    <span class="label label-blue uniform_width" style="font-size: 12px"
                                          title="{{ 'Transcurrido: '.$current_date->diffInDays($assignment->quote_from).' de '.
                                                        $assignment->quote_from->diffInDays($assignment->quote_to) }}">
                                        {{ $current_date->diffInDays($assignment->quote_to,false).' dias' }}
                                    </span>
                                @endif
                            @elseif($assignment->statuses($assignment->status)=='Ejecución')
                                @if($assignment->start_line->year<1||$assignment->deadline->year<1)
                                {{-- $assignment->start_line=='0000-00-00 00:00:00' zero date comparison Carbon parsed --}}
                                    <span class="label label-gray uniform_width" style="font-size: 12px">
                                        {{ 'No especificado' }}
                                    </span>
                                @elseif($current_date->diffInDays($assignment->start_line,false)<=0)
                                    @if($current_date->diffInDays($assignment->deadline,false)<=1)
                                        <span class="label label-danger uniform_width" style="font-size: 12px"
                                              title="{{ 'Transcurrido: '.$current_date->diffInDays($assignment->start_line).
                                              ' de '.$assignment->start_line->diffInDays($assignment->deadline) }}">
                                            @if($current_date->diffInDays($assignment->deadline,false)==1)
                                                {{ '1 dia' }}
                                            @elseif($current_date->diffInDays($assignment->deadline,false)==0)
                                                {{ 'Vence hoy' }}
                                            @elseif($current_date->diffInDays($assignment->deadline,false)<0)
                                                {{ abs($current_date->diffInDays($assignment->deadline,false)).' dia(s) vencido' }}
                                            @endif
                                        </span>
                                    @else
                                        @if($current_date->diffInDays($assignment->deadline,false)<=3)
                                            <span class="label label-danger uniform_width" style="font-size: 12px"
                                                  title="{{ 'Transcurrido: '.$current_date->diffInDays($assignment->start_line)
                                                  .' de '.$assignment->start_line->diffInDays($assignment->deadline) }}">
                                        @elseif($current_date->diffInDays($assignment->deadline,false)<=5)
                                            <span class="label label-warning uniform_width" style="font-size: 12px"
                                                  title="{{ 'Transcurrido: '.$current_date->diffInDays($assignment->start_line)
                                                  .' de '.$assignment->start_line->diffInDays($assignment->deadline) }}">
                                        @elseif($current_date->diffInDays($assignment->deadline,false)<=10)
                                            <span class="label label-yellow uniform_width" style="font-size: 12px"
                                                  title="{{ 'Transcurrido: '.$current_date->diffInDays($assignment->start_line)
                                                  .' de '.$assignment->start_line->diffInDays($assignment->deadline) }}">
                                        @else
                                            <span class="label label-apple uniform_width" style="font-size: 12px"
                                                  title="{{ 'Transcurrido: '.$current_date->diffInDays($assignment->start_line)
                                                  .' de '.$assignment->start_line->diffInDays($assignment->deadline) }}">
                                        @endif
                                            {{ $current_date->diffInDays($assignment->deadline,false).' dias' }}
                                        </span>
                                    @endif
                                @else
                                    <span class="label label-blue uniform_width" style="font-size: 12px"
                                          title="{{ 'Transcurrido: '.$current_date->diffInDays($assignment->start_line).' de '.
                                                        $assignment->start_line->diffInDays($assignment->deadline) }}">
                                        {{ $current_date->diffInDays($assignment->deadline,false).' dias' }}
                                    </span>
                                @endif
                            @elseif($assignment->statuses($assignment->status)=='Cobro')
                                @if($assignment->billing_from->year<1||$assignment->billing_to->year<1)
                                {{-- $assignment->billing_from=='0000-00-00 00:00:00' zero date comparison Carbon parsed --}}
                                    <span class="label label-gray uniform_width" style="font-size: 12px">
                                        {{ 'No especificado' }}
                                    </span>
                                @elseif($current_date->diffInDays($assignment->billing_from,false)<=0)
                                    @if($current_date->diffInDays($assignment->billing_from)==1)
                                        <span class="label label-apple uniform_width" style="font-size: 12px"
                                              title="{{ 'Tiempo transcurrido: 1 día' }}">
                                            {{ '1 dia' }}
                                        </span>
                                    @else
                                        @if($current_date->diffInDays($assignment->billing_from)<=20)
                                            <span class="label label-apple uniform_width" style="font-size: 12px"
                                                  title="{{ 'Tiempo transcurrido: '.
                                                  $current_date->diffInDays($assignment->billing_from).' días' }}">
                                        @elseif($current_date->diffInDays($assignment->billing_from)<=40)
                                            <span class="label label-yellow uniform_width" style="font-size: 12px"
                                                  title="{{ 'Tiempo transcurrido: '.
                                                  $current_date->diffInDays($assignment->billing_from).' días' }}">
                                        @elseif($current_date->diffInDays($assignment->billing_from)<=60)
                                            <span class="label label-warning uniform_width" style="font-size: 12px"
                                                  title="{{ 'Tiempo transcurrido: '.
                                                  $current_date->diffInDays($assignment->billing_from).' días' }}">
                                        @elseif($current_date->diffInDays($assignment->billing_from)>=60)
                                            <span class="label label-danger uniform_width" style="font-size: 12px"
                                                  title="{{ 'Tiempo transcurrido: '.
                                                  $current_date->diffInDays($assignment->billing_from).' días' }}">
                                        @endif
                                            {{ $current_date->diffInDays($assignment->billing_from).' dias' }}
                                        </span>
                                    @endif
                                @endif
                            @elseif($assignment->status==$assignment->last_stat()/*'Concluído'*/)
                                <span class="label label-gray uniform_width" style="font-size: 12px"
                                      title="{{ 'Duración: desde '.$assignment->created_at.' hasta '.
                                                $assignment->updated_at.' ('.
                                                $assignment->created_at->diffInDays($assignment->updated_at).' dias'.')' }}">
                                    {{ $assignment->created_at->diffInDays($assignment->updated_at).' dias' }}
                                </span>
                            @else
                                <span class="label label-gray uniform_width" style="font-size: 12px">
                                    {{ 'N/A' /*\Carbon\Carbon::now()->diffInDays($assignment->updated_at).' dias'*/ }}
                                </span>
                            @endif
                        </td>
                        <td align="center">
                          @if ($user->priv_level > 0)
                            @if($assignment->status!=$assignment->last_stat()/*'Concluído'*/&&
                                $assignment->status!=0/*'No asignado'*/)
                                <a href="/files/assignment/{{ $assignment->id }}"
                                   title="Subir un archivo al sistema"><i class="fa fa-upload"></i></a>
                                &ensp;
                                <a href="/assignment/refresh_data/{{ $assignment->id }}"
                                   title="Actualizar números de asignación"><i class="fa fa-refresh"></i></a>
                            @endif
                            &ensp;
                            <a data-toggle="collapse" data-parent="#accordion" href="{{ '#collapse'.$assignment->id }}"
                               title="Ver avance general de esta asignación">
                                <i class="indicator glyphicon glyphicon-chevron-down"></i>
                            </a>
                          @endif
                        </td>
                    {{--@endif--}}
                    <td>
                        <a href="/site/{{ $assignment->id }}">
                            {{ $assignment->sites->count()==1 ? '1 sitio' : $assignment->sites->count().' sitios' }}
                        </a>
                    </td>
                </tr>

                <tr style="background-color: transparent" class="tablesorter-childRow expand-child">
                    <td colspan="13" style="padding: 0">
                        <div id="{{ 'collapse'.$assignment->id }}"
                             class="panel-collapse collapse mg-tp-px-10 col-sm-10 col-sm-offset-1" style="margin-bottom: 10px">

                            <div class="col-sm-12">
                                @include('app.assignment_brief_summary_table',
                                    array('assignment'=>$assignment, 'current_date'=>$current_date))

                                <table class="formal_table table_red mg-tp-px-10">
                                    <tr>
                                        <td colspan="3" align="right">
                                            <a href="{{ '/dead_interval?assig_id='.$assignment->id }}">
                                                Ver tiempos muertos
                                            </a>
                                            &emsp;
                                            <a href="{{ '/assignment/mail/asg_summary/select-recipient?asg='.$assignment->id }}">
                                                Enviar reporte a cliente
                                            </a>
                                            &emsp;
                                            <a href="/assignment/progress/per_site/{{ $assignment->id }}">
                                                Ver avance por sitio
                                            </a>
                                            &emsp;
                                            <a href="/assignment/progress/items/{{ $assignment->id }}">
                                                Ver avance general de items
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <br>

                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

    </div>
    <div class="col-sm-12 mg10" align="center">
        {!! $assignments->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="formal_table table_blue" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'assignments','id'=>0))
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

        $(function() {
            $('#fixable_table').tablesorter({
                cssAsc: 'headerSortUp',
                cssDesc: 'headerSortDown',
                cssNone: ''
            });
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('.confirm_close').on('click', function () {
            return confirm('Está seguro de que desea marcar este registro como: No asignado?');
        });

        $('.confirm_status_change').on('click', function () {
            return confirm('Está seguro de que desea cambiar el estado de este registro a ' + $(this).data('option') + '?');
        });

        $('.collapse').on('show.bs.collapse', function () {
            $('.collapse.in').collapse('hide');
            $(this).closest('tr').prev(".accordion-toggle").find('.indicator')
                    .toggleClass("glyphicon-chevron-up glyphicon-chevron-down");

        }).on('hide.bs.collapse', function () {
            $(this).closest('tr').prev(".accordion-toggle").find('.indicator')
                    .toggleClass("glyphicon-chevron-down glyphicon-chevron-up");
        });
    </script>
@endsection
