@extends('layouts.projects_structure')

@section('header')
  @parent
  <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
  @include('app.project_navigation_button', array('user'=>$user))
  <div class="btn-group">
    <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
      <i class="fa fa-money"></i> Rendiciones de gastos <span class="caret"></span>
    </button>
    <ul class="dropdown-menu dropdown-menu-prim">
      {{--<li><a href="{{ '/rendicion_viatico' }}"><i class="fa fa-refresh fa-fw"></i> Recargar página </a></li>--}}
      <li><a href="" onclick="window.location.reload();"><i class="fa fa-refresh fa-fw"></i> Recargar página </a></li>
      <li><a href="{{ '/rendicion_viatico/create' }}"><i class="fa fa-plus fa-fw"></i> Registrar rendición </a></li>
      {{--@if($user->action->aprobar_rendicion)--}}
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
  {{--
  @if($user->priv_level >= 2)
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchBox">
      <i class="fa fa-search"></i> Buscar
    </button>
  @endif
  --}}
@endsection

@section('content')

  <div id="loginbox" class="mg-tp-px-50 col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1 mg-btm-px-40">
    <div class="panel panel-info" >
      <div class="panel-heading" align="center">
        <div class="panel-title">
          {{ $observados->count() == 1 ? '1 rendición observada' : 'Rendiciones observadas' }}
        </div>
      </div>
      <div class="panel-body">
        <div class="mg20">
          <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
            <i class="fa fa-arrow-left"></i>
          </a>
          <a href="{{ '/rendicion_viatico' }}" class="btn btn-warning" title="Volver a lista de rendiciones">
            <i class="fa fa-arrow-up"></i>
          </a>
        </div>

        @include('app.session_flashed_messages', array('opt' => 1))

        {{--<form method="post" action="{{ '/approve_oc/' }}" id="approve_oc" accept-charset="UTF-8"
              enctype="multipart/form-data">

          <input type="hidden" name="_token" value="{{ csrf_token() }}">--}}

        <div class="col-sm-12">
          <table class="table table-striped table-hover table-bordered">
            <tbody>
              <tr>
                {{--<th style="text-align: center">
                  <input type="checkbox" name="select_all" id="select_all">
                </th>--}}
                <th>
                  <label for="select_all">Código</label>
                </th>
                <th>Fecha</th>
                <th>Corresponde a</th>
                <th>Elaborado por</th>
                <th width="35%">Observaciones</th>
                <th>Acciones</th>
              </tr>
              <?php $i=0 ?>
              @foreach($observados as $observado)
                <tr>
                  {{--<td align="center">
                    <input type="checkbox" name="{{ $i }}" id="{{ $i }}" value="{{ $oc->id }}"
                            class="checkbox">
                  </td>--}}
                  <td>
                    <label for="{{ $i }}" style="font-weight: normal">
                        <a href="/rendicion_viatico/{{ $observado->id }}">{{ $observado->codigo }}</a>
                    </label>
                  </td>
                  <td>{{ date_format($observado->created_at,'d-m-Y') }}</td>
                  <td>{{ $observado->solicitud ? $observado->solicitud->code : 'N/E' }}</td>
                  <td>{{ $observado->creadoPor ? $observado->creadoPor->name : 'N/E' }}</td>
                  <td>{{ $observado->observaciones }}</td>
                  <td align="center">
                    @if($user->id === $observado->usuario_creacion || $user->priv_level == 4)
                      <a href="/rendicion_viatico/{{ $observado->id }}/edit" title="Modificar rendición">
                        <i class="fa fa-pencil-square-o"></i>
                      </a>
                      &ensp;
                      <a href="{{ '/rendicion_viatico/estado?mode=cancelar&id='.$observado->id }}"
                        title="Cancelar registro de rendición de gastos">
                        <i class="fa fa-times"></i>
                      </a>
                      <a href="{{ '/rendicion_viatico/estado?mode=presentar&id='.$observado->id }}"
                        title="Presentar rendición para su aprobación">
                        <i class="fa fa-send"></i>
                      </a>
                    @endif
                  </td>
                </tr>
                <?php $i++ ?>
              @endforeach
            </tbody>
          </table>
        </div>

        {{--<input type="hidden" name="count" value="{{ $ocs->count() }}">

        <div class="col-sm-10 col-sm-offset-1" id="container" align="center">
          <label>Introduzca su contraseña por favor</label>

          <div class="input-group">
            <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
            <input required="required" type="password" class="form-control" name="password" id="password"
                    placeholder="Password" disabled="disabled">
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
            <button type="submit" id="submit_button" class="btn btn-success"
                    onclick="this.disabled=true; $('#wait').show(); this.form.submit()" disabled="disabled">
                <i class="fa fa-check-circle"></i> Aprobar
            </button>
          </div>
        </div>

        </form>--}}
      </div>
    </div>
  </div>

@endsection

@section('footer')
@endsection

@section('javascript')
  <script src="{{ asset('app/js/set_current_url.js') }}"></script> {{-- For recording current url --}}
  <script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("#wait").hide();

    /*
    var $password = $('#password'), $submit_button = $('#submit_button'), $container = $('#container');
    $("#approve_oc").change(function(){
        var checked = $('.checkbox:checked').size() > 0; //$("#approve_oc input:checked").length > 0;
        if (checked){
            $container.show();
            $password.removeAttr('disabled').show();
            $submit_button.removeAttr('disabled').show();
        }
        else{
            //alert("Please check at least one checkbox");
            $container.hide();
            $password.attr('disabled', 'disabled').hide();
            $submit_button.attr('disabled', 'disabled').hide();
        }
    }).trigger('change');
    */

    /*
    $('#select_all').click(function() {
        var status = this.checked;

        $('.checkbox').prop('checked', status);
    });
    */

    /*
    $("#select_all").change(function(){
        var status = this.checked;

        $('.checkbox').each(function(){
            this.checked = status;
        });
    });
    */

    /*
    $('.checkbox').change(function(){
        if(this.checked === false){
            $("#select_all")[0].checked = false;
        }

        if ($('.checkbox:checked').length == ($('.checkbox').length - 1) ){
            $("#select_all")[0].checked = true;
        }
    });
    */

    /*
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
    */
  </script>
@endsection
