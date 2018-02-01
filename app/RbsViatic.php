<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RbsViatic extends Model
{
    protected $fillable = ['id', 'user_id', 'type', 'num_sites', 'num_technicians', 'type_transport', 'departure_qty',
        'departure_cost_unit', 'return_qty', 'return_cost_unit', 'vehicle_rent_days', 'vehicle_rent_cost_day',
        'extra_expenses', 'extra_expenses_detail', 'viatic_amount', 'materials_cost', 'materials_detail', 'date_from',
        'date_to', 'work_description', 'status', 'sub_total_workforce', 'sub_total_viatic', 'pm_cost', 'social_benefits',
        'work_supplies', 'total_workforce', 'sub_total_transport', 'minor_tools_cost', 'total_cost'];
    
    public function technician_requests(){
        return $this->hasMany('App\RbsViaticRequest');
    }
    
    public function sites(){
        // A viatic request can be done for multiple sites
        return $this->belongsToMany('\App\Site','rbs_viatic_site','rbs_viatic_id','site_id')
            ->withPivot('cost_applied','status','created_at','updated_at')
            ->withTimestamps();
    }

    public function user(){
        return $this->belongsTo('App\User','user_id','id');
    }

    public function events(){
        return $this->morphMany('App\Event','eventable');
    }
    
    public function statuses($value){
        $statuses = array();
        $statuses[0] = 'Nueva';
        $statuses[1] = 'Observada';
        $statuses[2] = 'Modificada';
        $statuses[3] = 'Aprobada';
        $statuses[4] = 'Rechazada';
        $statuses[5] = 'Completada';
        $statuses[6] = 'Cancelada';
        
        return $statuses[$value];
    }
}
