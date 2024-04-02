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
            $table->decimal('settlement_fee_rate', 10, 2)->nullable()->change();
            $table->decimal('charge_fee_rate', 10, 2)->nullable()->change();
            $table->decimal('contract_rate', 10, 2)->nullable()->change();
            $table->integer('contract_method')->nullable()->change();
            $table->bigInteger('usage_fee_amount')->nullable()->change();
            $table->integer('service_type')->nullable()->change();
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
