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
        Schema::create('introducer_infomation', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->string('company_name', 250);
            $table->string('representative_name', 250);
            $table->string('email', 250);
            $table->string('address', 1000);
            $table->integer('contractor_id');
            $table->tinyInteger('presence');
            $table->tinyInteger('referral_classification')->comment('1: client 2:account');
            $table->string('referral_name', 250);
            $table->decimal('referral_fee', 10, 0);
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
        Schema::dropIfExists('introducer_infomation');
    }
};
