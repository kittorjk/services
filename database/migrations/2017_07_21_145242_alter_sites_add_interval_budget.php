<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSitesAddIntervalBudget extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dateTime('start_line')->after('contact_id');
            $table->dateTime('deadline')->after('start_line');
            $table->decimal('budget',10,2)->after('percentage_completed');
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
            $table->dropColumn('start_line');
            $table->dropColumn('deadline');
            $table->dropColumn('budget');
        });
    }
}
