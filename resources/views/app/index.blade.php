@extends('layouts.master')

@section('header')
    @parent
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 mg-btm-px-40 col-lg-4 col-lg-offset-4 col-sm-6 col-sm-offset-3 col-xs-12">
    	<div class="panel panel-info">
            @if($user&&$user->priv_level==4)
                <div class="panel-heading">
                    <div class="panel-title" align="center">Bienvenido! Seleccione un servicio</div>
                </div>
                <div class="panel-body">

                    @include('app.session_flashed_messages', array('opt' => 1))

                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon"><span class="badge">1</span> Acceder al registro de </span>
                            <a href="{{ '/cite' }}" class="btn btn-success uniform_width">
                                <i class="fa fa-envelope-o"></i> CITES
                            </a>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon"><span class="badge">2</span> Acceder al registro de </span>
                            <a href="{{ '/oc' }}" class="btn btn-success uniform_width"><i class="fa fa-money"></i> O.C.</a>
                        </div>
                    </div>
                    <!--
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon"><span class="badge">3</span> Acceder al registro de </span>
                            <a href="/project" class="btn btn-success uniform_width"><i class="fa fa-cogs"></i> S.S.P. </a>
                        </div>
                    </div>
                    -->
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon"><span class="badge">3</span> Acceder al registro de </span>
                            <a href="{{ '/assignment' }}" class="btn btn-success uniform_width">
                                <i class="fa fa-cogs"></i> Proyectos
                            </a>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon"><span class="badge">4</span> Acceder al registro de </span>
                            <a href="{{ '/contact' }}" class="btn btn-success uniform_width">
                                <i class="fa fa-phone"></i> Contactos
                            </a>
                        </div>
                    </div>
                    {{--
                    <div class="form-group">
                        <div class="input-group">
                             <span class="input-group-addon"><span class="badge">5</span> Acceder al registro de </span>
                             <a href="/user" class="btn btn-success uniform_width"><i class="fa fa-users"></i> Usuarios </a>
                        </div>
                    </div>
                    --}}
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon"><span class="badge">6</span> Acceder al registro de </span>
                            <a href="{{ '/active' }}" class="btn btn-success uniform_width">
                                <i class="fa fa-laptop"></i> Activos
                            </a>
                        </div>
                    </div>
                    <!--
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon"><span class="badge">7</span> Acceder al registro de </span>
                            <a href="{{ '/warehouse' }}" class="btn btn-success uniform_width">
                                <i class="fa fa-barcode"></i> Almacenes
                            </a>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon"><span class="badge">8</span> Acceder al registro de </span>
                            <a href="{{ '/staff' }}" class="btn btn-success uniform_width">
                                <i class="fa fa-users"></i> Personal
                            </a>
                        </div>
                    </div>
                    -->
                    {{--
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon"><span class="badge">8</span> Acceder al registro de </span>
                            <a href="/service_parameter" class="btn btn-success uniform_width">
                                <i class="fa fa-key"></i> Parametros
                            </a>
                        </div>
                    </div>
                    --}}
                    @if($user->priv_level==4)
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon" style="width:185px; text-align:left">
                                    <span class="badge">7</span> Acceder al menú de
                                </span>
                                <a href="{{ route('admin_menu') }}" class="btn btn-primary uniform_width">
                                    <i class="fa fa-key"></i> Admin
                                </a>
                            </div>
                        </div>
                    @endif

                        {{-- Qr Code test
                        <div class="text-center">
                            {!! QrCode::size(100)->generate(Request::url()) !!}
                        </div>

                        <div class="text-center">
                            <img src="data:image/png;base64,
                                {!! base64_encode(QrCode::format('png')->size(100)->generate($user->id)) !!} ">
                        </div>
                        --}}

                    <br><br>

                    <div class="form-group" style="float: left">
                        <div class="input-group">
                            <a href="/user/{{ $user->id }}/edit" class="btn btn-warning">
                                <i class="fa fa-pencil-square-o"></i> Actualizar datos
                            </a>
                        </div>
                    </div>
                    <div class="form-group" style="float: right">
                        <div class="input-group">
                            <a href="/logout/{{ $service }}" class="btn btn-danger">
                                <i class="fa fa-sign-out"></i> Cerrar sesión
                            </a>
                        </div>
                    </div>
                </div>
            @elseif(empty($user))
            {{-- $service&&empty($user) --}}
                <div class="panel-heading">
                    <div class="panel-title" align="center">Iniciar sesión</div>
                </div>
                <div class="panel-body">
                    <form novalidate="novalidate" action="{{ 'login' }}" method="post">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="service" value="{{ $service }}">
                        {{--
                        @if (Session::has('message'))
                            <div class="alert alert-danger" align="center">{{ Session::get('message') }}</div>
                        @endif
                        --}}
                        @include('app.session_flashed_messages', array('opt' => 1))

                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-user fa-fw"></i></span>
                                <input required="required" type="text" class="form-control" name="login" placeholder="Usuario">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-lock fa-fw"></i></span>
                                <input required="required" type="password" class="form-control" name="password"
                                       placeholder="Password" >
                            </div>
                        </div>
                        <div class="col-sm-12 form-group">
                            <a href="{{ '/login/pw_recovery?return='.($service ? $service : 'project') }}" class="pull-right"> Forgot password?</a>
                        </div>
                        <div class="form-group" align="center">
                            <button type="submit" class="btn btn-success" onclick="this.disabled=true; this.form.submit()">
                                <i class="fa fa-key"></i> <!--Login-->Entrar
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="panel-heading">
                    <div class="panel-title" align="center">Seleccione un servicio</div>
                </div>
                <div class="panel-body" align="center">
                    <div class="form-group">
                        <div class="input-group">
                            <a href="{{ '/cite' }}" class="btn btn-success uniform_width">
                                <span class="badge pull-left">1</span> CITES
                            </a>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <a href="{{ '/oc' }}" class="btn btn-success uniform_width" >
                                <span class="badge pull-left">2</span> O.C.
                            </a>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <a href="{{ $user ? '/assignment' : '/project' }}" class="btn btn-success uniform_width">
                                <span class="badge pull-left">3</span> Proyectos
                            </a>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <a href="{{ '/active' }}" class="btn btn-success uniform_width">
                                <span class="badge pull-left">4</span> Activos
                            </a>
                        </div>
                    </div>
                    {{-- Not visible until functional
                    <div class="form-group">
                        <div class="input-group">
                            <a href="/warehouse" class="btn btn-success uniform_width">
                                <span class="badge pull-left">5</span> Almacenes
                            </a>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <a href="/staff" class="btn btn-success uniform_width">
                                <span class="badge pull-left">6</span> Personal
                            </a>
                        </div>
                    </div>
                    --}}
                </div>
            @endif
        </div>  
    </div>

@endsection

@section('footer')
@endsection
