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
            $table->string('representative_name', 250)->nullable()->change();
            $table->string('email', 250)->nullable()->change();
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
            //
        });
    }
};
