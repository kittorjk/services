@extends('layouts.master')

@section('header')
    @parent
@endsection

@section('content')

<div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
    <div class="panel panel-info" >
        <div class="panel-heading">
            <div class="panel-title">Registro agregado correctamente</div>
        </div>
        <div class="panel-body" >
            <div class="col-sm-12 mg20" align="center">
                El código de Cite es:<br>
                <h2>
                    {{-- $cite->title.'-'.str_pad($cite->num_cite, 3, "0", STR_PAD_LEFT).
                        date_format($cite->created_at,'-Y') --}}
                    {{ $cite->code }}
                </h2>
                <p><a href="{{ '/cite' }}" class="btn btn-success"><i class="fa fa-check"></i> Continuar</a></p>
            </div>
        </div>
    </div>
</div>

@endsection

@section('footer')
@endsection
