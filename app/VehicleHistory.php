<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VehicleHistory extends Model
{
    protected $table = 'vehicle_histories';

    protected $fillable = ['id', 'vehicle_id', 'type', 'contents', 'status', 'historyable_id', 'historyable_type',
        'created_at'];

    public function vehicle(){
        //Several records belong to a vehicle
        return $this->belongsTo('App\Vehicle');
    }

    public function historyable(){
        //a record can be related to different dbs
        return $this->morphTo();
    }
}
