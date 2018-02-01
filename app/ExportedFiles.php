<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExportedFiles extends Model
{
    protected $table = 'exported_files';

    protected $fillable = ['id', 'user_id', 'url', 'description', 'exportable_id', 'exportable_type'];

    public function exportable(){
        //Many records belong to many models
        return $this->morphTo();
    }

    public function user(){
        return $this->belongsTo('App\User');
    }
}
