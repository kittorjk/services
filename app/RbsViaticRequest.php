<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RbsViaticRequest extends Model
{
    protected $fillable = ['id', 'rbs_viatic_id', 'technician_id', 'num_days', 'viatic_amount', 'departure_cost',
        'return_cost', 'extra_expenses', 'total_deposit', 'status'];
    
    public function viatic(){
        return $this->belongsTo('App\RbsViatic','id','rbs_viatic_id');
    }
    
    public function technician(){
        return $this->belongsTo('App\User');
    }
}
