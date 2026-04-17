<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->unsignedTinyInteger('punctuality_rating')->nullable()->after('rating');
            $table->unsignedTinyInteger('quality_rating')->nullable()->after('punctuality_rating');
            $table->unsignedTinyInteger('behavior_rating')->nullable()->after('quality_rating');
            $table->unsignedTinyInteger('value_rating')->nullable()->after('behavior_rating');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn([
                'punctuality_rating',
                'quality_rating',
                'behavior_rating',
                'value_rating',
            ]);
        });
    }
};
