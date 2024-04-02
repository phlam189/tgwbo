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
        Schema::create('invoice', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('contractor_id');
            $table->string('invoice_no');
            $table->date('invoice_date');
            $table->date('due_date');
            $table->decimal('sub_total', 15, 2);
            $table->decimal('discount_amount', 15, 2);
            $table->decimal('tax_rate', 5, 2);
            $table->decimal('total_tax', 15, 2);
            $table->decimal('balance', 15, 2);
            $table->text('memo')->nullable();
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
        Schema::dropIfExists('invoice');
    }
};
