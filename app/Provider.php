<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    protected $fillable = ['id', 'prov_name', 'nit', 'specialty', 'phone_number', 'alt_phone_number', 'address', 'bnk_account',
        'bnk_name', 'contact_name', 'contact_id', 'contact_id_place', 'contact_phone', 'fax', 'email', 'created_at'];

    public function ocs(){
        // A provider can have many ocs assigned
        return $this->hasMany('App\OC');
    }
}
