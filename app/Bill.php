<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    protected $fillable = ['id', 'user_id', 'code', 'billed_price', 'date_issued', 'detail', 'status', 'date_charged',
        'created_at'];

    public function orders(){
        return $this->belongsToMany('\App\Order')->withPivot('charged_amount','status','created_at','updated_at')
            ->withTimestamps();
    }
}
