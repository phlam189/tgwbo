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
        Schema::create('income_expenditure_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('income_expenditure_id');
            $table->string('item_name')->nullable();
            $table->string('client_id')->nullable();
            $table->string('type')->comment('1: deposit, 2: withdrawals, 3: Miscellaneous, 4: Expenses');
            $table->string('type_fee')->default(0)->comment('1: account fee deposit,2: account fee withdrawal, 3: outsourcing fee, 4: referal fee 1, 5: referal fee 2, 6: other fee');
            $table->tinyInteger('is_manual')->default(0);
            $table->text('memo')->nullable();
            $table->decimal('rate', 15, 2)->default(0);
            $table->integer('number_transaction')->default(0);
            $table->decimal('amount', 15, 2);
            $table->decimal('profit', 15, 2);

            $table->foreign('income_expenditure_id')->references('id')->on('income_expenditure')->onDelete('cascade');
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
        Schema::dropIfExists('income_expenditure_detail');
    }
};
