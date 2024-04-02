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
            $table->string('account_number', 255)->default(null)->after('system_usage_fee');
            $table->text('memo')->change()->default(null);
            $table->bigInteger('settlement_fee')->change()->default(0);
            $table->integer('number_refunds')->change()->default(0);
            $table->smallInteger('type_refund')->change()->default(null);
            $table->integer('refund_amount')->change()->default(0);
            $table->integer('system_usage_fee')->change()->default(0);
            $table->integer('refund_fee')->change()->default(0);
            $table->integer('account_balance')->default(0);
            $table->bigInteger('payment_amount')->default(0);
            $table->bigInteger('display_amount')->default(0);
            $table->dropColumn('payment_amout')->nullable();
            $table->dropColumn('acount_balance')->nullable();
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
