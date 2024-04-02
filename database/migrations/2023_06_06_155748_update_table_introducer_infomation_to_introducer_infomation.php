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
        Schema::table('introducer_infomation', function (Blueprint $table) {
            $table->bigInteger('client_id')->nullable();
            $table->bigInteger('account_contractor_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('introducer_infomation', function (Blueprint $table) {
            $table->dropColumn('client_id')->nullable();
            $table->dropColumn('account_contractor_id')->nullable();
        });
    }
};
