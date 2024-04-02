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
        Schema::table('client_aggregation', function (Blueprint $table) {
            $table->bigInteger('account_fee')->default(0)->after('charge_amount');
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
            $table->dropColumn('account_fee')->nullable();
        });
    }
};
