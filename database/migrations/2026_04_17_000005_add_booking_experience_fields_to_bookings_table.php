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
            $table->string('booking_mode')->default('scheduled')->after('status');
            $table->string('recurrence_type')->nullable()->after('booking_mode');
            $table->unsignedInteger('recurrence_interval')->default(1)->after('recurrence_type');
            $table->date('recurrence_end_date')->nullable()->after('recurrence_interval');
            $table->json('extra_service_ids')->nullable()->after('recurrence_end_date');
            $table->json('attachments')->nullable()->after('notes');
            $table->string('tracking_status')->default('not_started')->after('attachments');
            $table->decimal('provider_latitude', 10, 7)->nullable()->after('tracking_status');
            $table->decimal('provider_longitude', 10, 7)->nullable()->after('provider_latitude');
            $table->timestamp('tracking_updated_at')->nullable()->after('provider_longitude');
            $table->timestamp('estimated_arrival_at')->nullable()->after('tracking_updated_at');
            $table->string('payment_method')->nullable()->after('estimated_arrival_at');
            $table->string('payment_split_type')->default('full')->after('payment_method');
            $table->decimal('upfront_amount', 10, 2)->default(0)->after('payment_split_type');
            $table->decimal('remaining_amount', 10, 2)->default(0)->after('upfront_amount');
            $table->string('payment_status')->default('unpaid')->after('remaining_amount');
            $table->decimal('cashback_amount', 10, 2)->default(0)->after('payment_status');
            $table->timestamp('paid_at')->nullable()->after('cashback_amount');
            $table->timestamp('cashback_credited_at')->nullable()->after('paid_at');
            $table->string('receipt_number')->nullable()->after('cashback_credited_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'booking_mode',
                'recurrence_type',
                'recurrence_interval',
                'recurrence_end_date',
                'extra_service_ids',
                'attachments',
                'tracking_status',
                'provider_latitude',
                'provider_longitude',
                'tracking_updated_at',
                'estimated_arrival_at',
                'payment_method',
                'payment_split_type',
                'upfront_amount',
                'remaining_amount',
                'payment_status',
                'cashback_amount',
                'paid_at',
                'cashback_credited_at',
                'receipt_number',
            ]);
        });
    }
};