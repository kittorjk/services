<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 02/10/2017
 * Time: 03:18 PM
 */
?>

<h3>Hola, {{ $recipient ? $recipient->name : '' }}</h3>
<p>
    Este correo es para informarle que el requerimiento {{ $requirement->code }} por el que se le debió
    haber entregado una línea corporativa a {{ $requirement->person_for ? $requirement->person_for->name : 'N/E' }},
    ha sido rechazado por le siguiente motivo:
</p>
<p>
    {{ $requirement->stat_obs }}
</p>
<p>
    No requiere de ninguna acción adicional de su parte.
</p>
<p>
    Si este cambio es un error por favor comuníquese con la persona responsable del manejo de las líneas corporativas
    para coordinar la elaboración de un nuevo requerimiento.
</p>

<h5>This is an automated message sent by the tracking system. Please DO NOT reply to this message.</h5>