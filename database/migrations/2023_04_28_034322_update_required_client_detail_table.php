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
            $table->text('is_minimun_charge')->nullable()->change();
            $table->text('is_transfer_fee')->nullable()->change();
            $table->text('charge_fee_rate')->nullable()->change();
            $table->text('charge_fee_memo')->nullable()->change();
            $table->text('settlement_fee_rate')->nullable()->change();
            $table->text('settlement_fee_memo')->nullable()->change();
            $table->text('contract_date')->nullable()->change();
            $table->text('contract_rate')->nullable()->change();
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
