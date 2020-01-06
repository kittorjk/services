@extends('layouts.master')

@section('header')
    @parent
@endsection

@section('content')

<div id="loginbox" class="mg-tp-px-50 mg-btm-px-40 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
    <div class="panel panel-10gray">
        <div class="panel-heading" align="center">
            <div class="panel-title">{{ $current_user ? 'Actualizar información de usuario' : 'Crear usuario' }}</div>
        </div>
        <div class="panel-body">
            <div class="mg20">
                <!-- <a href="/" class="btn btn-warning"><i class="fa fa-home"></i> Inicio</a> -->
                <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                    <i class="fa fa-undo"></i>
                </a>
                @if($session_user->priv_level==4)
                    <a href="{{ '/user' }}" class="btn btn-warning" title="Volver a lista de usuarios">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                @endif
            </div>

            @include('app.session_flashed_messages', array('opt' => 1))

            @if($current_user)
                <form id="delete" action="/user/{{ $current_user->id }}" method="post">
                    <input type="hidden" name="_method" value="delete">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                </form>
                <form novalidate="novalidate" action="{{ '/user/'.$current_user->id }}" method="post">
                    <input type="hidden" name="_method" value="put">
                    @else
                        <form novalidate="novalidate" action="{{ '/user' }}" method="post">
                            @endif
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">

                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                        <div class="input-group" style="width: 100%">
                                            <label for="name" class="input-group-addon" style="width: 23%;text-align: left"
                                                title="Nombre desplegable en pantalla (visible para otros usuarios)">
                                                Nombre sist.: <span class="pull-right">*</span>
                                            </label>

                                            <input required="required" type="text" class="form-control" name="name" id="name"
                                                   value="{{ $current_user ? $current_user->name : old('name') }}"
                                                   placeholder="Nombre visible en el sistema (1er nombre + 1er apellido)">
                                        </div>

                                        <div class="input-group" style="width: 100%">
                                            <label for="full_name" class="input-group-addon" style="width: 23%;text-align: left"
                                                   title="Nombre completo para registro de usuario">
                                                Nombre comp.: <span class="pull-right">*</span>
                                            </label>

                                            <input required="required" type="text" class="form-control" name="full_name"
                                                   id="full_name"
                                                   value="{{ $current_user ? $current_user->full_name : old('full_name') }}"
                                                   placeholder="Nombre completo">
                                        </div>

                                        @if($current_user||$session_user->priv_level==4)
                                            <div class="input-group" style="width: 100%">
                                                <label for="login" class="input-group-addon" style="width: 23%;text-align: left"
                                                       title="Nombre de usuario para inicio de sesión">
                                                    Login: <span class="pull-right">*</span>
                                                </label>

                                                <input required="required" type="text" class="form-control" name="login"
                                                       id="login"
                                                       value="{{ $current_user ? $current_user->login : old('login') }}"
                                                       placeholder="Login (usuario)">
                                            </div>
                                        @endif

                                        @if(!$current_user&&$session_user->priv_level==4)
                                            <div class="input-group" style="width: 100%">
                                                <label for="password" class="input-group-addon" style="width: 23%;text-align: left"
                                                    title="{{ 'Por favor introduzca una contraseña segura que contenga'.
                                                        ' por lo menos un número y un caracter especial' }}">
                                                    Contraseña:
                                                </label>

                                                <input required="required" type="password" class="form-control" name="password"
                                                       id="password"
                                                       placeholder="{{ $current_user ? 'Confirmar password' : 'Password' }}">
                                            </div>
                                        @endif

                                        <div class="input-group" style="width: 100%">
                                            <label for="phone" class="input-group-addon" style="width: 23%;text-align: left">
                                                Teléfono:
                                            </label>

                                            <input required="required" type="number" class="form-control" name="phone"
                                                   id="phone" step="1" min="1"
                                                   value="{{ $current_user&&$current_user->phone!=0 ? $current_user->phone :
                                                    old('phone') }}"
                                                   placeholder="Número de teléfono fijo o celular">
                                        </div>

                                        <div class="input-group" style="width: 100%">
                                            <label for="email" class="input-group-addon" style="width: 23%;text-align: left">
                                                Correo:
                                            </label>

                                            <input required="required" type="text" class="form-control" name="email" id="email"
                                                   value="{{ $current_user ? $current_user->email : old('email') }}"
                                                   placeholder="Correo electronico">
                                        </div>

                                        @if(!$current_user||$session_user->priv_level==4)
                                            <div class="input-group" style="width: 100%">
                                                <label for="branch_id" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Oficina: <span class="pull-right">*</span>
                                                </label>

                                                <select required="required" class="form-control" name="branch_id" id="branch_id">
                                                    <option value="" hidden>Seleccione una oficina</option>
                                                    @foreach($branches as $branch)
                                                        <option value="{{ $branch->id }}"
                                                                {{ ($current_user&&$current_user->branch_id==$branch->id)||
                                                                old('branch_id')==$branch->id ?
                                                                'selected="selected"' : '' }}>{{ $branch->city }}</option>
                                                    @endforeach
                                                    {{--
                                                    <option value="La Paz"
                                                            {{ ($current_user&&$current_user->branch=='La Paz')||
                                                                old('branch')=='La Paz' ?
                                                                'selected="selected"' : '' }}>La Paz</option>
                                                    <option value="Santa Cruz"
                                                            {{ ($current_user&&$current_user->branch=='Santa Cruz')||
                                                                old('branch')=='Santa Cruz' ?
                                                                'selected="selected"' : '' }}>Santa Cruz</option>
                                                    <option value="Cochabamba"
                                                            {{ ($current_user&&$current_user->branch=='Cochabamba')||
                                                                old('branch')=='Cochabamba' ?
                                                                'selected="selected"' : '' }}>Cochabamba</option>
                                                    --}}
                                                </select>
                                            </div>

                                            <div class="input-group" style="width: 100%">
                                                <label for="area" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Área de trabajo: <span class="pull-right">*</span>
                                                </label>

                                                <select required="required" class="form-control" name="area" id="area">
                                                    <option value="" hidden>Area</option>
                                                    <option value="Gerencia Administrativa"
                                                            {{ ($current_user&&$current_user->area=='Gerencia Administrativa')||
                                                                old('area')=='Gerencia Administrativa' ?
                                                                'selected="selected"' : '' }}>Gerencia Administrativa</option>
                                                    <option value="Gerencia General"
                                                            {{ ($current_user&&$current_user->area=='Gerencia General')||
                                                                old('area')=='Gerencia General' ?
                                                                'selected="selected"' : '' }}>Gerencia General</option>
                                                    <option value="Gerencia Tecnica"
                                                            {{ ($current_user&&$current_user->area=='Gerencia Tecnica')||
                                                                old('area')=='Gerencia Tecnica' ?
                                                                'selected="selected"' : '' }}>Gerencia Tecnica</option>
                                                    <option value="Cliente"
                                                            {{ ($current_user&&$current_user->area=='Cliente')||
                                                                old('area')=='Cliente' ?
                                                                'selected="selected"' : '' }}>Cliente</option>
                                                    <option value="Subcontratista"
                                                            {{ ($current_user&&$current_user->area=='Subcontratista')||
                                                                old('area')=='Subcontratista' ?
                                                                 'selected="selected"' : '' }}>Subcontratista</option>
                                                </select>
                                            </div>

                                            <div class="input-group" style="width: 100%">
                                                <label for="work_type" class="input-group-addon" style="width: 23%;text-align: left">
                                                    División:
                                                </label>

                                                <select required="required" class="form-control" name="work_type" id="work_type">
                                                    <option value="" hidden>Área de trabajo</option>
                                                    <option value="Administrativo"
                                                            {{ ($current_user&&$current_user->work_type=='Administrativo')||
                                                                old('work_type')=='Administrativo' ?
                                                                'selected="selected"' : '' }}>Administrativo</option>
                                                    <option value="Almacén"
                                                            {{ ($current_user&&$current_user->work_type=='Almacén')||
                                                                old('work_type')=='Almacén' ?
                                                                'selected="selected"' : '' }}>Almacén</option>
                                                    <option value="Director Regional"
                                                            {{ ($current_user&&$current_user->work_type=='Director Regional')||
                                                                old('work_type')=='Director Regional' ?
                                                                'selected="selected"' : '' }}>Director Regional</option>
                                                    <option value="Fibra óptica"
                                                            {{ ($current_user&&$current_user->work_type=='Fibra óptica')||
                                                                old('work_type')=='Fibra óptica' ?
                                                                'selected="selected"' : '' }}>Fibra óptica</option>
                                                    <option value="Radiobases"
                                                            {{ ($current_user&&$current_user->work_type=='Radiobases')||
                                                                old('work_type')=='Radiobases' ?
                                                                'selected="selected"' : '' }}>Radiobases</option>
                                                    <option value="Instalación de energía"
                                                            {{ ($current_user&&$current_user->work_type=='Instalación de energía')||
                                                                old('work_type')=='Instalación de energía' ?
                                                                'selected="selected"' : '' }}>Instalación de energía</option>
                                                    <option value="Obras Civiles"
                                                            {{ ($current_user&&$current_user->work_type=='Obras Civiles')||
                                                                old('work_type')=='Obras Civiles' ?
                                                                'selected="selected"' : '' }}>Obras Civiles</option>
                                                    <option value="Venta de material"
                                                            {{ ($current_user&&$current_user->work_type=='Venta de material')||
                                                                old('work_type')=='Venta de material' ?
                                                                'selected="selected"' : '' }}>Venta de material</option>
                                                    <option value="Transporte"
                                                            {{ ($current_user&&$current_user->work_type=='Transporte')||
                                                                old('work_type')=='Transporte' ?
                                                                'selected="selected"' : '' }}>Transporte</option>
                                                </select>
                                            </div>

                                            <div class="input-group" style="width: 100%">
                                                <label for="role" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Cargo:
                                                </label>

                                                <select required="required" class="form-control" name="role" id="role">
                                                    <option value="" hidden>Seleccionar un cargo</option>
                                                    <option value="Asistente administrativo de cobranzas"
                                                            {{ ($current_user&&$current_user->role==
                                                                'Asistente administrativo de cobranzas')||
                                                                 old('role')=='Asistente administrativo de cobranzas' ?
                                                                 'selected="selected"' : '' }}>
                                                                 Asistente administrativo de cobranzas</option>
                                                    <option value="Secretaria"
                                                            {{ ($current_user&&$current_user->role=='Secretaria')||
                                                                old('role')=='Secretaria' ?
                                                                'selected="selected"' : '' }}>Secretaria</option>
                                                    <option value="Project Manager"
                                                            {{ ($current_user&&$current_user->role=='project Manager')||
                                                                old('role')=='Project Manager' ?
                                                                'selected="selected"' : '' }}>Project Manager</option>
                                                    <option value="Técnico"
                                                            {{ ($current_user&&$current_user->role=='Técnico')||
                                                                old('role')=='Técnico' ?
                                                                'selected="selected"' : '' }}>Técnico</option>
                                                    <option value="Director regional"
                                                            {{ ($current_user&&$current_user->role=='Director regional')||
                                                                old('role')=='Director regional' ?
                                                                'selected="selected"' : '' }}>Director regional</option>
                                                    <option value="Gerente General"
                                                            {{ ($current_user&&$current_user->role=='Gerente General')||
                                                                old('role')=='Gerente General' ?
                                                                'selected="selected"' : '' }}>Gerente General</option>
                                                    <option value="Administrador"
                                                            {{ ($current_user&&$current_user->role=='Administrador')||
                                                                old('role')=='Administrador' ?
                                                                'selected="selected"' : '' }}>Administrador</option>
                                                    <option value="Gerente Técnico"
                                                            {{ ($current_user&&$current_user->role=='Gerente Técnico')||
                                                                old('role')=='Gerente Técnico' ?
                                                                'selected="selected"' : '' }}>Gerente Técnico</option>
                                                </select>
                                            </div>

                                            <div class="input-group" style="width: 75%">
                                                <label for="rank" class="input-group-addon" style="width: 31%;text-align: left">
                                                    Antigüedad:
                                                </label>

                                                <select required="required" class="form-control" name="rank" id="rank">
                                                    <option value="" hidden>Rango (según experiencia y antigüedad)</option>
                                                    @for($rank=0;$rank<9;$rank++)
                                                        <option value="{{ $rank }}" {{ ($current_user&&$current_user->rank==$rank)||
                                                            old('rank')==$rank ?
                                                            'selected="selected"' : '' }}>{{ $rank }}</option>
                                                    @endfor
                                                </select>
                                            </div>

                                            <div class="input-group" style="width: 75%">
                                                <span class="input-group-addon" style="width: 31%;text-align: left">Salario:</span>

                                                <input required="required" type="number" class="form-control" name="cost"
                                                       id="cost" step="any" min="0"
                                                       value="{{ $current_user&&$current_user->cost!=0 ?
                                                            $current_user->cost : old('cost') }}"
                                                       placeholder="Monto pagado mensual">

                                                <span class="input-group-addon">Bs.</span>
                                            </div>

                                            @if($session_user->priv_level==4)

                                                <div class="input-group" style="width: 75%">
                                                    <label for="priv_level" class="input-group-addon"
                                                           style="width: 31%;text-align: left">
                                                        Nivel de acceso:
                                                    </label>

                                                    <select required="required" class="form-control" name="priv_level"
                                                            id="priv_level">
                                                        <option value="">Seleccione el nivel de acceso (privilegios)</option>
                                                        @for($i=0;$i<5;$i++)
                                                            <option value="{{ $i }}"
                                                                    {{ ($current_user&&$current_user->priv_level==$i)||
                                                                        old('priv_level')==$i ?
                                                                        'selected="selected"' : '' }}>{{ $i }}</option>
                                                        @endfor
                                                    </select>
                                                </div>

                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if($session_user->priv_level==4)
                                <div class="col-sm-offset-1">
                                    <label>Tiene acceso a: </label><br>

                                    <input type="checkbox" name="acc_cite" id="acc_cite" value="1"
                                            {{ $current_user&&$current_user->acc_cite==1 ? 'checked' : '' }}>
                                    <label for="acc_cite" style="font-weight: normal; margin-bottom: 0">Sistema de CITEs</label>
                                    <br>
                                    <input type="checkbox" name="acc_oc" id="acc_oc" value="1"
                                            {{ $current_user&&$current_user->acc_oc==1 ? 'checked' : '' }}>
                                    <label for="acc_oc" style="font-weight: normal; margin-bottom: 0">Sistema de OCs</label>
                                    <br>
                                    <input type="checkbox" name="acc_project" id="acc_project" value="1"
                                            {{ $current_user&&$current_user->acc_project==1 ? 'checked' : '' }}>
                                    <label for="acc_project" style="font-weight: normal; margin-bottom: 0">
                                        Sistema de Proyectos
                                    </label>
                                    <br>
                                    <input type="checkbox" name="acc_active" id="acc_active" value="1"
                                            {{ $current_user&&$current_user->acc_active==1 ? 'checked' : '' }}>
                                    <label for="acc_active" style="font-weight: normal; margin-bottom: 0">
                                        Sistema de seguimiento de activos</label>
                                    <br>
                                    <input type="checkbox" name="acc_warehouse" id="acc_warehouse" value="1"
                                            {{ $current_user&&$current_user->acc_warehouse==1 ? 'checked' : '' }}>
                                    <label for="acc_warehouse" style="font-weight: normal; margin-bottom: 0">
                                        Sistema de seguimiento de materiales (Almacén)
                                    </label>
                                    <br>
                                    <input type="checkbox" name="acc_staff" id="acc_staff" value="1"
                                            {{ $current_user&&$current_user->acc_staff==1 ? 'checked' : '' }}>
                                    <label for="acc_staff" style="font-weight: normal; margin-bottom: 0">
                                        Registro de personal
                                    </label>
                                </div>
                            @endif

                            @if($current_user)
                                <div class="checkbox col-sm-offset-1">
                                    <label>
                                        <input type="checkbox" name="chg_pass" id="chg_pass" value="1"
                                               checked=""> Cambiar contraseña
                                    </label>
                                </div>

                                <div class="input-group" id="pass_container">
                                    <span class="input-group-addon">
                                    <label>Ingrese su nueva contraseña</label>

                                    <input required="required" type="password" class="form-control" name="new_pass"
                                           id="new_pass" placeholder="Nueva contraseña" disabled="disabled">

                                    <input required="required" type="password" class="form-control" name="confirm_pass"
                                           id="confirm_pass" placeholder="Confirmar nueva contraseña" disabled="disabled">
                                    </span>
                                </div>
                            @endif

                            <br>
                            @include('app.loader_gif')

                            <div class="form-group" align="center">
                                <button type="submit" class="btn btn-success"
                                        onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                                    <i class="fa fa-floppy-o"></i> Guardar
                                </button>

                                @if($current_user&&$session_user->priv_level==4)
                                    <button type="submit" form="delete" class="btn btn-danger"
                                            title="Se inhabilitará el acceso y uso del registro de este usuario">
                                        <i class="fa fa-user-times"></i> Retirar
                                    </button>
                                @endif
                            </div>
                        </form>
        </div>
    </div>
</div>

@endsection

@section('footer')
@endsection

@section('javascript')
    <script src="{{ asset('app/js/prevent_enter_form_submit.js') }}"></script> {{-- Avoid submitting form on enter press --}}
    <script>
        $(document).ready(function(){
            $("#wait").hide();
        });

        var $chg_pass = $('#chg_pass'), $new_pass = $('#new_pass'), $confirm_pass = $('#confirm_pass'),
                $pass_container = $('#pass_container');
        $chg_pass.click(function () {
            if ($chg_pass.prop('checked')) {
                $pass_container.show();
                $new_pass.removeAttr('disabled').show();
                $confirm_pass.removeAttr('disabled').show();
            } else {
                $pass_container.hide();
                $new_pass.attr('disabled', 'disabled').hide();
                $confirm_pass.attr('disabled', 'disabled').hide();
            }
        }).trigger('click');
    </script>
@endsection
