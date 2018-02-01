<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 31/07/2017
 * Time: 12:19 PM
 */
?>

<h3>Hola, {{ $recipient->name }}</h3>
<p>
    Este correo es para informarle que se ha registrado una nueva solicitud de viáticos en el sistema, para los siguientes
    sitios:
</p>
<p>
    @foreach($rbs_viatic->sites as $site)
        {!! $site->name.'<br>' !!}
    @endforeach
</p>
<p>
    Puede ver los detalles de esta solicitud ingresando al sistema de seguimiento de proyectos a través de este
    <a href="http://services.gerteabros.com/rbs_viatic">enlace</a>. Es necesario que antes haya iniciado sesión.
</p>

<h5>This is an automated message sent by gerteabros.com system. Please DO NOT reply to this message.</h5>