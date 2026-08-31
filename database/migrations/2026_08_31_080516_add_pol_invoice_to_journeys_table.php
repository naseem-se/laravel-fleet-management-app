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
        Schema::table('journeys', function (Blueprint $table) {
            $table->string('pol_invoice_photo_path')->nullable()->after('pol_drawn');
        });
    }

    public function down(): void
    {
        Schema::table('journeys', fn (Blueprint $table) => $table->dropColumn('pol_invoice_photo_path'));
    }
};
