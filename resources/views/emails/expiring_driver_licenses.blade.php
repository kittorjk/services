<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 11/10/2017
 * Time: 04:43 PM
 */
?>

<h3>Hola, {{ $recipient->name }}</h3>
<p>
    Este correo es para informarle que las siguientes licencias de conducir registradas en el sistema estan próximas a vencer:
</p>

<table>
    <thead>
    <tr>
        <th>Número</th>
        <th>Perteneciente a</th>
        <th>Fecha de expiración</th>
        <th>Tiempo restante</th>
    </tr>
    </thead>
    <tbody>
    @foreach($licenses as $license)
        <tr>
            <td>{{ $license->number }}</td>
            <td>{{ $license->user ? $license->user->name : 'N/E' }}</td>
            <td>{{ $license->exp_date->year<1 ? 'N/E' : date_format($license->exp_date, 'd-m-Y') }}</td>
            <td>
                {{
                    $license->exp_date->year<1 ? 'N/E' :
                    (Carbon\Carbon::now()->hour(0)->minute(0)->second(0)->diffInDays($license->exp_date, false)<0 ?
                        'Vencida' :
                         Carbon\Carbon::now()->hour(0)->minute(0)->second(0)->diffInDays($license->exp_date, false).' dias')
                 }}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

<p>
    Por favor póngase en contacto con las personas detalladas a fin de regularizar los datos en el sistema.
    Las personas con licencias vencidas no podrán ser asignadas como responsables de un vehículo.
</p>

<h5>This is an automated message sent by gerteabros.com system. Please DO NOT reply to this message.</h5>