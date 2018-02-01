<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVehicleConditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicle_conditions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('vehicle_id');
            $table->integer('maintenance_id');
            $table->dateTime('last_maintenance');
            $table->decimal('mileage_start',10,2);
            $table->decimal('mileage_end',10,2);
            $table->string('gas_level');
            $table->decimal('gas_filled',5,2);
            $table->decimal('gas_cost',10,2);
            $table->string('gas_bill');
            $table->string('observations');
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
        Schema::drop('vehicle_conditions');
    }
}
