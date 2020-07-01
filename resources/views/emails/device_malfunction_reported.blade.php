<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 28/03/2017
 * Time: 12:16 PM
 */
?>

<h3>Hola, {{ $recipient->name }}</h3>
<p>
    Este correo es para informarle que {{ $responsible->name }} ha reportado un problema con el equipo
    {{ $device->type.' '.$device->model }} con número de serie {{ $device->serial }}, a través del sistema de
    seguimiento de activos.
</p>
<p>
    Los detalles del problema son:
</p>
<p>
    {{ $device->condition }}
</p>
<p>
    Puede ver más detalles de éste equipo ingresando al sistema de seguimiento a través de este
    <a href="https://services.gerteabros.com/device/{{ $device->id }}">enlace</a>. Es necesario que previamente
    haya iniciado sesión en el sistema.
</p>

<h5>This is an automated message sent by gerteabros.com system. Please DO NOT reply to this message.</h5>