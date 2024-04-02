<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_aggregation', function (Blueprint $table) {
            $table->bigInteger('settlement_amount')->default(0)->after('amount');
            $table->bigInteger('charge_amount')->default(0)->after('refund_fee');
            $table->bigInteger('charge_fee')->default(0)->after('charge_amount');
            $table->dropColumn('payment_amount')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
