<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 13/12/2017
 * Time: 12:24 PM
 */
?>

@if ($mode == 'li')
    <li {{--class="appBlue"--}} style="height: 31px;"
        title="Seleccione un módulo" data-popover="true" data-html=true id='app_container'
        data-content="
                            {{ '&ensp;' }}
                            <?php $i = 0; ?>
        @if ($user->action->acc_adm /*$user->priv_level==4*/)
            {{
                '<a href="/adm" title="Menú de administración" style="text-decoration: none; color: inherit">
                    <i class="fa fa-ge fa-2x fa-fw"></i>
                </a>
                &ensp;'
            }}
            <?php $i++; ?>
        @endif
        @if ($user->acc_cite == 1)
            {{
                '<a href="/cite" title="Módulo de CITEs" style="text-decoration: none; color: inherit">
                    <i class="fa fa-envelope fa-2x fa-fw"></i>
                </a>
                &ensp;'
            }}
            <?php $i++; ?>
        @endif
        @if ($user->acc_oc == 1)
            {{
                '<a href="/oc" title="Módulo de Ordenes de Compra" style="text-decoration: none; color: inherit">
                    <i class="fa fa-list-alt fa-2x fa-fw"></i>
                </a>
                &ensp;'
            }}
            <?php $i++; ?>
        @endif
        @if ($user->acc_project == 1)
            {{
                '<a href="/assignment" title="Módulo de Proyectos" style="text-decoration: none; color: inherit">
                    <i class="fa fa-cogs fa-2x fa-fw"></i>
                </a>
                &ensp;'
            }}
            <?php $i++; ?>
        @endif
        @if ($i >= 2)
            {{ '<br><p></p>&ensp;' }}
        @endif
        @if ($user->acc_project == 1)
           {{
               '<a href="/stipend_request" title="Módulo de Viáticos" style="text-decoration: none; color: inherit">
                   <i class="fa fa-plane fa-2x fa-fw"></i>
               </a>
               &ensp;'
           }}
           <?php $i++; ?>
        @endif
        @if ($user->acc_active == 1)
            {{
                '<a href="/device" title="Módulo de Equipos" style="text-decoration: none; color: inherit">
                    <i class="fa fa-laptop fa-2x fa-fw"></i>
                </a>
                &ensp;'
            }}
            <?php $i++; ?>
            {{
                '<a href="/vehicle" title="Módulo de Vehículos" style="text-decoration: none; color: inherit">
                    <i class="fa fa-car fa-2x fa-fw"></i>
                </a>
                &ensp;'
            }}
            <?php $i++; ?>
        @endif
                ">
        &emsp;<i class="glyphicon glyphicon-th" style="font-size: 1.1em; color:#ffffff;"></i>&emsp;
    </li>
@elseif ($mode == 'a')
    <a href="#" class="btn btn-primary" style="height: 34px"
       title="Seleccione un módulo" data-popover="true" data-html=true id='app_container'
       data-content="
                        {{ '&ensp;' }}
                        <?php $i = 0; ?>
       @if ($user->action->acc_adm /*$user->priv_level==4*/)
           {{
               '<a href="/adm" title="Menú de administración" style="text-decoration: none; color: inherit">
                   <i class="fa fa-ge fa-2x fa-fw"></i>
               </a>
               &ensp;'
           }}
           <?php $i++; ?>
       @endif
       @if ($user->acc_cite == 1)
           {{
               '<a href="/cite" title="Módulo de CITEs" style="text-decoration: none; color: inherit">
                   <i class="fa fa-envelope fa-2x fa-fw"></i>
               </a>
               &ensp;'
           }}
           <?php $i++; ?>
       @endif
       @if ($user->acc_oc == 1)
           {{
               '<a href="/oc" title="Módulo de Ordenes de Compra" style="text-decoration: none; color: inherit">
                   <i class="fa fa-list-alt fa-2x fa-fw"></i>
               </a>
               &ensp;'
           }}
           <?php $i++; ?>
       @endif
       @if ($user->acc_project == 1)
           {{
               '<a href="/assignment" title="Módulo de Proyectos" style="text-decoration: none; color: inherit">
                   <i class="fa fa-cogs fa-2x fa-fw"></i>
               </a>
               &ensp;'
           }}
           <?php $i++; ?>
       @endif
       @if ($i >= 2)
            {{ '<br><p></p>&ensp;' }}
       @endif
       @if ($user->acc_project == 1)
           {{
               '<a href="/stipend_request" title="Módulo de Viáticos" style="text-decoration: none; color: inherit">
                   <i class="fa fa-plane fa-2x fa-fw"></i>
               </a>
               &ensp;'
           }}
           <?php $i++; ?>
       @endif
       @if($user->acc_active == 1)
           {{
               '<a href="/device" title="Módulo de Equipos" style="text-decoration: none; color: inherit">
                    <i class="fa fa-laptop fa-2x fa-fw"></i>
               </a>
               &ensp;'
           }}
           <?php $i++; ?>
           {{
               '<a href="/vehicle" title="Módulo de Vehículos" style="text-decoration: none; color: inherit">
                    <i class="fa fa-car fa-2x fa-fw"></i>
               </a>
               &ensp;'
           }}
           <?php $i++; ?>
       @endif
               ">
        <i class="glyphicon glyphicon-th" style="font-size: 1.3em;"></i>
    </a>
@endif
