<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    protected $fillable = ['id', 'user_id', 'code', 'name', 'assignment_id', 'status', 'site_type', 'work_type',
        'origin_name', 'latitude', 'longitude', 'destination_name', 'lat_destination', 'long_destination',
        'department', 'municipality', 'type_municipality', 'resp_id', 'contact_id', 'start_line', 'deadline',
        'start_date', 'end_date', 'percentage_completed', 'budget', 'vehicle_dev_cost', 'quote_price', 'executed_price',
        'assigned_price', 'charged_price', 'observations', 'created_at'];

    protected $touches = ['assignment'];
    
    public function files(){
        return $this->morphMany('App\File','imageable');
    }

    public function assignment(){
        //Several sites can belong to a single assignment
        return $this->belongsTo('App\Assignment');
    }

    public function user(){
        return $this->belongsTo('App\User');
    }
    
    /*
    public function parameters(){
        return $this->hasOne('App\RbsSiteValue');
    }
    */
    public function rbs_char(){
        return $this->hasOne('App\RbsSiteCharacteristic');
    }
    
    public function contact(){
        //Several sites can be assigned a single contact person
        return $this->belongsTo('App\Contact');
    }
    /*
    public function activities(){
        return $this->hasMany('App\Activity');
    }
    */

    public function tasks(){
        return $this->hasMany('App\Task');
    }

    public function orders(){
        return $this->belongsToMany('\App\Order','order_site','site_id','order_id')
            ->withPivot('assigned_amount','status','created_at','updated_at')
            ->withTimestamps();
    }

    public function responsible(){
        return $this->belongsTo('App\User', 'resp_id', 'id');
    }

    public function events(){
        //A site can have several events
        return $this->morphMany('App\Event','eventable');
    }

    public function dead_intervals(){
        //A Site can have several dead intervals
        return $this->morphMany('App\DeadInterval','relatable');
    }

    /*
    public function rbs_viatics(){
        return $this->belongsToMany('\App\RbsViatic', 'rbs_viatic_site', 'site_id', 'rbs_viatic_id')
            ->withPivot('cost_applied','status','created_at','updated_at')
            ->withTimestamps();
    }
    */

    public function stipend_requests(){
        return $this->belongsToMany('App\StipendRequest', 'site_stipend_request', 'site_id',
            'stipend_request_id')
            ->withTimestamps();
    }

    public static $status_options = array(
        0 => 'No asignado',
        1 => 'Relevamiento',
        2 => 'Cotización',
        3 => 'Ejecución',
        4 => 'Elaboración de informes',
        5 => 'ATP',
        6 => 'Certificación (control de calidad)',
        7 => 'Aplicado',
        8 => 'Facturado',
        9 => 'Cobro',
        10 => 'Concluído',
    );
    
    public function statuses($value){
        $status_options = Site::$status_options;
        
        return $status_options[$value];
        /*
        $statuses = array();

        $statuses[0] = 'No asignado';
        $statuses[1] = 'Relevamiento';
        $statuses[2] = 'Cotización';
        $statuses[3] = 'Ejecución';
        $statuses[4] = 'Elaboración de informes';
        $statuses[5] = 'ATP';
        $statuses[6] = 'Certificación (Control de calidad)';
        $statuses[7] = 'Cobro';
        $statuses[8] = 'Concluído';

        return $statuses[$value];
        */
    }

    public function status_number($value){
        $status_options = Site::$status_options;
        
        $status_keys = array_flip($status_options);
        
        return $status_keys[$value];
        /*
        $stats = array();
        $stats['No signado'] = 0;
        $stats['Relevamiento'] = 1;
        $stats['Cotización'] = 2;
        $stats['Ejecución'] = 3;
        $stats['Elaboración de informes'] = 4;
        $stats['ATP'] = 5;
        $stats['Certificación (Control de calidad)'] = 6;
        $stats['Cobro'] = 7;
        $stats['Concluído'] = 8;

        return $stats[$value];
        */
    }

    public function last_stat(){
        $status_options = Site::$status_options;
        
        end($status_options);
        return key($status_options);
        
        //return 8; //Number of the last status existent (Concluído)
    }
}
