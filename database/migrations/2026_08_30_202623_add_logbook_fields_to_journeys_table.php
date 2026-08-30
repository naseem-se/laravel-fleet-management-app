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
            // All nullable — every one of these is optional and has no
            // effect on the existing start/end/ping flow if left blank.
            $table->text('detail_of_journey')->nullable()->after('purpose');
            $table->string('officer_name')->nullable()->after('detail_of_journey');
            $table->string('signature')->nullable()->after('officer_name');
            $table->decimal('pol_drawn', 8, 2)->nullable()->after('signature'); // Petrol/Oil/Lubricant drawn — litres
            $table->text('remarks')->nullable()->after('pol_drawn');
        });
    }

    public function down(): void
    {
        Schema::table('journeys', function (Blueprint $table) {
            $table->dropColumn(['detail_of_journey', 'officer_name', 'signature', 'pol_drawn', 'remarks']);
        });
    }
};
