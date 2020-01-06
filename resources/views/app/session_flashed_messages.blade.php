<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 20/07/2017
 * Time: 10:13 AM
 */
?>

@if (session('message'))
{{--@if (Session::has('message'))--}}

    <div class="alert alert-{{ $opt==0 ? 'info' : 'danger' }}" align="center" id="alert">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        <i class="fa fa-{{ $opt==0 ? 'info-circle' : 'warning' }} fa-2x pull-left"></i>
        {{ session('message') }}
        {{-- Session::get('message') --}}
    </div>

@endif
