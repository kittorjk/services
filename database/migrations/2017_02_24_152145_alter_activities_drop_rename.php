<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterActivitiesDropRename extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropForeign('activities_site_id_foreign');
            $table->dropColumn('site_id');
            $table->dropColumn('cite_id');
            $table->dropColumn('oc_id');
            $table->dropColumn('type');
            $table->renameColumn('description','observations');
            $table->renameColumn('start_date','date');
            $table->integer('responsible_id')->after('task_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->integer('site_id')->after('user_id');
            $table->integer('cite_id')->after('task_id');
            $table->integer('oc_id')->after('cite_id');
            $table->string('type')->after('number');
            $table->renameColumn('observations','description');
            $table->renameColumn('date','start_date');
            $table->dropColumn('responsible_id');
        });
    }
}
