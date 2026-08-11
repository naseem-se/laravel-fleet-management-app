<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journeys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');

            $table->decimal('start_km', 10, 2);
            $table->string('start_photo_path')->nullable();
            $table->decimal('start_lat', 10, 7)->nullable();
            $table->decimal('start_lng', 10, 7)->nullable();
            $table->dateTime('start_time');

            $table->decimal('end_km', 10, 2)->nullable();
            $table->string('end_photo_path')->nullable();
            $table->decimal('end_lat', 10, 7)->nullable();
            $table->decimal('end_lng', 10, 7)->nullable();
            $table->dateTime('end_time')->nullable();

            // stored (not purely generated) so we can set them explicitly in the EndJourney service
            // and index/query them cheaply in reports without recomputing
            $table->decimal('total_distance', 10, 2)->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();

            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['vehicle_id', 'status']);
            $table->index(['driver_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journeys');
    }
};