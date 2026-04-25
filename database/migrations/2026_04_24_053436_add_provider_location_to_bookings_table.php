<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'provider_latitude')) {
                $table->decimal('provider_latitude', 10, 8)->nullable();
            }

            if (!Schema::hasColumn('bookings', 'provider_longitude')) {
                $table->decimal('provider_longitude', 11, 8)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'provider_latitude')) {
                $table->dropColumn('provider_latitude');
            }

            if (Schema::hasColumn('bookings', 'provider_longitude')) {
                $table->dropColumn('provider_longitude');
            }
        });
    }
};