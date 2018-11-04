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
use App\Email;
use App\TechGroup;

class TechGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id)) {
            return View('app.index', ['service'=>'staff', 'user'=>null]);
        }
        if($user->acc_staff==0)
            return redirect()->action('LoginController@logout', ['service' => 'staff']);

        $active = Input::get('act');
        $archived = Input::get('arch');

        $groups = TechGroup::where('id', '>', 0);

        if($active)
            $groups = $groups->where('status', 0);

        if($archived)
            $groups = $groups->where('status', 1);

        $groups = $groups->orderBy('group_number', 'asc')->paginate(20);

        $service = Session::get('service');

        return View::make('app.tech_group_brief', ['groups' => $groups, 'service' => $service, 'user' => $user]);
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

        return View::make('app.tech_group_form', ['group' => 0, 'user' => $user, 'service' => $service]);
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
            'group_area'            => 'required',
            'group_number'          => 'required',
            'group_head_name'       => 'required|exists:users,name',
        ],
            [
                'required'                  => 'Este campo es obligatorio!',
                'exists'                    => 'El nombre especificado no fue encontrado en el sistema!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', 'Sucedió un error al enviar el formulario!');
            return redirect()->back()->withErrors($v)->withInput();
        }

        $group_head_name = Request::input('group_head_name');
        $tech_2_name = Request::input('tech_2_name');
        $tech_3_name = Request::input('tech_3_name');
        $tech_4_name = Request::input('tech_4_name');
        $tech_5_name = Request::input('tech_5_name');

        $fail = false;
        $message = '';

        if(($tech_2_name!=''&&!(User::where('name', $tech_2_name)->exists()))||
            ($tech_3_name!=''&&!(User::where('name', $tech_3_name)->exists()))||
            ($tech_4_name!=''&&!(User::where('name', $tech_4_name)->exists()))||
            ($tech_5_name!=''&&!(User::where('name', $tech_5_name)->exists())))
        {
            $message = 'Uno o más nombres de personal técnico no fueron encontrados en el sistema!
                    Asegúrese de que todos los nombres especificados correspondan a personal registrado en el sistema.';
            $fail = true;
        }

        $check_names = array($group_head_name, ($tech_2_name ?: 2), ($tech_3_name ?: 3), ($tech_4_name ?: 4),
            ($tech_5_name ?: 5));

        if($this->duplicated($check_names)){
            $message = 'Uno o más nombres de técnicos son repetidos!';
            $fail = true;
        }

        if($fail){
            Session::flash('message', $message);
            return redirect()->back()->withInput();
        }
        // End of form validation

        $group = new TechGroup(Request::all());

        $group->status = 0; //A new group is always on active (0) status

        $group_head = User::where('name', $group_head_name)->first();
        $tech_2 = User::where('name', $tech_2_name)->first();
        $tech_3 = User::where('name', $tech_3_name)->first();
        $tech_4 = User::where('name', $tech_4_name)->first();
        $tech_5 = User::where('name', $tech_5_name)->first();

        $group->group_head_id = $group_head ? $group_head->id : 0;
        $group->tech_2_id = $tech_2 ? $tech_2->id : 0;
        $group->tech_3_id = $tech_3 ? $tech_3->id : 0;
        $group->tech_4_id = $tech_4 ? $tech_4->id : 0;
        $group->tech_5_id = $tech_5 ? $tech_5->id : 0;

        $group->save();

        Session::flash('message', "El grupo de trabajo fue agegado al sistema");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('tech_group.index');
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

        $group = TechGroup::find($id);

        return View::make('app.tech_group_info', ['group' => $group, 'service' => $service, 'user' => $user]);
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

        $group = TechGroup::find($id);

        return View::make('app.tech_group_form', ['group' => $group, 'user' => $user, 'service' => $service]);
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
            'group_area'            => 'required',
            'group_number'          => 'required',
            'group_head_name'       => 'required|exists:users,name',
        ],
            [
                'required'                  => 'Este campo es obligatorio!',
                'exists'                    => 'El nombre especificado no fue encontrado en el sistema!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', 'Sucedió un error al enviar el formulario!');
            return redirect()->back()->withErrors($v)->withInput();
        }

        $group_head_name = Request::input('group_head_name');
        $tech_2_name = Request::input('tech_2_name');
        $tech_3_name = Request::input('tech_3_name');
        $tech_4_name = Request::input('tech_4_name');
        $tech_5_name = Request::input('tech_5_name');

        $fail = false;
        $message = '';

        if(($tech_2_name!=''&&!(User::where('name', $tech_2_name)->exists()))||
            ($tech_3_name!=''&&!(User::where('name', $tech_3_name)->exists()))||
            ($tech_4_name!=''&&!(User::where('name', $tech_4_name)->exists()))||
            ($tech_5_name!=''&&!(User::where('name', $tech_5_name)->exists())))
        {
            $message = 'Uno o más nombres de personal técnico no fueron encontrados en el sistema!
                    Asegúrese de que todos los nombres especificados correspondan a personal registrado en el sistema.';
            $fail = true;
        }

        $check_names = array($group_head_name, ($tech_2_name ?: 2), ($tech_3_name ?: 3), ($tech_4_name ?: 4),
            ($tech_5_name ?: 5));

        if($this->duplicated($check_names)){
            $message = 'Uno o más nombres de técnicos son repetidos!';
            $fail = true;
        }

        if($fail){
            Session::flash('message', $message);
            return redirect()->back()->withInput();
        }
        // End of form validation

        $group = TechGroup::find($id);

        $group->fill(Request::all());

        $group_head = User::where('name', $group_head_name)->first();
        $tech_2 = User::where('name', $tech_2_name)->first();
        $tech_3 = User::where('name', $tech_3_name)->first();
        $tech_4 = User::where('name', $tech_4_name)->first();
        $tech_5 = User::where('name', $tech_5_name)->first();

        $group->group_head_id = $group_head ? $group_head->id : 0;
        $group->tech_2_id = $tech_2 ? $tech_2->id : 0;
        $group->tech_3_id = $tech_3 ? $tech_3->id : 0;
        $group->tech_4_id = $tech_4 ? $tech_4->id : 0;
        $group->tech_5_id = $tech_5 ? $tech_5->id : 0;

        $group->save();

        Session::flash('message', "Datos modificados correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('tech_group.index');
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
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $group = TechGroup::find($id);

        if($group){
            $group->status = 1; //Mark as "archived"
            $group->save();
        }
        else{
            Session::flash('message', 'El registro solicitado no fue encontrado!');
            return redirect()->back();
        }

        Session::flash('message', 'El registro seleccionado ha sido archivado');
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('tech_group.index');
    }

    function duplicated(array $input_array) {
        return count($input_array) !== count(array_flip($input_array));
    }
}
