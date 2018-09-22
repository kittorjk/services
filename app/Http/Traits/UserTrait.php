<?php
/**
 * User: Admininstrador
 * Date: 12/09/2018
 * Time: 18:41 PM
 */

namespace App\Http\Traits;

//use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\ClientSession;
use App\User;
use Exception;

trait UserTrait {
    
    public function trackService($user, $service) {
        $open_sessions = ClientSession::where('status', 0)->where('user_id', $user->id)->get();

        foreach ($open_sessions as $open_session) {
            if (strpos($open_session->service_accessed, $service) === false) {
                if ($open_session->service_accessed !== '') {
                    $open_session->service_accessed .= ', '.$service;
                } else {
                    $open_session->service_accessed = $service;
                }
                $open_session->save();
            }
        }
    }
    
}
