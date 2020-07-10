<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 09/11/2017
 * Time: 04:27 PM
 */
?>

@extends('layouts.master')

@section('header')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1 mg-btm-px-40">
        <div class="panel panel-info">
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    {{ 'Lista de ordenes de compra rechazadas' }}
                </div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                    <a href="{{ '/oc' }}" class="btn btn-warning" title="Volver a lista de OCs">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <div class="col-sm-12">
                    <table class="table table-striped table-hover table-bordered">
                        <tbody>
                        <tr>
                            <th>Nº OC</th>
                            <th>Fecha</th>
                            <th width="55%">Observaciones</th>
                            <th>Acciones</th>
                        </tr>
                        @foreach($ocs as $oc)
                            <tr>
                                <td>
                                    <a href="/oc/{{ $oc->id }}">{{ $oc->code }}</a>
                                </td>
                                <td>{{ date_format($oc->created_at,'d-m-Y') }}</td>
                                <td>{{ $oc->observations }}</td>
                                <td align="center">
                                    @if($user->action->oc_edt)
                                        <a href="{{ '/oc/'.$oc->id.'/edit?action=reject_disable' }}" title="Modificar/Corregir OC"
                                           style="text-decoration: none">
                                            <i class="fa fa-pencil-square-o"></i>
                                        </a>
                                    @endif
                                    @if($user->action->oc_nll)
                                        &emsp;
                                        <a href="{{ '/oc/cancel/'.$oc->id }}" title="Anular OC" style="text-decoration: none">
                                            <i class="fa fa-ban" style="color: darkred"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
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

@section('javascript')
    <script src="{{ asset('app/js/set_current_url.js') }}"></script> {{-- For recording current url --}}
    <script>
        $('#alert').delay(2000).fadeOut('slow');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
@endsection
