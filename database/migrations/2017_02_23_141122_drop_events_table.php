<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('events');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->increments('id');
            $table->date('event_date');
            $table->integer('event_number');
            $table->integer('project_id');
            $table->string('project_site');
            $table->integer('user_id');
            $table->string('brief_description');
            $table->string('detailed_description', 1000);
            $table->string('resp_abr');
            $table->string('resp_client');
            $table->timestamps();
        });
    }
}
