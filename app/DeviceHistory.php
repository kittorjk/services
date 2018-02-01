<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeviceHistory extends Model
{
    protected $table = 'device_histories';

    protected $fillable = ['id', 'device_id', 'type', 'contents', 'status', 'historyable_id', 'historyable_type',
        'created_at'];

    public function device(){
        //Several records belong to a device
        return $this->belongsTo('App\Device');
    }

    public function historyable(){
        //a record can be related to different dbs
        return $this->morphTo();
    }
}
