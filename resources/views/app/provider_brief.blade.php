@extends('layouts.ocs_structure')

@section('header')
  @parent
  <style>
    .dropdown-menu-prim > li > a {
      width: 190px;
      /*white-space: normal; /* Set content to a second line */
    }
  </style>
  <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
  <li><a href="#">&ensp;<i class="fa fa-truck"></i> PROVEEDORES <span class="caret"></span>&ensp;</a>
    <ul class="sub-menu">
      <li><a href="{{ '/provider' }}"><i class="fa fa-list fa-fw"></i> Ver todo </a></li>
      <li><a href="{{ '/provider/create' }}"><i class="fa fa-plus fa-fw"></i> Agregar proveedor </a></li>
      <li><a href="{{ '/provider/incomplete' }}"><i class="fa fa-list fa-fw"></i> Lista de registros incompletos </a></li>
      @if($user->action->oc_prv_exp /*$user->priv_level>=3*/)
        <li><a href="{{ '/excel/providers' }}"><i class="fa fa-file-excel-o fa-fw"></i> Exportar a Excel</a></li>
      @endif
    </ul>
  </li>
  <li>
    <!--<a href="/search/providers/0"><i class="fa fa-search"></i> BUSCAR </a>-->
    <a data-toggle="modal" href="#searchBox">&ensp;<i class="fa fa-search"></i> BUSCAR&ensp;</a>
  </li>
@endsection

@section('content')

  <div class="col-sm-12 mg10">
    @include('app.session_flashed_messages', array('opt' => 0))
  </div>

  <div class="col-sm-12 mg10 mg-tp-px-5">
    <p>Registros de proveedor encontrados: {{ $providers->total() }}</p>

    <table class="fancy_table table_dark_orange" id="fixable_table">
      <thead>
        <tr>
          <th width="10%">NIT</th>
          <th width="20%">Nombre o razón social</th>
          <th>Especialidad</th>
          <th>Persona de contacto</th>
          <th>Correo electrónico</th>
          <th width="18%" colspan="2">Teléfono(s)</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($providers as $provider)
          <tr>
            <td>{{ $provider->nit ? $provider->nit : '' }}</td>
            <td>
              <a href="/provider/{{ $provider->id }}" title="Ver información de proveedor">
                {{ $provider->prov_name }}
              </a>
              @if($user->action->oc_prv_edt /*($user->area=='Gerencia Tecnica'&&$user->priv_level==2)||
                $user->priv_level>=3*/)
                <a href="{{ '/provider/'.$provider->id.'/edit' }}">
                    <i class="fa fa-pencil-square-o"></i>
                </a>
              @endif
            </td>
            <td>{{ $provider->specialty }}</td>
            <td>{{ $provider->contact_name }}</td>
            <td><a href="mailto:{{ $provider->email }}">{{ $provider->email }}</a></td>
            <td>{{ $provider->phone_number ? $provider->phone_number : '' }}</td>
            <td>{{ $provider->alt_phone_number ? $provider->alt_phone_number : '' }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="col-sm-12 mg10" align="center">
    {!! $providers->appends(request()->except('page'))->render() !!}
  </div>

  <div class="col-sm-12 mg10" id="fixed">
    <table class="fancy_table table_dark_orange" id="cloned"></table>
  </div>

  <!-- Search Modal -->
  <div id="searchBox" class="modal fade" role="dialog">
    @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'providers','id'=>0))
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
  </script>
@endsection
