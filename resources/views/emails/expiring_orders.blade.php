<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 16/02/2017
 * Time: 12:49 PM
 */
?>

<h3>Hola, {{ $recipient->name }}</h3>
<p>
    Este correo es para recordarle que las siguientes órdenes siguen en estado pendiente:
</p>

<table>
    <thead>
    <tr>
        <th>Código</th>
        <th>Cliente</th>
        <th>Tiempo transcurrido</th>
    </tr>
    </thead>
    <tbody>
    @foreach($orders as $order)
        <tr>
            <td>{{ $order->type.' - '.$order->code }}</td>
            <td>{{ $order->client }}</td>
            <td>
                {{ Carbon\Carbon::now()->diffInDays($order->date_issued).' dias' }}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

<p>
    Puede ver los detalles de estas ordenes ingresando al sistema de seguimiento a través de este
    <a href="https://services.gerteabros.com/project">enlace</a>, y seleccionando la opción órdenes.
</p>

<h5>This is an automated message sent by gerteabros.com system. Please DO NOT reply to this message.</h5>