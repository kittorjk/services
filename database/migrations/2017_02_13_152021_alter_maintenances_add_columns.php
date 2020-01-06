<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterMaintenancesAddColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->decimal('usage',10,2)->after('active');
            $table->string('type')->after('device_id');
            $table->integer('parameter_id')->after('type');
            $table->boolean('completed')->after('parameter_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropColumn('usage');
            $table->dropColumn('type');
            $table->dropColumn('parameter_id');
            $table->dropColumn('completed');
        });
    }
}
