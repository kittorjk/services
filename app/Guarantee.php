<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Guarantee extends Model
{
    protected $fillable = ['id', 'user_id', 'guaranteeable_id', 'guaranteeable_type', 'code', 'company', 'type', 
        'applied_to', 'start_date', 'expiration_date', 'closed', 'created_at'];

    public function files(){
        //A policy can have several files
        return $this->morphMany('App\File','imageable');
    }

    public function guaranteeable(){
        //Different policies belong to different services
        return $this->morphTo();
    }
    /*
    public function assignment(){
        return $this->belongsTo('App\Assignment', 'assignment_id', 'id');
    }
    */

    public function user(){
        return $this->belongsTo('App\User');
    }
}
