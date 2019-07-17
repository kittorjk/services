<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
  protected $fillable = ['id', 'user_id', 'oc_id', 'oc_certification_id', 'number', 'amount',
    'date_issued', 'transaction_code', 'transaction_date', 'flags', 'concept', 'status', 'detail',
    'created_at'];

  protected $touches = ['oc'];
  
  public function oc() {
    return $this->belongsTo('App\OC', 'oc_id', 'id');
  }

  public function oc_certification() {
    return $this->belongsTo('App\OcCertification', 'oc_certification_id', 'id');
  }

  public function files() {
    return $this->morphMany('App\File','imageable');
  }

  public function events() {
    return $this->morphMany('App\Event','eventable');
  }
  
  public function user() {
    return $this->belongsTo('App\User');
  }
}
