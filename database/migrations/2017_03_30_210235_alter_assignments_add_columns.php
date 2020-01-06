<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAssignmentsAddColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->integer('project_id')->after('user_id');
            $table->dateTime('quote_from')->after('contact_id');
            $table->dateTime('quote_to')->after('quote_from');
            $table->dateTime('billing_from')->after('end_date');
            $table->dateTime('billing_to')->after('billing_from');
            $table->string('type_award')->after('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn('project_id');
            $table->dropColumn('quote_from');
            $table->dropColumn('quote_to');
            $table->dropColumn('billing_from');
            $table->dropColumn('billing_to');
            $table->dropColumn('type_award');
        });
    }
}
