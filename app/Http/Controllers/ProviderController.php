<?php

namespace App\Http\Controllers;

use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use View;
use App\Cite;
use App\User;
use App\OC;
use App\Provider;
use App\Http\Traits\ProviderTrait;

class ProviderController extends Controller
{
    use ProviderTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id)) {
            return View('app.index', ['service' => 'oc', 'user' => null]);
        }
        if($user->acc_oc==0)
            return redirect()->action('LoginController@logout', ['service' => 'oc']);

        //$session_user = $user;
        $providers = Provider::where('id', '>', 0)->orderBy('prov_name')->paginate(20);

        $service = Session::get('service');

        return View::make('app.provider_brief', ['providers' => $providers, 'service' => $service, 
            'user' => $user]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $session_user = $user;

        $bank_options = Provider::select('bnk_name')->where('bnk_name', '<>', '')->groupBy('bnk_name')->get();
        $specialtyOptions = Provider::select('specialty')->where('specialty', '<>', '')->groupBy('specialty')->get();

        return View::make('app.provider_form', ['provider' => 0, 'bank_options' => $bank_options,
            'specialtyOptions' => $specialtyOptions, 'service' => $service, 'session_user' => $session_user]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $provider = new Provider(Request::all());

        $v = \Validator::make(Request::all(), [
            'prov_name'         => 'required',
            'nit'               => 'required|unique:providers|numeric|digits_between:8,10',
            'specialty'         => 'required',
            'bnk_account'       => 'required',
            'address'           => 'required',
            'bnk_name'          => 'required',
            'other_bnk_name'    => 'required_if:bnk_name,Otro',
            'phone_number'      => 'required|numeric|digits_between:7,8',
            'alt_phone_number'  => 'numeric|digits_between:7,8',
            'contact_name'      => 'required',
            'contact_id'        => 'required|numeric|digits_between:6,8',
            'contact_id_place'  => 'required',
            'contact_phone'     => 'required|numeric|digits_between:7,8',
            'email'             => 'email',
        ],
            [
                'prov_name.required'              => 'Debe especificar un nombre o razón social!',
                'nit.required'                    => 'Debe especificar un número de NIT!',
                'nit.unique'                      => 'El NIT de proveedor ya está registrado!',
                'nit.numeric'                     => 'El campo NIT sólo puede contener números!',
                'nit.digits_between'              => 'Número de NIT no válido!',
                'specialty.required'              => 'Debe especificar el área de especialidad del proveedor!',
                'bnk_account.required'            => 'Debe especificar un número de cuenta!',
                'address.required'                => 'Debe especificar la dirección del proveedor!',
                'bnk_name.required'               => 'Debe especificar un Banco!',
                'other_bnk_name.required_if'      => 'Debe especificar un banco!',
                'phone_number.required'           => 'Debe especificar el número de teléfono del proveedor!',
                'phone_number.numeric'            => 'El campo teléfono principal sólo puede contener números!',
                'phone_number.digits_between'     => 'Número de teléfono principal no válido!',
                'alt_phone_number.numeric'        => 'El campo teléfono alternativo sólo puede contener números!',
                'alt_phone_number.digits_between' => 'Número de teléfono alternativo no válido!',
                'contact_name.required'           => 'Debe especificar una persona de contacto del proveedor!',
                'contact_id.required'             => 'Debe especificar el número de documento de identidad de la persona de contacto!',
                'contact_id.numeric'              => 'El campo C.I. de contacto sólo puede contener números!',
                'contact_id.digits_between'       => 'Número de C.I. no válido',
                'contact_id_place.required'       => 'Debe especificar la extensión del documento de identidad
                                                        de la persona de contacto!',
                'contact_phone.required'          => 'Debe especificar el número de teléfono de la persona de contacto!',
                'contact_phone.numeric'           => 'El campo teléfono de contacto sólo puede contener números!',
                'contact_phone.digits_between'    => 'Número de teléfono de contacto no válido!',
                'email.email'                     => 'Debe introducir un correo electrónico válido',
            ]
        );

        if ($v->fails()) {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }
        
        $provider->bnk_name = $provider->bnk_name == "Otro" ? Request::input('other_bnk_name') : $provider->bnk_name;
        $provider->specialty = $provider->specialty === 'Otro' ? Request::input('other_specialty') : $provider->specialty;
        
        $provider->save();

        Session::flash('message', "Proveedor registrado correctamente");
        return redirect()->route('provider.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $provider = Provider::find($id);
        $ocs = OC::where('provider_id', $provider->id)->where('flags', 'like', '01%0')->get(); //select only active records

        return View::make('app.provider_info', ['provider' => $provider, 'service' => $service, 'user' => $user,
            'ocs' => $ocs]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $session_user = $user;
        $provider = Provider::find($id);

        $bank_options = Provider::select('bnk_name')->where('bnk_name', '<>', '')->groupBy('bnk_name')->get();
        $specialtyOptions = Provider::select('specialty')->where('specialty', '<>', '')->groupBy('specialty')->get();

        return View::make('app.provider_form', ['provider' => $provider, 'bank_options' => $bank_options,
            'specialtyOptions' => $specialtyOptions, 'service' => $service, 'session_user' => $session_user]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $modify_provider = Provider::find($id);

        if (Request::input('nit') != $modify_provider->nit) {
            $v = \Validator::make(Request::all(), [
                'nit'               => 'required|unique:providers|numeric|digits_between:8,10',
            ],
                [
                    'nit.required'                    => 'Debe especificar un número de NIT!',
                    'nit.unique'                      => 'El NIT de proveedor ya está registrado!',
                    'nit.numeric'                     => 'El campo NIT sólo puede contener números!',
                    'nit.digits_between'              => 'Número de NIT no válido!',
                ]
            );
            
            if ($v->fails()) {
                Session::flash('message', $v->messages()->first());
                return redirect()->back()->withInput();
            }
        }
        
        $v = \Validator::make(Request::all(), [
            'prov_name'         => 'required',
            'specialty'         => 'required',
            'bnk_account'       => 'required',
            'address'           => 'required',
            'bnk_name'          => 'required',
            'other_bnk_name'    => 'required_if:bnk_name,Otro',
            'phone_number'      => 'required|numeric|digits_between:7,8',
            'alt_phone_number'  => 'numeric|digits_between:7,8',
            'contact_name'      => 'required',
            'contact_id'        => 'required|numeric|digits_between:6,8',
            'contact_id_place'  => 'required',
            'contact_phone'     => 'required|numeric|digits_between:7,8',
            'email'             => 'email',
        ],
            [
                'prov_name.required'              => 'Debe especificar un nombre o razón social!',
                'specialty.required'              => 'Debe especificar el área de especialidad del proveedor!',
                'bnk_account.required'            => 'Debe especificar un número de cuenta!',
                'address.required'                => 'Debe especificar la dirección del proveedor!',
                'bnk_name.required'               => 'Debe especificar un Banco!',
                'other_bnk_name.required_if'      => 'Debe especificar un banco!',
                'phone_number.required'           => 'Debe especificar el número de teléfono del proveedor!',
                'phone_number.numeric'            => 'El campo teléfono principal sólo puede contener números!',
                'phone_number.digits_between'     => 'Número de teléfono principal no válido!',
                'alt_phone_number.numeric'        => 'El campo teléfono alternativo sólo puede contener números!',
                'alt_phone_number.digits_between' => 'Número de teléfono alternativo no válido!',
                'contact_name.required'           => 'Debe especificar una persona de contacto del proveedor!',
                'contact_id.required'             => 'Debe especificar el número de documento de identidad de la persona de contacto!',
                'contact_id.numeric'              => 'El campo C.I. de contacto sólo puede contener números!',
                'contact_id.digits_between'       => 'Número de C.I. no válido',
                'contact_id_place.required'       => 'Debe especificar la extensión del documento de identidad
                                                        de la persona de contacto!',
                'contact_phone.required'          => 'Debe especificar el número de teléfono de la persona de contacto!',
                'contact_phone.numeric'           => 'El campo teléfono de contacto sólo puede contener números!',
                'contact_phone.digits_between'    => 'Número de teléfono de contacto no válido!',
                'email.email'                     => 'Debe introducir un correo electrónico válido',
            ]
        );
        
        if ($v->fails()) {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $modify_provider->fill(Request::all());

        $modify_provider->bnk_name = $modify_provider->bnk_name=="Otro" ? Request::input('other_bnk_name') :
            $modify_provider->bnk_name;
        $modify_provider->specialty = $modify_provider->specialty == 'Otro' ? Request::input('other_specialty') :
            $modify_provider->specialty;

        $modify_provider->save();

        Session::flash('message', "El registro de proveedor ha sido modificado correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('provider.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function incomplete_registers()
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');
        
        $service = Session::get('service');
        
        $providers = $this->incompleteProviderRecords();
        
        /*
        $providers = Provider::whereNull('specialty')->orwhere('nit','=',0)->orwhere('phone_number','=',0)
            ->orwhere('address','=','')->orwhere('bnk_account','=','')->orwhere('bnk_name','=','')
            ->orwhere('contact_name','=','')->orwhere('contact_id','=',0)
            ->orwhere('contact_id_place','=','')->orwhere('contact_phone','=',0)
            ->orderBy('prov_name')->get();
            */

        return View::make('app.provider_incomplete_list', ['providers' => $providers, 'service' => $service,
            'user' => $user]);
    }
}
