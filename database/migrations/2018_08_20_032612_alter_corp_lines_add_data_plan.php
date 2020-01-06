<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCorpLinesAddDataPlan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('corp_lines', function (Blueprint $table) {
            $table->string('data_plan')->after('puk');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('corp_lines', function (Blueprint $table) {
            $table->dropColumn('data_plan');
        });
    }
}
