<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 01/08/2017
 * Time: 12:50 PM
 */
?>

<h3>Hola, {{ $recipient->name }}</h3>
<p>
    Este correo es para informarle que se registró una nueva solicitud de viáticos para técnicos de Radiobases
    en el sistema, con los siguientes datos:
</p>
<p>
    Número de solicitud: {{ $rbs_viatic->id }}<br>
    Descripción de trabajo: {{ $rbs_viatic->work_description }}<br>
    Sitios a los que corresponde:
</p>
<p>
    @foreach($rbs_viatic->sites as $site)
        {!! $site->name.'<br>' !!}
    @endforeach
</p>
<p>
    Puede ver los detalles de esta solicitud ingresando al sistema de seguimiento de proyectos a través de este
    <a href="https://services.gerteabros.com/rbs_viatic">enlace</a>. Es necesario que antes haya iniciado sesión.
</p>

<h5>This is an automated message sent by gerteabros.com system. Please DO NOT reply to this message.</h5>