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
            {{--@if($user->priv_level==4)--}}
                <li class="divider"></li>
                <li><a href="{{ '/excel/contacts' }}"><i class="fa fa-file-excel-o"></i> Exportar a Excel </a></li>
            {{--@endif--}}
        </ul>
    </div>
    <!--<a href="/search/contacts/0" class="btn btn-primary"><i class="fa fa-search"></i> Buscar </a>-->
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchBox">
        <i class="fa fa-search"></i> Buscar
    </button>
@endsection

@section('content')

    <div class="col-sm-12 mg10 mg-tp-px-10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10 mg-tp-px-10">
        <p>Contactos registrados: {{ $contacts->total() }}</p>

        <table class="fancy_table table_sky" id="fixable_table">
            <thead>
            <tr>
                <th>Nombre</th>
                <th>Cargo</th>
                <th>Empresa</th>
                <th width="20%">Teléfono(s)</th>
                <th>Correo electrónico</th>
            </tr>
            </thead>
            <tbody>
            @foreach($contacts as $contact)
                <tr>
                    <td>
                        <a href="/contact/{{ $contact->id }}">{{ $contact->name }}</a>

                        @if($user->action->prj_ctc_edt /*$user->priv_level>=1*/)
                            <a href="/contact/{{ $contact->id }}/edit">
                                <i class="fa fa-pencil-square-o"></i>
                            </a>
                        @endif
                    </td>
                    <td>{{ $contact->position }}</td>
                    <td>{{ $contact->company }}</td>
                    <td>
                        {{ $contact->phone_1 ? ($contact->phone_2 ? $contact->phone_1.' - '.$contact->phone_2 :
                            $contact->phone_1) : '' }}
                    </td>
                    <td><a href="{{ 'mailto:'.$contact->email }}">{{ $contact->email }}</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-sm-12 mg10" align="center">
        {!! $contacts->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="fancy_table table_sky" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'contacts','id'=>0))
    </div>

@endsection

@section('footer')
    @parent
@endsection

@section('javascript')
    <script src="{{ asset('app/js/fix_table_header.js') }}"></script> {{-- For fixed header --}}
    <script>
        $('#alert').delay(2000).fadeOut('slow');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function(){
            $.post('/set_current_url', { url: window.location.href }, function(){});
        });
    </script>
@endsection
