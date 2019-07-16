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
use App\Employee;
use App\User;
use App\Email;
use App\Branch;
use Carbon\Carbon;
use Hash;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $stat = Input::get('stat');

        $employees = Employee::where('id','>',0);

        if ($stat == 'retired')
            $employees = $employees->where('active', 0);
        if ($stat == 'active')
            $employees = $employees->where('active', 1);

        $employees = $employees->orderBy('last_name')->paginate(20);

        return View::make('app.employee_brief', ['employees' => $employees, 'service' => $service,
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

        $bnk_options = Employee::select('bnk')->where('bnk', '<>', '')->groupBy('bnk')->get();

        $branches = Branch::select('id', 'name', 'city')->where('name','<>','')->where('active', 1)
          ->orderBy('name')->get();

        return View::make('app.employee_form', ['employee' => 0, 'bnk_options' => $bnk_options,
          'service' => $service, 'branches' => $branches, 'user' => $user]);
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

        //$service = Session::get('service');

        $v = \Validator::make(Request::all(), [
          'first_name'    => 'required',
          'last_name'     => 'required',
          'birthday'      => 'date',
          'id_card'       => 'required',
          'id_extension'  => 'required',
          'bnk_account'   => 'required_with:bnk',
          'bnk'           => 'required_with:bnk_account',
          'other_bnk'     => 'required_if:bnk,Otro',
          'area'          => 'required',
          'branch_id'     => 'required',
          'income'        => 'numeric',
          'basic_income'  => 'numeric',
          'production_bonus' => 'numeric',
          'payable_amount' => 'numeric',
          'corp_email'    => 'email',
          'ext_email'     => 'email',
          'phone'         => 'numeric|digits_between:7,8',
        ],
          [
            'first_name.required'   => 'Debe especificar el/los nombre(s) del empleado!',
            'last_name.required'    => 'Debe especificar el/los apellido(s) del empleado!',
            'birthday.date'         => 'La fecha de nacimiento no tiene un formato válido!',
            'id_card.required'      => 'Debe especificar el número de carnet de identidad del empleado!',
            'id_extension.required' => 'Debe especificar el lugar de extensión del carnet de identidad del empleado!',
            'bnk_account.required_with' => 'Debe indicar un número de cuenta si especifica un banco!',
            'bnk.required_with'     => 'Debe indicar un banco si especifica un número de cuenta!',
            'other_bnk.required_if' => 'Debe indicar el nombre del banco si selecciona la opción "Otro"!',
            'area.required'         => 'Debe especificar el área en que desempeña sus funciones este empleado!',
            'branch_id.required'    => 'Debe especificar la oficina en la que desempeña sus funciones este empleado!',
            'income.numeric'        => 'El campo "ingreso" sólo puede contener números!',
            'basic_income.numeric'  => 'El campo "Sueldo básico" sólo puede contener números!',
            'production_bonus.numeric' => 'El campo "Bonus de producción" sólo puede contener números!',
            'payable_amount.numeric' => 'El campo "Líquido pagable" sólo puede contener números!',
            'corp_email.email'      => 'Debe introducir un correo electrónico corporativo válido',
            'ext_email.email'       => 'Debe introducir un correo electrónico externo válido',
            'phone.numeric'         => 'El campo "teléfono" sólo puede contener números!',
            'phone.digits_between'  => 'Número de teléfono no válido!',
          ]
        );

        if ($v->fails()) {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $employee = new Employee(Request::all());

        $employee->user_id = $user->id;

        $employee->bnk = $employee->bnk && $employee->bnk != 'Otro' ?
            $employee->bnk : (Request::input('other_bnk') ?: '');

        $employee->active = 1; //Currently active
        $employee->date_in = $employee->date_in ?: Carbon::now();

        $employee->save();

        $this->fill_code_column(); //Fill records' codes where empty
        
        Session::flash('message', "El empleado fue registrado correctamente");

        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('employee.index');
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

        $employee = Employee::find($id);

        $employee->birthday = Carbon::parse($employee->birthday);
        $employee->date_in = Carbon::parse($employee->date_in);
        $employee->date_in_employee = Carbon::parse($employee->date_in_employee);
        $employee->date_out = Carbon::parse($employee->date_out);

        $exists_picture = false;
        foreach($employee->files as $file) {
            if ($file->type == 'jpg' || $file->type == 'jpeg' || $file->type == 'png')
                $exists_picture = true;
        }

        return View::make('app.employee_info', ['employee' => $employee, 'service' => $service,
          'exists_picture' => $exists_picture, 'user' => $user]);
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

        $employee = Employee::find($id);

        $service = Session::get('service');

        $bnk_options = Employee::select('bnk')->where('bnk', '<>', '')->groupBy('bnk')->get();

        $branches = Branch::select('id', 'name', 'city')->where('name','<>','')->where('active', 1)
          ->orderBy('name')->get();

        $employee->date_in = Carbon::parse($employee->date_in)->format('Y-m-d');
        $employee->date_in_employee = Carbon::parse($employee->date_in_employee)->format('Y-m-d');

        return View::make('app.employee_form', ['employee' => $employee, 'bnk_options' => $bnk_options,
            'branches' => $branches, 'service' => $service, 'user' => $user]);
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
          'first_name'    => 'required',
          'last_name'     => 'required',
          'birthday'      => 'date',
          'id_card'       => 'required',
          'id_extension'  => 'required',
          'bnk_account'   => 'required_with:bnk',
          'bnk'           => 'required_with:bnk_account',
          'other_bnk'     => 'required_if:bnk,Otro',
          'area'          => 'required',
          'branch_id'     => 'required',
          'income'        => 'numeric',
          'basic_income'  => 'numeric',
          'production_bonus' => 'numeric',
          'payable_amount' => 'numeric',
          'corp_email'    => 'email',
          'ext_email'     => 'email',
          'phone'         => 'numeric|digits_between:7,8',
        ],
          [
            'first_name.required'   => 'Debe especificar el/los nombre(s) del empleado!',
            'last_name.required'    => 'Debe especificar el/los apellido(s) del empleado!',
            'birthday.date'         => 'La fecha de nacimiento no tiene un formato válido!',
            'id_card.required'      => 'Debe especificar el número de carnet de identidad del empleado!',
            'id_extension.required' => 'Debe especificar el lugar de extensión del carnet de identidad del empleado!',
            'bnk_account.required_with' => 'Debe indicar un número de cuenta si especifica un banco!',
            'bnk.required_with'     => 'Debe indicar un banco si especifica un número de cuenta!',
            'other_bnk.required_if' => 'Debe indicar el nombre del banco si selecciona la opción "Otro"!',
            'area.required'         => 'Debe especificar el área en que desempeña sus funciones este empleado!',
            'branch_id.required'    => 'Debe especificar la oficina en la que desempeña sus funciones este empleado!',
            'income.numeric'        => 'El campo "ingreso" sólo puede contener números!',
            'basic_income.numeric'  => 'El campo "Sueldo básico" sólo puede contener números!',
            'production_bonus.numeric' => 'El campo "Bonus de producción" sólo puede contener números!',
            'payable_amount.numeric' => 'El campo "Líquido pagable" sólo puede contener números!',
            'corp_email.email'      => 'Debe introducir un correo electrónico corporativo válido',
            'ext_email.email'       => 'Debe introducir un correo electrónico externo válido',
            'phone.numeric'         => 'El campo "teléfono" sólo puede contener números!',
            'phone.digits_between'  => 'Número de teléfono no válido!',
          ]
        );

        if ($v->fails()) {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $employee = Employee::find($id);
        $employee->fill(Request::all());

        $employee->bnk = $employee->bnk && $employee->bnk != 'Otro' ?
            $employee->bnk : (Request::input('other_bnk') ?: '');

        $employee->save();

        Session::flash('message', "Datos modificados correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('employee.index');
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

        Employee::find($id)
            ->update([
                'active'        => 0,
                'date_out'      => Carbon::now()
            ]);

        Session::flash('message', 'El empleado seleccionado ha sido marcado como "Retirado"');
        if (Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('employee.index');
    }

    function fill_code_column()
    {
        $employees = Employee::where('code','')->get();

        foreach ($employees as $employee) {
            $employee->code = 'ER-'.Carbon::now()->format('y').
                str_pad($employee->id, 3, "0", STR_PAD_LEFT);

            $employee->save();
        }
    }
}
