<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = ['name', 'city', 'address', 'phone', 'alt_phone', 'head_id', 'active'];

    public function head_person(){
        return $this->hasOne('App\Employee', 'id', 'head_id');
    }

    /*
    public function sites(){
        return $this->hasMany('App\Site');
    }

    public function assignments(){
        return $this->hasMany('App\Assignment');
    }
    */
}
