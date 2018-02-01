<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 16/02/2017
 * Time: 05:39 PM
 */
?>

<h3>Hola, {{ $recipient->name }}</h3>
<p>
    Este correo es para recordarle que los siguientes contratos están por vencer:
</p>

<table>
    <thead>
    <tr>
        <th>Código</th>
        <th>Cliente</th>
        <th>Fecha de vencimiento</th>
    </tr>
    </thead>
    <tbody>
    @foreach($contracts as $contract)
        <tr>
            <td>{{ $contract->code }}</td>
            <td>{{ $contract->client }}</td>
            <td>{{ date_format($contract->valid_to,'d-m-Y') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<p>
    Puede ver los detalles de estas ordenes ingresando al sistema de seguimiento a través de este
    <a href="http://services.gerteabros.com/project">enlace</a>, y seleccionando la opción contratos en la
    parte superior de la página.
</p>

<h5>This is an automated message sent by gerteabros.com system. Please DO NOT reply to this message.</h5>