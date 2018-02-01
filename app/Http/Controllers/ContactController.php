<?php

namespace App\Http\Controllers;

use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use View;
use App\User;
use App\Contact;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id)) {
            return View('app.index', ['service' => 'project', 'user' => null]);
        }
        if($user->acc_project==0)
            return redirect()->action('LoginController@logout', ['service' => 'project']);

        //$session_user = $user;
        $contacts = Contact::where('id', '>', 0)->orderBy('name')->paginate(20);

        $service = Session::get('service');

        return View::make('app.contact_brief', ['contacts' => $contacts, 'service' => $service, 'user' => $user]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $company_options = Contact::select('company')->where('company', '<>', '')->groupBy('company')->get();

        return View::make('app.contact_form', ['contact' => 0, 'company_options' => $company_options,
            'service' => $service, 'user' => $user]);
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
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $contact = new Contact(Request::all());

        $v = \Validator::make(Request::all(), [
            'name'              => 'required|unique:contacts',
            'position'          => 'required',
            'company'           => 'required',
            'other_company'     => 'required_if:company,Otro',
            'phone_1'           => 'numeric|digits_between:7,8',
            'phone_2'           => 'numeric|digits_between:7,8',
            'email'             => 'email',
        ],
            [
                'unique'                          => 'Este contacto ya existe!',
                'name.required'                   => 'Debe especificar el nombre del contacto!',
                'position.required'               => 'Debe especificar el cargo del contacto!',
                'company.required'                => 'Debe especificar una Empresa!',
                'other_company.required_if'       => 'Debe especificar una Empresa!',
                'phone_1.digits_between'          => 'Número de teléfono principal no válido!',
                'phone_2.digits_between'          => 'Número de teléfono alternativo no válido!',
                'email.email'                     => 'Debe introducir un correo electrónico válido',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $contact->company = $contact->company=="Otro" ? Request::input('other_company') : $contact->company;

        $contact->save();

        Session::flash('message', "Contacto registrado correctamente");
        return redirect()->route('contact.index');
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

        $contact = Contact::find($id);

        return View::make('app.contact_info', ['contact' => $contact, 'service' => $service, 'user' => $user]);
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
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');
        
        $contact = Contact::find($id);

        $company_options = Contact::select('company')->where('company', '<>', '')->groupBy('company')->get();

        return View::make('app.contact_form', ['contact' => $contact, 'company_options' => $company_options,
            'service' => $service, 'user' => $user]);
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
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $modify_contact = Contact::find($id);

        if(Request::input('name')!=$modify_contact->name){
            $v = \Validator::make(Request::all(), [
                'name'               => 'required|unique:contacts',
            ],
                [
                    'unique'                          => 'Este contacto ya existe!',
                    'name.required'                   => 'Debe especificar el nombre del contacto!',
                ]
            );

            if ($v->fails())
            {
                Session::flash('message', $v->messages()->first());
                return redirect()->back()->withInput();
            }
        }

        $v = \Validator::make(Request::all(), [
            'position'          => 'required',
            'company'           => 'required',
            'other_company'     => 'required_if:company,Otro',
            'phone_1'           => 'numeric|digits_between:7,8',
            'phone_2'           => 'numeric|digits_between:7,8',
            'email'             => 'email',
        ],
            [
                'position.required'               => 'Debe especificar el cargo del contacto!',
                'company.required'                => 'Debe especificar una Empresa!',
                'other_company.required_if'       => 'Debe especificar una Empresa!',
                'phone_1.digits_between'          => 'Número de teléfono principal no válido!',
                'phone_2.digits_between'          => 'Número de teléfono alternativo no válido!',
                'email.email'                     => 'Debe introducir un correo electrónico válido',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $modify_contact->fill(Request::all());

        $modify_contact->company = $modify_contact->company=="Otro" ? Request::input('other_company') :
            $modify_contact->company;

        $modify_contact->save();

        Session::flash('message', "Datos actualizados correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('contact.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        /*
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $contact = Contact::find($id);

        if($contact){
            foreach($contact->assignments as $assignment){
                $assignment->contact_id = 0; //Remove FK from assignments
                $assignment->save();
            }

            foreach($contact->sites as $site){
                $site->contact_id = 0; //Remove FK from sites
                $site->save();
            }

            $contact->delete();

            $message = 'El registro de contacto ha sido eliminado del sistema, todas sus asignaciones han sido removidas';
        }
        else{
            $message = 'El registro solicitado no existe en el sistema!';
        }
        */

        Session::flash('message', 'Esta función está deshabilitada!');
        return redirect()->back();
    }
}
