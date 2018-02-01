<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 12/09/2017
 * Time: 10:46 AM
 */
?>

<h3>Hola, {{ $recipient ? $recipient->name : '' }}</h3>
<p>
    Este correo es para informarle que el vehículo {{ $requirement->vehicle ? $requirement->vehicle->type.' '.
    $requirement->vehicle->model.' con placa '.$requirement->vehicle->license_plate : 'N/E' }},
    actualmente a su cargo, ha sido requerido por el área técnica mediante el sistema, y se le solicita entregar
    el mismo a {{ $requirement->person_for ? $requirement->person_for->name : 'N/E' }}.
</p>
<p>
    por favor comuníquese con la persona mencionada a la brevedad posible para coordinar la entrega y registrar el
    cambio de responsable en el sistema.
</p>
<p>
    Puede ver el detalle de este requerimiento ingresando al sistema con el siguiente enlace
    <a href="http://services.gerteabros.com/vehicle_requirement">http://services.gerteabros.com/vehicle_requirement</a>
</p>
<p>
    Es necesario que previamente inicie sesión con su usuario y contraseña.
</p>

<h5>This is an automated message sent by the tracking system. Please DO NOT reply to this message.</h5>