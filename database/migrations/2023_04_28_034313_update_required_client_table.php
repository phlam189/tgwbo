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
        Schema::table('client', function (Blueprint $table) {
            $table->text('address')->nullable()->change();
            $table->text('license_number')->nullable()->change();
            $table->text('total_year')->nullable()->change();
            $table->text('contractor_id')->nullable()->change();
            $table->text('client_id')->nullable()->change();
            $table->text('service_name')->nullable()->change();
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
