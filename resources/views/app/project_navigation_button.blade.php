<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 18/07/2017
 * Time: 03:30 PM
 */
?>

<div class="btn-group">
    <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
        <i class="fa fa-arrow-circle-right"></i> Ir a <span class="caret"></span>
    </button>
    <ul class="dropdown-menu dropdown-menu-prim">
        {{-- @if($user->area=='Gerencia Tecnica'||$user->priv_level>=2) --}}
        <li><a href="{{ '/project' }}"><i class="fa fa-arrow-right"></i> Contratos</a></li>
        <li><a href="{{ '/assignment' }}"><i class="fa fa-arrow-right"></i> Asignaciones </a></li>
        <li><a href="{{ '/item_category' }}"><i class="fa fa-arrow-right"></i> Categorías de items </a></li>
        <li><a href="{{ '/tender' }}"><i class="fa fa-arrow-right"></i> Licitaciones </a></li>
        <li><a href="{{ '/contact' }}"><i class="fa fa-phone"></i> Contactos</a></li>
        {{--<li><a href="{{ '/site' }}"><i class="fa fa-arrow-right"></i> Sitios </a></li>--}}
        {{-- @endif --}}
        {{--@if($user->area=='Gerencia Administrativa'||$user->area=='Gerencia General'||$user->priv_level>=3)--}}
        @if($user->action->prj_acc_wty)
            <li><a href="{{ '/guarantee' }}"><i class="fa fa-arrow-right"></i> Polizas </a></li>
        @endif
        {{--
        @if($user->priv_level==4)
            <li><a href="{{ '/contract' }}"><i class="fa fa-arrow-right"></i> Contratos </a></li>
        @endif
        --}}
        @if($user->action->prj_acc_rdr)
            <li><a href="{{ '/order' }}"><i class="fa fa-arrow-right"></i> Ordenes </a></li>
            <li><a href="{{ '/bill' }}"><i class="fa fa-arrow-right"></i> Facturas </a></li>
        @endif
        <li><a href="{{ '/stipend_request' }}"><i class="fa fa-arrow-right"></i> Solicitudes de viáticos </a></li>
        {{--@endif--}}
    </ul>
</div>
