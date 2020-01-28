<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCiteCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cite_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 10);
            $table->string('area');
            $table->integer('branch_id');
            $table->boolean('status');
            $table->integer('usuario_creacion');
            $table->integer('usuario_modificacion');
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
        Schema::drop('cite_codes');
    }
}
