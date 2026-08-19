<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journey_locations', function (Blueprint $table) {
            $table->decimal('accuracy_meters', 8, 2)->nullable()->after('speed_kmh');
        });
        Schema::table('vehicles', function (Blueprint $table) {
            $table->decimal('last_accuracy_meters', 8, 2)->nullable()->after('last_location_at');
        });
    }

    public function down(): void
    {
        Schema::table('journey_locations', fn (Blueprint $t) => $t->dropColumn('accuracy_meters'));
        Schema::table('vehicles', fn (Blueprint $t) => $t->dropColumn('last_accuracy_meters'));
    }
};