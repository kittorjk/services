<?php

namespace App\Http\Controllers;

use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use View;
use Mail;
use Exception;
use App\User;
use App\Email;
use App\Branch;
use App\UserAction;
use Hash;

class UserController extends Controller
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
        
        $records = User::orderBy('name')->paginate(20);

        $service = Session::get('service');

        return View::make('app.user_brief', ['records' => $records, 'service' => $service, 'user' => $user]);
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

        $session_user = $user;
        $service = Session::get('service');

        $branches = Branch::select('id', 'name', 'city')->where('name','<>','')->where('active', 1)->orderBy('name')->get();

        return View::make('app.user_form', ['current_user' => 0, 'service' => $service, 'branches' => $branches,
            'session_user' => $session_user]);
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
        
        $service = Session::get('service');

        $add_user = new User(Request::all());

        if ($user->priv_level == 4) {
            $v = \Validator::make(Request::all(), [
                'name'          => 'required',
                'full_name'     => 'required',
                'login'         => 'required|unique:users',
                'branch_id'     => 'required',
                'area'          => 'required',
                'phone'         => 'numeric|digits_between:7,8',
                'email'         => 'email',
                'cost'          => 'numeric',
            ],
                [
                    'name.required'         => 'Debe especificar el nombre que será visible del usuario en el sistema!',
                    'full_name.required'    => 'Debe especificar el nombre completo del usuario!',
                    'login.unique'          => 'El usuario ya existe!',
                    'branch_id.required'    => 'Debe especificar la oficina en la que desempeña sus funciones este usuario!',
                    'area.required'         => 'Debe especificar el área de trabajo de éste usuario!',
                    'phone.numeric'         => 'El campo de teléfono sólo puede contener números!',
                    'phone.digits_between'  => 'Número de teléfono no válido!',
                    'email.email'           => 'Debe introducir un correo electrónico válido',
                    'cost.numeric'          => 'El campo "Salario" contine caracteres no válidos!',
                ]
            );

            if ($v->fails()) {
                Session::flash('message', $v->messages()->first());
                return redirect()->back()->withInput();
            }
        } else {
            $v = \Validator::make(Request::all(), [
                'name'          => 'required',
                'full_name'     => 'required',
                'branch_id'     => 'required',
                'phone'         => 'numeric|digits_between:7,8',
                'email'         => 'required|email',
                'area'          => 'required',
                'work_type'     => 'required',
                'cost'          => 'numeric',
            ],
                [
                    'name.required'         => 'Debe especificar el nombre que será visible del usuario en el sistema!',
                    'full_name.required'    => 'Debe especificar el nombre completo del usuario!',
                    'branch_id.required'    => 'Debe especificar la oficina en la que desempeña sus funciones este usuario!',
                    'phone.numeric'         => 'El campo de teléfono sólo puede contener números!',
                    'phone.digits_between'  => 'Número de teléfono no válido!',
                    'email.required'        => 'Debe especificar el correo electrónico del nuevo usuario',
                    'email.email'           => 'Debe introducir un correo electrónico válido',
                    'area.required'         => 'Debe especificar el área a que pertenece el nuevo usuario',
                    'work_type.required'    => 'Debe especificar el área a que pertenece el nuevo usuario',
                    'cost.numeric'          => 'El campo "Salario" contine caracteres no válidos!',
                ]
            );

            if ($v->fails()) {
                Session::flash('message', $v->messages()->first());
                return redirect()->back()->withInput();
            }

            $random_string = $this->generatePass();

            $add_user->login = $random_string;
            $add_user->password = $random_string;
            $add_user->priv_level = 0;
            $add_user->acc_project = 1;
            $add_user->acc_active = 1;
            $add_user->acc_warehouse = 0;
            $add_user->acc_staff = 0;
        }

        $pass_to_hash = $add_user->password;
        $add_user->password = Hash::make($pass_to_hash);

        $add_user->cost_day = $add_user->cost>0 ? $add_user->cost/22 : 0;

        $add_user->status = 'Activo';
        
        $add_user->save();

        /* Add actions registry */
        $this->add_actions_registry($add_user->id);

        /* send email */
        $recipient = $add_user;

        $data = array('recipient' => $recipient);
        $mail_structure = 'emails.new_user';
        $subject = 'Nueva cuenta en el sistema gerteabros.com';

        $view = View::make($mail_structure, $data /*['recipient' => $recipient]*/);
        $content = (string) $view;
        
        $success = 1;
        try {
            Mail::send($mail_structure, $data, function($message) use($recipient, $subject) {
                $message->to($recipient->email, $recipient->name)
                    ->subject($subject)
                    ->from('postmaster@gerteabros.com', 'postmaster@gerteabros.com');
            });
        } catch (Exception $ex) {
            $success = 0;
        }

        $email = new Email;
        $email->sent_by = 'postmaster@gerteabros.com';
        $email->sent_to = $recipient->email;
        $email->subject = $subject;
        $email->content = $content;
        $email->success = $success;
        $email->save();

        Session::flash('message', "El usuario fue agregado al sistema de forma correcta!");
        
        if (Session::has('url'))
            return redirect(Session::get('url'));
        elseif ($user->priv_level == 4)
            return redirect()->route('user.index');
        else
            return redirect()->route($service.'.index');
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

        $view_user = User::find($id);

        return View::make('app.user_info', ['view_user' => $view_user, 'service' => $service, 'user' => $user]);
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

        $session_user = $user;
        $current_user = User::find($id);

        $service = Session::get('service');

        $branches = Branch::select('id', 'name', 'city')->where('name','<>','')->where('active', 1)->orderBy('name')->get();

        if ($current_user->id == $session_user->id || $session_user->priv_level == 4 || $session_user->action->adm_add_usr == 1) {
            return View::make('app.user_form', ['current_user' => $current_user, 'service' => $service,
                'branches' => $branches, 'session_user' => $session_user]);
        } else {
            if (Session::has('url'))
                return redirect(Session::get('url'));
            else
                return redirect()->route('root');
        }
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

        $v = \Validator::make(Request::all(), [
            'name'          => 'required',
            'full_name'     => 'required',
            //'branch_id'     => 'required',
            //'area'          => 'required',
            'phone'         => 'numeric|digits_between:7,8',
            'email'         => 'email',
            'new_pass'      => 'same:confirm_pass',
            'cost'          => 'numeric',
        ],
            [
                'name.required'         => 'Debe especificar el nombre que será visible del usuario en el sistema!',
                'full_name.required'    => 'Debe especificar el nombre completo del usuario!',
                //'branch_id.required'    => 'Debe especificar la oficina en la que desempeña sus funciones este usuario!',
                //'area.required'         => 'Debe especificar el área de trabajo de éste usuario!',
                'phone.numeric'         => 'El campo de teléfono sólo puede contener números!',
                'phone.digits_between'  => 'Número de teléfono no válido!',
                'email.email'           => 'Debe introducir un correo electrónico válido',
                'new_pass.same'         => 'Las contraseñas introducidas no coinciden!',
                'cost.numeric'          => 'El campo "Salario" contiene caracteres no válidos!',
            ]
        );

        if ($v->fails()) {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        if (Request::input('login') != $user->login && $user->priv_level != 4) {
            $v = \Validator::make(Request::all(), [
                'login'         => 'required|unique:users',
            ]);

            if ($v->fails()) {
                Session::flash('message', " El usuario ya existe! ");
                return redirect()->back()->withInput();
            }
        }

        $modify_user = User::find($id);
        $modify_user->fill(Request::all());
        
        if ($user->priv_level == 4) {
            $modify_user->acc_cite = Request::input('acc_cite') ? 1 : 0;
            $modify_user->acc_oc = Request::input('acc_oc') ? 1 : 0;
            $modify_user->acc_project = Request::input('acc_project') ? 1 : 0;
            $modify_user->acc_active = Request::input('acc_active') ? 1 : 0;
            $modify_user->acc_warehouse = Request::input('acc_warehouse') ? 1 : 0;
            $modify_user->acc_staff = Request::input('acc_staff') ? 1 : 0;

            $modify_user->cost_day = $modify_user->cost > 0 ? $modify_user->cost/22 : 0;
        }

        if (Request::input('chg_pass') == 1) {
            $pass_to_hash = Request::input('new_pass');
            $modify_user->password = Hash::make($pass_to_hash);
        }

        $modify_user->save();

        $service = Session::get('service');

        Session::flash('message', "Datos modificados correctamente");

        if (Session::has('url'))
            return redirect(Session::get('url'));
        elseif ($user->priv_level == 4)
            return redirect()->route('user.index');
        else
            return redirect()->route($service.'.index');
        //return redirect()->route('root');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        User::find($id)
        ->update([
            //'role'          => 'Retirado',
            //'priv_level'    => 0,
            'acc_cite'      => 0,
            'acc_oc'        => 0,
            'acc_project'   => 0,
            'acc_active'    => 0,
            'acc_warehouse' => 0,
            'acc_staff'     => 0,
            'status'        => 'Retirado',
        ]);

        //$delete_user->delete();

        if (Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('user.index');
    }

    public function generatePass()
    {
        //A string with the possible characters is defined
        $chain = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
        //The string's length
        $chainLength = strlen($chain);

        //new variable to store the pass
        $pass = "";
        //a variable to define the length of the pass
        $passLength = 8;

        for ($i = 1; $i <= $passLength; $i++) {
            //generate a random number indicating the position of the character on the string from 0 to length-1
            $pos = rand(0,$chainLength - 1);

            //put the character obtained on the pass variable
            $pass .= substr($chain,$pos, 1);
        }

        return $pass;
    }

    function add_actions_registry($id)
    {
        //$data = 0; // Every action is restricted for new users

        $action = new UserAction();

        $action->user_id = $id;

        /*
        $action->enl_ct = $data;
        $action->enl_oc = $data;
        $action->enl_prj = $data;
        $action->enl_acv = $data;
        $action->enl_adm = $data;
        $action->ct_upl_fmt = $data;
        $action->ct_vw_all = $data;
        $action->ct_exp = $data;
        $action->ct_edt = $data;
        $action->oc_edt = $data;
        $action->oc_apv_tech = $data;
        $action->oc_apv_gg = $data;
        $action->oc_nll = $data;
        $action->oc_exp = $data;
        $action->oc_prv_edt = $data;
        $action->oc_prv_exp = $data;
        $action->oc_ctf_edt = $data;
        $action->oc_ctf_exp = $data;
        $action->oc_inv_edt = $data;
        $action->oc_inv_exp = $data;
        $action->oc_inv_pmt = $data;
        $action->prj_edt = $data;
        $action->prj_exp = $data;
        $action->prj_asg_edt = $data;
        $action->prj_asg_exp = $data;
        $action->prj_evt_edt = $data;
        $action->prj_di_edt = $data;
        $action->prj_ctc_edt = $data;
        $action->prj_st_edt = $data;
        $action->prj_st_del = $data;
        $action->prj_st_exp = $data;
        $action->prj_vtc_mod = $data;
        $action->prj_vtc_edt = $data;
        $action->prj_vtc_pmt = $data;
        $action->prj_vtc_exp = $data;
        $action->prj_tk_edt = $data;
        $action->prj_tk_clr = $data;
        $action->prj_tk_del = $data;
        $action->prj_tk_exp = $data;
        $action->prj_acc_cat = $data;
        $action->prj_cat_exp = $data;
        $action->prj_act_edt = $data;
        $action->prj_act_del = $data;
        $action->prj_act_exp = $data;
        $action->acv_vhc_req = $data;
        $action->acv_vhc_edt = $data;
        $action->acv_vhc_add = $data;
        $action->acv_vhc_exp = $data;
        $action->acv_drv_upl_fmt = $data;
        $action->acv_dvc_req = $data;
        $action->acv_dvc_edt = $data;
        $action->acv_dvc_add = $data;
        $action->acv_dvc_exp = $data;
        $action->acv_ln_req = $data;
        $action->acv_ln_edt = $data;
        $action->acv_ln_add = $data;
        $action->acv_ln_asg = $data;
        $action->acc_adm = $data;
        $action->adm_acc_file = $data;
        $action->adm_acc_mail = $data;
        $action->adm_acc_stf = $data;
        $action->adm_acc_bch = $data;
        */

        $action->save();
    }
}
