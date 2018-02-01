<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterOcCertificationsAddColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oc_certifications', function (Blueprint $table) {
            $table->string('type_reception')->after('amount');
            $table->integer('num_reception')->after('type_reception');
            $table->dateTime('date_ack')->after('num_reception');
            $table->dateTime('date_acceptance')->after('date_ack');
            $table->string('observations',500)->after('date_acceptance');
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
            $table->dropColumn('type_reception');
            $table->dropColumn('num_reception');
            $table->dropColumn('date_ack');
            $table->dropColumn('date_acceptance');
            $table->dropColumn('observations');
        });
    }
}
