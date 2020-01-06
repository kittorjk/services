<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 16/02/2017
 * Time: 11:03 AM
 */
?>

<h3>Hola, {{ $recipient->name }}</h3>
<p>
    Este correo es para recordarle que la asignación: {{ $assignment->code.' "'.$assignment->name.'"' }} de
    {{ $assignment->client }}
    @if(Carbon\Carbon::now()->diffInDays($assignment->deadline,false)==1)
        {{ 'vence en 1 dia' }}
    @elseif(Carbon\Carbon::now()->diffInDays($assignment->deadline,false)==0)
        {{ 'vence hoy' }}
    @elseif(Carbon\Carbon::now()->diffInDays($assignment->deadline,false)<0)
        {{ 'lleva '.abs(Carbon\Carbon::now()->diffInDays($assignment->deadline,false)).' dia(s) vencida' }}
    @elseif(Carbon\Carbon::now()->diffInDays($assignment->deadline,false)>1)
        {{ 'vence en '.Carbon\Carbon::now()->diffInDays($assignment->deadline,false).' dias' }}
    @endif
</p>
<p>
    Puede ver los detalles de esta asignación ingresando al sistema de seguimiento de proyectos a través de este
    <a href="http://services.gerteabros.com/project">enlace</a>. Es necesario que antes haya iniciado sesión.
</p>

<h5>This is an automated message sent by gerteabros.com system. Please DO NOT reply to this message.</h5>