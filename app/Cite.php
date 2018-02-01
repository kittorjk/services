<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cite extends Model
{
	protected $fillable = ['id', 'code', 'title', 'created_at', 'destino', 'responsable', 'para_empresa',
		'area', 'asunto', 'num_cite'];

	public function files(){
		//un Cite tiene varios archivos
		return $this->morphMany('App\File','imageable');
	}
	
}
