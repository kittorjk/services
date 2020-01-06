<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceParameter extends Model
{
    protected $table = 'service_parameters';

    protected $fillable = ['id', 'user_id', 'name', 'group', 'description', 'literal_content', 'numeric_content',
        'units', 'created_at'];

    public function user(){
        return $this->belongsTo('App\User','user_id','id');
    }
}
