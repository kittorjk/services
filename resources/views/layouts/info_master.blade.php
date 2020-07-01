<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 22/05/2017
 * Time: 12:25 PM
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
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>

        <!-- Latest compiled JavaScript -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>

        <link rel="stylesheet" href="{{ asset("app/css/abros.css") }}">
        <link rel="stylesheet" href="{{ asset("app/css/additional_code.css") }}">
        <link rel="stylesheet" href="{{ asset("app/css/nav_menu.css") }}">

        <!-- Reference inline properties -->
        <script src="https://cdn.jsdelivr.net/jquery.metadata/2.0/jquery.metadata.min.js"></script>
    @show

    @yield('stylesheet')

</head>
<body>

@yield('content')

@section('footer')
    <div class="row_spacing"></div>
    <div class="footer">
        <p>Contact information: <a href="mailto:nestor.romero{!! '@' !!}abrostec.com">nestor.romero{!! '@' !!}abrostec.com</a></p>
        <p>Copyright &copy; 2016</p>
    </div>
@show

@yield('javascript')

</body>
</html>
