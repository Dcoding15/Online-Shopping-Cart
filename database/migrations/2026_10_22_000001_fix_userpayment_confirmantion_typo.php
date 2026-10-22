<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fix the typo: rename column "userpayment_confirmantion" to "userpayment_confirmation".
     */
    public function up(): void
    {
        Schema::table('users', function ($table) {
            $table->renameColumn('userpayment_confirmantion', 'userpayment_confirmation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function ($table) {
            $table->renameColumn('userpayment_confirmation', 'userpayment_confirmantion');
        });
    }
};
