<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterOcCertificationsAddDatePrintAck extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oc_certifications', function (Blueprint $table) {
            $table->datetime('date_print_ack')->after('date_acceptance');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('oc_certifications', function (Blueprint $table) {
            $table->dropColumn('date_print_ack');
        });
    }
}
