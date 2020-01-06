<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 17/02/2017
 * Time: 02:40 PM
 */
?>

<h3>Hola, {{ $recipient->name }}</h3>
<p>
    Este correo es para recordarle que las siguientes facturas siguen pendientes de cobro:
</p>

<table>
    <thead>
    <tr>
        <th>Número</th>
        <th>Fecha de emisión</th>
        <th>Tiempo transcurrido</th>
    </tr>
    </thead>
    <tbody>
    @foreach($bills as $bill)
        <tr>
            <td>{{ $bill->code }}</td>
            <td>{{ date_format($bill->date_issued,'d-m-Y') }}</td>
            <td>
                {{ Carbon\Carbon::now()->diffInDays($bill->date_issued).' dias' }}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

<p>
    Puede ver los detalles de estas facturas ingresando al sistema de seguimiento a través de este
    <a href="http://services.gerteabros.com/project">enlace</a>, y seleccionando la opción facturas
    en la parte superior de la página.
</p>

<h5>This is an automated message sent by gerteabros.com system. Please DO NOT reply to this message.</h5>