<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 04/07/2017
 * Time: 12:17 PM
 */
?>

<html lang="en">
<head>
    @section('header')
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <title class="no-print">Abros</title>
        <link rel="shortcut icon" href="/imagenes/{{$service}}.ico" />
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css"
              integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.2/toastr.min.css" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.34.7/css/bootstrap-dialog.min.css" />

        <!-- jQuery library -->
        <script src="{{ asset('https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js') }}"></script>

        <!-- Latest compiled JavaScript -->
        <script src="{{ asset('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js') }}"></script>

        <link rel="stylesheet" href="{{ asset("app/css/abros.css") }}">
        <link rel="stylesheet" href="{{ asset("app/css/additional_code.css") }}">
        <link rel="stylesheet" href="{{ asset("app/css/nav_menu.css") }}">

        <!-- Reference inline properties -->
        <script src="{{ asset('https://cdn.jsdelivr.net/jquery.metadata/2.0/jquery.metadata.min.js') }}"></script>
        <!-- Sort table by columns -->
        <script type="text/javascript" src="{{ asset('/app/js/jquery.tablesorter.min.js') }}"></script>
    @show

    @yield('stylesheet')
    <style>
        .menuFijo {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1;
            background: #FFF;
        }
        .margenSuperior {
            top: 64px;
        }
    </style>
</head>
<body>

@section('menu_bar')
    <div class="menuFijo">
        <div class="col-sm-12 mg20 mg-tp-px-10">
            <div class="row">
                <div class="col-sm-8">
                    @if($user->priv_level==4)
                        <a href="/" class="btn btn-primary" title="Menú raíz del sistema"><b>{{ '/' }}</b></a>
                    @endif
        
                    @include('app.menu_app_popup', array('user' => $user, 'mode' => 'a'))

                    <a href="{{ '/active' }}" class="btn btn-primary" title="Inicio"><i class="fa fa-home" style="font-size: 1.4em;"></i></a>
                    <a href="#" onclick="history.back();" class="btn btn-primary" title="Volver">
                        <i class="fa fa-arrow-circle-left" style="font-size: 1.4em;"></i>
                    </a>
                    {{--<a href="{{ Request::fullUrl() }}" class="btn btn-primary" title="Recargar página"><i class="fa fa-refresh" style="font-size: 1.4em;"></i></a>--}}
                    <a href="" onclick="window.location.reload();" class="btn btn-primary" title="Recargar página"><i class="fa fa-refresh" style="font-size: 1.4em;"></i></a>
        
                    @yield('menu_options')
        
                </div>
                <div class="col-sm-4" align="right">
                    @include('app.menu_upper_right')
                </div>
            </div>
        </div>
    </div>
@show

<div style="margin-top:60px">
    @yield('content')
</div>

@section('footer')
    <div class="row_spacing"></div>
    <div class="footer">
        <p>Contact information: <a href="mailto:nestor.romero{!! '@' !!}abrostec.com">nestor.romero{!! '@' !!}abrostec.com</a></p>
        <p>Copyright &copy; 2016</p>
    </div>
@show

<script src="{{ asset('app/js/popover.js') }}"></script> {{-- For tooltips --}}
@yield('javascript')

</body>
</html>
