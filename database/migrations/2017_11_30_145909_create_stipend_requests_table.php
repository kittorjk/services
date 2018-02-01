<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStipendRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stipend_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code');
            $table->integer('user_id');
            $table->integer('employee_id');
            $table->integer('assignment_id');
            $table->integer('site_id');
            $table->dateTime('date_from');
            $table->dateTime('date_to');
            $table->integer('in_days');
            $table->decimal('per_day_amount',8,2);
            $table->decimal('total_amount',8,2);
            $table->decimal('transport_amount',8,2);
            $table->decimal('gas_amount',8,2);
            $table->decimal('taxi_amount',8,2);
            $table->decimal('comm_amount',8,2);
            $table->decimal('hotel_amount',8,2);
            $table->decimal('materials_amount',8,2);
            $table->decimal('extras_amount',8,2);
            $table->decimal('additional',8,2);
            $table->string('reason',500);
            $table->string('work_area');
            $table->string('trc_code');
            $table->string('observations',500);
            $table->string('status');
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
        Schema::drop('stipend_requests');
    }
}
