<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $fillable = ['id', 'user_id', 'vehicle_id', 'vehicle_requirement_id', 'who_delivers', 'woh_receives',
        'date', 'project_id', 'project_type', 'destination', 'reason', 'mileage_before', 'mileage_after',
        'mileage_traveled', 'observations', 'confirmation_flags', 'confirmation_obs', 'date_confirmed'];

    public function files(){
        //A driver can add several images or files
        return $this->morphMany('App\File','imageable');
    }

    public function user(){
        return $this->belongsTo('App\User');
    }

    public function vehicle(){
        //Several drivers are assigned one vehicle
        return $this->belongsTo('App\Vehicle');
    }

    public function requirement(){
        return $this->belongsTo('App\VehicleRequirement', 'vehicle_requirement_id');
    }
    
    public function deliverer(){
        return $this->belongsTo('App\User','who_delivers');
    }

    public function receiver(){
        return $this->belongsTo('App\User','who_receives');
    }

    public function assignment(){
        return $this->belongsTo('App\Assignment','project_id');
    }
}
