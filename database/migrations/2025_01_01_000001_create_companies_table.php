<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('slug')->unique(); // used for tenant resolution (subdomain or login code)
            $table->string('logo_path')->nullable();
            $table->enum('status', ['trial', 'active', 'suspended'])->default('trial');
            $table->string('timezone')->default('UTC'); // used to schedule reminders in the company's local time
            $table->json('settings')->nullable(); // e.g. gps_ping_interval_seconds, distance_unit
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};