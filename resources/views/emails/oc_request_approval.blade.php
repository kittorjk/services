<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 20/03/2017
 * Time: 12:22 PM
 */
?>

<h3>Hola, {{ $recipient->name }}</h3>
<p>
    La órden de compra {{ $oc->code }} previamente observada, ha sido corregida en el sistema
    y espera su aprobación.
</p>
<p>
    Puede ingresar al sistema a través de este <a href="https://services.gerteabros.com/">enlace</a>
</p>
<p>
    Para ver los detalles de esta órden haga click <a href="https://services.gerteabros.com/oc/{{ $oc->id }}">aquí</a>,
    Para ir a la lista de OCs pendientes de aprobación haga click <a href="https://services.gerteabros.com/approve_oc/">aquí</a>.
    Es necesario que previamente haya iniciado sesión en el sistema.
</p>

<h5>This is an automated message sent by gerteabros.com system. Please DO NOT reply to this message.</h5>
