<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 20/04/2017
 * Time: 12:11 PM
 */
?>

<p>
    Este correo es para recordarle que el plazo para presentación a las siguientes licitaciones vence pronto:
</p>
<table>
    <thead>
    <tr>
        <th>Licitación</th>
        <th>Deadline</th>
        <th>Días restantes</th>
    </tr>
    </thead>
    <tbody>
    @foreach($projects as $project)
        <tr>
            <td>{{ $project->name }}</td>
            <td>{{ date_format($project->application_deadline,'d-m-Y') }}</td>
            <td>
                {{ Carbon\Carbon::now()->hour(0)->minute(0)->second(0)->diffInDays($project->application_deadline,false) }}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
<p>
    Puede ver los detalles de estas licitaciones ingresando al sistema de seguimiento a través de este
    <a href="http://services.gerteabros.com/project">enlace</a>, y seleccionando la opción "Proyectos" en el menú
    desplegable "Ir a" de la parte superior de la página.
</p>

<h5>This is an automated message sent by gerteabros.com system. Please DO NOT reply to this message.</h5>