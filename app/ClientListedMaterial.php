<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientListedMaterial extends Model
{
    protected $fillable = ['id', 'user_id', 'client', 'code', 'name', 'model', 'applies_to'];
}
