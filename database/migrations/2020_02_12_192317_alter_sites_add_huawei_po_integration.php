<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSitesAddHuaweiPoIntegration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->string('du_id')->after('observations');
            $table->string('isdp_account')->after('du_id');
            $table->integer('order_id')->unsigned()->after('isdp_account');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn('du_id');
            $table->dropColumn('isdp_account');
            $table->dropColumn('order_id');
        });
    }
}
