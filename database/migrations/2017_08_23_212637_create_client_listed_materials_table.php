<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientListedMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_listed_materials', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('client');
            $table->string('code');
            $table->string('name');
            $table->string('model');
            $table->string('applies_to');
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
        Schema::drop('client_listed_materials');
    }
}
