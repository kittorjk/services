<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = ['id', 'number', 'client_code', 'description', 'units', 'cost_unit_central', 'detail',
        'category', 'item_category_id', 'subcategory', 'area'];

    public function tasks(){
        //An item is related to ono or many tasks
        return $this->hasMany('App\Task');
    }
    
    public function item_category(){
        return $this->belongsTo('App\ItemCategory');
    }
}
