<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeviceHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('device_id');
            $table->string('type');
            $table->string('contents',500);
            $table->string('status');
            $table->integer('historyable_id');
            $table->string('historyable_type');
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
        Schema::drop('device_histories');
    }
}
