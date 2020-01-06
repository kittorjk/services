<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterOcsAddLinkedColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('o_c_s', function (Blueprint $table) {
            $table->integer('link_id')->after('pm_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('o_c_s', function (Blueprint $table) {
            $table->dropColumn('link_id');
        });
    }
}
