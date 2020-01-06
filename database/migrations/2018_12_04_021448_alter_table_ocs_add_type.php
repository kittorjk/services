<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableOcsAddType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('o_c_s', function (Blueprint $table) {
        $table->string('type')->after('provider');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('o_c_s', function (Blueprint $table) {
        $table->dropColumn('type');
      });
    }
}
