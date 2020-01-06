<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VehicleRequirement extends Model
{
    protected $fillable = ['id', 'code', 'user_id', 'vehicle_id', 'for_id', 'from_id', 'branch_origin',
        'branch_destination', 'reason', 'status', 'stat_change', 'stat_obs', 'type'];

    public function user(){
        return $this->belongsTo('App\User');
    }

    public function vehicle(){
        return $this->belongsTo('App\Vehicle');
    }

    public function person_for(){
        return $this->belongsTo('App\User','for_id');
    }

    public function person_from(){
        return $this->belongsTo('App\User','from_id');
    }

    public static $types = array(
        'borrow'            => 'Préstamo',
        'transfer_tech'     => 'Transferencia entre personal',
        'transfer_branch'   => 'Transferencia entre sucursales',
        'devolution'        => 'Devolución',
    );

    public static $stat_names = array(
        0 => 'Rechazado',
        1 => 'En proceso',
        2 => 'Completado',
    );
}
