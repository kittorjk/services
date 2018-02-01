<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterOperatorsAddRequirementRelation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('operators', function (Blueprint $table) {
            $table->integer('device_requirement_id')->after('device_id');
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
        Schema::table('operators', function (Blueprint $table) {
            $table->dropColumn('device_requirement_id');
            $table->dropColumn('confirmation_obs');
            $table->dropColumn('date_confirmed');
        });
    }
}
