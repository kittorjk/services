<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('projects');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('name');
            $table->string('client');
            $table->integer('status');
            $table->integer('asig_file_id');
            $table->string('asig_num');
            $table->integer('asig_deadline');
            $table->integer('quote_file_id');
            $table->decimal('quote_amount',10,2);
            $table->integer('pc_org_id');
            $table->integer('pc_sgn_id');
            $table->integer('pc_deadline');
            $table->decimal('pc__amount',10,2);
            $table->dateTime('ini_date');
            $table->string('ini_obs');
            $table->integer('matsh_org_id');
            $table->integer('matsh_sgn_id');
            $table->integer('costsh_org_id');
            $table->integer('costsh_sgn_id');
            $table->decimal('costsh_amount',10,2);
            $table->integer('qcc_file_id');
            $table->integer('bill_number');
            $table->dateTime('bill_date');
            $table->integer('wty_file_id');
            $table->integer('sch_file_id');
            $table->timestamps();
        });
    }
}
