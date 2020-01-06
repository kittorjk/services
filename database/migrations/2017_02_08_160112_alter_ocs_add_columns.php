<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterOcsAddColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('o_c_s', function (Blueprint $table) {
            $table->integer('provider_id')->after('pm_id');
            $table->string('delivery_place')->after('client_ad');
            $table->integer('delivery_term')->after('delivery_place');
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
            $table->dropColumn('provider_id');
            $table->dropColumn('delivery_place');
            $table->dropColumn('delivery_term');
        });
    }
}
