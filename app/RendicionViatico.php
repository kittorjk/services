<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RendicionViatico extends Model
{
  protected $fillable = ['codigo', 'stipend_request_id', 'nro_rendicion', 'fecha_deposito',
    'monto_deposito', 'total_facturas_validas', 'total_recibos_validos', 'total_rendicion',
    'subtotal_alimentacion', 'subtotal_transporte', 'subtotal_combustible', 'subtotal_taxi',
    'subtotal_comunicaciones', 'subtotal_hotel', 'subtotal_materiales', 'subtotal_extras',
    'monto_sobrante', 'saldo_favor_empresa', 'devuelto_empresa', 'monto_excedente',
    'saldo_favor_persona', 'devuelto_persona', 'observaciones', 'fecha_presentado', 'fecha_estado',
    'estado', 'usuario_creacion', 'usuario_modificacion'];

  public function solicitud() {
      return $this->belongsTo('App\StipendRequest','stipend_request_id','id');
  }

  public function creadoPor() {
      return $this->belongsTo('App\User','usuario_creacion','id');
  }

  public function modificadoPor() {
      return $this->belongsTo('App\User','usuario_modificacion','id');
  }

  public function respaldos() {
    return $this->hasMany('App\RendicionRespaldo', 'rendicion_id', 'id');
  }

  public function events() {
    return $this->morphMany('App\Event','eventable');
  }
}
