<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
  protected $table = 'employees';

  protected $fillable = ['user_id', 'code', 'first_name', 'last_name', 'birthday', 'id_card',
    'id_extension', 'bnk_account', 'bnk', 'role', 'category', 'area', 'branch_id', 'branch',
    'income', 'basic_income', 'production_bonus', 'payable_amount', 'corp_email', 'ext_email',
    'phone', 'active', 'access_id', 'date_in', 'date_in_employee', 'date_out', 'reason_out'];

  public function access() {
    return $this->hasOne('App\User', 'id', 'access_id');
  }

  public function user() {
    // Who created a record in this table
    return $this->hasOne('App\User');
  }

  public function branch_record() {
    return $this->hasOne('App\Branch', 'id', 'branch_id');
  }
  
  public function files() {
    // Un vehículo puede tener varios archivos
    return $this->morphMany('App\File','imageable');
  }

  public function stipend_requests() {
    return $this->hasMany('App\StipendRequest', 'employee_id', 'id');
  }
}
