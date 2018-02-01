<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSitesAddLocationColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->decimal('latitude',10,7)->after('status');
            $table->decimal('longitude',10,7)->after('latitude');
            $table->string('municipality')->after('longitude');
            $table->string('type_municipality',6)->after('municipality');
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
            $table->dropColumn('latitude');
            $table->dropColumn('longitude');
            $table->dropColumn('municipality');
            $table->dropColumn('type_municipality');
        });
    }
}
