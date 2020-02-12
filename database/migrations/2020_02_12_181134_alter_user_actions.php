<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUserActions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_actions', function (Blueprint $table) {
            $table->boolean('adm_emp_edt')->after('adm_bch_mod');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_actions', function (Blueprint $table) {
            $table->dropColumn('adm_emp_edt');
        });
    }
}
