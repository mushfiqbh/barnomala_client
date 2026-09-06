<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            // Stable identifier from the legacy HR/source system. Used by
            // the API sync to upsert the same record instead of inserting
            // duplicates when the human-readable `staff_code` is changed.
            $table->unsignedBigInteger('legacy_id')->nullable()->unique()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropUnique(['legacy_id']);
            $table->dropColumn('legacy_id');
        });
    }
};
