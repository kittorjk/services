<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 29/11/2017
 * Time: 06:16 PM
 */
?>

@extends('layouts.adm_structure')

@section('header')
    @parent
    <link rel="stylesheet" href="{{ asset("app/css/image_modal.css") }}">
    <style>
      .sub-menu > li > a {
          width: 180px;
      }
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endsection

@section('menu_options')
    <li><a href="#">&ensp;<i class="fa fa-users"></i> EMPLEADOS <span class="caret"></span>&ensp;</a>
        <ul class="sub-menu">
            <li><a href="{{ '/employee' }}"><i class="fa fa-bars fa-fw"></i> Ver todo </a></li>
            <li><a href="{{ '/employee?stat=active' }}"><i class="fa fa-bars fa-fw"></i> Ver empleados activos </a></li>
            <li><a href="{{ '/employee?stat=retired' }}"><i class="fa fa-bars fa-fw"></i> Ver empleados retirados </a></li>
            <li><a href="{{ '/employee/create' }}"><i class="fa fa-user-plus fa-fw"></i> Agregar empleado </a></li>
            {{--@if($user->priv_level==4)--}}
                <li><a href="{{ '/excel/employees' }}"><i class="fa fa-file-excel-o"></i> Exportar a Excel </a></li>
            {{--@endif--}}
        </ul>
    </li>
    <li>
        <a data-toggle="modal" href="#searchBox">&ensp;<i class="fa fa-search"></i> BUSCAR&ensp;</a>
    </li>
@endsection

@section('content')
    <div class="col-sm-12 mg10 mg-tp-px-10">
        @include('app.session_flashed_messages', array('opt' => 0))
    </div>

    <div class="col-sm-12 mg10 mg-tp-px-10">
        <p>Registros encontrados: {{ $employees->total() }}</p>

        <table class="fancy_table table_gray" id="fixable_table">
            <thead>
            <tr>
                <th>Foto</th>
                <th>Código</th>
                <th>Apellidos</th>
                <th>Nombres</th>
                <th>C.I.</th>
                <th>Cargo</th>
                <th>Categoría</th>
                <th>Área</th>
                <th>Email corporativo</th>
                <th>Teléfono</th>
            </tr>
            </thead>
            <tbody>
            <?php
                $areas = array();
                $areas['Gerencia Tecnica'] = 'Tecnica';
                $areas['Gerencia General'] = 'G. General';
                $areas['Gerencia Administrativa'] = 'Administrativa';
                $areas['Cliente'] = 'Cliente';
                $areas['Subcontratista'] = 'Subcontratista';
            ?>

            @foreach ($employees as $employee)
                <tr @if($employee->active==0)style="background-color: #ba5e5e" title="Empleado retirado"@endif>
                    <td align="center">
                        @foreach($employee->files as $key => $file)
                            <img class="myImg" src="/files/thumbnails/{{ 'thumb_'.$file->name }}" height="50"
                                 border="0" alt="{{ $file->description }}" onclick="show_modal(this)">
                        @endforeach
                        
                        @if($employee->files->count() === 0 && (($user->action->adm_emp_edt && $employee->active === 1) || $user->priv_level === 4))
                            <a href="/files/employee_img/{{ $employee->id }}"><i class="fa fa-upload"></i> Subir foto</a>
                        @endif
                    </td>
                    <td>
                        <a href="/employee/{{ $employee->id }}" title="Ver información de empleado"
                            @if($employee->active==0)style="color: inherit"@endif>
                            {{ $employee->code }}
                        </a>
                        @if (($user->action->adm_emp_edt && $employee->active === 1) || $user->priv_level === 4)
                            <a href="/employee/{{ $employee->id }}/edit" title="Modificar registro de empleado"
                                @if($employee->active==0)style="color: inherit"@endif>
                                <i class="fa fa-pencil-square"></i>
                            </a>
                        @endif
                    </td>
                    <td>{{ $employee->last_name }}</td>
                    <td>{{ $employee->first_name }}</td>
                    <td>{{ $employee->id_card.' '.$employee->id_extension }}</td>
                    <td>{{ $employee->role }}</td>
                    <td>{{ $employee->category }}</td>
                    <td>{{ $employee->area !== '' ? $areas[$employee->area] : 'N/E' }}</td>
                    <td>{{ $employee->corp_email }}</td>
                    <td>{{ $employee->phone!=0 ? $employee->phone : '' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="col-sm-12 mg10" align="center">
        {!! $employees->appends(request()->except('page'))->render() !!}
    </div>

    <div class="col-sm-12 mg10" id="fixed">
        <table class="fancy_table table_gray" id="cloned"></table>
    </div>

    <!-- Search Modal -->
    <div id="searchBox" class="modal fade" role="dialog">
        @include('app.search_box', array('user'=>$user,'service'=>$service,'table'=>'employees','id'=>0))
    </div>
    
    <!-- Image preview Modal -->
    <div id="picModal" class="pic_modal">
        <span class="pic_close" id="pic_close">&times;</span>
        <img class="pic_modal-content" id="pic_modal_content" src="">
        <div id="pic_caption"></div>
    </div>
@endsection

@section('footer')
    @parent
@endsection

@section('javascript')
    <script src="{{ asset('app/js/fix_table_header.js') }}"></script> {{-- For fixed header --}}
    <script src="{{ asset('app/js/set_current_url.js') }}"></script> {{-- For recording current url --}}
    <script>
        $('#alert').delay(2000).fadeOut('slow');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        var modal = document.getElementById('picModal');
        // Get the image and insert it inside the modal - use its "alt" text as a caption
        var modalImg = document.getElementById("pic_modal_content");
        var captionText = document.getElementById("pic_caption");
        function show_modal(element) {
            var fullSizedSource = element.src.replace('thumbnails/thumb_', '');

            modal.style.display = "block";
            modalImg.src = fullSizedSource;
            captionText.innerHTML = element.alt;
        }
        // Get the <span> element that closes the modal
        var span = document.getElementById("pic_close");
        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = "none";
        }
    </script>
@endsection
