<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = ['id', 'serial', 'type', 'model', 'owner', 'branch_id', 'branch', 'value', 'status',
        'responsible', 'destination', 'condition', 'flags', 'main_pic_id', 'created_at'];

    public function files(){
        //un equipo puede tener varios archivos
        return $this->morphMany('App\File','imageable');
    }

    public function main_pic(){
        //relación de un registro a su foto principal
        return $this->hasOne('App\File','id','main_pic_id');
    }

    public function operators(){
        return $this->hasMany('App\Operator', 'device_id', 'id');
    }

    public function maintenances(){
        return $this->hasMany('App\Maintenance', 'device_id', 'id');
    }

    public function last_maintenance(){
        //record of the last maintenance registered for the device
        return $this->hasOne('App\Maintenance', 'device_id', 'id')->orderby('created_at','desc');
    }

    public function user(){
        //indicador de responsable en un determinado tiempo
        return $this->belongsTo('App\User','responsible');
    }

    public function last_operator(){
        return $this->hasOne('App\Operator', 'device_id', 'id')->orderBy('id','desc');
    }

    public function characteristics(){
        return $this->hasMany('App\DeviceCharacteristic', 'device_id', 'id');
    }
    
    /*
    public function scopeLastOperator($query, Device $device)
    {
        return $query->whereHas('operators', function($q) {
            $q->orderBy('updated_at','desc')->first();
        });
        
           /*
            ->join('operators', 'operators.device_id', '=', 'devices.id')
            ->where('devices.id', '=', $device->id)->orderBy('updated_at','desc')->first();

            /*
            ->whereHas('operators', function($q) {
            $q->orderBy('updated_at','desc')->first();
        });
    }
    */
    public function failure_reports(){
        return $this->hasMany('App\DvcFailureReport');
    }
    
    public function branch_record(){
        return $this->hasOne('App\Branch', 'id', 'branch_id');
    }
}
