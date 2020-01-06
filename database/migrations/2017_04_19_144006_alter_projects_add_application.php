<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterProjectsAddApplication extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->integer('contact_id')->after('user_id');
            $table->string('application_details',500)->after('award');
            $table->dateTime('application_deadline')->after('application_details');
            $table->boolean('applied')->after('application_deadline');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('contact_id');
            $table->dropColumn('application_details');
            $table->dropColumn('application_deadline');
            $table->dropColumn('applied');
        });
    }
}
