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
            // Add columns for B2B corporate bookings
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('set null')->after('provider_id');
            $table->foreignId('branch_id')->nullable()->constrained('company_branches')->onDelete('set null')->after('company_id');
            $table->foreignId('requested_by')->nullable()->constrained('users')->onDelete('set null')->after('branch_id');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null')->after('requested_by');
            $table->boolean('is_corporate')->default(false)->after('approved_by');
            $table->timestamp('approved_at')->nullable()->after('is_corporate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeignIdFor('companies');
            $table->dropForeignIdFor('company_branches');
            $table->dropColumn(['company_id', 'branch_id', 'requested_by', 'approved_by', 'is_corporate', 'approved_at']);
        });
    }
};
