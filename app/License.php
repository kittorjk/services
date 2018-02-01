<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    protected $fillable = ['id', 'user_id', 'number', 'exp_date', 'category', 'created_at'];

    public function user(){
        return $this->belongsTo('App\User');
    }
}
