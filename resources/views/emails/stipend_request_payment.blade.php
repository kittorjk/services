<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 04/12/2017
 * Time: 10:38 AM
 */
?>

<h3>Hola, {{ $recipient->name }}</h3>
<p>
    Este correo es para informarle que tiene solicitudes de viáticos pendientes de pago. Las mismas se detallan en el
    archivo adjunto y en la tabla siguiente:
</p>

<table width="60%">
    <thead>
        <tr>
            <th width="35%">Código</th>
            <th>Solicitado para</th>
            <th>Solicitado por</th>
        </tr>
    </thead>
    <tbody>
        @foreach($requests as $request)
            <tr>
                <td>{{ $request->code }}</td>
                <td>{{ $request->employee ? $request->employee->first_name.' '.$request->employee->last_name : 'N/E' }}</td>
                <td>{{ $request->user ? $request->user->name : '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
<br>
<p>
    Puede ver la lista de solicitudes de viáticos ingresando al sistema a través de este
    <a href="http://services.gerteabros.com/">enlace</a>. Es necesario que antes haya iniciado sesión.
</p>

<h5>This is an automated message sent by gerteabros.com system. Please DO NOT reply to this message.</h5>