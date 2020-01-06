<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterGuaranteesTableAddColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('guarantees', function (Blueprint $table) {
            $table->string('guaranteeable_type')->after('guaranteeable_id');
            $table->string('code')->after('guaranteeable_type');
            $table->string('company')->after('code');
            $table->string('type')->after('company');
            $table->dateTime('start_date')->after('type');
            $table->boolean('closed')->after('expiration_date');
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
            $table->dropColumn('guaranteeable_type');
            $table->dropColumn('code');
            $table->dropColumn('company');
            $table->dropColumn('type');
            $table->dropColumn('start_date');
            $table->dropColumn('closed');
        });
    }
}
