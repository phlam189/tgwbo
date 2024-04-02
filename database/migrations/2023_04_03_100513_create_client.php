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
        Schema::create('client', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->string('company_name', 500)->comment('会社名');
            $table->string('represent_name', 500)->comment('代表者名');
            $table->string('email', 250);
            $table->string('address', 1000);
            $table->tinyInteger('presence')->default(0)->comment('1:presence 0:absence');
            $table->string('license_number', 50);
            $table->integer('total_year')->comment('the month from which to calculate the year total');
            $table->integer('contractor_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client');
    }
};
