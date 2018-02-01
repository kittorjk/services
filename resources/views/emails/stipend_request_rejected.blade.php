<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 04/12/2017
 * Time: 10:36 AM
 */
?>

<h3>Hola, {{ $recipient->name }}</h3>
<p>
    Este correo es para informarle que se la solicitud de viáticos {{ 'STP-'.$stipend->id.' de '.
    date_format($stipend->created_at,'d-m-Y') }} ha sido <strong>rechazada</strong> por Gerencia técnica bajo el
    siguiente detalle:
</p>
<p>
    {{ $stipend->observations }}
</p>
<p>
    Las modificaciones a esta solicitud han sido bloqueadas y ya no se requiere de ninguna acción de su parte.
</p>
<p>
    Puede ver los detalles de esta solicitud ingresando a la ficha de la solicitud a través de este
    <a href="http://services.gerteabros.com/stipend_request/{{ $stipend->id }}">enlace</a>.
    Es necesario que antes haya iniciado sesión.
</p>

<h5>This is an automated message sent by gerteabros.com system. Please DO NOT reply to this message.</h5>