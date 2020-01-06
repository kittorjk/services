<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVhcFailureReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vhc_failure_reports', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code');
            $table->integer('user_id');
            $table->integer('vehicle_id');
            $table->integer('maintenance_id');
            $table->string('reason', 1000);
            $table->tinyInteger('status');
            $table->dateTime('date_stat');
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
        Schema::drop('vhc_failure_reports');
    }
}
