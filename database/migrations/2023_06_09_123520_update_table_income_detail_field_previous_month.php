<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('income_expenditure_detail', function (Blueprint $table) {
            $table->decimal('previous_month', 15, 2)->default(0);
            $table->date('payment_status')->nullable(0);
        });
        Schema::table('income_expenditure', function (Blueprint $table) {
            $table->dropColumn('previous_month');
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
