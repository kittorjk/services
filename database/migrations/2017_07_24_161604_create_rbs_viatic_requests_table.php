<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRbsViaticRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rbs_viatic_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('rbs_viatic_id');
            $table->integer('technician_id');
            $table->tinyInteger('num_days');
            $table->decimal('viatic_amount',6,2);
            $table->decimal('departure_cost',6,2);
            $table->decimal('return_cost',6,2);
            $table->decimal('extra_expenses',8,2);
            $table->decimal('total_deposit',8,2);
            $table->tinyInteger('status');
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
        Schema::drop('rbs_viatic_requests');
    }
}
