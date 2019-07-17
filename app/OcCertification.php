<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OcCertification extends Model
{
  protected $table = 'oc_certifications';

  protected $fillable = ['id', 'user_id', 'oc_id', 'code', 'amount', 'type_reception', 'num_reception',
    'date_ack', 'date_acceptance', 'date_print_ack', 'observations', 'created_at'];

  public function files() {
    //A certificate can have files uploaded
    return $this->morphMany('App\File','imageable');
  }
  
  public function oc() {
    //A certification associates a OC to a user
    return $this->belongsTo('App\OC', 'oc_id', 'id');
  }
  
  public function user() {
    //A certification associates a OC to a user
    return $this->belongsTo('App\User','user_id','id');
  }

  public function invoices() {
    return $this->hasMany('App\Invoice', 'oc_certification_id', 'id');
  }
}
