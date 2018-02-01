<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWarehouseOutletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warehouse_outlets', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('warehouse_id');
            $table->integer('material_id');
            $table->dateTime('date');
            $table->decimal('qty',7,2);
            $table->string('received_by');
            $table->integer('received_id');
            $table->string('delivered_by');
            $table->integer('delivered_id');
            $table->string('reason',500);
            $table->string('outlet_type');
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
        Schema::drop('warehouse_outlets');
    }
}
