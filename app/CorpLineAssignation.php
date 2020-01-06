<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CorpLineAssignation extends Model
{
    protected $fillable = ['id', 'user_id', 'corp_line_id', 'corp_line_requirement_id', 'service_area', 'resp_before_id',
        'resp_after_id', 'observations'];

    public function files(){
        return $this->morphMany('App\File','imageable');
    }

    public function user(){
        return $this->belongsTo('App\User','user_id');
    }
    
    public function line(){
        return $this->belongsTo('App\CorpLine', 'corp_line_id');
    }

    public function resp_before(){
        return $this->belongsTo('App\User','resp_before_id');
    }

    public function resp_after(){
        return $this->belongsTo('App\User','resp_after_id');
    }

    public function requirement(){
        return $this->belongsTo('App\CorpLineRequirement', 'corp_line_requirement_id');
    }
}
