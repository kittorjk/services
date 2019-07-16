<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterEmployeesAddAmounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('employees', function (Blueprint $table) {
        $table->date('birthday')->after('last_name');
        $table->string('category')->after('role');
        $table->decimal('basic_income',8,2)->after('income');
        $table->decimal('production_bonus',8,2)->after('basic_income');
        $table->decimal('payable_amount',8,2)->after('production_bonus');
        $table->dateTime('date_in_employee')->after('date_in');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('employees', function (Blueprint $table) {
        $table->dropColumn('birthday');
        $table->dropColumn('category');
        $table->dropColumn('basic_income');
        $table->dropColumn('production_bonus');
        $table->dropColumn('payable_amount');
        $table->dropColumn('date_in_employee');
      });
    }
}
