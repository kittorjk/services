<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCitesAddColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cites', function (Blueprint $table) {
            $table->string('responsable');
            $table->string('para_empresa');
            $table->string('area');
            $table->longtext('asunto');
            $table->integer('num_cite');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cites', function (Blueprint $table) {
            $table->dropColumn('responsable');
            $table->dropColumn('para_empresa');
            $table->dropColumn('area');
            $table->dropColumn('asunto');
            $table->dropColumn('num_cite');
        });
    }
}
