<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 27/01/2017
 * Time: 05:23 PM
 */
?>

<h3>Hola, {{ $recipient->name }}</h3>
<p>
    Las siguientes facturas de proveedor fueron aprobadas por Gerencia General, por lo que ya pueden ser canceladas:
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
    Puede ver los detalles de estas facturas ingresando al sistema a través de este
    <a href="https://services.gerteabros.com/oc">enlace</a> y seleccionando la opción Pagos en la parte
    superior de la página.
</p>

<h5>This is an automated message sent by gerteabros.com system. Please DO NOT reply to this message.</h5>
