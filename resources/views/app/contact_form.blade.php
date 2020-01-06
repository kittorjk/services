@extends('layouts.master')

@section('header')
    @parent
@endsection

@section('content')

    <div id="loginbox" class="mg-tp-px-50 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 mg-btm-px-40">
        <div class="panel panel-sky">
            <div class="panel-heading" align="center">
                <div class="panel-title">
                    {{ $contact ? 'Actualizar información de contacto' : 'Agregar nuevo contacto' }}
                </div>
            </div>
            <div class="panel-body">
                <div class="mg20">
                    <a href="#" onclick="history.back();" class="btn btn-warning" title="Atrás">
                        <i class="fa fa-undo"></i>
                    </a>
                    <a href="{{ '/contact' }}" class="btn btn-warning" title="Volver a lista de contactos">
                        <i class="fa fa-arrow-up"></i>
                    </a>
                </div>

                @include('app.session_flashed_messages', array('opt' => 1))

                <p><em>Nota.- Los campos con * son obligatorios</em></p>

                @if($contact)
                    <form id="delete" action="/contact/{{ $contact->id }}" method="post">
                        <input type="hidden" name="_method" value="delete">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                    <form novalidate="novalidate" action="{{ '/contact/'.$contact->id }}" method="post">
                        <input type="hidden" name="_method" value="put">
                        @else
                            <form novalidate="novalidate" action="{{ '/contact' }}" method="post">
                                @endif
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="form-group">
                                    <div class="input-group">

                                        <div class="input-group-addon"><i class="fa fa-pencil-square-o"></i>

                                            <div class="input-group" style="width: 100%">
                                                <label for="name" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Nombre: <span class="pull-right">*</span>
                                                </label>

                                                <input required="required" type="text" class="form-control" name="name" id="name"
                                                       value="{{ $contact ? $contact->name : old('name') }}"
                                                       placeholder="Nombre de contacto">
                                            </div>

                                            <div class="input-group" style="width: 100%">
                                                <label for="position" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Cargo: <span class="pull-right">*</span>
                                                </label>

                                                <input required="required" type="text" class="form-control" name="position"
                                                       id="position" value="{{ $contact ? $contact->position : old('position') }}"
                                                       placeholder="Cargo">
                                            </div>

                                            <div class="input-group" style="width: 100%">
                                                <label for="company" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Empresa: <span class="pull-right">*</span>
                                                </label>

                                                <select required="required" class="form-control" name="company" id="company">
                                                    <option value="" hidden>Seleccione una empresa</option>
                                                    @foreach($company_options as $company_option)
                                                        <option value="{{ $company_option->company }}"
                                                                {{ ($contact&&$contact->company==$company_option->company)||
                                                                    old('company')==$company_option->company ?
                                                                    'selected="selected"' :
                                                                     '' }}>{{ $company_option->company }}</option>
                                                    @endforeach
                                                    <option value="Otro" {{ old('company')=='Otro' ?
                                                        'selected="selected"' : '' }}>Otro</option>
                                                </select>
                                            </div>
                                            <input required="required" type="text" class="form-control" name="other_company"
                                                   id="other_company" value="{{ old('other_company') }}"
                                                   placeholder="Empresa *" disabled="disabled">

                                            <div class="input-group" style="width: 100%">
                                                <label for="phone_1" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Telf principal:
                                                </label>

                                                <input required="required" type="number" class="form-control" name="phone_1"
                                                       id="phone_1" step="1" min="1"
                                                       value="{{ $contact&&$contact->phone_1!=0 ? $contact->phone_1 :
                                                            old('phone_1') }}"
                                                       placeholder="Número de teléfono principal">
                                            </div>

                                            <div class="input-group" style="width: 100%">
                                                <label for="phone_2" class="input-group-addon" style="width: 23%;text-align: left">
                                                    Telf alternativo:
                                                </label>

                                                <input required="required" type="number" class="form-control" name="phone_2"
                                                       id="phone_2" step="1" min="1"
                                                       value="{{ $contact&&$contact->phone_2!=0 ? $contact->phone_2 :
                                                            old('phone_2') }}"
                                                       placeholder="Número de teléfono alternativo">
                                            </div>

                                            <div class="input-group" style="width: 100%">
                                                <label for="email" class="input-group-addon" style="width: 23%;text-align: left">
                                                    E-mail:
                                                </label>

                                                <input required="required" type="text" class="form-control" name="email"
                                                       id="email" value="{{ $contact ? $contact->email : old('email') }}"
                                                       placeholder="Correo electronico">
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                @include('app.loader_gif')

                                <div class="form-group" align="center">
                                    <button type="submit" class="btn btn-success"
                                            onclick="this.disabled=true; $('#wait').show(); this.form.submit()">
                                        <i class="fa fa-floppy-o"></i> Guardar
                                    </button>

                                    @if($contact&&$user->priv_level==4)
                                        <button type="submit" form="delete" class="btn btn-danger">
                                            <i class="fa fa-trash-o"></i> Quitar
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
        var $company = $('#company'), $other_company = $('#other_company');
        $company.change(function () {
            if ($company.val()==='Otro') {
                $other_company.removeAttr('disabled').show();
            } else {
                $other_company.attr('disabled', 'disabled').val('').hide();
            }
        }).trigger('change');

        $(document).ready(function(){
            $("#wait").hide();
        });
    </script>
@endsection
