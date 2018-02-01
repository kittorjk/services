<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $table = 'activities';

    protected $fillable = ['id', 'user_id', 'task_id', 'responsible_id', 'number', 'observations',
        'progress', 'date', 'created_at'];

    protected $touches = ['task'];
    
    public function files(){
        //una actividad tiene varios archivos
        return $this->morphMany('App\File','imageable');
    }
    
    public function task(){
        //varias actividades pertenecen a una misma tarea
        return $this->belongsTo('App\Task');
    }

    public function user(){
        return $this->belongsTo('App\User');
    }

    public function responsible(){
        return $this->belongsTo('App\User','responsible_id','id');
    }
}
