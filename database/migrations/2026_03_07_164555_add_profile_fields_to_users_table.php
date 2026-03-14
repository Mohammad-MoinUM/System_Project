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
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('phone', 20)->nullable()->after('last_name');
            $table->string('alt_phone', 20)->nullable()->after('phone');
            $table->string('city')->nullable()->after('alt_phone');
            $table->string('area')->nullable()->after('city');
            $table->string('photo')->nullable()->after('area');
            $table->boolean('onboarding_completed')->default(false)->after('photo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name', 'last_name', 'phone', 'alt_phone',
                'city', 'area', 'photo', 'onboarding_completed',
            ]);
        });
    }
};
