<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CorpLine extends Model
{
    protected $fillable = ['id', 'number', 'service_area', 'technology', 'pin', 'puk', 'avg_consumption',
        'credit_assigned', 'status', 'responsible_id', 'observations', 'flags'];

    public function files(){
        return $this->morphMany('App\File','imageable');
    }

    public function assignations(){
        return $this->hasMany('App\CorpLineAssignation', 'corp_line_id', 'id');
    }
    
    public function responsible(){
        return $this->belongsTo('App\User','responsible_id');
    }

    public function last_assignation(){
        return $this->hasOne('App\CorpLineAssignation', 'corp_line_id', 'id')->orderBy('id','desc');
    }
}
