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
        Schema::table('charge_history', function (Blueprint $table) {
            $table->decimal('payment_amount', 10, 1)->default(0)->change();
            $table->decimal('transfer_amount', 10, 1)->default(0)->change();
            $table->decimal('charge_fee', 10, 1)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('charge_history', function (Blueprint $table) {
            //
        });
    }
};
