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
            $table->bigInteger('company_type')->default(0)->change();
            $table->renameColumn('presence_or_absence_of_contract', 'presence');
            $table->renameColumn('existence_of_secondment_contract', 'existence');
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
            $table->dropColumn('company_type');
            $table->dropColumn('presence');
            $table->dropColumn('existence');
        });
    }
};
