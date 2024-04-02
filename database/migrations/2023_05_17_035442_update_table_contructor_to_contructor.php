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
            $table->tinyInteger('company_type')->nullable();
            $table->string('representative_name', 30)->nullable();
            $table->dateTime('date_of_birth')->nullable();
            $table->dateTime('contract_date')->nullable();
            $table->boolean('presence_or_absence_of_contract')->nullable();
            $table->boolean('existence_of_secondment_contract')->nullable();
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
            $table->dropColumn('company_type')->nullable();
            $table->dropColumn('representative_name')->nullable();
            $table->dropColumn('date_of_birth')->nullable();
            $table->dropColumn('contract_date')->nullable();
            $table->dropColumn('presence_or_absence_of_contract')->nullable();
            $table->dropColumn('existence_of_secondment_contract')->nullable();
        });
    }
};
