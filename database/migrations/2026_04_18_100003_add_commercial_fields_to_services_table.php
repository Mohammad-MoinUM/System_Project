<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->boolean('is_insured')->default(false)->after('is_active');
            $table->boolean('guarantee_enabled')->default(false)->after('is_insured');
            $table->decimal('flash_deal_price', 10, 2)->nullable()->after('guarantee_enabled');
            $table->timestamp('flash_deal_ends_at')->nullable()->after('flash_deal_price');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['is_insured', 'guarantee_enabled', 'flash_deal_price', 'flash_deal_ends_at']);
        });
    }
};
