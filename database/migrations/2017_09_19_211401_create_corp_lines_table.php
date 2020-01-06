<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCorpLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('corp_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('number');
            $table->string('service_area');
            $table->string('technology');
            $table->integer('pin');
            $table->string('puk');
            $table->decimal('avg_consumption',6,2);
            $table->decimal('credit_assigned',6,2);
            $table->string('status');
            $table->integer('responsible_id');
            $table->string('observations', 1000);
            // $table->integer('flags');
            $table->char('flags',4);
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
        Schema::drop('corp_lines');
    }
}
