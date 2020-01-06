<?php

namespace App\Http\Controllers;

use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use View;
use Mail;
use Input;
use Exception;
use App\User;
use App\CorpLine;
use App\Email;
use App\CorpLineAssignation;
use App\CorpLineRequirement;
use Carbon\Carbon;
use App\Http\Traits\FilesTrait;

class CorpLineRequirementController extends Controller
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
            return View('app.index', ['service' => 'active', 'user' => null]);
        }
        if($user->acc_active==0)
            return redirect()->action('LoginController@logout', ['service' => 'active']);

        $service = Session::get('service');

        $requirements = CorpLineRequirement::where('id', '>', 0);

        if($user->priv_level<2){
            $requirements = $requirements->where(function ($query) use($user) {
                $query->where('for_id', $user->id)->orwhere('user_id', '=', $user->id);
            });
        }

        $requirements = $requirements->orderBy('updated_at','desc')->paginate(20);

        return View::make('app.corp_line_requirement_brief', ['requirements' => $requirements, 'service' => $service,
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
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        return View::make('app.corp_line_requirement_form', ['requirement' => 0, 'service' => $service, 'user' => $user]);
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

        $v = \Validator::make(Request::all(), [
            'for_name'              => 'required|exists:users,name',
            'reason'                => 'required',
        ],
            [
                'for_name.required'         => 'Debe especificar a quién se entregará la línea que se solicita!',
                'for_name.exists'           => 'El nombre del receptor de la línea no está registrado en el sistema!',
                'reason.required'           => 'Debe especificar el motivo del requerimiento de la línea!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $requirement = new CorpLineRequirement(Request::all());

        $date_time = Carbon::now()->format('ymdhis');
        $requirement->code = 'LR-'.$date_time;
        $requirement->user_id = $user->id;

        $person_for = User::select('id')->where('name',Request::input('for_name'))->first();

        if($person_for==''){
            Session::flash('message', "El nombre del receptor del vehículo no está registrado en el sistema!");
            return redirect()->back()->withInput();
        }
        else
            $requirement->for_id = $person_for->id;

        $requirement->status = 1; //In process

        $requirement->save();

        /* Send notification mail */
        $recipient = User::where('area', 'Gerencia General')->where('priv_level', 2)->first();
        $cc = $requirement->person_for;

        $data = array('recipient' => $recipient, 'requirement' => $requirement, 'cc' => $cc);
        $mail_structure = 'emails.new_line_requirement';
        $subject = 'Requerimiento de entrega de línea corporativa';

        $this->send_email($recipient, $cc, $data, $mail_structure, $subject);

        Session::flash('message', "El requerimiento de línea fue registrado correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('line_requirement.index');
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

        $requirement = CorpLineRequirement::find($id);

        return View::make('app.corp_line_requirement_info', ['requirement' => $requirement, 'service' => $service,
            'user' => $user]);
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

        $requirement = CorpLineRequirement::find($id);

        return View::make('app.corp_line_requirement_form', ['requirement' => $requirement, 'service' => $service,
            'user' => $user]);
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

        $v = \Validator::make(Request::all(), [
            'for_name'              => 'required|exists:users,name',
            'reason'                => 'required',
        ],
            [
                'for_name.required'         => 'Debe especificar a quién se entregará la línea que se solicita!',
                'for_name.exists'           => 'El nombre del receptor de la línea no está registrado en el sistema!',
                'reason.required'           => 'Debe especificar el motivo del requerimiento de la línea!',
            ]
        );

        if ($v->fails()) {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $requirement = CorpLineRequirement::find($id);

        $requirement->fill(Request::all());

        $person_for = User::select('id')->where('name',Request::input('for_name'))->first();

        if ($person_for == '') {
            Session::flash('message', "El nombre del receptor del vehículo no está registrado en el sistema!");
            return redirect()->back()->withInput();
        } else
            $requirement->for_id = $person_for->id;

        $requirement->save();

        /* Send notification mail */
        $recipient = User::where('area', 'Gerencia General')->where('priv_level', 2)->first();
        $cc = $requirement->person_for;

        $data = array('recipient' => $recipient, 'requirement' => $requirement, 'cc' => $cc);
        $mail_structure = 'emails.new_line_requirement';
        $subject = 'Requerimiento de entrega de línea corporativa';

        $this->send_email($recipient, $cc, $data, $mail_structure, $subject);

        Session::flash('message', "Requerimiento modificado correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('line_requirement.index');
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

        $requirement = CorpLineRequirement::find($id);

        if($requirement){
            $requirement->delete();

            Session::flash('message', "El registro fue eliminado del sistema");
            if(Session::has('url'))
                return redirect(Session::get('url'));
            else
                return redirect()->route('line_requirement.index');
        }
        else {
            Session::flash('message', "Error al ejecutar el borrado, no se encontró el registro solicitado.");
            return redirect()->back();
        }
    }

    public function reject_form($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $requirement = CorpLineRequirement::find($id);

        if(!$requirement){
            Session::flash('message', 'No se encontró el registro solicitado!');
            return redirect()->back();
        }

        return View::make('app.corp_line_requirement_reject_form', ['requirement' => $requirement,
            'service' => $service, 'user' => $user]);
    }

    public function reject(Request $request, $id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $v = \Validator::make(Request::all(), [
            'stat_obs'      => 'required',
        ],
            [
                'stat_obs.required'     => 'Debe especificar el motivo de rechazo de este requerimiento!',
            ]
        );

        if ($v->fails()) {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $requirement = CorpLineRequirement::find($id);

        $requirement->fill(Request::all());
        $requirement->status = 0; //Rejected
        $requirement->stat_change = Carbon::now();

        $requirement->save();

        /* Send notification mail */
        $recipient = $requirement->user;
        $cc = $requirement->person_for;
        $data = array('recipient' => $recipient, 'requirement' => $requirement, 'cc' => $cc);
        $mail_structure = 'emails.line_requirement_rejected';
        $subject = 'Requerimiento de entrega de línea corporativa rechazado';

        $this->send_email($recipient, $cc, $data, $mail_structure, $subject);

        Session::flash('message', "El requerimiento ha sido rechazado");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('line_requirement.index');
    }

    function send_email($recipient, $cc, $data, $mail_structure, $subject)
    {
        $view = View::make($mail_structure, $data /*['recipient' => $recipient, 'requirement' => $requirement]*/);
        $content = (string)$view;

        $success = 1;

        try {
            Mail::send($mail_structure, $data, function ($message) use ($recipient, $cc, $subject) {
                $message->to($recipient->email, $recipient->name)
                    ->cc($cc->email, $cc->name)
                    ->subject($subject)
                    ->from('postmaster@gerteabros.com', 'postmaster@gerteabros.com');
            });
        } catch (Exception $ex) {
            $success = 0;
        }

        $email = new Email;
        $email->sent_by = 'postmaster@gerteabros.com';
        $email->sent_to = $recipient->email;
        $email->sent_cc = $cc->email;
        $email->subject = $subject;
        $email->content = $content;
        $email->success = $success;
        $email->save();
    }
}
