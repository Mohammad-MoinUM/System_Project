<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('promo_code')->nullable()->after('payment_split_type');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('promo_code');
            $table->decimal('original_total', 10, 2)->nullable()->after('discount_amount');
            $table->boolean('emergency_cancel_flag')->default(false)->after('cancellation_reason');
            $table->decimal('cancellation_fee', 10, 2)->default(0)->after('emergency_cancel_flag');
            $table->text('cancellation_policy_note')->nullable()->after('cancellation_fee');
            $table->boolean('sos_triggered')->default(false)->after('cancellation_policy_note');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'promo_code',
                'discount_amount',
                'original_total',
                'emergency_cancel_flag',
                'cancellation_fee',
                'cancellation_policy_note',
                'sos_triggered',
            ]);
        });
    }
};
