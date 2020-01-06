<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VehicleCondition extends Model
{
    protected $table = 'vehicle_conditions';

    protected $fillable = ['id', 'user_id', 'vehicle_id', 'maintenance_id', 'last_maintenance', 'mileage_start',
        'mileage_end', 'gas_level', 'gas_filled', 'gas_cost', 'gas_bill', 'observations', 'created_at'];

    public function vehicle(){
        //Several condition records belong to a same vehicle
        return $this->belongsTo('App\Vehicle');
    }

    public function maintenance(){
        //Several condition records are associated to one maintenance record
        return $this->belongsTo('App\Maintenance');
    }

    public function user(){
        return $this->belongsTo('App\User','user_id','id');
    }
    
}
