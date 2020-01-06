<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 27/01/2017
 * Time: 06:26 PM
 */
?>

<h3>Hola, {{ $recipient->name }}</h3>
<p>
    Este correo es para informarle que se ha agregado la asignación {{ $assignment->code.' '.$assignment->name }} para
    {{ $assignment->client }} al sistema,
    {{ $pm_assigned ? 'y se ha designado a '.$pm_assigned->name.' como Project Manager de este trabajo.' :
        'aún no se ha designado un Project Manager para este trabajo.' }}
</p>
<p>
    Puede ver los detalles de esta asignación ingresando al sistema de seguimiento de proyectos a través de este
    <a href="http://services.gerteabros.com/project">enlace</a>. Es necesario que antes haya iniciado sesión.
</p>

<h5>This is an automated message sent by gerteabros.com system. Please DO NOT reply to this message.</h5>