<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeadIntervalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dead_intervals', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->dateTime('date_from');
            $table->dateTime('date_to');
            $table->integer('total_days');
            $table->string('reason',5000);
            $table->integer('relatable_id');
            $table->string('relatable_type');
            $table->boolean('closed');
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
        Schema::drop('dead_intervals');
    }
}
