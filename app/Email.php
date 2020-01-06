<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    protected $fillable = ['id', 'sent_by', 'sent_to', 'sent_cc', 'subject', 'content', 'success', 'created_at'];
    
}
