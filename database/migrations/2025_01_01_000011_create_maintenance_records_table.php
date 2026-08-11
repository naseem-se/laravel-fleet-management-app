<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['oil_change', 'service', 'repair', 'other']);
            $table->text('description')->nullable();
            $table->decimal('cost', 10, 2)->default(0);
            $table->decimal('odometer_at_service', 10, 2)->nullable();
            $table->date('service_date');
            $table->date('next_service_date')->nullable();
            $table->decimal('next_service_km', 10, 2)->nullable();
            $table->string('performed_by')->nullable(); // vendor/workshop name
            $table->timestamps();

            $table->index(['company_id', 'vehicle_id']);
            $table->index('next_service_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_records');
    }
};