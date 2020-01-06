<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterVehiclesAddColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('owner')->after('model');
            $table->string('branch')->after('owner');
            $table->string('destination')->after('responsible');
            $table->integer('main_pic_id')->after('flags');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('owner');
            $table->dropColumn('branch');
            $table->dropColumn('destination');
            $table->dropColumn('main_pic_id');
        });
    }
}
