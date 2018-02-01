<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSitesAddWorkType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->string('site_type')->after('status');
            $table->string('work_type')->after('site_type');
            $table->string('origin_name')->after('work_type');
            $table->string('destination_name')->after('longitude');
            $table->decimal('lat_destination',10,7)->after('destination_name');
            $table->decimal('long_destination',10,7)->after('lat_destination');
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
            $table->dropColumn('site_type');
            $table->dropColumn('work_type');
            $table->dropColumn('origin_name');
            $table->dropColumn('destination_name');
            $table->dropColumn('lat_destination');
            $table->dropColumn('long_destination');
        });
    }
}
