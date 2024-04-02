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
        Schema::create('invoice_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->string('type');
            $table->text('description')->nullable();
            $table->decimal('rate', 15, 2);
            $table->integer('number_transaction');
            $table->decimal('amount', 15, 2);
            $table->decimal('total_amount', 15, 2);
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('invoice')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice_details');
    }
};
