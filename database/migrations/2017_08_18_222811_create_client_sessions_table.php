<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_sessions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('service_accessed');
            $table->boolean('status');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `client_sessions` ADD COLUMN `ip_address` VARBINARY(16) NOT NULL AFTER `service_accessed`');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('client_sessions');
    }
}
