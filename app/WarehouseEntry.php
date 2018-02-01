<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WarehouseEntry extends Model
{
    protected $fillable = ['id', 'user_id', 'warehouse_id', 'material_id', 'date', 'qty', 'received_by', 'received_id',
        'delivered_by', 'delivered_id', 'reason', 'entry_type', 'created_at'];

    public function files(){
        //A warehouse entry can have documents and images uploaded
        return $this->morphMany('App\File','imageable');
    }

    public function user(){
        //Person who created the record
        return $this->belongsTo('App\User');
    }
    
    public function warehouse(){
        return $this->belongsTo('App\Warehouse');
    }

    public function material(){
        return $this->belongsTo('App\Material');
    }

    public function receiver(){
        return $this->belongsTo('App\User','received_id');
    }
    
    public function deliverer(){
        return $this->belongsTo('App\User','delivered_id');
    }

    public function events(){
        //An event is recorded for any action on the system
        return $this->morphMany('App\Event','eventable');
    }
}
