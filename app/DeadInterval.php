<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeadInterval extends Model
{
    protected $table = 'dead_intervals';

    protected $fillable = ['id', 'user_id', 'date_from', 'date_to', 'total_days', 'reason', 'relatable_id',
        'relatable_type', 'closed', 'created_at'];

    public function files(){
        return $this->morphMany('App\File','imageable');
    }
    
    public function relatable(){
        //a record can be related to different tables
        return $this->morphTo();
    }

    public function user(){
        return $this->belongsTo('App\User');
    }
}
