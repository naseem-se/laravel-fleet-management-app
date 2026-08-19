<?php
// database/migrations/2025_01_06_000001_add_profile_photo_to_drivers_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->string('profile_photo_path')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('drivers', fn (Blueprint $table) => $table->dropColumn('profile_photo_path'));
    }
};