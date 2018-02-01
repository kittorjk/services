<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $fillable = ['id', 'code', 'name', 'type', 'description', 'units', 'cost_unit',
        'brand', 'supplier', 'category', 'main_pic_id', 'created_at'];

    public function files(){
        //A material can have several pictures
        return $this->morphMany('App\File','imageable');
    }

    public function main_pic(){
        //A material has a main picture
        return $this->hasOne('App\File','id','main_pic_id');
    }
    
    public function warehouses(){
        return $this->belongsToMany('\App\Warehouse','material_warehouse','material_id','warehouse_id')
            ->withPivot('qty','created_at','updated_at')
            ->withTimestamps();
    }
}
