<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 08/08/2017
 * Time: 06:24 PM
 */

namespace App\Http\Traits;

//use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

trait ProjectTrait {

    public function refresh_task($task)
    {
        if($task->status!=$task->last_stat()/*'Concluído'*/&&$task->status!=0/*'No asignado'*/){
            //$activities = Activity::where('task_id',$task->id)->get();
            $task->progress = 0;

            foreach($task->activities as $activity){
                $task->progress += $activity->progress;
            }

            $task->executed_price = $task->progress*$task->quote_price;
            $task->save();
        }
    }

    public function refresh_site($site)
    {
        if($site->status!=$site->last_stat()/*'Concluído'*/&&$site->status!=0/*'No asignado'*/){
            //$total_tasks_progress = 0;
            //$total_tasks_expected = 0;
            $task_percentage = 0;
            $total_quoted = 0;
            $total_executed = 0;
            $count = 0;

            foreach($site->tasks as $task){

                //$total_tasks_progress += $task->progress*$task->pondered_weight;
                //$total_tasks_expected += $task->total_expected*$task->pondered_weight;
                $task_percentage += (($task->progress/$task->total_expected)*100)*$task->pondered_weight;
                $total_quoted += $task->assigned_price;
                $total_executed += $task->executed_price;
                $count += $task->pondered_weight;
            }

            $site->percentage_completed = $task_percentage/($count==0 ? 1 : $count);
            //$site->percentage_completed = ($total_tasks_progress/$total_tasks_expected)*100;

            //if($site->quote_price==0)
            $site->quote_price = $total_quoted;
            //if($site->assigned_price==0)
            //$site->assigned_price = $total_assigned;

            $site->executed_price = $total_executed;

            foreach ($site->orders as $order) {
                $site->assigned_price += $order->pivot->assigned_amount;
            }

            $site->save();
        }
    }

    public function refresh_assignment($assignment)
    {
        if($assignment->status!=$assignment->last_stat()/*'Concluído'*/&&$assignment->status!=0/*'No asignado'*/){
            $site_percentage = 0;
            $total_quoted = 0;
            $total_executed = 0;
            $total_charged = 0;
            $total_assigned = 0;
            $count = 0;

            foreach($assignment->sites as $site){
                $site_percentage += $site->percentage_completed;
                $total_quoted += $site->quote_price;
                $total_executed += $site->executed_price;
                $total_charged += $site->charged_price;
                $count++;

                foreach ($site->orders as $order) {
                    $site->assigned_price += $order->pivot->assigned_amount;
                }
                $total_assigned += $site->assigned_price;
            }

            $assignment->percentage_completed = $site_percentage/($count==0 ? 1 : $count);

            //if($assignment->quote_price==0)
            $assignment->quote_price = $total_quoted;
            //if($assignment->assigned_price==0)
            $assignment->assigned_price = $total_assigned;

            $assignment->executed_price = $total_executed;
            $assignment->charged_price = $total_charged;

            $assignment->save();
        }
    }

    public function new_stat_task($task, $parent_new_stat)
    {
        if($task->status!=$task->last_stat()/*'Concluído'*/&&$task->status!=0/*'No asignado'*/){

            if($task->status<$parent_new_stat||$parent_new_stat==0){
                $task->status = $parent_new_stat;

                $task->save();

                if($task->status==$task->last_stat()||$task->status==0){
                    foreach($task->activities as $activity){
                        foreach($activity->files as $file){
                            $this->blockFile($file);
                        }
                    }
                }
            }

            /*
            if($parent_new_stat=='Relevamiento'){
                $task->status = 'En espera';
                $task->save();
            }
            elseif($parent_new_stat=='Cotizado'){
                $task->status = 'En espera';
                $task->save();
            }
            elseif($parent_new_stat=='Ejecución'){
                if($task->status=='En espera'){
                    $task->status = 'Ejecución';
                    $task->save();
                }
            }
            elseif($parent_new_stat=='Revisión'){
                $task->status = 'Revisión';
                $task->save();
            }
            elseif($parent_new_stat=='Cobro'){
                $task->status = 'Revisión';
                $task->save();
            }
            elseif($parent_new_stat=='Concluído'){
                $task->status = 'Concluído';
                $task->save();

                foreach($task->activities as $activity){
                    foreach($activity->files as $file){
                        $this->blockFile($file);
                    }
                }
            }
            elseif($parent_new_stat=='No asignado'){
                $task->status = 'No asignado';
                $task->save();

                foreach($task->activities as $activity){
                    foreach($activity->files as $file){
                        $this->blockFile($file);
                    }
                }
            }
            */
        }
    }

    public function new_stat_site($site, $parent_new_stat)
    {
        if($site->status!=$site->last_stat()/*'Concluído'*/&&$site->status!=0/*'No asignado'*/){

            if($site->status<$parent_new_stat||$parent_new_stat==0){
                $site->status = $parent_new_stat;

                $site->save();

                if($site->status==$site->last_stat()||$site->status==0){
                    foreach($site->files as $file){
                        $this->blockFile($file);
                    }
                }

                foreach($site->tasks as $task){
                    $this->new_stat_task($task, $site->status); //Set the status of the child tasks
                }
            }

            /*
            if($parent_new_stat=='Relevamiento'){
                $site->status = 'Relevamiento';
                $site->save();

                foreach($site->tasks as $task){
                    $this->new_stat_task($task, 'Relevamiento'); //Set the status of the child tasks
                }
            }
            elseif($parent_new_stat=='Cotizado'){
                $site->status = 'Cotizado';
                $site->save();

                foreach($site->tasks as $task){
                    $this->new_stat_task($task, 'Cotizado'); //Set the status of the child tasks
                }
            }
            elseif($parent_new_stat=='Ejecución'){
                if($site->status=='Relevamiento'||$site->status=='Cotizado'){
                    $site->status = 'Ejecución';
                    $site->save();

                    foreach($site->tasks as $task){
                        $this->new_stat_task($task, 'Ejecución'); //Set the status of the child tasks
                    }
                }
            }
            elseif($parent_new_stat=='Revisión'){
                $site->status = 'Revisión';
                $site->save();

                foreach($site->tasks as $task){
                    $this->new_stat_task($task, 'Revisión'); //Set the status of the child tasks
                }
            }
            elseif($parent_new_stat=='Cobro'){
                $site->status = 'Cobro';
                $site->save();

                foreach($site->tasks as $task){
                    $this->new_stat_task($task, 'Cobro'); //Set the status of the child tasks
                }
            }
            elseif($parent_new_stat=='Concluído'){
                $site->status = 'Concluído';
                $site->save();

                foreach($site->tasks as $task){
                    $this->new_stat_task($task, 'Concluído'); //Set the status of the child tasks
                }

                foreach($site->files as $file){
                    $this->blockFile($file);
                }
            }
            elseif($parent_new_stat=='No asignado'){
                $site->status = 'No asignado';
                $site->save();

                foreach($site->tasks as $task){
                    $this->new_stat_task($task, 'No asignado'); //Set the status of the child tasks
                }

                foreach($site->files as $file){
                    $this->blockFile($file);
                }
            }
            */
        }
    }
}
