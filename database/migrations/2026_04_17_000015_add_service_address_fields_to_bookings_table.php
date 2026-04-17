<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('service_address_label')->nullable()->after('notes');
            $table->string('service_address_line1')->nullable()->after('service_address_label');
            $table->string('service_address_line2')->nullable()->after('service_address_line1');
            $table->string('service_city')->nullable()->after('service_address_line2');
            $table->string('service_area')->nullable()->after('service_city');
            $table->string('service_postal_code')->nullable()->after('service_area');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'service_address_label',
                'service_address_line1',
                'service_address_line2',
                'service_city',
                'service_area',
                'service_postal_code',
            ]);
        });
    }
};