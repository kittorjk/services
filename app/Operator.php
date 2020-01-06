<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Operator extends Model
{
    protected $fillable = ['id', 'user_id', 'device_id', 'device_requirement_id', 'who_delivers', 'who_receives',
        'date', 'project_id', 'project_type', 'destination', 'reason', 'observations', 'confirmation_flags',
        'confirmation_obs', 'date_confirmed'];

    public function files(){
        //An operator can add several files as a backup
        return $this->morphMany('App\File','imageable');
    }

    public function device(){
        //Several operators are assigned to a single device
        return $this->belongsTo('App\Device');
    }

    public function requirement(){
        return $this->belongsTo('App\DeviceRequirement', 'device_requirement_id');
    }
    
    public function deliverer(){
        return $this->belongsTo('App\User','who_delivers');
    }

    public function receiver(){
        return $this->belongsTo('App\User','who_receives');
    }

    public function assignment(){
        //Several records reference a single project
        return $this->belongsTo('App\Assignment','project_id');
    }

    public function user(){
        return $this->belongsTo('App\User');
    }
}
