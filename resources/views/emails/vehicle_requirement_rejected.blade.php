<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 12/09/2017
 * Time: 11:42 AM
 */
?>

<h3>Hola, {{ $recipient ? $recipient->name : '' }}</h3>
<p>
    Este correo es para informarle que el requerimiento {{ $requirement->code }} por el que se le debió
    haber entregado el equipo {{ $requirement->vehicle ? $requirement->vehicle->type.' '.
    $requirement->vehicle->model.' con placa '.$requirement->vehicle->license_plate : 'N/E' }},
    ha sido rechazado y ya no requiere de ninguna acción de su parte.
</p>
<p>
    Si este cambio es un error por favor comuníquese con la persona responsable del requerimiento para
    coordinar la elaboración de un nuevo requerimiento.
</p>

<h5>This is an automated message sent by the tracking system. Please DO NOT reply to this message.</h5>