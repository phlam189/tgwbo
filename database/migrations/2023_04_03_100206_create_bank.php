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
        Schema::create('bank', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->string('bank_name', 250);
            $table->bigInteger('client_withdrawal_fee_1');
            $table->bigInteger('client_withdrawal_fee_2');
            $table->bigInteger('contract_withdrawal_fee_1');
            $table->bigInteger('contract_withdrawal_fee_2');
            $table->bigInteger('client_condition_number');
            $table->bigInteger('contract_condition_number');
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
        Schema::dropIfExists('bank');
    }
};
