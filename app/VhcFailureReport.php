<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VhcFailureReport extends Model
{
    protected $fillable = ['id', 'code', 'user_id', 'vehicle_id', 'maintenance_id', 'reason', 'status', 'date_stat'];

    public function files(){
        return $this->morphMany('App\File','imageable');
    }
    
    public function user(){
        return $this->belongsTo('App\User');
    }

    public function vehicle(){
        return $this->belongsTo('App\Vehicle');
    }

    public function maintenance(){
        return $this->belongsTo('App\Maintenance');
    }

    public static $stat_names = array(
        0 => 'Pendiente',
        1 => 'En proceso',
        2 => 'Resuelto',
    );
}
