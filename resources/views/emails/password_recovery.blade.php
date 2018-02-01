<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 06/04/2017
 * Time: 06:09 PM
 */
?>

<h3>Hola, {{ $recipient->name }}</h3>
<p>
    Este correo es para informarle que se se recibió una solicitud de reestablecimiento de contraseña de su cuenta
    en el sistema <a href="http://services.gerteabros.com/">services.gerteabros.com</a>.
</p>
<p>
    Su nueva contraseña es la siguiente:
</p>
<p>
    <strong>{{ $new_password }}</strong>
</p>
<p>
    Puede cambiar este valor ingresando al sistema y seleccionando la opción "Actualizar datos" en la parte superior
    derecha de la página.
</p>
<p>
    Si usted no solicitó este cambio de contraseña por favor póngase en contacto con el administrador del sitio.
</p>

<h5>This is an automated message sent by gerteabros.com system. Please DO NOT reply to this message.</h5>
