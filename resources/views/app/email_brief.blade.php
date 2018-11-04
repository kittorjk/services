<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 18/05/2017
 * Time: 10:24 AM
 */
?>

@extends('layouts.adm_structure')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/image_modal.css") }}">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    <li><a href="#">&ensp;<i class="fa fa-envelope"></i> CORREOS <span class="caret"></span>&ensp;</a>
        <ul class="sub-menu">
            <li><a href="{{ '/email' }}"><i class="fa fa-refresh"></i> Recargar página </a></li>
            @if($user->priv_level==4)
                <li><a href="{{ '/excel/emails' }}"><i class="fa fa-file-excel-o"></i> Exportar lista</a></li>
            @endif
        </ul>
    </li>
    <li><a data-toggle="modal" href="#searchBox">&ensp;<i class="fa fa-search"></i> BUSCAR&ensp;</a></li>
@endsection

@section('content')
    <div class="col-sm-12 mg10 mg-tp-px-10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10 mg-tp-px-10">
        <p>Correos encontrados: {{ $emails->total() }}</p>

        <table class="fancy_table table_gray" id="fixable_table">
            <thead>
            <tr>
                <th width="10%">Fecha</th>
                <th>Asunto</th>
                <th>Enviado a</th>
                <th>Estado</th>
                <th width="13%">Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php
                $string_ok = "<i class=\"glyphicon glyphicon-ok\" style=\"color:darkgreen\"></i>";
                $string_remove = "<i class=\"glyphicon glyphicon-remove\" style=\"color:darkred\"></i>";
            ?>
            @foreach ($emails as $email)
                <tr>
                    <td>{{ date_format($email->created_at,'d/m/Y') }}</td>
                    <td>{{ $email->subject }}</td>
                    {{--<td><a href="mailto:{{ $email->sent_by }}">{{ $email->sent_by }}</a></td>--}}
                    <td><a href="mailto:{{ $email->sent_to }}">{{ $email->sent_to }}</a></td>
                    <td align="center">{!! $email->success==1 ? $string_ok : $string_remove !!}</td>
                    <td align="center">
                        <a href="#" title="Ver contenido de correo" data-toggle="modal"
                           data-target="{{ '#emailContentBox'.$email->id }}" style="text-decoration: none">
                            <i class="fa fa-envelope"></i>
                        </a>
                        &emsp;
                        {{-- Insert option to generate .eml file if necessary
                            <a href="/download/{{ $email->id }}" title="Descargar"><i class="fa fa-download"></i></a>
                         --}}
                        <a href="/mail/send/{{ $email->id }}" title="Enviar correo a otro destinatario"
                           style="text-decoration: none">
                            <i class="fa fa-send"></i>
                        </a>
                        &emsp;
                        <a href="/mail/resend/{{ $email->id }}" title="Reenviar correo al mismo destinatario"
                           style="text-decoration: none">
                            <i class="fa fa-mail-forward"></i>
                        </a>

                        <!-- Email Content Modal -->
                        <div id="{{ 'emailContentBox'.$email->id }}" class="modal fade" role="dialog" style="text-align: left">
                            @include('app.email_content_modal', array('user'=>$user,'service'=>$service,'email'=>$email))
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-sm-12 mg10" align="center">
        {!! $emails->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="fancy_table table_gray" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'emails','id'=>0))
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

        /*
        $('.available_confirmation').on('click', function () {
            var text = "Marcar equipo como disponible?";
            return confirm(text);
        });
        */
    </script>
@endsection
