<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterInvoicesAddStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('invoices', function (Blueprint $table) {
        $table->integer('oc_certification_id')->after('oc_id');
        $table->string('concept')->after('flags');
        $table->string('status')->after('concept');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('invoices', function (Blueprint $table) {
        $table->dropColumn('oc_certification_id');
        $table->dropColumn('concept');
        $table->dropColumn('status');
      });
    }
}
