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
        Schema::table('client_aggregation', function (Blueprint $table) {
            $table->decimal('account_fee', 10, 2)->default(0)->change();
            $table->decimal('account_usage_rate', 10, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_aggregation', function (Blueprint $table) {
            $table->dropColumn('account_usage_rate', 10, 2)->nullable();
        });
    }
};