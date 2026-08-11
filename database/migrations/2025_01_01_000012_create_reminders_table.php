<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->enum('reminder_type', ['maintenance', 'document', 'license']);
            $table->morphs('reference'); // reference_type + reference_id -> MaintenanceRecord, VehicleDocument, or Driver
            $table->date('due_date')->nullable();
            $table->decimal('due_km', 10, 2)->nullable();
            $table->enum('status', ['pending', 'sent', 'dismissed'])->default('pending');
            $table->timestamps();

            $table->index(['company_id', 'status', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};