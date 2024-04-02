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
        Schema::table('client_contract_detail', function (Blueprint $table) {
            $table->dropColumn('charge_fee_rate');
            $table->dropColumn('settlement_fee_rate');
            $table->dropColumn('charge_fee_memo');
            $table->dropColumn('settlement_fee_memo');
            $table->dropColumn('is_transfer_fee');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_contract_detail', function (Blueprint $table) {
            //
        });
    }
};
