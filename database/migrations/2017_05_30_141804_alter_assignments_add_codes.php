<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAssignmentsAddCodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->string('literal_code')->after('code');
            $table->string('client_code')->after('literal_code');
            $table->string('sub_type')->after('type');
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
            $table->dropColumn('literal_code');
            $table->dropColumn('client_code');
            $table->dropColumn('sub_type');
        });
    }
}
