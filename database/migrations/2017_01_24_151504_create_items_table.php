<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->increments('id');
            $table->char('number',8);
            $table->string('description', 1000);
            $table->string('units');
            $table->decimal('cost_unit_central',10,2);
            $table->decimal('cost_unit_remote',10,2);
            $table->string('detail', 1000);
            $table->string('category');
            $table->string('area');
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
        Schema::drop('items');
    }
}
