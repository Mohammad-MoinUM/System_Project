<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->string('escrow_status')->default('not_required')->after('payment_status');
            $table->timestamp('provider_completed_at')->nullable()->after('escrow_status');
            $table->timestamp('customer_confirmed_at')->nullable()->after('provider_completed_at');
            $table->timestamp('escrow_released_at')->nullable()->after('customer_confirmed_at');
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->timestamp('released_at')->nullable()->after('captured_at');
            $table->foreignId('released_by_user_id')->nullable()->constrained('users')->nullOnDelete()->after('released_at');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('released_by_user_id');
            $table->dropColumn('released_at');
        });

        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropColumn([
                'escrow_status',
                'provider_completed_at',
                'customer_confirmed_at',
                'escrow_released_at',
            ]);
        });
    }
};