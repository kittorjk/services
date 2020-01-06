<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = ['id', 'license_plate', 'type', 'model', 'owner', 'branch_id', 'branch', 'mileage', 'gas_type',
        'gas_capacity', 'status', 'responsible', 'destination', 'condition', 'flags', 'main_pic_id', 'policy_id',
        'gas_inspection_exp'];

    public function files(){
        //un vehiculo puede tener varios archivos
        return $this->morphMany('App\File','imageable');
    }

    public function vhc_gas_inspection(){
        return $this->hasOne('App\File', 'imageable_id', 'id')->where('name', 'like', 'VGI%');
    }

    public function main_pic(){
        //relación de un registro a su foto principal
        return $this->hasOne('App\File','id','main_pic_id');
    }
    
    public function drivers(){
        return $this->hasMany('App\Driver', 'vehicle_id', 'id');
    }

    public function maintenances(){
        return $this->hasMany('App\Maintenance', 'vehicle_id', 'id');
    }

    public function user(){
        //indicador de responsable en un determinado tiempo
        return $this->belongsTo('App\User','responsible');
    }

    public function condition_records(){
        return $this->hasMany('App\VehicleCondition', 'vehicle_id', 'id');
    }

    public function last_driver(){
        //Get the record of the last driver assignation
        return $this->hasOne('App\Driver', 'vehicle_id', 'id')->orderBy('id','desc');
    }
    
    public function last_maintenance(){
        //record of the last maintenance registered for the vehicle
        return $this->hasOne('App\Maintenance', 'vehicle_id', 'id')->orderby('created_at','desc');
    }

    public function last_mant2500(){
        //last preventive maintenance recorded (each 2500 Km) 
        return $this->hasOne('App\Maintenance', 'vehicle_id', 'id')
            ->where('parameter_id',2)->orderBy('created_at','desc');
    }

    public function last_mant5000(){
        //last preventive maintenance recorded (each 5000 Km)
        return $this->hasOne('App\Maintenance', 'vehicle_id', 'id')
            ->where('parameter_id',3)->orderBy('created_at','desc');
    }

    public function last_mant10000(){
        //last preventive maintenance recorded (each 10000 Km)
        return $this->hasOne('App\Maintenance', 'vehicle_id', 'id')
            ->where('parameter_id',4)->orderBy('created_at','desc');
    }

    public function last_mant20000(){
        //last preventive maintenance recorded (each 20000 Km)
        return $this->hasOne('App\Maintenance', 'vehicle_id', 'id')
            ->where('parameter_id',5)->orderBy('created_at','desc');
    }

    public function policy(){
        //A vehicle can be assigned to a policy
        return $this->belongsTo('App\Guarantee','policy_id','id');
    }

    public function failure_reports(){
        return $this->hasMany('App\VhcFailureReport');
    }

    public function branch_record(){
        return $this->hasOne('App\Branch', 'id', 'branch_id');
    }

    public function requirements(){
        return $this->hasMany('App\VehicleRequirement');
    }
}
