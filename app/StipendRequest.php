<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StipendRequest extends Model
{
    protected $fillable = ['code', 'user_id', 'employee_id', 'assignment_id', 'site_id', 'date_from', 'date_to',
        'in_days', 'per_day_amount', 'total_amount', 'transport_amount', 'gas_amount', 'taxi_amount', 'comm_amount',
        'hotel_amount', 'materials_amount', 'extras_amount', 'additional', 'reason', 'work_area', 'trc_code',
        'observations', 'status', 'xls_gen'];

    public function user(){
        return $this->belongsTo('App\User');
    }

    public function employee(){
        return $this->belongsTo('App\Employee');
    }

    public function assignment(){
        return $this->belongsTo('App\Assignment');
    }

    /*
    public function site(){
        return $this->belongsTo('App\Site');
    }
    */

    public function sites(){
        return $this->belongsToMany('App\Site','site_stipend_request','stipend_request_id','site_id')
            ->withTimestamps();
    }

    public function rendicion_viatico () {
      return $this->hasOne('App\RendicionViatico');
    }

    public static $stats = array(
        'Pending'       => 'Pendiente',
        'Observed'      => 'Observada',
        'Approved_tech' => 'Aprobada',
        'Rejected'      => 'Rechazada',
        'Completed'     => 'Completada',
        'Sent'          => 'Enviada',
        'Documented'    => 'Rendido'
    );
}
