<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable = ['id', 'name', 'location', 'created_at'];

    public function files(){
        //Several documents can be uploaded fora warehouse
        return $this->morphMany('App\File','imageable');
    }

    public function entries(){
        return $this->hasMany('App\WarehouseEntry');
    }

    public function outlets(){
        return $this->hasMany('App\WarehouseOutlet');
    }

    public function materials(){
        return $this->belongsToMany('\App\Material','material_warehouse','warehouse_id','material_id')
            ->withPivot('qty','created_at','updated_at')
            ->withTimestamps();
    }

    public function events(){
        //A warehouse can have events recorded
        return $this->morphMany('App\Event','eventable');
    }
}
