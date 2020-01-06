<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = ['id', 'user_id', 'eventable_id', 'eventable_type', 'date', 'date_to', 'number',
        'description', 'detail', 'responsible_id', 'user_generated'];

    public function files(){
        //An event can have several files
        return $this->morphMany('App\File','imageable');
    }

    public function eventable(){
        //Several events belong to several services
        return $this->morphTo();
    }

    public function user(){
        return $this->belongsTo('App\User');
    }

    public function responsible(){
        //Each event has an employee responsible for it
        return $this->belongsTo('App\User', 'responsible_id', 'id');
    }
}
