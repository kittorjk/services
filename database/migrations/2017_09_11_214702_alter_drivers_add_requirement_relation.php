<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterDriversAddRequirementRelation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->integer('vehicle_requirement_id')->after('vehicle_id');
            $table->string('confirmation_obs',500)->after('confirmation_flags');
            $table->dateTime('date_confirmed')->after('confirmation_obs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn('vehicle_requirement_id');
            $table->dropColumn('confirmation_obs');
            $table->dropColumn('date_confirmed');
        });
    }
}
