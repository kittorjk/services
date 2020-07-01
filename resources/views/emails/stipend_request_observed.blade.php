<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 04/12/2017
 * Time: 10:29 AM
 */
?>

<h3>Hola, {{ $recipient->name }}</h3>
<p>
    Este correo es para informarle que se la solicitud de viáticos {{ 'STP-'.$stipend->id.' de '.
    date_format($stipend->created_at,'d-m-Y') }} ha sido observada por Gerencia técnica bajo el siguiente detalle:
</p>
<p>
    {{ $stipend->observations }}
</p>
<p>
    Por favor revise y modifique ésta solicitud para que pueda ser considerada nuevamente para su aprobación.
</p>
<p>
    Puede ver los detalles de esta solicitud ingresando a la ficha de la solicitud a través de este
    <a href="https://services.gerteabros.com/stipend_request/{{ $stipend->id }}">enlace</a>.
    Es necesario que antes haya iniciado sesión.
</p>

<h5>This is an automated message sent by gerteabros.com system. Please DO NOT reply to this message.</h5>