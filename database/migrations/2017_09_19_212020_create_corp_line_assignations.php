<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCorpLineAssignations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('corp_line_assignations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('corp_line_id');
            $table->string('type');
            $table->string('service_area');
            $table->integer('resp_before_id');
            $table->integer('resp_after_id');
            $table->string('observations', 1000);
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
        Schema::drop('corp_line_assignations');
    }
}
