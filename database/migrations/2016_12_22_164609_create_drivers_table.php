<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDriversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('vehicle_id');
            $table->integer('who_delivers');
            $table->integer('who_receives');
            $table->dateTime('date');
            $table->integer('project_id');
            $table->string('project_type');
            $table->string('reason');
            $table->decimal('mileage_before',10,2);
            $table->decimal('mileage_after',10,2);
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
        Schema::drop('drivers');
    }
}
