<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('prov_name');
            $table->bigInteger('nit', 20);
            $table->integer('phone_number');
            $table->integer('alt_phone_number');
            $table->string('address');
            $table->string('bnk_account');
            $table->string('bnk_name');
            $table->string('contact_name');
            $table->integer('contact_id');
            $table->char('contact_id_place',4);
            $table->integer('contact_phone');
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
        Schema::drop('providers');
    }
}
