<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 30/05/2017
 * Time: 05:28 PM
 */
?>

<a href="/download/{{ $file->id }}" title="Descargar archivo" style="text-decoration: none">
    @if($file->type=='pdf')
        <img src="{{ '/imagenes/pdf-icon.png' }}" alt="PDF"/>
    @elseif($file->type=='xls'||$file->type=='xlsx')
        <img src="{{ '/imagenes/excel-icon.png' }}" alt="EXCEL"/>
    @elseif($file->type=='doc'||$file->type=='docx')
        <img src="{{ '/imagenes/word-icon.png' }}" alt="WORD"/>
    @elseif($file->type=='jpg'||$file->type=='jpeg'||$file->type=='png')
        <img src="{{ '/imagenes/image-icon.png' }}" alt="IMAGE"/>
    @endif
</a>

&ensp;
<a href="/file/{{ $file->id }}" title="Ver información de archivo">Inf. de archivo</a>

@if($file->type=='pdf')
    &ensp;
    <a href="/display_file/{{ $file->id }}" target="_blank"
       title="Mostrar archivo en una nueva pestaña del navegador">Ver PDF</a>
@endif