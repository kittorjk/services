<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 02/02/2017
 * Time: 05:48 PM
 */
?>

<h3>Hola, {{ $recipient->name }}</h3>
<p>
    Este correo es para informarle que se le ha asignado una nueva cuenta de usuario en el sistema
    <a href="https://services.gerteabros.com">gerteabros.com</a>.
</p>
<p>
    Puede ingresar a su cuenta siguiendo el enlace, con los siguiente valores:
</p>
<p>
    Usuario: {{ $recipient->login }}
    Contraseña: {{ $recipient->login }}
</p>
<p>
    Los datos de inicio de sesión pueden ser modificados una vez dentro de su cuenta en la opción "Actualizar datos".
</p>

<h5>This is an automated message sent by gerteabros.com system. Please DO NOT reply to this message.</h5>