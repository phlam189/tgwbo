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
        Schema::dropIfExists('account_balance_history');
        Schema::create('account_balance_history', function (Blueprint $table) {
            $table->bigInteger('id')->autoIncrement();
            $table->string('account_number', 100);
            $table->decimal('balance', 12,2)->default(0);
            $table->dateTime('date_history')->nullable();
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
        //
    }
};
