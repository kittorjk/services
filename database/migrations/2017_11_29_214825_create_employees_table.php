<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('code');
            $table->string('first_name');
            $table->string('last_name');
            $table->integer('id_card');
            $table->char('id_extension',2);
            $table->string('bnk_account');
            $table->string('bnk');
            $table->string('role');
            $table->string('area');
            $table->string('branch');
            $table->decimal('income',8,2);
            $table->string('corp_email');
            $table->string('ext_email');
            $table->integer('phone');
            $table->boolean('active');
            $table->integer('access_id');
            $table->dateTime('date_in');
            $table->dateTime('date_out');
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
        Schema::drop('employees');
    }
}
