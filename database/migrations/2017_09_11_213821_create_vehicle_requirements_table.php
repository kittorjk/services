<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVehicleRequirementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicle_requirements', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code');
            $table->integer('user_id');
            $table->integer('vehicle_id');
            $table->integer('for_id');
            $table->integer('from_id');
            $table->string('branch_origin',20);
            $table->string('branch_destination',20);
            $table->string('reason',500);
            $table->tinyInteger('status');
            $table->dateTime('stat_change');
            $table->string('stat_obs',500);
            $table->string('type');
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
        Schema::drop('vehicle_requirements');
    }
}
