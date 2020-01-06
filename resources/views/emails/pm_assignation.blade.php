<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 27/01/2017
 * Time: 06:34 PM
 */
?>

<h3>Hola, {{ $recipient->name }}</h3>
<p>
    Este correo es para informarle que usted ha sido designado(a) como Project Manager de la asignación
    {{ $assignment->code.' '.$assignment->name }} para {{ $assignment->client }}.
</p>
<p>
    Puede ver los detalles de esta asignación ingresando al sistema de seguimiento de proyectos a través de éste
    <a href="http://services.gerteabros.com/assignment">enlace</a>. Es necesario que antes haya iniciado sesión.
</p>

<h5>This is an automated message sent by gerteabros.com system. Please DO NOT reply to this message.</h5>