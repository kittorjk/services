<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RbsSiteCharacteristic extends Model
{
    protected $fillable = ['id', 'user_id', 'site_id', 'tech_group_id', 'type_station', 'solution', 'type_rbs',
        'height', 'number_floors'];

    protected $touches = ['site'];

    public function site(){
        return $this->belongsTo('App\Site');
    }

    public function tech_group(){
        return $this->belongsTo('App\TechGroup');
    }

    public function user(){
        return $this->belongsTo('App\User');
    }
}
