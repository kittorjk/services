<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['id', 'user_id', 'site_id', 'item_id', 'number', 'code', 'name', 'description', 
        'pondered_weight', 'total_expected', 'units', 'progress', 'status', 'responsible', 'quote_price',
        'executed_price', 'assigned_price', 'charged_price', 'start_date', 'end_date', 'additional'];

    protected $touches = ['site'];
    
    public function site(){
        return $this->belongsTo('App\Site');
    }

    public function item(){
        return $this->belongsTo('App\Item');
    }
    
    public function activities(){
        return $this->hasMany('App\Activity');
    }

    public function person_responsible(){
        return $this->belongsTo('App\User','responsible');
    }

    public function user(){
        return $this->belongsTo('App\User');
    }

    public function events(){
        return $this->morphMany('App\Event','eventable');
    }

    public function summary_category(){
        return $this->hasOne('App\TaskSummaryCategory', 'task_id');
    }

    public static $status_options = array(
        0 => 'No asignado',
        1 => 'Relevamiento',
        2 => 'Cotización',
        3 => 'Ejecución',
        4 => 'Elaboración de informes',
        5 => 'ATP',
        6 => 'Certificación (control de calidad)',
        7 => 'Cobro',
        8 => 'Concluído',
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
