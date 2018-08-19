<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOcsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('o_c_s', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('pm_id');
            $table->string('provider');
            $table->string('proy_name');
            $table->string('proy_concept');
            $table->string('proy_description');
            $table->decimal('oc_amount',10,2);
            $table->string('client');
            $table->integer('client_oc');
            $table->string('client_ad');
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
        Schema::drop('o_c_s');
    }
}
