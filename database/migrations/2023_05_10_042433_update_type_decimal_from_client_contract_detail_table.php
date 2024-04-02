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
            $table->decimal('charge_fee_rate', 10, 1)->comment('振込金額の日本円換算の％')->change();
            $table->decimal('settlement_fee_rate', 10, 1)->comment('決済金額の日本円換算の％')->change();
            $table->decimal('contract_rate', 10, 1)->change();
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
