<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 11/07/2016
 * Time: 04:22 PM
 */

namespace App;
use Illuminate\Database\Eloquent\Model;


class File extends Model
{
    protected $table = 'files';

    public function imageable(){
        //varios files pertenecen a varios servicios
        return $this->morphTo();
    }

    public function user(){
        //varios archivos pertenecen a un mismo usuario
        return $this->belongsTo('App\User');
    }
}
