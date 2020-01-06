<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterOcsAddFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('o_c_s', function (Blueprint $table) {
            $table->decimal('payed_amount',10,2)->after('oc_amount');
            $table->string('percentages')->after('payed_amount');
            $table->string('observations')->after('client_ad');
            $table->char('flags',8)->after('status');
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
            $table->dropColumn('payed_amount');
            $table->dropColumn('percentages');
            $table->dropColumn('observations');
            $table->dropColumn('flags');
        });
    }
}
