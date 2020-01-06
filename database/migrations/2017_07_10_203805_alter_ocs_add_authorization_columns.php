<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterOcsAddAuthorizationColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('o_c_s', function (Blueprint $table) {
            $table->dateTime('auth_tec_date')->after('flags');
            $table->string('auth_tec_code')->after('auth_tec_date');
            $table->dateTime('auth_ceo_date')->after('auth_tec_code');
            $table->string('auth_ceo_code')->after('auth_ceo_date');
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
            $table->dropColumn('auth_tec_date');
            $table->dropColumn('auth_tec_code');
            $table->dropColumn('auth_ceo_date');
            $table->dropColumn('auth_ceo_code');
        });
    }
}
