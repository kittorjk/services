<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterOcsAddExectutedAmount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('o_c_s', function (Blueprint $table) {
            $table->decimal('executed_amount',10,2)->after('oc_amount');
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
            $table->dropColumn('executed_amount');
        });
    }
}
