<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 27/01/2017
 * Time: 05:19 PM
 */
?>

<h3>Hola, {{ $recipient->name }}</h3>
<p>
    Las siguientes facturas de proveedor fueron aprobadas por el área Tecnica y esperan su aprobación:
</p>
<p></p>
    <table>
        <tr>
           <td>Nro Factura</td>
           <td>Proveedor</td>
        </tr>
        @foreach($approved as $invoice)
            <tr>
                <td>{{ $invoice['number'] }}</td>
                <td>{{ $invoice['provider'] }}</td>
            </tr>
        @endforeach
    </table>
<p>
    Puede ingresar al sistema a través de este <a href="https://services.gerteabros.com/oc">enlace</a>
</p>
<p>
    Para ver los detalles de estas facturas haga click <a href="https://services.gerteabros.com/invoice">aquí</a>,
    es necesario que previamente haya iniciado sesión en el sistema.
</p>

<h5>This is an automated message sent by gerteabros.com system. Please DO NOT reply to this message.</h5>
