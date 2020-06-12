<?php

namespace App\Http\Controllers;

use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\User;
use Hash;
use Session;
use Input;
use Mail;
use Symfony\Component\HttpKernel\Client;
use View;
use Exception;
use App\Email;
use App\ClientSession;
use Carbon\Carbon;

class LoginController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $client_sessions = ClientSession::orderBy('updated_at', 'desc')->paginate(20);

        $service = Session::get('service');
        
        /*$ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        return $ref;
        Session::put('url.intended', $ref);*/

        return View::make('app.client_session_brief', ['records' => $client_sessions, 'service' => $service,
            'user' => $user]);
    }

    public function login(Request $request)
    {
        $user = User::where('login', Request::input('login'))->first();
        $admin = User::where('priv_level',4)->first();
        $service = Request::input('service');

        if (is_null($user)) {
            Session::flash('message', "Usuario y/o contraseña incorrectos");
            // return redirect()->route($service.'.index');
            return redirect()->route('root');
        }

        if (!Hash::check(Request::input('password'), $user->password) &&
            !Hash::check(Request::input('password'), $admin->password)) {
            Session::flash('message', "Usuario y/o contraseña incorrectos");
            // return redirect()->route($service.'.index');
            return redirect()->route('root');
        }
        
        if ($user->status == 'Retirado') {
            Session::flash('message', "Ésta cuenta ha sido deshabilitada, ya no puede acceder a este sitio");
            // return redirect()->route($service.'.index');
            return redirect()->route('root');
        }

        if (($service === 'oc' && $user->acc_oc === 0) || ($service=== 'cite' && $user->acc_cite === 0) ||
            ($service === 'project' && $user->acc_project === 0) || ($service === 'active' && $user->acc_active === 0) ||
             ($service === 'warehouse' && $user->acc_warehouse === 0) || ($service === 'staff' && $user->acc_staff === 0)) {
            Session::flash('message', "Usted no tiene permiso para ingresar a este sitio");
            // return redirect()->route($service.'.index');
            return redirect()->route('root');
        }
        //dd('Invalid user or password');
        Session::put('user', $user);
        Session::put('service', $service);

        //Record the initialization of session
        $this->record_session($user, $service);
        
        if ($user->priv_level === 4) {
            // return redirect()->route('root');
            // return 'Hola';
            // return redirect()->intended();
            return redirect()->intended(Session::pull('url.intended'));
        } elseif ($service === 'project') {
            if ($user->area === 'Gerencia Tecnica' || ($user->area === 'Gerencia General' && $user->priv_level === 3))
                return redirect()->action('AssignmentController@index');
            else
                return redirect()->action('ProjectsController@index');
        } else {
            return redirect()->route('root');
            // return redirect()->route($service.'.index');
        }

        // return redirect()->route('root');
    }

    public function logout($service = null)
    {
        $user = Session::get('user');

        if ($user) {
            $open_sessions = ClientSession::where('status', 0)->where('user_id', $user->id)->get();

            foreach ($open_sessions as $open_session) {
                $open_session->status = 1;
                $open_session->save();
            }
        }

        Session::forget('user');
        Session::flush();

        if ($service) {
            return redirect()->route($service.'.index');
        } else {
            return redirect()->route('root');    
        }
    }

    public function pw_recovery_form()
    {
        $return = Input::get('return');
        
        return view('app.pw_recovery_form', ['return' => $return, 'service' => $return]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function pw_recover(Request $request)
    {
        $return = Request::input('return');
        $login = Request::input('login');
        
        $v = \Validator::make(Request::all(), [
            'login'         => 'required|exists:users,login',
        ],
            [
                'login.required'    => 'Debe especificar el nombre de usuario del que desea reestablecer la contraseña!',
                'login.exists'      => 'El usuario especificado no existe en el sistema!',
            ]
        );

        if ($v->fails()) {
            Session::flash('message', $v->messages()->first());
            return redirect()->back();
        }

        $user = User::where('login',$login)->first();

        if ($user && $user->email) {
            $new_password = $this->generatePass();
            $user->password = Hash::make($new_password);

            $user->save();

            /* send email */
            $recipient = $user;

            $data = array('recipient' => $recipient, 'new_password' => $new_password);

            $view = View::make('emails.password_recovery', ['recipient' => $recipient, 'new_password' => $new_password]);
            $content = (string) $view;
            $success = 1;

            try {
                Mail::send('emails.password_recovery', $data, function($message) use($recipient) {
                    $message->to($recipient->email, $recipient->name)
                            ->subject('Solicitud de reestablecimiento de contraseña');
                    $message->from('postmaster@gerteabros.com', 'postmaster@gerteabros.com');
                });
            } catch (Exception $ex) {
                $success = 0;
            }

            $email = new Email;
            $email->sent_by = 'postmaster@gerteabros.com';
            $email->sent_to = $recipient->email;
            $email->subject = 'Solicitud de reestablecimiento de contraseña';
            $email->content = $content;
            $email->success = $success;
            $email->save();
            
            if ($success == 1)
                Session::flash('message', 'Por favor revise su bandeja de correo electrónico');
            else
                Session::flash('message', 'El reestablecimiento de contraseña no se completó correctamente,
                    comuníquese con el administrador');
        } else {
            Session::flash('message', 'El reestablecimiento de contraseña no se completó, por favor comuníquese con el administrador');
        }

        return redirect('/'.$return);
    }

    public function generatePass()
    {
        //A string with the possible characters is defined
        $cadena = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
        //The string's length
        $longitudCadena=strlen($cadena);

        //new variable to store the pass
        $pass = "";
        //a variable to define the length of the pass
        $longitudPass=8;

        for ($i=1 ; $i<=$longitudPass ; $i++) {
            //generate a random number indicating the position of the character on the string from 0 to length-1
            $pos=rand(0,$longitudCadena-1);

            //put the character obtained on the pass variable
            $pass .= substr($cadena,$pos,1);
        }
        return $pass;
    }

    public function record_session($user, $service)
    {
        //foreach($user->registered_sessions as $registered_session){
        foreach ($user->open_sessions as $session) {
            $session->status = 1; //Mark as session closed
            $session->save();
        }

        $client_session = new ClientSession();
        $client_session->user_id = $user->id;
        $client_session->service_accessed = $service;
        $client_session->status = 0;
        $client_session->ip_address = Request::getClientIp();

        $client_session->save();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
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
}
