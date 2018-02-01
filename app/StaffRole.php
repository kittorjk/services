<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StaffRole extends Model
{
    protected $fillable = ['user_id', 'code', 'name', 'description', 'in_use'];

    public function system_users(){
        $this->hasMany('App\User', 'role', 'code');
    }
}
