<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journey_location_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journey_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('point_count');
            $table->decimal('min_lat', 10, 7)->nullable();
            $table->decimal('max_lat', 10, 7)->nullable();
            $table->decimal('min_lng', 10, 7)->nullable();
            $table->decimal('max_lng', 10, 7)->nullable();
            $table->decimal('max_speed_kmh', 6, 2)->nullable();
            $table->decimal('avg_speed_kmh', 6, 2)->nullable();
            $table->timestamp('first_recorded_at')->nullable();
            $table->timestamp('last_recorded_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->unique('journey_id'); // one summary per journey — re-running the archival job updates, not duplicates
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journey_location_summaries');
    }
};