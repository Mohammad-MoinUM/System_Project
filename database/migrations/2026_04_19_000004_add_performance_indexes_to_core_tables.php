<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['provider_id', 'status'], 'bookings_provider_status_idx');
            $table->index(['provider_id', 'updated_at'], 'bookings_provider_updated_at_idx');
            $table->index(['provider_id', 'scheduled_at'], 'bookings_provider_scheduled_at_idx');
            $table->index(['provider_id', 'taker_id'], 'bookings_provider_taker_idx');
            $table->index(['taker_id', 'status'], 'bookings_taker_status_idx');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->index(['provider_id', 'updated_at'], 'reviews_provider_updated_at_idx');
            $table->index(['provider_id', 'rating'], 'reviews_provider_rating_idx');
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->index(['user_id', 'type', 'created_at'], 'wallet_transactions_user_type_created_idx');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->index(['provider_id', 'is_active'], 'services_provider_active_idx');
            $table->index(['provider_id', 'category'], 'services_provider_category_idx');
        });

        Schema::table('provider_service_areas', function (Blueprint $table) {
            $table->index(['user_id', 'is_active'], 'provider_service_areas_user_active_idx');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_provider_status_idx');
            $table->dropIndex('bookings_provider_updated_at_idx');
            $table->dropIndex('bookings_provider_scheduled_at_idx');
            $table->dropIndex('bookings_provider_taker_idx');
            $table->dropIndex('bookings_taker_status_idx');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex('reviews_provider_updated_at_idx');
            $table->dropIndex('reviews_provider_rating_idx');
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropIndex('wallet_transactions_user_type_created_idx');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex('services_provider_active_idx');
            $table->dropIndex('services_provider_category_idx');
        });

        Schema::table('provider_service_areas', function (Blueprint $table) {
            $table->dropIndex('provider_service_areas_user_active_idx');
        });
    }
};