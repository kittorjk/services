@extends('layouts.ocs_structure')

@section('header')
  @parent
  <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
  <li><a href="#">&ensp;<i class="fa fa-list-alt"></i> O.C.s <span class="caret"></span>&ensp;</a>
    <ul class="sub-menu">
      <li><a href="{{ '/oc' }}"><i class="fa fa-bars fa-fw"></i> Ver OCs</a></li>
      @if($user->action->oc_add)
        <li><a href="{{ '/oc/create' }}"><i class="fa fa-plus fa-fw"></i> Agregar O.C.</a></li>
        <li>
          <a href="{{ '/oc/create?action=cmp' }}">
            <i class="fa fa-plus fa-fw"></i> Agregar O.C. complementaria
          </a>
        </li>
      @endif
      <li><a href="{{ '/invoice/create' }}"><i class="fa fa-plus fa-fw"></i> Agregar factura</a></li>
      @if($user->action->oc_exp /*$user->priv_level>=3*/)
        <li><a href="{{ '/excel/ocs' }}"><i class="fa fa-file-excel-o fa-fw"></i> Exportar a Excel</a></li>
      @endif
    </ul>
  </li>
  @if($user->priv_level >= 1)
    <li><a href="{{ '/provider' }}">&ensp;<i class="fa fa-truck"></i> PROVEEDORES&ensp;</a></li>
  @endif
  @if($user->priv_level >= 2)
    <li><a href="{{ '/oc_certificate' }}">&ensp;<i class="fa fa-file-text-o"></i> CERTIFICADOS&ensp;</a></li>
  @endif
  <li><a href="{{ '/invoice' }}">&ensp;<i class="fa fa-money"></i> PAGOS&ensp;</a></li>
  <!--<li>
    <a data-toggle="modal" href="#searchBox">&ensp;<i class="fa fa-search"></i> BUSCAR&ensp;</a>
  </li>-->
@endsection

@section('content')

  <div id="loginbox" class="mg-tp-px-50 col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1 mg-btm-px-40">
    <div class="panel panel-info" >
      <div class="panel-heading" align="center">
        <div class="panel-title">
          {{ $ocs->count() == 1 ? 'Aprobar orden de compra' : 'Aprobar ordenes de compra' }}
        </div>
      </div>
      <div class="panel-body">
        <div class="mg20">
          <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
            <i class="fa fa-arrow-left"></i>
          </a>
          <a href="{{ '/oc' }}" class="btn btn-warning" title="Volver a lista de OCs">
            <i class="fa fa-arrow-up"></i>
          </a>
        </div>

        @include('app.session_flashed_messages', array('opt' => 1))

        <form method="post" action="{{ '/approve_oc/' }}" id="approve_oc" accept-charset="UTF-8"
              enctype="multipart/form-data">

          <input type="hidden" name="_token" value="{{ csrf_token() }}">

          <div class="col-sm-12">
            <table class="table table-striped table-hover table-bordered">
              <tbody>
                <tr>
                  <th style="text-align: center">
                    <input type="checkbox" name="select_all" id="select_all">
                  </th>
                  <th>
                    <label for="select_all">Nº OC</label>
                  </th>
                  <th>Fecha</th>
                  <th width="55%">Concepto</th>
                </tr>
                <?php $i=0 ?>
                @foreach($ocs as $oc)
                  <tr style="{{ $oc->code === $oc_code ? 'background-color: #a6d8a6' : '' }}">
                    <td align="center">
                      <input type="checkbox" name="{{ $i }}" id="{{ $i }}" value="{{ $oc->id }}"
                              class="checkbox">
                    </td>
                    <td>
                      <label for="{{ $i }}" style="font-weight: normal">
                          <a href="/oc/{{ $oc->id }}">{{ $oc->code }}</a>
                      </label>

                      <a href="{{ '/oc/reject?id='.$oc->id }}" class="pull-right" title="Rechazar esta OC">
                          <i class="fa fa-times" style="color: darkred"></i>
                      </a>
                    </td>
                    <td>{{ date_format($oc->created_at,'d-m-Y') }}</td>
                    <td>{{ $oc->proy_concept }}</td>
                  </tr>
                  <?php $i++ ?>
                @endforeach
              </tbody>
            </table>
          </div>

          <input type="hidden" name="count" value="{{ $ocs->count() }}">

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

        </form>
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

    $('#select_all').click(function() {
        var status = this.checked;

        $('.checkbox').prop('checked', status);
    });

    /*
    $("#select_all").change(function(){
        var status = this.checked;

        $('.checkbox').each(function(){
            this.checked = status;
        });
    });
    */

    $('.checkbox').change(function(){
        if(this.checked === false){
            $("#select_all")[0].checked = false;
        }

        if ($('.checkbox:checked').length == ($('.checkbox').length - 1) ){
            $("#select_all")[0].checked = true;
        }
    });

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
