<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterBillOrderAddChargedAmount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bill_order', function (Blueprint $table) {
            $table->decimal('charged_amount',10,2)->after('order_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bill_order', function (Blueprint $table) {
            $table->dropColumn('charged_amount');
        });
    }
}
