<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 07/09/2017
 * Time: 05:00 PM
 */
?>

<h3>Hola, {{ $recipient ? $recipient->name : '' }}</h3>
<p>
    Este correo es para informarle que el requerimiento {{ $requirement->code }} por el que se le debió
    haber entregado el equipo {{ $requirement->device ? $requirement->device->type.' '.
    $requirement->device->model.' con serial número '.$requirement->device->serial : 'N/E' }},
    ha sido rechazado y ya no requiere de ninguna acción de su parte.
</p>
<p>
    Si este cambio es un error por favor comuníquese con la persona responsable del requerimiento para
    coordinar la elaboración de un nuevo requerimiento.
</p>

<h5>This is an automated message sent by the tracking system. Please DO NOT reply to this message.</h5>