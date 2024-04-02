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
            $table->date('contract_date')->after('referral_fee');
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
            $table->dropColumn('contract_date')->nullable();
        });
    }
};
