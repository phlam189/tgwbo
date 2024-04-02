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
            $table->text('memo_internal')->default(null)->after('memo');
            $table->integer('type_client_aggregation')->default(0)->after('type');
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
            $table->dropColumn('memo_internal');
        });
    }
};
