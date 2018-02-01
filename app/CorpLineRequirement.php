<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CorpLineRequirement extends Model
{
    protected $fillable = ['id', 'code', 'user_id', 'for_id', 'reason', 'status', 'stat_change', 'stat_obs'];

    public function user(){
        return $this->belongsTo('App\User');
    }

    public function person_for(){
        return $this->belongsTo('App\User','for_id');
    }

    public static $stat_names = array(
        0 => 'Rechazado',
        1 => 'En proceso',
        2 => 'Completado',
    );
}
