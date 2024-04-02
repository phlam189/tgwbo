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
        Schema::create('client_aggregation', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->integer('client_id');
            $table->integer('type')->comment('1: deposit 2 withdrawl');
            $table->dateTime('date');
            $table->integer('number_trans');
            $table->bigInteger('amount');
            $table->bigInteger('payment_amout');
            $table->bigInteger('settlement_fee');
            $table->integer('number_refunds')->comment('1-1: 利息ほか入金 1-2 返金（入金）1-3 資金移動 1-4 決済 1-5:入金チャージ 2-1返金（出金）2-2残高調整');
            $table->tinyInteger('type_refund');
            $table->integer('refund_amount');
            $table->integer('refund_fee');
            $table->integer('system_usage_fee');
            $table->integer('acount_balance');
            $table->integer('memo');
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
        Schema::dropIfExists('client_aggregation');
    }
};
