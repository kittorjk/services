<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CiteCode extends Model
{
    protected $fillable = ['code', 'area', 'branch_id', 'status', 'usuario_creacion', 'usuario_modificacion', 'created_at'];

    public function branch_record(){
        return $this->hasOne('App\Branch', 'id', 'branch_id');
    }

    public function creadoPor() {
        return $this->belongsTo('App\User','usuario_creacion','id');
    }
  
    public function modificadoPor() {
        return $this->belongsTo('App\User','usuario_modificacion','id');
    }
}
