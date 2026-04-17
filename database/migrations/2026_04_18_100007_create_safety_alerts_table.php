<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('safety_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->foreignId('triggered_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('user_role', ['customer', 'provider', 'admin']);
            $table->text('message')->nullable();
            $table->enum('status', ['open', 'acknowledged', 'resolved'])->default('open');
            $table->timestamp('triggered_at');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'triggered_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('safety_alerts');
    }
};
