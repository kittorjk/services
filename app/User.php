<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'full_name', 'login', 'password', 'branch_id', 'branch', 'area', 'work_type', 'role',
        'rank', 'cost', 'cost_day', 'phone', 'email', 'priv_level', 'acc_cite', 'acc_oc', 'acc_project', 'acc_active',
        'acc_warehouse', 'acc_staff', 'status'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    public function license(){
        //One user can have one driver license record
        return $this->hasOne('App\License');
    }
    
    public function registered_sessions(){
        return $this->hasMany('App\ClientSession');
    }
    
    public function open_sessions(){
        return $this->hasMany('App\ClientSession')->where('status', 0);
    }

    public function branch_record(){
        return $this->hasOne('App\Branch', 'id', 'branch_id');
    }

    public function action(){
        return $this->hasOne('App\UserAction');
    }

    public function employee() {
      return $this->belongsTo('App\Employee', 'id', 'access_id');
    }
}
