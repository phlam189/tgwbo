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
        Schema::table('contructor', function (Blueprint $table) {
            $table->boolean('presence')->default(0)->comment('1:presence 0:absence')->change();
            $table->boolean('existence')->default(0)->comment('1: yes 0: no')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contructor', function (Blueprint $table) {
            //
        });
    }
};
