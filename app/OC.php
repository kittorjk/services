<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OC extends Model
{
  protected $fillable = ['id', 'code', 'user_id', 'pm_id', 'link_id', 'provider_id', 'provider',
    'type', 'assignment_id', 'proy_name', 'proy_concept', 'proy_description', 'oc_amount',
    'executed_amount', 'payed_amount', 'percentages', 'client', 'client_oc', 'client_ad',
    'delivery_place', 'delivery_term', 'observations', 'status', 'payment_status', 'flags',
    'auth_tec_date', 'auth_tec_code', 'auth_ceo_date', 'auth_ceo_code', 'created_at'];

  public function files() {
    //One OC has several files
    return $this->morphMany('App\File','imageable');
  }

  public function user() {
    //Several OCs are created by one user
    return $this->belongsTo('App\User');
  }

  public function invoices() {
    return $this->hasMany('App\Invoice', 'oc_id', 'id');
  }

  public function provider_record() {
    //Several OCs have one provider
    return $this->belongsTo('App\Provider', 'provider_id', 'id');
  }
  
  public function certificates() {
    //A OC's amount can be certified by one or more certificates
    return $this->hasMany('App\OcCertification', 'oc_id', 'id');
  }

  public function rows() {
    // A OC can have one or more rows (items)
    return $this->hasMany('App\OcRow', 'oc_id', 'id');
  }

  public function complements() {
    //A OC can be linked to another if the amount of the first exceeds the total available
    return $this->hasMany('App\OC', 'link_id', 'id');
  }
  
  public function linked() {
    //A OC can be linked to another if the amount of the first exceeds the total available
    return $this->belongsTo('App\OC', 'link_id', 'id');
  }

  public function responsible() {
    //A OC has a Project Manager or responsible
    return $this->hasOne('App\User', 'id', 'pm_id');
  }

  public function events() {
    //An Assignment can have several events
    return $this->morphMany('App\Event','eventable');
  }

  public function assignment() {
    return $this->belongsTo('App\Assignment');
  }
}
