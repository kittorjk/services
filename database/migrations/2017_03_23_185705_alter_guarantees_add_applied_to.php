<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterGuaranteesAddAppliedTo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('guarantees', function (Blueprint $table) {
            $table->string('applied_to')->after('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('guarantees', function (Blueprint $table) {
            $table->dropColumn('applied_to');
        });
    }
}
