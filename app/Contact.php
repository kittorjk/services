<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = ['id', 'name', 'company', 'position', 'phone_1', 'phone_2', 'email', 'password', 'created_at'];
    
    protected $hidden = ['password', 'remember_token'];

    public function sites(){
        return $this->hasMany('App\Site');
    }

    public function assignments(){
        return $this->hasMany('App\Assignment');
    }
}
