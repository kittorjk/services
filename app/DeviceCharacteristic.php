<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeviceCharacteristic extends Model
{
    protected $table = 'device_characteristics';

    protected $fillable = ['id', 'device_id', 'type', 'value', 'units', 'created_at'];

    public function device(){
        return $this->belongsTo('App\Device','device_id','id');
    }
}
