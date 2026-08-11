<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuel_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('journey_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('driver_id')->constrained()->cascadeOnDelete();

            $table->decimal('quantity_litres', 8, 2);
            $table->decimal('rate_per_litre', 8, 2);
            $table->decimal('total_cost', 10, 2); // set in FuelService, kept as a real column for fast report SUMs
            $table->decimal('odometer_reading', 10, 2);
            $table->string('receipt_photo_path')->nullable();
            $table->dateTime('entry_time');

            $table->timestamps();

            $table->index(['company_id', 'vehicle_id', 'entry_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_entries');
    }
};