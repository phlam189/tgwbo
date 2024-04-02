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
        Schema::create('log_action_histories', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->string('table_name', 250);
            $table->unsignedBigInteger('user_id');
            $table->string('action', 250);
            $table->integer('row_id');
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
        Schema::dropIfExists('log_action_histories');
    }
};
