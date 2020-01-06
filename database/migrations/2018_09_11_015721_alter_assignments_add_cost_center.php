<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAssignmentsAddCostCenter extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('assignments', function (Blueprint $table) {
        $table->integer('cost_center')->after('client_code');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('assignments', function (Blueprint $table) {
        $table->dropColumn('cost_center');
      });
    }
}
