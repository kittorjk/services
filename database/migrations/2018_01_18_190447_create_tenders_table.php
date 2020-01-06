<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTendersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('contact_id');
            $table->string('code');
            $table->string('name');
            $table->string('description', 500);
            $table->string('client');
            $table->string('area');
            $table->string('application_details', 1000);
            $table->dateTime('application_deadline');
            $table->boolean('applied');
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
        Schema::drop('tenders');
    }
}
