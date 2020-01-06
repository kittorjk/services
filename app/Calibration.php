<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Calibration extends Model
{
    protected $fillable = ['id', 'user_id', 'device_id', 'date_in', 'date_out', 'detail', 'completed', 'created_at'];

    public function files(){
        //A record can have a certification document
        return $this->morphMany('App\File','imageable');
    }

    public function device(){
        //A record references a device
        return $this->belongsTo('App\Device');
    }

    public function user(){
        //A record is related to the user who created it
        return $this->belongsTo('App\User');
    }
}
