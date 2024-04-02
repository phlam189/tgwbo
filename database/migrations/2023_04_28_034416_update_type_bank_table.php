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
        Schema::table('bank', function (Blueprint $table) {
            $table->bigInteger('client_withdrawal_fee_1')->change();
            $table->bigInteger('client_withdrawal_fee_2')->change();
            $table->bigInteger('contract_withdrawal_fee_1')->change();
            $table->bigInteger('contract_withdrawal_fee_2')->change();
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
