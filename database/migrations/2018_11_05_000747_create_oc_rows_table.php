<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOcRowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('oc_rows', function (Blueprint $table) {
        $table->increments('id');
        $table->integer('user_id');
        $table->integer('oc_id');
        $table->integer('num_order');
        $table->string('description');
        $table->decimal('qty', 10, 2);
        $table->string('units', 20);
        $table->decimal('unit_cost', 10, 2);
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
      Schema::drop('oc_rows');
    }
}
