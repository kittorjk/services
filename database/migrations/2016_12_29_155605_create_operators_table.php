<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOperatorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('operators', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('device_id');
            $table->integer('who_delivers');
            $table->integer('who_receives');
            $table->dateTime('date');
            $table->integer('project_id');
            $table->string('project_type');
            $table->string('reason');
            $table->string('observations');
            $table->char('confirmation_flags',4);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('operators');
    }
}
