<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('mobile_api_token_hash', 64)->nullable()->after('remember_token');
            $table->timestamp('mobile_api_token_created_at')->nullable()->after('mobile_api_token_hash');
            $table->index('mobile_api_token_hash', 'users_mobile_api_token_hash_idx');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex('users_mobile_api_token_hash_idx');
            $table->dropColumn(['mobile_api_token_hash', 'mobile_api_token_created_at']);
        });
    }
};