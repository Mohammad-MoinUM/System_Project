<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update all pending companies to approved
        DB::table('companies')
            ->where('status', 'pending')
            ->update([
                'status' => 'approved',
                'approved_at' => now()
            ]);
    }

    public function down(): void
    {
        // Revert if needed
        DB::table('companies')
            ->where('status', 'approved')
            ->update([
                'status' => 'pending',
                'approved_at' => null
            ]);
    }
};
