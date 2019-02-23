<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RendicionRespaldo extends Model
{
    protected $fillable = ['rendicion_id', 'fecha_respaldo', 'tipo_respaldo', 'nit', 'nro_respaldo',
      'codigo_autorizacion', 'codigo_control', 'razon_social', 'detalle', 'corresponde_a', 'monto',
      'valido', 'observaciones', 'estado', 'usuario_creacion', 'usuario_modificacion'];

    public function rendicion() {
        return $this->belongsTo('App\RendicionViatico','rendicion_id','id');
    }

    public function usuario_creacion() {
        return $this->belongsTo('App\User','usuario_creacion','id');
    }

    public function usuario_modificacion() {
        return $this->belongsTo('App\User','usuario_modificacion','id');
    }

    public function files() {
        return $this->morphMany('App\File','imageable');
    }
}
