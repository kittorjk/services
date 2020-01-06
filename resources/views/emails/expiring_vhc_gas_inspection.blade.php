<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 13/11/2017
 * Time: 12:35 PM
 */
?>

<h3>Hola, {{ $recipient->name }}</h3>
<p>
    Este correo es para informarle que los documentos de inspección técnica para vehículos a GNV registrados
    en el sistema para los siguientes vehículos estan próximos a vencer:
</p>

<table>
    <thead>
    <tr>
        <th>Vehículo</th>
        <th>Placa</th>
        <th>Fecha de expiración</th>
        <th>Tiempo restante</th>
    </tr>
    </thead>
    <tbody>
    @foreach($exp_inspections as $inspection)
        <tr>
            <td>{{ $inspection->type.' '.$inspection->model }}</td>
            <td>{{ $inspection->license_plate ?: 'N/E' }}</td>
            <td>{{ $inspection->gas_inspection_exp->year<1 ? 'N/E' : date_format($inspection->gas_inspection_exp, 'd-m-Y') }}</td>
            <td>
                {{
                    $inspection->gas_inspection_exp->year<1 ? 'N/E' :
                    (Carbon\Carbon::now()->hour(0)->minute(0)->second(0)->diffInDays($inspection->gas_inspection_exp, false)<0 ?
                        'Vencida' :
                         Carbon\Carbon::now()->hour(0)->minute(0)->second(0)
                         ->diffInDays($inspection->gas_inspection_exp, false).' dias')
                 }}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

<p>
    Por favor renueve estos documentos y actualice los cambios en el sistema.
</p>

<h5>This is an automated message sent by gerteabros.com system. Please DO NOT reply to this message.</h5>