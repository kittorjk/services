<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCorpLineAssignationsAddRequirementRelation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('corp_line_assignations', function (Blueprint $table) {
            $table->integer('corp_line_requirement_id')->after('corp_line_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('corp_line_assignations', function (Blueprint $table) {
            $table->dropColumn('corp_line_requirement_id');
        });
    }
}
