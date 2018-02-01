<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Thumbnail extends Model
{
    protected $fillable = ['id', 'name', 'org_name', 'path', 'type', 'size', 'created_at'];
}
