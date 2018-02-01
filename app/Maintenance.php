<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    protected $fillable = ['id', 'user_id', 'date', 'detail', 'active', 'cost', 'vehicle_id', 'device_id', 'type',
        'parameter_id', 'completed', 'created_at'];

    public function files(){
        return $this->morphMany('App\File','imageable');
    }

    public function vehicle(){
        return $this->belongsTo('App\Vehicle');
    }

    public function device(){
        return $this->belongsTo('App\Device');
    }

    public function user(){
        return $this->belongsTo('App\User');
    }
    
    public function parameter(){
        return $this->belongsTo('App\ServiceParameter');
    }
}
