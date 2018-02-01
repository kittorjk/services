<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTechGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tech_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('group_area');
            $table->tinyInteger('group_number');
            $table->integer('group_head_id');
            $table->integer('tech_2_id');
            $table->integer('tech_3_id');
            $table->integer('tech_4_id');
            $table->integer('tech_5_id');
            $table->string('observations',1000);
            $table->boolean('status');
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
        Schema::drop('tech_groups');
    }
}
