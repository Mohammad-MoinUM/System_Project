<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('referral_code', 20)->nullable()->unique()->after('password');
            $table->foreignId('referred_by_user_id')->nullable()->constrained('users')->nullOnDelete()->after('referral_code');
            $table->json('preferred_time_slots')->nullable()->after('referred_by_user_id');
            $table->string('provider_gender_preference')->nullable()->after('preferred_time_slots');
            $table->unsignedInteger('loyalty_points')->default(0)->after('provider_gender_preference');
            $table->timestamp('referral_reward_claimed_at')->nullable()->after('loyalty_points');
        });

        DB::table('users')->orderBy('id')->chunkById(100, function ($users): void {
            foreach ($users as $user) {
                $code = null;

                do {
                    $code = Str::upper(Str::random(8));
                } while (DB::table('users')->where('referral_code', $code)->exists());

                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['referral_code' => $code]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('referred_by_user_id');
            $table->dropColumn([
                'referral_code',
                'preferred_time_slots',
                'provider_gender_preference',
                'loyalty_points',
                'referral_reward_claimed_at',
            ]);
        });
    }
};