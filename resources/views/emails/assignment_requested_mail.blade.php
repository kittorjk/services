<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 04/10/2017
 * Time: 02:10 PM
 */
?>

<h3>Reporte de avance general {{ $assignment->name.' '.date('d-m-Y') }}</h3>

{!! $table !!}

@if($comments!='')
    <p>{{ 'Observaciones: '.$comments }}</p>
@endif

<p>
    Este correo ha sido enviado a {{ $recipient }} a solicitud de ABROS TECHNOLOGIES SRL. Si usted desconoce esta
    solicitud ignore este correo electrónico.
</p>

<h5>This is an automated message sent by gerteabros.com system. Please DO NOT reply to this message.</h5>