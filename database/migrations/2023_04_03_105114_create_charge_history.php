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
        Schema::create('charge_history', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->integer('client_id');
            $table->tinyInteger('type')->comment('1: settlement 2:deposit');
            $table->decimal('payment_amount', 10, 0)->default(0);
            $table->decimal('transfer_amount', 10, 0)->default(0);
            $table->decimal('charge_fee', 10, 0)->default(0);
            $table->text('memo');
            $table->date('create_date');
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
        Schema::dropIfExists('charge_history');
    }
};
