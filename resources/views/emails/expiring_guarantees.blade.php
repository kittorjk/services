<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 17/02/2017
 * Time: 03:05 PM
 */
?>

<p>
    Este correo es para recordarle que las siguientes polizas de garantía están por vencer:
</p>
<table>
    <thead>
    <tr>
        <th>Número de poliza</th>
        <th>Fecha de vencimiento</th>
        <th>Días restantes</th>
    </tr>
    </thead>
    <tbody>
    @foreach($guarantees as $guarantee)
        <tr>
            <td>{{ $guarantee->code }}</td>
            <td>{{ date_format($guarantee->expiration_date,'d-m-Y') }}</td>
            <td>
                {{ Carbon\Carbon::now()->hour(0)->minute(0)->second(0)->diffInDays($guarantee->expiration_date,false) }}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
<p>
    Puede ver los detalles de estas polizas ingresando al sistema de seguimiento a través de este
    <a href="https://services.gerteabros.com/project">enlace</a>, y seleccionando la opción "polizas" en el menú
    desplegable de la parte superior de la página.
</p>

<h5>This is an automated message sent by gerteabros.com system. Please DO NOT reply to this message.</h5>
