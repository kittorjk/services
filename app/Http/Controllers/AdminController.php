<?php

namespace App\Http\Controllers;

use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Session;
use View;
use Input;
use App\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function menu()
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id)) {
            return View('app.index', ['service' => 'cite', 'user' => null]);
        }

        // return $user->priv_level < 4 || $user->action->acc_adm == 0 ? "false" : "true";

        if ($user->priv_level < 4 && $user->action->acc_adm == 0)
            return redirect()->back();

        $service = Session::get('service');

        return View::make('app.admin_menu', ['service' => $service, 'user' => $user]);
    }
}
