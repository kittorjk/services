<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OcRow extends Model
{
  protected $table = 'oc_rows';

  protected $fillable = ['id', 'user_id', 'oc_id', 'num_order', 'description', 'qty', 'units',
      'unit_cost', 'created_at'];

  public function oc() {
      // A row belongs to a OC
      return $this->belongsTo('App\OC', 'oc_id', 'id');
  }
  
  public function user() {
      // A row is created by a user
      return $this->belongsTo('App\User','user_id','id');
  }
}
