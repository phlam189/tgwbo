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
        Schema::create('account', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->integer('account_id');
            $table->integer('bank_id');
            $table->tinyInteger('service_type')->comment('1:Deposit 2:Withdraw 3:Both');
            $table->string('category_name', 100);
            $table->string('bank_name', 100);
            $table->string('branch_name', 100);
            $table->string('representative_account', 100);
            $table->string('account_number', 100);
            $table->string('acount_holder', 100);
            $table->decimal('commission_rate', 10, 0)->default(0);
            $table->decimal('balance', 10, 0)->default(0);
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
        Schema::dropIfExists('account');
    }
};
