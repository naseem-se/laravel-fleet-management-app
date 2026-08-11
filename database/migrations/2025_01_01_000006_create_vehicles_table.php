<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->string('registration_number');
            $table->uuid('qr_code_value')->unique(); // random token, NOT the plate number — see design notes
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('vehicle_type')->nullable(); // truck, van, sedan, bike, etc.
            $table->string('engine_number')->nullable();
            $table->string('chassis_number')->nullable();
            $table->string('fuel_type')->nullable(); // petrol, diesel, cng, electric
            $table->decimal('tank_capacity_litres', 8, 2)->nullable();
            $table->decimal('current_odometer', 10, 2)->default(0);
            $table->decimal('avg_kmpl_cached', 8, 2)->nullable(); // refreshed nightly, see RecalculateAvgKmpl job
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');

            // fast "where is it now" reads without scanning journey_locations
            $table->decimal('last_lat', 10, 7)->nullable();
            $table->decimal('last_lng', 10, 7)->nullable();
            $table->timestamp('last_location_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'registration_number']);
            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};