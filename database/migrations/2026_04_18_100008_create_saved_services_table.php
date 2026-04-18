<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('taker_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['taker_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_services');
    }
};
