<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // High-volume table. No company_id here on purpose — always query via the
        // journeys relationship so we don't duplicate a column onto millions of rows.
        Schema::create('journey_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journey_id')->constrained()->cascadeOnDelete();
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->decimal('speed_kmh', 6, 2)->nullable();
            $table->dateTime('recorded_at');

            $table->index(['journey_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journey_locations');
    }
};