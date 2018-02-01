<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $fillable = ['id', 'user_id', 'project_id', 'code', 'literal_code', 'client_code', 'name', 'client',
        'status', 'resp_id', 'contact_id', 'start_line', 'deadline', 'quote_from', 'quote_to', 'start_date', 'end_date',
        'billing_from', 'billing_to', 'type', 'sub_type', 'type_award', 'percentage_completed', 'quote_price',
        'executed_price', 'assigned_price', 'charged_price', 'observations', 'branch_id', 'branch', 'created_at'];

    public function files(){
        //un Proyecto o asignación tiene varios archivos
        return $this->morphMany('App\File','imageable');
    }

    public function sites(){
        return $this->hasMany('App\Site');
    }

    public function guarantees(){
        return $this->morphMany('App\Guarantee','guaranteeable');
    }
    
    public function responsible(){
        /* Se asigna un responsable de la empresa al proyecto */
        return $this->hasOne('App\User', 'id','resp_id');
    }

    public function contact(){
        /* Se asigna un responsable del cliente al proyecto */
        return $this->hasOne('App\Contact', 'id','contact_id');
    }

    public function project(){
        return $this->belongsTo('App\Project');
    }

    public function user(){
        return $this->belongsTo('App\User');
    }
    
    public function dead_intervals(){
        //An Assignation can have several dead intervals
        return $this->morphMany('App\DeadInterval','relatable');
    }

    public function events(){
        //An Assignment can have several events
        return $this->morphMany('App\Event','eventable');
    }
    
    public function branch_record(){
        return $this->hasOne('App\Branch', 'id', 'branch_id');
    }

    public function stipend_requests(){
        return $this->hasMany('App\StipendRequest');
    }

    public static $status_names = array(
        0 => 'No asignado',
        1 => 'Relevamiento',
        2 => 'Cotización',
        3 => 'Ejecución',
        4 => 'Elaboración de informes',
        5 => 'ATP',
        6 => 'Certificación (Control de calidad)',
        7 => 'Cobro',
        8 => 'Concluído',
    );

    public function statuses($value){
        $status_names = Assignment::$status_names;

        return $status_names[$value];
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
        $status_names = Assignment::$status_names;

        $status_numbers = array_flip($status_names);

        return $status_numbers[$value];
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
        $status_names = Assignment::$status_names;
        
        end($status_names);
        return key($status_names);
        
        //return 8; //Number of the last status existent (Concluído)
    }
}
