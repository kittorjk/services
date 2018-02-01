 <a href="/user/{{ $user->id }}/edit" class="btn btn-warning"><i class="fa fa-pencil-square-o"></i> Actualizar datos </a>

 <div class="btn-group">
     <button type="button" data-toggle="dropdown" class="btn btn-danger dropdown-toggle">
         <i class="fa fa-cog"></i> Mi cuenta <span class="caret"></span>
     </button>
     <ul class="dropdown-menu dropdown-menu-right dropdown-menu-dang">
         <li><a href="/user/{{ $user->id }}"><i class="fa fa-user fa-fw"></i> {{ $user->name }}</a></li>
         <li><a href="/user/{{ $user->id }}/edit"><i class="fa fa-pencil-square-o fa-fw"></i> Actualizar datos </a></li>
         @if($user->action->adm_add_usr /*$user->priv_level>=3*/)
             <li><a href="{{ '/user/create' }}"><i class="fa fa-plus fa-fw"></i> Agregar usuario </a></li>
         @endif
         <li class="divider"></li>
         <li><a href="/logout/{{ $service }}"><i class="fa fa-sign-out fa-fw"></i> Cerrar sesión</a></li>
     </ul>
 </div>
