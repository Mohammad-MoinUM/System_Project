<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('skill_verification_status', ['pending', 'verified', 'rejected'])->default('pending')->after('verification_status');
            $table->enum('background_check_status', ['pending', 'clear', 'flagged'])->default('pending')->after('skill_verification_status');
            $table->string('provider_document_path')->nullable()->after('background_check_status');
            $table->timestamp('skill_verified_at')->nullable()->after('provider_document_path');
            $table->timestamp('background_checked_at')->nullable()->after('skill_verified_at');
            $table->timestamp('nid_verified_at')->nullable()->after('background_checked_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'skill_verification_status',
                'background_check_status',
                'provider_document_path',
                'skill_verified_at',
                'background_checked_at',
                'nid_verified_at',
            ]);
        });
    }
};
