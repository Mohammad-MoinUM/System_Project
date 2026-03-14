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
            $table->string('education')->nullable()->after('onboarding_completed');
            $table->string('institution')->nullable()->after('education');
            $table->text('expertise')->nullable()->after('institution');
            $table->text('bio')->nullable()->after('expertise');
            $table->unsignedTinyInteger('experience_years')->nullable()->after('bio');
            $table->json('services_offered')->nullable()->after('experience_years');
            $table->json('certifications')->nullable()->after('services_offered');
            $table->string('nid_number', 30)->nullable()->after('certifications');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'education', 'institution', 'expertise', 'bio',
                'experience_years', 'services_offered', 'certifications', 'nid_number',
            ]);
        });
    }
};
