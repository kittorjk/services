<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterStipendRequestsAddXlsGen extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stipend_requests', function (Blueprint $table) {
            $table->string('xls_gen', 16)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stipend_requests', function (Blueprint $table) {
            $table->dropColumn('xls_gen');
        });
    }
}
