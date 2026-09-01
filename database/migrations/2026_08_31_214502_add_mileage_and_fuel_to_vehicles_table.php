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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->decimal('mileage_rated', 8, 2)->nullable()->after('avg_kmpl_cached'); // manufacturer/expected KMPL, for comparison against actual
            $table->decimal('current_fuel_litres', 8, 2)->default(0)->after('current_odometer'); // running fuel-in-tank estimate, increased by each logged fuel entry
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['mileage_rated', 'current_fuel_litres']);
        });
    }
};
