<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tender extends Model
{
    protected $fillable = ['user_id', 'contact_id', 'code', 'name', 'description', 'client', 'area',
        'application_details', 'application_deadline', 'applied', 'status'];

    public function files(){
        return $this->morphMany('App\File','imageable');
    }

    public function user(){
        return $this->belongsTo('App\User');
    }

    public function contact(){
        return $this->hasOne('App\Contact', 'id','contact_id');
    }

    public function project(){
        return $this->hasOne('App\Project', 'tender_id', 'id');
    }
}
