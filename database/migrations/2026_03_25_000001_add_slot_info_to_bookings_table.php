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
        Schema::table('bookings', function (Blueprint $table) {
            $table->date('booking_date')->nullable()->after('provider_id'); // Date of booking
            $table->time('time_from')->nullable()->after('booking_date');   // Start time of slot
            $table->time('time_to')->nullable()->after('time_from');        // End time of slot
            $table->integer('slot_duration_minutes')->default(60)->after('time_to'); // Slot duration (60, 90, 120 mins)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['booking_date', 'time_from', 'time_to', 'slot_duration_minutes']);
        });
    }
};
