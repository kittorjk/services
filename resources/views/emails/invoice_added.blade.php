<h3>Hola, {{ $recipient->name }}</h3>
<p>
    La factura de proveedor {{ $invoice->number }} correspondiente a la orden
    {{ 'OC-'.str_pad($invoice->oc->id, 5, "0", STR_PAD_LEFT) }} ha sido agregada al sistema y está pendiente de pago.
    {{--espera su aprobación.--}}
</p>
<p>
    Puede ingresar al sistema a través de este <a href="http://services.gerteabros.com/oc">enlace</a>
</p>
<p>
    Puede ver los detalles de esta factura y proceder a su pago //aprobación haga click
    <a href="http://services.gerteabros.com/invoice/{{ $invoice->id }}">aquí</a>,
    es necesario que previamente haya iniciado sesión en el sistema.
</p>
<!--
<p>
    Para ver los detalles de esta factura haga click ingresando al sistema a través de este
    <a href="http://services.gerteabros.com/oc">enlace</a> y seleccionando la opción Pagos en la parte
    superior de la página.
</p>
-->

<h5>This is an automated message sent by gerteabros.com system. Please DO NOT reply to this message.</h5>
