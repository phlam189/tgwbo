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
        Schema::create('client_contract_detail', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->integer('client_id');
            $table->tinyInteger('service_type')->default(0)->comment('1:Deposit 2:Withdraw 3:Both');
            $table->tinyInteger('is_minimun_charge')->comment('1: yes 2: no');
            $table->tinyInteger('is_transfer_fee')->comment('1: yes 2: no');
            $table->decimal('charge_fee_rate', 10 , 0)->comment('振込金額の日本円換算の％');
            $table->string('charge_fee_memo',250)->comment('BTC:x%/ETH:x%/USDT:x%/USDC:x%');
            $table->decimal('settlement_fee_rate',10, 0)->comment('決済金額の日本円換算の％');
            $table->string('settlement_fee_memo', 250)->comment('BTC:x%/ETH:x%/USDT:x%/USDC:x%');
            $table->date('contract_date');
            $table->decimal('contract_rate', 10, 0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_contract_detail');
    }
};
