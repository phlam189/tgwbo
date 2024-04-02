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
        Schema::table('account', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('contructor', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('client', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('account', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('contructor', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('client', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
};
