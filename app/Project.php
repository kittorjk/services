<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = ['user_id', 'contact_id', 'code', 'name', 'description', 'client', 'type', 'award',
        'tender_id', 'application_details', 'application_deadline', 'applied', 'valid_from', 'valid_to', 'status'];

    public function files(){
        //un Proyecto puede tener varios archivos
        return $this->morphMany('App\File','imageable');
    }

    public function assignments(){
        return $this->hasMany('App\Assignment');
    }

    public function user(){
        return $this->belongsTo('App\User');
    }

    public function guarantees(){
        return $this->morphMany('App\Guarantee','guaranteeable');
    }

    public function contact(){
        /* Se asigna un responsable del cliente al proyecto */
        return $this->hasOne('App\Contact', 'id','contact_id');
    }

    public function item_categories(){
        return $this->hasMany('App\ItemCategory');
    }

    public function tender(){
        return $this->belongsTo('App\Tender');
    }
}
