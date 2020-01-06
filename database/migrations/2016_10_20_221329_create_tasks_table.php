<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('site_id')->unsigned();
            $table->foreign('site_id')->references('id')->on('sites');
            $table->integer('number');
            $table->string('name',1000);
            $table->string('description', 500);
            $table->decimal('total_expected',10,2);
            $table->string('units');
            $table->decimal('progress',10,2);
            $table->string('status');
            $table->integer('responsible');
            // $table->integer('quote_price');
            // $table->integer('executed_price');
            // $table->integer('assigned_price');
            // $table->integer('charged_price');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
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
        Schema::drop('tasks');
    }
}
