<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 19/01/2018
 * Time: 10:57 AM
 */
?>

<p>
    Este correo es para informarle que se ha agregado al sistema la licitación <strong>{{ $tender->name }}</strong>
    de {{ $tender->client }}.
</p>
<p>
    El plazo de presentación a esta licitación es hasta el
    {{ Carbon\Carbon::parse($tender->application_deadline)->format('d-m-Y') }}
</p>
<p>
    Puede ver los detalles de esta licitación ingresando al sistema de seguimiento a través de este
    <a href="http://services.gerteabros.com/tender">enlace</a>.
</p>
<p>
    Es necesario que previamente inicie sesión en el sistema.
</p>

<h5>This is an automated message sent by gerteabros.com system. Please DO NOT reply to this message.</h5>