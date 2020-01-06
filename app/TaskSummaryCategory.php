<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaskSummaryCategory extends Model
{
    protected $fillable = ['id', 'user_id', 'task_id', 'cat_name'];

    protected $touches = ['task'];

    public function task(){
        return $this->belongsTo('App\Task');
    }
}
