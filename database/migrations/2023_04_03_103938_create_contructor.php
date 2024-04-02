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
        Schema::create('contructor', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->string('company_name', 500)->comment('会社名');
            $table->string('manager', 250)->comment('担当者名');
            $table->string('email', 250)->comment('メールアドレス');
            $table->string('address', 1000)->comment('住所');
            $table->string('invoice_prefix', 2)->comment('インボイス接頭文字');
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
        Schema::dropIfExists('contructor');
    }
};
