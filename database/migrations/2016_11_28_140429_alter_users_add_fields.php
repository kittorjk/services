<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUsersAddFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('work_type')->after('area');
            $table->string('role')->after('work_type');
            $table->integer('phone')->after('role');
            $table->string('email')->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('work_type');
            $table->dropColumn('role');
            $table->dropColumn('phone');
            $table->dropColumn('email');
        });
    }
}
