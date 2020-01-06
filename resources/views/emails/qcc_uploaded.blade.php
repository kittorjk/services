<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 30/01/2017
 * Time: 12:27 PM
 */
?>

<h3>Hola, {{ $recipient->name }}</h3>
<p>
    Este correo es para informarle que se ha agregado un nuevo certificado de control de calidad para la asignación
    {{ $assignment->name }} de {{ $assignment->client }} al sistema.
</p>
<p>
    {{ $pm ? 'Este proyecto está a cargo de '.$pm->name : '' }}
</p>
<p>
    Puede ver los detalles de este certificado ingresando al sistema de seguimiento de proyectos a través de este
    <a href="http://services.gerteabros.com/project">enlace</a>. Es necesario que antes haya iniciado sesión.
</p>

<h5>This is an automated message sent by gerteabros.com system. Please DO NOT reply to this message.</h5>