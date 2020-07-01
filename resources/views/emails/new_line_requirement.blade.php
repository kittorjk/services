<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 02/10/2017
 * Time: 03:22 PM
 */
?>

<h3>Hola, {{ $recipient ? $recipient->name : '' }}</h3>
<p>
    Este correo es para informarle que el usuario {{ $requirement->user ? $requirement->user->name : 'N/E' }}
    ha solicitado el préstamo de una línea corporativa para {{ $requirement->person_for ?
     $requirement->person_for->name : 'N/E' }}, por lo que se le solicita atienda este requerimiento en el sistema.
</p>
<p>
    Por favor comuníquese con la persona mencionada a la brevedad posible para coordinar la entrega y registrar el
    cambio de responsable en el sistema.
</p>
<p>
    Puede ver el detalle de este requerimiento ingresando al sistema con el siguiente enlace
    <a href="https://services.gerteabros.com/line_requirement">https://services.gerteabros.com/line_requirement</a>
</p>
<p>
    Es necesario que previamente inicie sesión con su usuario y contraseña.
</p>

<h5>This is an automated message sent by the tracking system. Please DO NOT reply to this message.</h5>