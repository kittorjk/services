@extends('layouts.projects_structure')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    @include('app.project_navigation_button', array('user'=>$user))
    <div class="btn-group">
        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
            <i class="fa fa-user"></i> Contactos <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-prim">
            <li><a href="{{ '/contact' }}"><i class="fa fa-bars"></i> Ver todo </a></li>
            <li><a href="{{ '/contact/create' }}"><i class="fa fa-plus"></i> Agregar contacto </a></li>
            <li class="divider"></li>
            <li><a href="{{ '/excel/contacts' }}"><i class="fa fa-file-excel-o"></i> Exportar a Excel </a></li>
        </ul>
    </div>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchBox">
        <i class="fa fa-search"></i> Buscar
    </button>
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

        <div class="panel panel-sky">
            <div class="panel-heading" align="center">
                <div class="panel-title">Información de contacto</div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning">
                        <i class="fa fa-arrow-circle-left"></i> Volver
                    </a>
                    <a href="{{ '/contact' }}" class="btn btn-warning" title="Ir a la tabla de contactos">
                        <i class="fa fa-arrow-circle-up"></i> Contactos
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <div class="col-sm-12 mg10 mg-tp-px-10">

                    <table class="table table-striped table-hover table-bordered">
                        <thead>
                        <tr>
                            <th width="40%">Nombre o razón social:</th>
                            <td>{{ $contact->name }}</td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <th>Cargo</th>
                            <td>{{ $contact->position ?: 'N/E' }}</td>
                        </tr>
                        <tr>
                            <th>Empresa:</th>
                            <td>{{ $contact->company }}</td>
                        </tr>

                        @if($contact->phone_1!=0||$contact->phone_2!=0)
                            <tr><td colspan="2"></td></tr>
                            <tr>
                                <th colspan="2">Teléfono(s)</th>
                            </tr>
                            @if($contact->phone_1!=0)
                                <tr>
                                    <td>Principal</td>
                                    <td>{{ $contact->phone_1 }}</td>
                                </tr>
                            @endif
                            @if($contact->phone_2!=0)
                                <tr>
                                    <td>Alternativo</td>
                                    <td>{{ $contact->phone_2 }}</td>
                                </tr>
                            @endif
                        @endif

                        @if($contact->email!='')
                            <tr><td colspan="2"> </td></tr>
                            <tr>
                                <th>Correo electrónico</th>
                                <td><a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a></td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
                @if($user->action->prj_ctc_edt /*$user->priv_level>=2*/)
                    <div class="col-sm-12 mg10" align="center">
                        <a href="/contact/{{ $contact->id }}/edit" class="btn btn-success">
                            <i class="fa fa-pencil-square-o"></i> Actualizar datos
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'contacts','id'=>0))
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
