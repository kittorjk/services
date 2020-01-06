<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTasksAddColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->decimal('quote_price',10,2)->after('responsible');
            $table->decimal('executed_price',10,2)->after('quote_price');
            $table->decimal('assigned_price',10,2)->after('executed_price');
            $table->decimal('charged_price',10,2)->after('assigned_price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('quote_price');
            $table->dropColumn('executed_price');
            $table->dropColumn('assigned_price');
            $table->dropColumn('charged_price');
        });
    }
}
