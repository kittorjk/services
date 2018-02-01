<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 20/03/2017
 * Time: 12:25 PM
 */
?>

<h3>Hola, {{ $recipient->name }}</h3>
<p>
    Las siguientes Órdenes de compra fueron aprobadas por el área Tecnica y esperan su aprobación:
</p>
<p>
    {{ $approved }}
</p>
<p>
    Puede ingresar al sistema a través de este <a href="http://services.gerteabros.com/oc">enlace</a>.
    Para ir a la lista de OCs pendientes de aprobación haga click <a href="http://services.gerteabros.com/approve_oc/">aquí</a>.
    Es necesario que previamente haya iniciado sesión en el sistema.
</p>

<h5>This is an automated message sent by gerteabros.com system. Please DO NOT reply to this message.</h5>
