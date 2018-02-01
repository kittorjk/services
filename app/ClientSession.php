<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientSession extends Model
{
    protected $fillable = ['id', 'user_id', 'service_accessed', 'status', 'ip_address'];

    public function user(){
        return $this->belongsTo('App\User');
    }

    public function getIpAddressAttribute($value)
    {
        return inet_ntop($value);
    }

    public function setIpAddressAttribute($value)
    {
        $this->attributes['ip_address'] = inet_pton($value);
    }
}
