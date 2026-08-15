<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journeys', function (Blueprint $table) {
            // Covers the fleet-summary per-vehicle monthly aggregate exactly —
            // filters by vehicle + status, then range-scans start_time within
            // that, instead of scanning every completed journey for the vehicle.
            $table->index(['vehicle_id', 'status', 'start_time'], 'journeys_vehicle_status_start_idx');
        });

        Schema::table('fuel_entries', function (Blueprint $table) {
            // Same reasoning — fleet summary also sums fuel litres per vehicle
            // within a date range.
            $table->index(['vehicle_id', 'entry_time'], 'fuel_entries_vehicle_entry_idx');
        });
    }

    public function down(): void
    {
        Schema::table('journeys', function (Blueprint $table) {
            $table->dropIndex('journeys_vehicle_status_start_idx');
        });

        Schema::table('fuel_entries', function (Blueprint $table) {
            $table->dropIndex('fuel_entries_vehicle_entry_idx');
        });
    }
};