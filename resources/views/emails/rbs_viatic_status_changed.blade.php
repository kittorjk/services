<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 01/08/2017
 * Time: 04:38 PM
 */
?>

<h3>Hola, {{ $recipient->name }}</h3>
<p>
    Este correo es para informarle que la solicitud de viáticos número {{ $rbs_viatic->id }}
    perteneciente a los sitios:
</p>
<p>
    @foreach($rbs_viatic->sites as $site)
        {!! $site->name.'<br>' !!}
    @endforeach
</p>
<p>
    {{ $rbs_viatic->status==1 ? 'Ha sido observada y requiere de su modificación para poder ser aprobada.' : '' }}
    {{ $rbs_viatic->status==2 ? 'Ha sido modificada y espera su revisión para poder ser pagada.' : '' }}
    {{ $rbs_viatic->status==4 ? 'Ha sido rechazada por el Project Manager a cargo de Radiobases,
        por lo que no se realizará el pago indicado.' : '' }}
    {{ $rbs_viatic->status==6 ? 'Ha sido cancelada,  por lo que su aprobación ya no es requerida.' : '' }}
</p>
<p>
    Puede ver los detalles de esta solicitud ingresando al sistema de seguimiento de proyectos a través de este
    <a href="http://services.gerteabros.com/rbs_viatic">enlace</a>. Es necesario que antes haya iniciado sesión.
</p>

<h5>This is an automated message sent by gerteabros.com system. Please DO NOT reply to this message.</h5>