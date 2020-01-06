<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['id', 'user_id', 'code', 'type', 'client', 'payment_percentage', 'number_of_sites',
        'assigned_price', 'charged_price', 'date_issued', 'status', 'date_charged', 'detail', 'created_at'];
    
    public function files(){
        return $this->morphMany('App\File','imageable');
    }

    public function sites(){
        return $this->belongsToMany('\App\Site')->withPivot('assigned_amount','status','created_at','updated_at')
            ->withTimestamps();
    }

    public function bills(){
        return $this->belongsToMany('\App\Bill','bill_order','order_id','bill_id')
            ->withPivot('charged_amount','status','created_at','updated_at')
            ->withTimestamps();
    }
    
    public function user(){
        return $this->belongsTo('App\User');
    }
}
