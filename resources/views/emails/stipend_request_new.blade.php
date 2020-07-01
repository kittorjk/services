<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 04/12/2017
 * Time: 10:21 AM
 */
?>

<h3>Hola, {{ $recipient->name }}</h3>
<p>
    Este correo es para informarle que se ha registrado una nueva solicitud de viáticos en el sistema con el siguiente
    detalle:
</p>
<p></p>
    <table width="80%">
        <thead>
        <tr>
            <td width="20%">Número</td>
            <td>{{ 'STP-'.$stipend->id }}</td>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>Para</td>
            <td>{{ $stipend->employee->first_name.' '.$stipend->employee->last_name }}</td>
        </tr>
        <tr>
            <td>Monto</td>
            <td>{{ number_format($stipend->total_amount+$stipend->additional,2).' Bs' }}</td>
        </tr>
        <tr>
            <td>Motivo</td>
            <td>{{ $stipend->reason }}</td>
        </tr>
        </tbody>
    </table>
<br>
<p>
    Puede ver los detalles de esta solicitud ingresando a la ficha de la solicitud a través de este
    <a href="https://services.gerteabros.com/stipend_request/{{ $stipend->id }}">enlace</a>.
    Es necesario que antes haya iniciado sesión.
</p>

<h5>This is an automated message sent by gerteabros.com system. Please DO NOT reply to this message.</h5>