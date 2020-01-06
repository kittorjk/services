<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRbsViaticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rbs_viatics', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('type');
            $table->tinyInteger('num_sites');
            $table->tinyInteger('num_technicians');
            $table->string('work_description');
            $table->dateTime('date_from');
            $table->dateTime('date_to');
            $table->string('type_transport');
            $table->tinyInteger('departure_qty');
            $table->decimal('departure_cost_unit',6,2);
            $table->tinyInteger('return_qty');
            $table->decimal('return_cost_unit',6,2);
            $table->tinyInteger('vehicle_rent_days');
            $table->decimal('vehicle_rent_cost_day',6,2);
            $table->decimal('extra_expenses',8,2);
            $table->string('extra_expenses_detail',1000);
            $table->decimal('viatic_amount',6,2);
            $table->decimal('materials_cost',8,2);
            $table->string('materials_detail',1000);
            $table->tinyInteger('status');
            $table->decimal('sub_total_workforce',10,2);
            $table->decimal('sub_total_viatic',10,2);
            $table->decimal('pm_cost',8,2);
            $table->decimal('social_benefits',10,2);
            $table->decimal('work_supplies',8,2);
            $table->decimal('total_workforce',10,2);
            $table->decimal('sub_total_transport',10,2);
            $table->decimal('minor_tools_cost',6,2);
            $table->decimal('total_cost',10,2);
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
        Schema::drop('rbs_viatics');
    }
}
