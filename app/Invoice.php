<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = ['id', 'user_id', 'oc_id', 'number', 'amount', 'date_issued', 'transaction_code',
        'transaction_date', 'flags', 'detail', 'created_at'];

    protected $touches = ['oc'];
    
    public function oc(){
        return $this->belongsTo('App\OC', 'oc_id', 'id');
    }

    public function files(){
        return $this->morphMany('App\File','imageable');
    }

    public function events(){
        return $this->morphMany('App\Event','eventable');
    }
    
    public function user(){
        return $this->belongsTo('App\User');
    }
}
