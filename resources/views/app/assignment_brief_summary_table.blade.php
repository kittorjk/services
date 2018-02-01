<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 03/10/2017
 * Time: 11:44 AM
 */
?>

<head>
    <style>
        .table_red th {
            background-color: #9e2e2e;
        }
        .formal_table {
            font-family: Arial, Helvetica, sans-serif;
            border-collapse: collapse;
            width: 100%;
            font-size: 15px;
        }

        .formal_table td, .formal_table th {
            border: 1px solid #ddd;
            padding: 5px;
        }

        .formal_table tr:hover {background-color: #ddd;}

        .formal_table th {
            padding-top: 10px;
            padding-bottom: 10px;
            text-align: left;
            color: white;
            font-weight: normal;

        }
    </style>
</head>

<body>
<table class="formal_table table_red">
    <tr>
        <th style="text-align: center">{{ 'Avance general' }}</th>
    </tr>
    <tr>
        <td style="background-color: white; padding: 0" align="center">
            @if(true /*$assignment->type=='Fibra óptica'*/)
                <table class="formal_table" width="100%">
                    @if($assignment->cable_projected>0)
                        <tr>
                            <td width="40%">
                                Cable instalado
                                {{--
                                <i class="fa fa-question-circle pull-right"
                                   title="{{ 'Debe agregar items a esta categoría para'.
                                        ' reflejar su avance' }}">
                                </i>
                                --}}
                            </td>
                            <td align="center">
                                {!! '<strong>'.$assignment->cable_executed.'</strong> de <strong>'.
                                    $assignment->cable_projected.'</strong>' !!}
                            </td>
                            <td align="center">
                                <strong>{{ $assignment->cable_percentage.'%' }}</strong>
                            </td>
                        </tr>
                    @endif
                    @if($assignment->splice_projected>0)
                        <tr>
                            <td width="40%">
                                Empalmes ejecutados
                                {{--
                                <i class="fa fa-question-circle pull-right"
                                   title="{{ 'Debe agregar items a esta categoría para'.
                                        ' reflejar su avance' }}">
                                </i>
                                --}}
                            </td>
                            <td align="center">
                                {!! '<strong>'.$assignment->splice_executed.'</strong> de <strong>'.
                                       $assignment->splice_projected.'</strong>' !!}
                            </td>
                            <td align="center">
                                <strong>{{ $assignment->splice_percentage.'%' }}</strong>
                            </td>
                        </tr>
                    @endif
                    @if($assignment->posts_projected>0)
                        <tr>
                            <td width="40%">
                                Postes plantados
                                {{--
                                <i class="fa fa-question-circle pull-right"
                                   title="{{ 'Debe agregar items a esta categoría para'.
                                        ' reflejar su avance' }}">
                                </i>
                                --}}
                            </td>
                            <td align="center">
                                {!! '<strong>'.$assignment->posts_executed.'</strong> de <strong>'.
                                       $assignment->posts_projected.'</strong>' !!}
                            </td>
                            <td align="center">
                                <strong>{{ $assignment->posts_percentage.'%' }}</strong>
                            </td>
                        </tr>
                    @endif
                    @if($assignment->meassures_projected>0)
                        <tr>
                            <td width="40%">
                                Medidas ópticas
                                {{--
                                <i class="fa fa-question-circle pull-right"
                                   title="{{ 'Debe agregar items a esta categoría para'.
                                        ' reflejar su avance' }}">
                                </i>
                                --}}
                            </td>
                            <td align="center">
                                {!! '<strong>'.$assignment->meassures_executed.'</strong> de <strong>'.
                                    $assignment->meassures_projected.'</strong>' !!}
                            </td>
                            <td align="center">
                                <strong>{{ $assignment->meassures_percentage.'%' }}</strong>
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td width="40%">Tiempo transcurrido</td>
                        <td align="center">
                            @if($assignment->statuses($assignment->status)=='Relevamiento')
                                {!! '<strong>'.$current_date->diffInDays($assignment->quote_from).
                                    '</strong> de <strong>'.$assignment->quote_from->diffInDays($assignment->quote_to).
                                    ' días</strong>' !!}
                            @elseif($assignment->statuses($assignment->status)=='Ejecución')
                                {!! '<strong>'.$current_date->diffInDays($assignment->start_line).
                                    '</strong> de <strong>'.$assignment->start_line->diffInDays($assignment->deadline).
                                    ' días</strong>' !!}
                            @elseif($assignment->statuses($assignment->status)=='Cobro'&&
                                $assignment->billing_from->year>0)
                                {!! '<strong>'.$current_date->diffInDays($assignment->billing_from).' días</strong>' !!}
                            @elseif($assignment->status==$assignment->last_stat()/*'Concluído'*/)
                                {!! 'Duración: desde <strong>'.$assignment->created_at.'</strong> hasta <strong>'.
                                    $assignment->updated_at.'</strong> (<strong>'.
                                    $assignment->created_at->diffInDays($assignment->updated_at).
                                    ' dias</strong>)' !!}
                            @elseif($assignment->status==0 /* not assigned */)
                                {{ 'No aplica' }}
                            @else
                                {!! '<strong>'.$current_date->diffInDays($assignment->created_at).' días</strong>'.
                                   ' desde la creación de ésta asignación' !!}
                            @endif
                        </td>
                        <td align="center">
                            @if($assignment->statuses($assignment->status)=='Relevamiento'&&
                                $assignment->quote_from->diffInDays($assignment->quote_to)!=0)
                                {!! '<strong>'.number_format(($current_date->diffInDays($assignment->quote_from)/
                                    $assignment->quote_from->diffInDays($assignment->quote_to))*100,2).
                                    '%</strong>' !!}
                            @elseif($assignment->statuses($assignment->status)=='Ejecución'&&
                                $assignment->start_line->diffInDays($assignment->deadline)!=0)
                                {!! '<strong>'.number_format(($current_date->diffInDays($assignment->start_line)/
                                    $assignment->start_line->diffInDays($assignment->deadline))*100,2).
                                    '%</strong>' !!}
                            @else
                                {{ '-' }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td width="40%">Tiempo restante</td>
                        <td align="center">
                            @if($assignment->statuses($assignment->status)=='Relevamiento')
                                {!! '<strong>'.(($assignment->quote_from->diffInDays($assignment->quote_to)-
                                    $current_date->diffInDays($assignment->quote_from))>0 ?
                                    ($assignment->quote_from->diffInDays($assignment->quote_to)-
                                    $current_date->diffInDays($assignment->quote_from)) : 0).'</strong>'.
                                    ' de <strong>'.$assignment->quote_from->diffInDays($assignment->quote_to).
                                    ' días</strong>' !!}
                            @elseif($assignment->statuses($assignment->status)=='Ejecución')
                                {!! '<strong>'.(($assignment->start_line->diffInDays($assignment->deadline)-
                                    $current_date->diffInDays($assignment->start_line))>0 ?
                                    ($assignment->start_line->diffInDays($assignment->deadline)-
                                    $current_date->diffInDays($assignment->start_line)) : 0).'</strong>'.
                                    ' de <strong>'.$assignment->start_line->diffInDays($assignment->deadline).
                                    ' días</strong>' !!}
                            @else
                                {{ 'No aplica' }}
                            @endif
                        </td>
                        <td align="center">
                            @if($assignment->statuses($assignment->status)=='Relevamiento'&&
                                $assignment->quote_from->diffInDays($assignment->quote_to)!=0)
                                {!! '<strong>'.(($assignment->quote_from->diffInDays($assignment->quote_to)-
                                    $current_date->diffInDays($assignment->quote_from))>0 ?
                                    number_format(((
                                    $assignment->quote_from->diffInDays($assignment->quote_to)-
                                    $current_date->diffInDays($assignment->quote_from))/
                                    $assignment->quote_from->diffInDays($assignment->quote_to))*100,2).
                                    '%' : '0%').'</strong>' !!}
                            @elseif($assignment->statuses($assignment->status)=='Ejecución'&&
                                $assignment->start_line->diffInDays($assignment->deadline)!=0)
                                {!! '<strong>'.(($assignment->start_line->diffInDays($assignment->deadline)-
                                    $current_date->diffInDays($assignment->start_line))>0 ?
                                    number_format((($assignment->start_line->diffInDays($assignment->deadline)-
                                    $current_date->diffInDays($assignment->start_line))/
                                    $assignment->start_line->diffInDays($assignment->deadline))*100,2).
                                    '%' : '0%').'</strong>' !!}
                            @else
                                {{ '-' }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td width="40%">Tiempo sin actividad (por motivos ajenos a ABROS)</td>
                        <td align="center">
                            <?php $total_days=0; ?>
                            @foreach($assignment->dead_intervals as $dead_interval)
                                <?php $total_days += $dead_interval->total_days; ?>
                            @endforeach
                                {!! '<strong>'.($total_days==1 ? '1 día' : $total_days.' días').'</strong>' !!}
                            {{--
                            <a href="{{ '/dead_interval?assig_id='.$assignment->id }}"
                               style="color: inherit">
                                {!! '<strong>'.($total_days==1 ? '1 día' : $total_days.' días').'</strong>' !!}
                            </a>
                            --}}
                        </td>
                        <td align="center">
                            @if($assignment->statuses($assignment->status)=='Relevamiento'&&
                                $assignment->quote_from->diffInDays($assignment->quote_to)!=0)
                                {!! '<strong>'.number_format(($total_days/
                                    $assignment->quote_from->diffInDays($assignment->quote_to))*100,2).'%</strong>' !!}
                            @elseif($assignment->statuses($assignment->status)=='Ejecución'&&
                                $assignment->start_line->diffInDays($assignment->deadline)!=0)
                                {!! '<strong>'.number_format(($total_days/
                                    $assignment->start_line->diffInDays($assignment->deadline))*100,2).'%</strong>' !!}
                            @else
                                {{ '-' }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Fecha estimada de finalización</td>
                        <td colspan="2" align="center">
                            @if($assignment->statuses($assignment->status)=='Relevamiento')
                                {!! $assignment->quote_to->year>1 ?
                                    '<strong>'.$assignment->quote_to->format('d-m-Y').'</strong>' : 'No especificado' !!}
                            @elseif($assignment->statuses($assignment->status)=='Ejecución')
                                {!! $assignment->end_date->year>1 ?
                                    '<strong>'.$assignment->end_date->format('d-m-Y').'</strong>' : 'No especificado' !!}
                            @else
                                {{ '-' }}
                            @endif
                        </td>
                    </tr>
                </table>
            @else
                {{ 'No existen datos que mostrar' }}
            @endif
        </td>
    </tr>
</table>
</body>