@extends('layouts.master')

@section('header')
    @parent
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 mg-btm-px-40 col-md-8 col-md-offset-2">
        <div class="panel panel-info">
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    {{ 'Registros de proveedor incompletos' }}
                </div>
            </div>
            <div class="panel-body">
                <div class="col-sm-12 mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning">
                        <i class="fa fa-arrow-circle-left"></i> Volver
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <div class="col-sm-12">
                    <p>
                        {{ $providers->count()==1 ? '1 registro incompleto' :
                        $providers->count().' registros incompletos' }}
                    </p>
                    <table class="table table-striped table-hover table-bordered">
                        <thead>
                        <tr>
                            <th width="10%" style="text-align: center">#</th>
                            <th width="50%">Proveedor</th>
                            <th>Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $i=1; ?>
                        @foreach($providers as $provider)
                            <tr>
                                <td align="center">{{ $i }}</td>
                                <td>{{ $provider->prov_name }}</td>
                                <td>
                                    <a href="/provider/{{ $provider->id }}/edit" title="Abrir formulario de proveedor">
                                        <i class="fa fa-pencil"></i> Completar registro
                                    </a>
                                </td>
                            </tr>
                            <?php $i++ ?>
                        @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

@endsection

@section('footer')
@endsection
