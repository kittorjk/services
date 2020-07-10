<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 09/11/2017
 * Time: 03:39 PM
 */
?>

<h3>Hola, {{ $recipient->name }}</h3>
<p>
    Éste correo es para informarle que la orden de compra {{ $oc->code }} ha sido observada por {{ $user->name }}.
    Las siguientes observaciones han sido registradas en el sistema:
</p>
<p>
    {{ $oc->observations }}
</p>

<p>
    Por favor corrija las observaciones señaladas o anule la orden de compra y cree una nueva.
</p>

<p>
    Ingrese al sistema de ordenes de compra a través de este <a href="https://services.gerteabros.com/oc">enlace</a>.
    <br>
    Para ir a la lista de OCs rechazadas haga click <a href="https://services.gerteabros.com/rejected_ocs/">aquí</a>.
    Es necesario que previamente haya iniciado sesión en el sistema.
</p>

<h5>This is an automated message sent by gerteabros.com system. Please DO NOT reply to this message.</h5>