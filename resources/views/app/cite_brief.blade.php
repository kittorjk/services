@extends('layouts.master')

@section('header')
    @parent
    <style>
        .dropdown-menu-prim > li > a {
            width: 190px;
        }
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')
    <div class="mg20 menuFijo">
        <nav>
            <ul class="menu navgreen">
                @if($user->priv_level==4)
                    <li><a href="/" title="Menú raíz del sistema">&ensp;<b>{{ '/' }}</b>&ensp;</a></li>
                @endif

                @include('app.menu_app_popup', array('user' => $user, 'mode' => 'li'))

                <li><a href="{{ '/cite' }}" title="Inicio">&ensp;<i class="fa fa-home" style="font-size: 1.3em;"></i>&ensp;</a></li>
                <li><a href="{{ Request::url() }}" title="Recargar página">&ensp;<i class="fa fa-refresh" style="font-size: 1.3em;"></i>&ensp;</a></li>

                <li><a href="#">&ensp;<i class="fa fa-envelope-o"></i> CITES <span class="caret"></span>&ensp;</a>
                    <ul class="sub-menu">
                        <li><a href="{{ '/cite' }}"><i class="fa fa-bars"></i> Ver todo </a></li>
                        @if($user->action->ct_vw_all /*($user->priv_level==3&&$user->area=='Gerencia General')||$user->priv_level==4*/)
                            <li><a href="{{ '/cite?prefix=gg' }}"><i class="fa fa-bars"></i> CITES de Gerencia general </a></li>
                            <li><a href="{{ '/cite?prefix=adm' }}"><i class="fa fa-bars"></i> CITES de Administración </a></li>
                            <li><a href="{{ '/cite?prefix=tec' }}"><i class="fa fa-bars"></i> CITES de Gerencia técnica </a></li>
                        @endif
                        <li><a href="{{ '/cite/create' }}"><i class="fa fa-plus"></i> Agregar CITE </a></li>
                        {{--<li><a href="{{ '/delete/cite' }}"><i class="fa fa-trash-o"></i> Borrar un archivo</a></li>--}}
                        @if($user->action->ct_exp /*$user->priv_level==4*/)
                            <li><a href="{{ '/excel/cites' }}"><i class="fa fa-file-excel-o"></i> Exportar</a></li>
                        @endif
                    </ul>
                </li>
                <li><a href="#">&ensp;<i class="fa fa-file-text-o"></i> FORMATO <span class="caret"></span>&ensp;</a>
                    <ul class="sub-menu">
                        <li><a href="{{ '/download/ct-0' }}"><i class="fa fa-download"></i> Descargar formato </a></li>
                        @if($user->action->ct_upl_fmt /*$user->priv_level>=3*/)
                            <li><a href="{{ '/files/format/0' }}"><i class="fa fa-upload"></i> Nuevo formato </a></li>
                        @endif
                    </ul>
                </li>
                <li>
                    {{--<!--<a href="/search/cites/0"><i class="fa fa-search"></i> BUSCAR </a>-->--}}
                    <a data-toggle="modal" href="#searchBox">&ensp;<i class="fa fa-search"></i> BUSCAR&ensp;</a>
                </li>
                <li style="float:right; padding-right:40px;">
                    <a href="#">&ensp;<i class="fa fa-user"></i> &ensp;Mi cuenta&ensp; <span class="caret"></span>&ensp;</a>
                    <ul class="sub-menu">
                        <li><a href="/user/{{ $user->id }}"><i class="fa fa-user fa-fw"></i> {{ $user->name }}</a></li>
                        <li><a href="/user/{{ $user->id }}/edit"><i class="fa fa-pencil-square-o"></i> Actualizar datos </a></li>
                        <li><a href="/logout/{{ $service }}"><i class="fa fa-sign-out"></i> Salir </a></li>
                    </ul>
                </li>
            </ul>
        </nav>
    </div>

        <div class="col-sm-12 mg10 mg-tp-px-10">
            @include('app.session_flashed_messages', array('opt' => 0))
        </div>

        <div class="col-sm-12 mg10 mg-tp-px-10">
            <p>Registros encontrados: {{ $cites->total() }}</p>

        <table class="fancy_table table_green" id="fixable_table">
            <thead>
                <tr>
                    <th width="13%">Nº CITE</th>
                    <th width="10%">Fecha</th>
                    <th>Area</th>
                    <th>Responsable</th>
                    <th>Para Empresa</th>
                    <th>Destinatario</th>
                    <th>Asunto</th>
                    <th width="13%">Archivos</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cites as $cite)
                    <tr>
                        <td>
                            @if($user->action->ct_edt /*$user->priv_level==4*/)
                                <a href="/cite/{{ $cite->id }}/edit" title="Modificar registro">
                                    {{ $cite->code }}
                                </a>
                            @else
                                {{ $cite->code }}
                            @endif
                            {{-- $cite->title.'-'.str_pad($cite->num_cite, 3, "0", STR_PAD_LEFT).
                                date_format($cite->created_at,'-Y') --}}
                        </td>
                        <td>{{ date_format($cite->created_at,'d-m-Y') }}</td>
                        <td>
                            @if($cite->area=='Gerencia Tecnica')
                                {{ 'Tecnica' }}
                            @elseif($cite->area=='Gerencia Administrativa')
                                {{ 'Administrativa' }}
                            @elseif($cite->area=='Gerencia General')
                                {{ 'G. General' }}
                            @else
                                {{ $cite->area }}
                            @endif
                        </td>
                        <td>{{ $cite->responsable }}</td>
                        <td>{{ $cite->para_empresa }}</td>
                        <td>{{ $cite->destino }}</td>
                        <td>{{ $cite->asunto }}</td>
                        <td>
                            @foreach($cite->files as $file)
                                <a href="/download/{{ $file->id }}" style="text-decoration: none">
                                    @if($file->type=="pdf")
                                        <img src="{{ '/imagenes/pdf-icon.png' }}" alt="PDF" />
                                        <!--http://icons.iconarchive.com/icons/fatcow/farm-fresh/32/file-extension-pdf-icon.png
                                           http://icons.iconarchive.com/icons/carlosjj/microsoft-office-2013/32/Word-icon.png-->
                                    @elseif($file->type=="docx"||$file->type=="doc")
                                        <img src="{{ '/imagenes/word-icon.png' }}" alt="WORD" />
                                    @endif
                                </a>
                                {{--<!--
                                   <input type="button"  value="Print" name="Submit" id="printbtn"
                                          onclick="to_print(mysrc='{{ '/display_file/'.$file->id }}')">
                                   -->--}}
                            @endforeach
                            @if($cite->files->count()<2)
                                <a href="/files/cite/{{ $cite->id }}"><i class="fa fa-upload"></i> Subir archivo</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        <div class="col-sm-12 mg10" align="center">
            {!! $cites->appends(request()->except('page'))->render() !!}
        </div>

<div class="col-sm-12 mg10" id="fixed">
    <table class="fancy_table table_green" id="cloned"></table>
</div>

<!-- Search Modal -->
<div id="searchBox" class="modal fade" role="dialog">
    @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'cites','id'=>0))
</div>

@endsection

@section('footer')
    @parent
@endsection

@section('javascript')
    <script src="{{ asset('app/js/fix_table_header.js') }}"></script> {{-- For fixed header --}}
    <script src="{{ asset('app/js/popover.js') }}"></script> {{-- For tooltips --}}
    <script src="{{ asset('app/js/set_current_url.js') }}"></script> {{-- For recording current url --}}
    <script>
        $('#alert').delay(2000).fadeOut('slow');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        /*
        $('.edit_button').on('click', function(){
            $.post('/set_current_url', { url: window.location.href }, function(){});

            //alert(window.location.href);
        });
        */

        /*
        function to_print(mysrc)
        {
            //alert(mysrc);
            var w = window.open(mysrc);
            //w.document.write(mysrc);
            w.onload(w.print());
            //w.print();
            w.close();
            //w.setTimeout("close()", 1000);
        }
        */
    </script>
@endsection
