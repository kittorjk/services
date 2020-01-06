<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemCategory extends Model
{
    protected $fillable = ['id', 'project_id', 'name', 'description', 'area', 'client', 'status'];

    public function items(){
        return $this->hasMany('App\Item');
    }

    public function project(){
        return $this->belongsTo('App\Project');
    }
}
