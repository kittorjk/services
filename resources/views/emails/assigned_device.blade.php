<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 21/02/2017
 * Time: 10:42 AM
 */
?>

<h3>Hola, {{ $recipient->name }}</h3>
<p>
    Este correo es para informarle que se le ha asignado el equipo {{ $device->type.' '.$device->model }}
    con número de serie {{ $device->serial }}.
</p>
<p>
    Para ver los detalles de esta asignación y confirmar que recibió el equipo, por favor ingrese al sistema
    de seguimiento de activos haciendo click <a href="https://services.gerteabros.com/active">aquí</a>.
</p>
<p>
    Es necesario que previamente inicie sesión en el sistema.
</p>

<h5>This is an automated message sent by the tracking system. Please DO NOT reply to this message.</h5>
