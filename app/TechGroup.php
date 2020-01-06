<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TechGroup extends Model
{
    protected $fillable = ['id', 'group_area', 'group_number', 'group_head_id', 'tech_2_id', 'tech_3_id', 'tech_4_id',
        'tech_5_id', 'observations', 'status'];

    public function group_head(){
        return $this->belongsTo('App\User','group_head_id');
    }
    
    public function tech_2(){
        return $this->belongsTo('App\User','tech_2_id');
    }

    public function tech_3(){
        return $this->belongsTo('App\User','tech_3_id');
    }

    public function tech_4(){
        return $this->belongsTo('App\User','tech_4_id');
    }

    public function tech_5(){
        return $this->belongsTo('App\User','tech_5_id');
    }
}
