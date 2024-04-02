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
        Schema::table('client', function (Blueprint $table) {
            $table->decimal('charge_fee_rate', 2, 1)->comment('振込金額の日本円換算の％');
            $table->decimal('settlement_fee_rate', 2, 1)->comment('決済金額の日本円換算の％');
            $table->text('charge_fee_memo')->nullable();
            $table->text('settlement_fee_memo')->nullable();
            $table->boolean('is_minimun_charge')->comment('1: yes 0: no')->default(0);
            $table->boolean('is_transfer_fee')->comment('1: yes 0: no')->default(0);
            $table->dateTime('termination_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client', function (Blueprint $table) {
            $table->dropColumn('charge_fee_rate')->nullable();
            $table->dropColumn('settlement_fee_rate')->nullable();
            $table->dropColumn('charge_fee_memo')->nullable();
            $table->dropColumn('settlement_fee_memo')->nullable();
            $table->dropColumn('is_minimun_charge')->nullable();
            $table->dropColumn('is_transfer_fee')->nullable();
            $table->dropColumn('termination_date')->nullable();
        });
    }
};
