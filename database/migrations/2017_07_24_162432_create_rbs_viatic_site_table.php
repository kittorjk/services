<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRbsViaticSiteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rbs_viatic_site', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('rbs_viatic_id')->unsigned();
            $table->foreign('rbs_viatic_id')->references('id')->on('rbs_viatics');
            $table->integer('site_id')->unsigned();
            $table->foreign('site_id')->references('id')->on('sites');
            $table->decimal('cost_applied',10,2);
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
        Schema::drop('rbs_viatic_site');
    }
}
