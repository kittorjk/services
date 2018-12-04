@extends('layouts.info_master')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">

        <div class="panel panel-10gray">
            <div class="panel-heading" align="center">
                <div class="panel-title">Información de archivo</div>
            </div>
            <div class="panel-body">
                <div class=" col-sm-12 mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning">
                        <i class="fa fa-arrow-circle-left"></i> Volver
                    </a>
                    @if($user->action->adm_acc_file /*$user->priv_level==4*/)
                        <a href="{{ '/file' }}" class="btn btn-warning" title="Ir a la tabla de archivos">
                            <i class="fa fa-arrow-circle-up"></i> Archivos
                        </a>
                    @endif
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <div class="col-sm-12 mg10 mg-tp-px-10">
                    <table class="table table-striped table-hover table-bordered">
                        <thead>
                        <tr>
                            <th width="40%">Nombre de archivo:</th>
                            <td>{{ $file->name }}</td>
                        </tr>
                        </thead>
                        <tbody>
                        @if($file->description)
                        <tr>
                            <th>Título / Descripción</th>
                            <td>{{ $file->description }}</td>
                        </tr>
                        @endif
                        <tr>
                            <th>Tipo (extensión):</th>
                            <td>
                                @if($file->type=='pdf')
                                    <img src="{{ '/imagenes/pdf-icon.png' }}" alt="PDF" /> PDF
                                @elseif($file->type=='doc'||$file->type=='docx')
                                    <img src="{{ '/imagenes/word-icon.png' }}" alt="WORD" /> MS WORD
                                @elseif($file->type=='xls'||$file->type=='xlsx')
                                    <img src="{{ '/imagenes/excel-icon.png' }}" alt="EXCEL" /> MS EXCEL
                                @elseif($file->type=='jpg'||$file->type=='jpeg'||$file->type=='png')
                                    <img src="{{ '/imagenes/image-icon.png' }}" alt="IMAGE" /> {{ strtoupper($file->type) }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Tamaño:</th>
                            <td>{{ number_format($file->size,2).' Kb' }}</td>
                        </tr>
                        <tr><td colspan="2"> </td></tr>
                        <tr>
                            <th>Subido por</th>
                            <td>{{ $file->user->name }}</td>
                        </tr>
                        <tr>
                            <th>Subido el</th>
                            <td>{{ date_format($file->created_at,'d-m-Y') }}</td>
                        </tr>
                        @if($file->created_at!=$file->updated_at)
                            <tr>
                                <th>Última modificación</th>
                                <td>{{ date_format($file->updated_at,'d-m-Y') }}</td>
                            </tr>
                        @endif
                        <tr><td colspan="2"> </td></tr>
                        <tr>
                            <th>Acciones</th>
                            <td>
                                <a href="/download/{{ $file->id }}"><i class="fa fa-download"></i> Descargar</a>
                                @if($file->type=='pdf')
                                    &emsp;
                                    <a href="/display_file/{{ $file->id }}"><i class="fa fa-file-pdf-o"></i> Ver</a>
                                @endif
                                @if($file->status==0||$user->priv_level==4)
                                    &emsp;
                                    <a href="/files/replace/{{ $file->id }}"><i class="fa fa-refresh"></i> Reemplazar</a>
                                @endif
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                @if($user->action->adm_file_del /*$user->priv_level==4*/)
                    <div class="col-sm-12 mg10" align="center">
                        <a href="{{ '/delete/file' }}" class="btn btn-danger"><i class="fa fa-trash-o"></i> Borrar archivo</a>
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection

@section('footer')
@endsection

@section('javascript')
    {{-- <script src="{{ asset('app/js/set_current_url.js') }}"></script> For recording current url --}}
    <script>
        $('#alert').delay(2000).fadeOut('slow');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
@endsection
